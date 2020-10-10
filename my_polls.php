<?php
require_once 'includes/header.inc.php';
require_once 'classes/dbh.class.php';
require_once 'classes/user.class.php';
?>

<main>
  <h1>My Polls</h1>
  <div class='poll_list'>
    <?php
    if (!isLoggedIn()) {
      header('Location: index.php');
      exit();
    }
    else {
      $dbh = new Dbh();
      $user_obj = User::fromUserID($dbh, getSessionUserID());
      $polls_objs = $user_obj->getPollsWithCounts($dbh);

      if (!empty($polls_objs)) {
        echo '<table>'
              . join('', array_map(function ($p) use ($dbh) {
                  $count = $p->getResponseCount($dbh);
                  return "
                    <tr>
                      <td><a href='poll.php?id={$p->getSecret()}'>{$p->getQuestion()}</a></td>"
                        . ($count == 0 ? '<td class="response-count zero-responses">' : '<td class="response-count">')
                        . $count
                        . ($count > 1 ? ' responses' : ' response') . '
                      </td>
                    </tr>
                  ';}, $polls_objs))
              . '</table>';
      }
      else {
        echo '<div class="no-polls"> None yet! </div>';
      }
    }
    ?>
  </div>
</main>

<?php
require_once 'includes/footer.inc.php';
?>
