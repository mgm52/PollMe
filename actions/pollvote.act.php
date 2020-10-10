<?php
function signupError($form_error, $field_name, $email) {
  header("Location: ../signup.php?form_error=$form_error&field_name=$field_name&email=$email");
  exit();
}

$post_keys = array_keys($_POST);

// First key is the name of the submit button used, should be in format 'pollvote-submit-{$response_id}'
if (count($post_keys) == 1 && strpos($post_keys[0], 'pollvote-submit') === 0)
{
  require_once '../classes/dbh.class.php';
  require_once '../includes/session_info.inc.php';
  $dbh = new Dbh();

  // Identify which response the user voted for via button name
  preg_match('/[0-9]+$/', $post_keys[0], $matches);
  $response_id = $matches[0];

  $ip = getIP();
  $session_id = getSessionID();

  $dbh->execute('
    INSERT INTO votes (ip, session_id, response_id)
    SELECT ?, ?, response_id
    FROM polls_responses
    WHERE response_id = ?
    ON DUPLICATE KEY UPDATE ip=ip',
    [$ip, $session_id, $response_id]
  );

  header("Location: {$_SERVER['HTTP_REFERER']}");
}
else
{
  //User formed an invalid request somehow
  header('Location: ../error.php');
}
?>
