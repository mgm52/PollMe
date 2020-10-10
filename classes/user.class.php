<?php
require_once 'poll.class.php';

class User
{
  protected $polls;
  protected $user_id;

  protected function __construct() {
    $this->checked_owner = false;
  }

  public static function fromUserID($dbh, $user_id) {
    $instance = new self();
    $instance->loadByUserID($dbh, $user_id);
    return $instance;
  }

  //Load basic data
  protected function loadByUserID($dbh, $user_id) {
    //Could achieve moderate speed increase by maintaining a vote count column (0.002s vs 0.003s in tests)
    $this->user_id = $user_id;
  }

  public function getPollsWithCounts($dbh) {
    $polls = $dbh->selectAll('
      SELECT p.poll_id, p.poll_id_secret, p.question, user_id, count(r.poll_id) AS response_count
      FROM polls AS p
      JOIN poll_owners_users AS pou
        ON p.poll_id = pou.poll_id
        AND pou.user_id = ?
      LEFT JOIN (
        SELECT v.response_id, pr.poll_id
        FROM polls_responses AS pr
        JOIN votes AS v
          ON v.response_id = pr.response_id
      ) AS r
        ON r.poll_id = p.poll_id
      GROUP BY p.poll_id',
      [$this->user_id]
    );

    $polls_objs = array_map(function ($p) use ($dbh) {return Poll::fromRow($dbh, $p);}, $polls);
    return $polls_objs;
  }
}
?>
