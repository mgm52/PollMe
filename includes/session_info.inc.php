<?php
function getIP() {
  $http_headers = apache_request_headers();
  //Rightmost ip address is most reliable https://en.wikipedia.org/wiki/X-Forwarded-For#Format
  $original_ip_list = explode(',', $http_headers['X-Forwarded-For']);
  $original_ip = end($original_ip_list);

  return $original_ip;
}

function getSessionID() {
  startSession();
  return session_id();
}

function getSessionUserID() {
  startSession();
  return $_SESSION['userId'];
}

function getSessionDisplayName() {
  startSession();
  return $_SESSION['displayName'];
}

function setSessionInfo($user_id, $display_name) {
  startSession();
  $_SESSION['userId'] = $user_id;
  $_SESSION['displayName'] = $display_name;
}

function isLoggedIn() {
  startSession();
  return isset($_SESSION['userId']);
}

function startSession() {
  if (session_status() == PHP_SESSION_NONE) session_start();
}
?>
