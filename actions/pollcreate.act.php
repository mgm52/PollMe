<?php
if (isset($_POST['pollcreate-submit']))
{
  require_once '../classes/poll.class.php';
  require_once '../includes/session_info.inc.php';
  require_once '../classes/dbh.class.php';
  $dbh = new Dbh();

  // Send back to poll create page with errors
  function pollcreateError($form_error, $field_name) {
    $field_name = urlencode($field_name);
    $form_error = urlencode($form_error);
    header("Location: ../index.php?form_error=$form_error&field_name=$field_name");
    exit();
  }

  $question = $_POST['question'];
  if (empty($question)) pollcreateError('no_question', 'question');

  $responses = array_values(array_filter($_POST, function ($v, $k) {return (!empty($v)) && strpos($k, 'response') === 0;}, ARRAY_FILTER_USE_BOTH));
  if (empty($responses)) pollcreateError('no_responses', 'response1');

  $strict_voting = array_key_exists('strict-voting', $_POST);

  if (isLoggedIn()) {
    $poll_obj = Poll::createNewWithUser($dbh, $question, $responses, getIP(), getSessionID(), $strict_voting, getSessionUserID());
  }
  else {
    $poll_obj = Poll::createNew($dbh, $question, $responses, getIP(), getSessionID(), $strict_voting);
  }

  header("Location: ../poll.php?id={$poll_obj->getSecret($dbh)}");
}
else
{
  //User formed an invalid request somehow
  header('Location: ../error.php');
}
?>
)
