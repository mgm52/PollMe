<?php
require_once 'includes/header.inc.php';
require_once 'includes/form_messages.inc.php';
?>

<main>
  <h1>Sign up</h1>
  <form action='actions/signup.act.php' method='post'>
    <input type='text' name='email' placeholder='Email' value='<?php
      if (isset($_GET['email'])) {
        echo filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
      }
    ?>'>

    <br>

    <input type='password' name='pword1' placeholder='Password'>
    <input type='password' name='pword2' placeholder='Repeat password'>

    <br>

    <?php
    showErrors(array(
      'invalid_email' => 'Please enter a valid email address',
      'duplicate_email' => 'Email already exists',
      'pword_unequal' => 'Passwords must be the same',
      'pword_length' => 'Password must be at least 8 characters long',
      'pword_no_letter' => 'Password must include at least one letter',
      'pword_contained' => 'Password must not be contained in email'
    ));
    showSuccess('Account created successfully.');
    ?>

    <button type='submit' name='signup-submit'>Sign up</button>
  </form>
</main>

<?php
require_once 'includes/footer.inc.php';
?>
