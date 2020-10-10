<?php
require_once 'includes/header.inc.php';
require_once 'includes/form_messages.inc.php';
?>

<main>
  <h1>Poll creation</h1>
  <form action='actions/pollcreate.act.php' method='post'>
    <input type='text' name='question' placeholder='Question'>

    <br>

    <div class='response_inputs'></div>
    <script>
      var inputs = document.getElementsByClassName('response_inputs')[0];
      var responses = 0;

      // One response input field to begin with
      addInput();

      // Add new response input fields as they're filled in
      function responseChanged() {
        if (inputs.childNodes[inputs.childNodes.length - 1].value) {
          addInput();
        }
        else while(inputs.childNodes.length > 2 && !inputs.childNodes[inputs.childNodes.length - 2].value) {
          removeInput();
        }
      }

      function removeInput() {
        responses--;
        inputs.removeChild(inputs.childNodes[inputs.childNodes.length - 1]);
      }

      function addInput() {
        responses++;
        var x = document.createElement('INPUT');
        x.setAttribute('onkeyup', 'responseChanged()');
        x.setAttribute('type', 'text');
        x.setAttribute('name', `response${responses}`);
        x.setAttribute('placeholder', 'New response...');
        inputs.appendChild(x);
      }
    </script>

    <br>

    <?php
    showErrors(array(
      'no_responses' => 'Please enter at least one response',
      'no_question' => 'Please enter a question'
    ));
    ?>

    <div class='strict-checkbox'>
      <input type='checkbox' id='strict-voting' name='strict-voting'>
      <label for='strict-voting'>Strict IP-based duplicate vote prevention</label>
    </div>

    <button type='submit' class='create-button' name='pollcreate-submit'>Create poll</button>

  </form>
</main>

<?php
require_once 'includes/footer.inc.php';
?>
