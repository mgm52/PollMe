<?php
if (isset($_POST['login-submit']))
{
  require_once '../classes/dbh.class.php';
  require_once '../includes/session_info.inc.php';
  $dbh = new Dbh();

  $email = $_POST['email'];
  $pword = $_POST['pword'];

  $hashed_pword_id = $dbh->selectOne('
    SELECT pword, user_id
    FROM users
    WHERE email = ?',
    [$email]
  );

  // Try logging in. Otherwise, return to referer page and give error effect.
  if (isset($hashed_pword_id['pword']) && password_verify($pword, $hashed_pword_id['pword'])) {
    setSessionInfo($hashed_pword_id['user_id'], strtok($email, '@'));
    header('Location: ../index.php?login_success');
    exit();
  }
  else {
    $base_url = $_SERVER['HTTP_REFERER'];
    if (strpos($base_url, 'login_fail') == false) {
      if (strpos($base_url, '?') !== false) {
        header("Location: $base_url&login_fail");
      }
      else {
        header("Location: $base_url?login_fail");
      }
    }
    else {
      header("Location: $base_url");
    }
    exit();
  }
}
else
{
  //User formed an invalid request somehow
  header('Location: ../error.php');
}
?>
