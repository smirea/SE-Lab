<?php
  require_once 'config.php';
  require_once 'utils.php';

  require_once 'People_Model.php';
  require_once 'Groups_Model.php';
  require_once 'Evaluations_Model.php';
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" type="text/css" href="css/css-reset.css"/>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="jquery-ui/jquery-ui.css"/>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <link rel="stylesheet" type="text/css" href="css/admin.css"/>
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="jquery-ui/jquery-ui.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    <script type="text/javascript">
      <?php
        echo "
          var phase = '$phase';
          var phases = '" . implode(',', $phases) . "'.split(',');
        ";
      ?>
    </script>
    <script src="js/shared.js" type="text/javascript"></script>
    <script src="js/admin.js" type="text/javascript"></script>
  </head>
  <body>

<?php require_once 'login.php'; ?>

<?php
  if (!is_admin()) {
    echo '<div class="alert alert-error">You are not an admin, so no :)</div>';
    exit();
  }
?>

  <?php
    $PM = new People_Model();
    $GM = new Groups_Model($PM);
    $EM = new Evaluations_Model();

    if (isset($_GET['phase'])) {
      $phase = $_GET['phase'];
    }

    $phase_select = array();
    foreach ($phases as $phase_name) {
      $selected = $phase_name === $phase ? 'selected' : '';
      $phase_select[] = '<option '.$selected.'>' . $phase_name .'</option>';
    }
    $phase_select = '<select name="phase" class="phase-select">' . implode('', $phase_select) . '</select>';
  ?>

  <div id="phase-change" class="content-block">
    View phase:
    <?php
      foreach ($phases as $phase_name) {
        $current = $phase_name === $phase ? 'style="color:#000;"' : '';
        echo '<a href="?phase='.$phase_name.'" ' . $current . '>' . $phase_name . '</a>';
      }
    ?>
  </div>
  <div id="add-user-wrapper" class="content-block">
    <input type="text" id="add-user" placeholder="add user (jPeople)" /> <br />
    <div id="users"></div>
  </div>
  <form class="navbar-form content-block" id="generate-new-groups" action="" method="get">
      <input type="number" name="group-size" value="2" min="1" max="10" />
      <?php echo $phase_select; ?>
      <input type="text" name="exclude" placeholder="Usernames to exclude from groups" value="<?php echo ADMINS; ?>" />
      <input type="submit" class="btn btn-primary" value="Generate new groups!" />
      <input type="button" id="clear-new-groups" class="btn btn-danger" value="Clear" disabled />
      <input type="button" id="set-new-groups" class="btn btn-success" value="Set the new groups" disabled />
  </form>
  <div id="new-groups" class="sortable-groups content-block" style="display:none"></div>
  <div id="feedback">
    <?php

      $last_evaluations = $EM->get_last_evaluations_from_phase($phase, 0, 15);
      $last_people = array();
      foreach ($last_evaluations as $eid) {
        $person = $PM->get_by_eid($eid);
        $person = $person[0];
        $group_number = $GM->get_group_number($person['eid'], $phase);
        $group_number = $group_number[0];
        // v_export($person);
        $last_people[] = '
          <a class="face-tiny" href="#'.$person['eid'].'">
            <img src="' . $person['photo_url'] . '" alt="X" />
            <span class="name">' . $person['fname'] . ' ' . $person['lname'] . '</span>
            <span class="group-number">' . $group_number . '</span>
          </a>
          ';
      }
      echo '
        <div id="last-people" class="content-block">
          <div>Last '.count($last_people).' people who submited peer-review:</div>
          ' . implode('', $last_people) . '
        </div>
      ';

      /** generate Peer Evaluation Table **/

      $evaluations_from_phase = $EM->get_evaluations_from_phase($phase);
      $people_from_phase = $GM->get_people_from_phase($phase);

      $groups = array();
      $people = array();
      $evaluations = array();
      $from_evaluations = array();

      foreach ($people_from_phase as $person) {
        $groups[$person['group_number']][] = $person;
        $people[$person['eid']] = $person;
      }

      foreach ($evaluations_from_phase as $row) {
        $evaluations[$row['to_eid']][] = $row;
        $from_evaluations[$row['from_eid']] = $row;
      }

      krsort($groups);
      ksort($people);
      ksort($evaluations);
      ksort($from_evaluations);

      $header = array();
      $header[] = '<th>Who?</th>';
      foreach ($questions as $question) {
        $header[] = '<th>'.$question.'</th>';
      }
      $header[] = '<th>Comments</th>';
      $header[] = '<th>Score</th>';
      $header = '<tr>' . implode('', $header) . '</tr>';

      $num_questions = count($questions);
      $num_options = count($options);
      $max_score = ($num_options - 1) * $num_questions;

      $body = array();
      foreach ($groups as $group_number => $members) {
        $num_members = count($members);
        $body[] = '<tr><td class="group-number" colspan="'.($num_questions + 3).'">Group #' . $group_number . '</td></tr>';
        foreach ($members as $person) {
          $row = array();
          $row[] = '
            <td class="name clearfix" id="'.$person['eid'].'">
              <img src="' . $person['photo_url'] . '" alt="photo" />
              <span>' . $person['fname'] . ' ' . $person['lname'] . '</span>
            </td>';

          // add the question cells
          $teammates_score = $max_score * ($num_members - 1);
          $total_score = $teammates_score;
          if (isset($evaluations[$person['eid']])) {
            foreach ($questions as $question_number => $question) {
              $cell = array();
              foreach ($evaluations[$person['eid']] as $feedback) {
                $score = $feedback['question_' . $question_number];
                $from = $people[$feedback['from_eid']];
                $cell[] = '<span class="score-cell" title="'.$from['fname'] . ' ' . $from['lname'] . '">
                            <span class="value">' . $score . '</span>
                            <img src="'.$from['photo_url'].'" />
                          </span>';
                $total_score -= ($num_options - 1 - $score);
              }
              $cell = '<td>' . implode('', $cell) . '</td>';
              $row[] = $cell;
            }
          } else {
            for ($i=0; $i<$num_questions; ++$i) {
              $row[] = '<td>no feedback</td>';
            }
          }

          // add the comment cell
          if (isset($from_evaluations[$person['eid']])) {
            $row[] = '<td class="comment">' . $from_evaluations[$person['eid']]['comment'] . '</td>';
          } else {
            $row[] = '<td class="comment"></td>';
          }

          $row[] = '<td class="score">
              <span class="value">' . number_format($teammates_score > 0 ? $total_score / $teammates_score : 0, 2) . '</span>
              <div>(' . $total_score . ' / ' . $teammates_score . ')</div>
            </td>';
          $row = '<tr>' . implode('', $row) . '</tr>';
          $body[] = $row;
        }
      }
      $body = implode('', $body);

      echo '<table class="peer-evaluation table">' . $header . $body . '</table>';
    ?>
  </div>
  <hr />
  <div id="groups" class="sortable-groups content-block"></div>

  </body>
</html>