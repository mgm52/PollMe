<?php
// Insert error message + highlighting into page
function showErrors($error_msgs) {
  if (isset($_GET['form_error'])) {
    echo "<p class='errormsg'>{$error_msgs[$_GET['form_error']]}</p><br>";
  }
  if (isset($_GET['field_name'])) {
    $field_name = preg_replace('/[^A-z0-9-_ \.:]/', '', $_GET['field_name']);
    echo "<style type='text/css'>
    main input[name='$field_name'] {
      border-style: solid;
      border-color: #ff4444;
      border-width: 2px;
    }
    </style>";
  }
}

// Insert success message
function showSuccess($success_message) {
  if (isset($_GET['success'])) {
    echo "<p class='successmsg'>$success_message</p><br>";
  }
}
?>
