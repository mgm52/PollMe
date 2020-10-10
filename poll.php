<?php
require_once 'includes/header.inc.php';
require_once 'classes/dbh.class.php';
require_once 'classes/poll.class.php';
require_once 'includes/session_info.inc.php';
?>

<main>
  <?php
  $dbh = new Dbh();

  $poll_obj = Poll::fromSecret($dbh, $_GET['id']);

  $poll_id = $poll_obj->getID();
  $question = $poll_obj->getQuestion();
  $responses = $poll_obj->getCanvasJsResults($dbh);

  $already_voted = $poll_obj->hasVoted($dbh, getIP(), getSessionID());
  $owns_poll = isLoggedIn() && $poll_obj->isUserOwner($dbh, getSessionUserID())
               || $poll_obj->isSessionOwner($dbh, getIP(), getSessionID());

  echo "<h1>{$question}</h1><br>";

  if ($owns_poll) {
    echo 'You created this poll - send this URL to anyone else to allow them to vote.
          <br>';
  }
  else if ($already_voted) {
    echo 'You have already voted in this poll.
          <br>';
  }

  // Display results if already voted or owner of poll
  if ($owns_poll || $already_voted) {
    $has_results = false;
    foreach($responses as $r) {
      if ($r['y'] > 0) {
        $has_results = true;
        break;
      }
    }

    if ($has_results) {
      $response_counts = array_map(function ($r) {return $r['y'];}, $responses);
      $winning_responses = array_keys($response_counts, max($response_counts));

      foreach($winning_responses as $i) {
        $responses[$i] = array_merge($responses[$i], array('exploded' => true));
      }

      echo 'Here are the results:
            <br>';
    }
    else {
      echo 'Nobody has voted on this poll yet:
            <br>';
    }

    echo '<table class="response-results">'
          .  join('', array_map(function ($r) {return "
               <tr><td class=result-label>{$r['label']}</td><td>{$r['y']}</td></tr>
             ";}, $responses))
          . '</table>';

    if ($has_results) {
      ?>
      <script>
        window.onload = function () {
          var chart = new CanvasJS.Chart('chartContainer', {
            animationEnabled: true,
            theme: 'light2',
            explodeOnClick: false,
            data: [{
              type: 'pie',
              indexLabelFontSize: 16,
              indexLabel: '{label} - #percent%',
              yValueFormatString: '#',
              dataPoints: <?php echo json_encode($responses, JSON_NUMERIC_CHECK); ?>
            }]
          });
          chart.render();
        }
      </script>
      <div id='chartContainer' style='height: 370px; width: 100%;'></div>
      <script src='canvasjs.min.js'></script>
      <?php
    }
  }
  else {
    // Offering poll response buttons
    echo '<form class="response-buttons" action="actions/pollvote.act.php" method="post">'
          . join('<br>', array_map(function ($r) {return "
              <button type='submit' name='pollvote-submit-{$r['response_id']}'>{$r['label']}</button>
            ";}, $responses))
          . '</form>';
  }
  ?>
</main>

<?php
require_once 'includes/footer.inc.php';
?>
