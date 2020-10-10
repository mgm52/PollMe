<?php
class Poll
{
  protected $id;
  protected $secret;
  protected $question;

  //If owner doesnt have a user_id, then user_id = null after checked_owner = true
  protected $user_id;
  protected $checked_owner;

  protected $owner_ip;
  protected $owner_session_id;

  // Boolean
  protected $strict_voting;

  // Response count could change, functions are provided to re-fetch it
  protected $response_count;

  protected function __construct() {
    $this->checked_owner = false;
  }

  public static function fromID($dbh, $id) {
    $instance = new self();
    $instance->loadByID($dbh, $id);
    return $instance;
  }

  public static function fromSecret($dbh, $secret) {
    $instance = new self();
    $instance->loadBySecret($dbh, $secret);
    return $instance;
  }

  public static function fromRow($dbh, $row) {
    $instance = new self();
    $instance->loadByRow($dbh, $row);
    return $instance;
  }

  //Load basic data
  protected function loadByID($dbh, $id) {
    $poll_info = $dbh->selectOne('
      SELECT *
      FROM polls
      WHERE poll_id = ?',
      [$id]
    );
    $this->loadByRow($dbh, $poll_info);
  }

  //Load basic data
  protected function loadBySecret($dbh, $secret) {
    $poll_info = $dbh->selectOne('
      SELECT *
      FROM polls
      WHERE poll_id_secret = ?',
      [$secret]
    );
    $this->loadByRow($dbh, $poll_info);
  }

  //Load any data given
  protected function loadByRow($dbh, $row) {
    $property_names = array(
      'poll_id' => &$this->id,
      'poll_id_secret' => &$this->secret,
      'question' => &$this->question,
      'user_id' => &$this->user_id,
      'owner_ip' => &$this->owner_ip,
      'owner_session_id' => &$this->owner_session_id,
      'response_count' => &$this->response_count,
      'strict_voting' => &$this->strict_voting
    );

    foreach($row as $key => $value) {
      if (array_key_exists($key, $property_names)) {
        $property_names[$key] = $value;
      }
    }

    if (!empty($this->user_id)) $this->checked_owner = true;
  }

  //Basic info assigned when loaded
  public function getID() {
    return $this->id;
  }
  public function getQuestion() {
    return $this->question;
  }
  public function getSecret() {
    return $this->secret;
  }
  public function getOwnerIP() {
    return $this->owner_ip;
  }
  public function getOwnerSessionID() {
    return $this->owner_session_id;
  }
  public function isVotingStrict() {
    return $this->strict_voting;
  }

  // Poll ownership is a function of user_id ? (user_id) : (ip, session_id)
  // Poll votership is a function of strict_voting ? (ip) : (ip, session_id)

  // Using (ip, session_id) and not just (session_id) to avoid colliding duplicate session_ids

  public function isUserOwner($dbh, $user_id) {
    return $this->hasUserOwner($dbh) && $user_id == $this->getOwnerID($dbh);
  }

  public function hasUserOwner($dbh) {
    return null !== $this->getOwnerID($dbh);
  }

  public function getOwnerID($dbh) {
    if ($this->checked_owner) return $this->user_id;

    $user_id = $dbh->selectOne('
      SELECT user_id
      FROM poll_owners_users
      WHERE poll_id = ?',
      [$this->id]
    );

    $this->user_id = $user_id ? $user_id[0] : null;
    $this->checked_owner = true;

    return $this->user_id;
  }

  public function isSessionOwner($dbh, $ip, $session_id) {
    return $this->getOwnerIP() == $ip && $this->getOwnerSessionID() == $session_id;
  }

  public function hasVoted($dbh, $ip, $session_id) {
    $already_voted = $dbh->selectOne('
      SELECT *
      FROM polls as p
      JOIN polls_responses AS pr
        ON p.poll_id = ?
        AND p.poll_id = pr.poll_id
      JOIN votes AS v
        ON v.response_id = pr.response_id
        AND v.ip = ?
        AND (v.session_id = ? OR strict_voting = 1)',
      [$this->id, $ip, $session_id]
    );

    return !empty($already_voted);
  }

  public function getResponseCount($dbh) {
    if (isset($this->response_count)) return $this->response_count;

    return updateResponseCount($dbh);
  }

  //Response count value could change over time
  public function updateResponseCount($dbh) {
    $response_count = $dbh->selectOne('
      SELECT count(*) AS response_count
      FROM polls_responses AS pr
      JOIN votes AS v
        ON v.response_id = pr.response_id
      WHERE pr.poll_id = ?',
      [$this->id]
    );

    $this->loadByRow($dbh, $response_count);

    return $this->response_count;
  }

  //Response data formatted for canvasjs. Splitting this into multiple queries would create overhead.
  public function getCanvasJsResults($dbh) {
    $responses = $dbh->selectAll('
      SELECT response AS label, pr.response_id, count(v.response_id) AS y
      FROM polls_responses AS pr
      LEFT JOIN votes AS v
      ON v.response_id = pr.response_id
      WHERE pr.poll_id = ?
      GROUP BY pr.response_id',
      [$this->id]
    );

    return $responses;
  }

  public static function createNew($dbh, $question, $responses, $ip, $session_id, $strict_voting) {
    $dbh->execute('
      INSERT INTO polls (question, owner_ip, owner_session_id, strict_voting)
      VALUES (?, ?, ?, ?)',
      [$question, $ip, $session_id, $strict_voting]
    );

    $poll_id = $dbh->selectOne('
      SELECT LAST_INSERT_ID();',
      []
    )[0];

    $poll_obj = Poll::fromID($dbh, $poll_id);
    $poll_secret = $poll_obj->generateNewSecret($dbh);

    $values_placeholder = join(', ', array_fill(0, count($responses), "($poll_id, ?)"));

    $dbh->execute("
      INSERT INTO polls_responses (poll_id, response)
      VALUES $values_placeholder",
      $responses
    );

    return $poll_obj;
  }

  public static function createNewWithUser($dbh, $question, $responses, $ip, $session_id, $strict_voting, $user_id) {
    $poll_obj = Poll::createNew($dbh, $question, $responses, $ip, $session_id, $strict_voting);

    $dbh->execute('
      INSERT INTO poll_owners_users (user_id, poll_id)
      VALUES (?, ?)',
      [$user_id, $poll_obj->getID()]
    );

    return $poll_obj;
  }

  protected function generateNewSecret($dbh) {
    // The maximum ID of any word.
    $max_word = $dbh->selectOne('
      SELECT COUNT(*) FROM words',
      []
    )[0];

    // The second word ID is chosen at an offset from the first word ID. This offset increases as poll_id/max_word increases.
    $id_1 = ($this->id % $max_word) + 1;
    $id_2 = (($id_1 + ((int) ($this->id / $max_word)) + 500) % $max_word) + 1;

    $word_pair = $dbh->selectOne('
      SELECT w1.word, w2.word
      FROM words AS w1
      JOIN words AS w2
      ON w1.word_id = ?
      AND w2.word_id = ?',
      [$id_1, $id_2]
    );

    $secret = "{$word_pair[0]}-{$word_pair[1]}";

    $duplicate_secret = $dbh->selectOne('
      SELECT poll_id_secret
      FROM polls
      WHERE poll_id_secret = ?',
      [$secret]
    );

    if ($duplicate_secret) {
      $secret .= $this->id;
    }

    //Update secret in db
    $dbh->execute('
      UPDATE polls
      SET poll_id_secret = ?
      WHERE poll_id = ?',
      [$secret, $this->id]
    );

    $this->secret = $secret;

    return $secret;
  }
}
?>
