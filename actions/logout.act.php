<?php
// Log out by unsetting session variables
if (isset($_POST['logout-submit']))
{
  require_once '../includes/session_info.inc.php';
  setSessionInfo(NULL, NULL);
}
header('Location: ../index.php');
?>
