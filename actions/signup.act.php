<?php
if (isset($_POST['signup-submit']))
{
  require_once '../classes/dbh.class.php';
  $dbh = new Dbh();

  // Send back to signup page with errors
  function signupError($form_error, $field_name, $email) {
    $email = urlencode($email);
    $field_name = urlencode($field_name);
    $form_error = urlencode($form_error);
    header("Location: ../signup.php?form_error=$form_error&field_name=$field_name&email=$email");
    exit();
  }

  $email = $_POST['email'];
  $pword1 = $_POST['pword1'];
  $pword2 = $_POST['pword2'];

  if ($pword1 !== $pword2) {
    signupError('pword_unequal', 'pword2', $email);
  }

  if (strlen($pword1) < 8) {
    signupError('pword_length', 'pword1', $email);
  }

  if (!preg_match('/[A-z]+/', $pword1)) {
    signupError('pword_no_letter', 'pword1', $email);
  }

  if (preg_match('/$pword1/', $email)) {
    signupError('pword_contained', 'pword1', $email);
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    signupError('invalid_email', 'email', $email);
  }

  $duplicate_email = $dbh->selectOne('
    SELECT 1
    FROM users
    WHERE email = ?',
    [$email]
  );
  if ($duplicate_email) {
    signupError('duplicate_email', 'email', $email);
  }

  $dbh->execute('
    INSERT INTO users (email, pword)
    VALUES (?, ?)',
    [$email, password_hash($pword1, PASSWORD_DEFAULT)]
  );

  header('Location: ../signup.php?success=1');
}
else
{
  //User formed an invalid request somehow
  header('Location: ../error.php');
}
?>
