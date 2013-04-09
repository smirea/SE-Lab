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
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="css/style.css"/>
    <style>
      #evaluation-form td {
        padding: 2px;
      }
      #evaluation-form th, #evaluation-form select, #evaluation-form textarea, #evaluation-form input {
        font-size: 10pt;
      }
      #evaluation-form th {
        font-weight: bold;
      }
      #evaluation-form th.question {
        width: 250px;
        text-align: left;
      }
      .bar {
        text-align: center;
      }
      .bar output {
        font-family: arial;
        font-size: 10pt;
        font-weight: bold;
        line-height: 20px;
        text-shadow: 0 -1px 2px rgba(0, 0, 0, 0.5);
        color: #fff;
      }
      select {
        width: auto;
      }
      textarea[name="comment"] {
        width: 100%;
      }
    </style>
    <script src="js/jquery.js" type="text/javascript"></script>
    <script src="jquery-ui/jquery-ui.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/shared.js" type="text/javascript"></script>
    <script src="js/se-lab.js" type="text/javascript"></script>
  </head>

  <body>
    <?php

      require_once 'login.php';

      $PM = new People_Model();
      $GM = new Groups_Model($PM);
      $EM = new Evaluations_Model();

      $myself = $PM->get_by_account($_SESSION['user']);
      if (!$myself || !is_array($myself)) {
        echo '<div class="error">You are not registered for this course!</div>';
        exit();
      }
      $group_number = $GM->get_group_number($myself['eid'], $phase);
      if (!$group_number || !is_array($group_number) || count($group_number) === 0) {
        echo '<div class="error">You are not assigned to a group</div>';
        exit();
      }
      $group_number = $group_number[0];
      $full_group = $GM->get_people_from_group($phase, $group_number);

      // exclude yourself from the group
      $group = array();
      foreach ($full_group as $person) {
        if ($person['eid'] === $myself['eid']) {
          continue;
        }
        $group[] = $person;
      }

      $teammates = array();

      $header = array('<th>Question</th>');
      foreach ($group as $person) {
        $header[] = '<th>' . $person['fname'] . ' ' . $person['lname'] . '</th>';
        $teammates[] = '<input type="hidden" name="teammates[]" value="' . $person['eid'] . '" />';
      }

      $scores = null;
      $evaluation = $EM->get_evaluation($myself['eid'], $phase);
      if ($evaluation && is_array($evaluation) && count($evaluation) > 0) {
        $scores = array();
        foreach ($evaluation as $row) {
          $scores[$row['to_eid']] = array();
          foreach ($row as $key => $value) {
            if (strpos($key, 'question_') !== 0) {
              continue;
            }
            $scores[$row['to_eid']][intval(substr($key, strrpos($key, '_') + 1))] = $value;
          }
        }
        $scores['comment'] = $evaluation[0]['comment'];
      }

      $body = array();
      foreach ($questions as $question_number => $question) {
        $row = array();
        $row[] = '<th class="question">' . $question . '</th>';
        foreach ($group as $person_number => $person) {
          $classes = 'class="row-'.$question_number.' column-'.$person_number.'"';
          $row[] = '' .
            '<td>
              <select name="questions['.$person['eid'].'][]"
                      row="'.$question_number.'"
                      column="'.$person_number.'" '.$classes.'>
          ';
          foreach ($options as $value => $label) {
            $selected = '';
            if ($scores && isset($scores[$person['eid']]) && $scores[$person['eid']][$question_number] == $value) {
              $selected = 'selected';
            }
            $row[] = '<option value="'.$value.'" '.$selected.'>'.$label.'</option>';
          }
          $row[] = '
              </select>
            </td>
          ';
        }
        $body[] = '<tr>' . implode('', $row) . '</tr>';
      }
      $row = array('<td>Total</td>');
      foreach ($group as $person_number => $person) {
        $row[] = '
          <td>
            <div class="progress progress-striped active column-'.$person_number.'" id="total-' . $person['eid'] . '" column="'.$person_number.'">
              <div class="bar" style="width: 0;"><output></output></div>
            </div>
            <!-- <output id="total-' . $person['eid'] . '" class="column-'.$person_number.'" column="'.$person_number.'">0</output> -->
          </td>';
      }
      $body[] = '<tr>' . implode('', $row) . '</tr>';

      echo '
        <form action="ajax.php" method="get" id="evaluation-form">
          <input type="hidden" name="action" value="evaluate" />
          <input type="hidden" name="phase" value="'.$phase.'" />
          '. implode('', $teammates) .'
          <table>
            <tr>' . implode('', $header) . '</tr>
            ' . implode('', $body) .'
            <tr>
              <td colspan="'.count($header).'">
                <textarea name="comment" rows="5" placeholder="Insert your additional comments here, if any">'.($scores && $scores['comment'] ? $scores['comment'] : '').'</textarea>
              </td>
            </tr>
            <tr>
              <td colspan="'.count($header).'">
                <input type="submit" class="btn btn-primary" value="Submit Feedback" />
                <output id="result-message"></output>
              </td>
            </tr>
          </table>
        </form>
      ';
    ?>
  </body>
</html>