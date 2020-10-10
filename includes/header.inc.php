<?php
require_once 'session_info.inc.php';
?>

<!doctype html>
<title>PollMe</title>
<meta name='description' content='Create polls to send to your friends. Quick, easy, adjective.'>
<meta name='viewport' content='width=device-width'>
<meta charset='utf-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
<link rel='icon' href='../poll_icon_58.ico' type='image/ico' >
<link rel='shortcut icon' href='../poll_icon_58.ico' type='image/ico' >
<link rel='stylesheet' href='styles.css' type='text/css'>

<?php
if (isset($_GET['login_fail'])) { echo '
  <style type="text/css">
    .login-button {
      animation: shake 0.7s;
      border: 1px solid #ff3333;
      box-sizing: border-box;
      padding: 5px 9px;
    }
  </style>';
}
?>

<nav class='header-nav'>
  <a href='index.php' class='create-link'><img src='poll_icon_58.ico' alt='logo'><div class='create-link-text'>CREATE</div></a>
  <?php
  if (isLoggedIn()) {
    $display_name = getSessionDisplayName();
    echo '
      <a href = "my_polls.php">MY POLLS</a>
    ';
  }
  ?>
  <a href='about.php'>ABOUT</a>

  <div class='header-login'>
    <?php
    if (!isLoggedIn()) { echo '
      <form action="/actions/login.act.php" method="post">
      <input type="text" name="email" placeholder="Email">
      <input type="password" name="pword" placeholder="Password">
      <button type="submit" class="login-button" name="login-submit">Login</button>
      </form>
      <a href = "signup.php">Sign up</a>
    ';}
    else { echo "
      <div class='display-name'> Logged in - $display_name </div>
      <form action='actions/logout.act.php' method='post'>
      <button type='submit' class='logout-submit' name='logout-submit'>Log out</button>
      </form>
    ";}
    ?>
  </div>
</nav>
