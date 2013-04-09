<?php

  require_once 'config.php';
  require_once 'utils.php';
  require_once 'Log_Model.php';
  require_once 'People_Model.php';
  require_once 'Groups_Model.php';
  require_once 'Evaluations_Model.php';

  e_assert_isset($_GET, 'action');

  $Log = new Log_Model();
  $PM = new People_Model();
  $GM = new Groups_Model($PM);
  $EM = new Evaluations_Model();

  recursive_escape($_GET);

  e_assert($_SESSION['user'], 'You are not logged-in!');

  $Log->log($_SESSION['user'], $_GET['action'], serialize($_GET));

  switch ($_GET['action']) {
    case 'evaluate':
      e_assert_isset($_GET, 'phase,teammates,questions,comment');

      $_GET['comment'] = htmlspecialchars($_GET['comment']);

      $myself = $PM->get_by_account($_SESSION['user']);
      e_assert($myself && is_array($myself), 'You are not registered for this course');

      $group_number = $GM->get_group_number($myself['eid'], $_GET['phase']);
      e_assert($group_number && is_array($group_number), 'You are not assigned to a group');

      $group_number = $group_number[0];
      $full_group = $GM->get_eids_from_group($_GET['phase'], $group_number);
      foreach ($_GET['questions'] as $eid => $questions) {
        e_assert($eid !== $myself['eid'], "Nice try, but you can't give yourself feedback!");
        e_assert(array_search($eid, $full_group) !== false, "You and `$eid` were not teammates!");
      }
      $EM->remove_evaluation($myself['eid'], $_GET['phase']);
      e_assert($EM->add_evaluation($myself['eid'], $_GET), mysql_error());

      json_output(array(
        'result' => true
      ));
      break;
    case 'set_new_groups':
      e_is_admin();
      e_assert_isset($_GET, 'phase,groups');
      e_assert(is_array($_GET['groups']), '`groups` attribute must be an array');
      $GM->delete_phase($_GET['phase']);
      json_output(array(
        'result' => $GM->set_phase($_GET['phase'], $_GET['groups']),
        'error' => mysql_error()
      ));
      break;
    case 'get_people_from_phase':
      e_is_admin();
      e_assert_isset($_GET, 'phase');
      $in_groups = $GM->get_people_from_phase($_GET['phase']);
      e_assert(is_array($in_groups), mysql_error());
      $without_groups = $GM->get_people_not_in_phase($_GET['phase']);
      e_assert(is_array($without_groups), mysql_error());
      json_output(array(
        'result' => true,
        'in_groups' => $in_groups,
        'without_groups' => $without_groups
      ));
      break;
    case 'get_groups':
      e_is_admin();
      e_assert_isset($_GET, 'phase');
      $result = $GM->get_group_numbers($_GET['phase']);
      e_assert(is_array($result), mysql_error());
      json_output(array(
        'result' => true,
        'records' => $result
      ));
      break;
    case 'add_to_group':
      e_is_admin();
      e_assert_isset($_GET, 'eid,phase,number');
      e_assert(
        $GM->remove_from_group($_GET['eid'], $_GET['phase']),
        mysql_error()
      );
      e_assert(
        $GM->add_to_group($_GET['eid'], $_GET['phase'], $_GET['number']),
        mysql_error()
      );
      json_output(array('result' => true));
      break;
    case 'remove_from_group':
      e_is_admin();
      e_assert_isset($_GET, 'eid,phase');
      e_assert(
        $GM->remove_from_group($_GET['eid'], $_GET['phase']),
        mysql_error()
      );
      json_output(array('result' => true));
      break;
    case 'get_all_groups':
      e_is_admin();
      $result = $GM->get_all();
      e_assert(is_array($result), mysql_error());
      json_output(array(
        'result' => true,
        'records' => $result
      ));
      break;
    case 'get_all':
      e_is_admin();
      $result = $PM->get_all();
      e_assert(is_array($result), mysql_error());
      json_output(array(
        'result' => true,
        'records' => $result
      ));
      break;
    case 'add':
      e_is_admin();
      e_assert_isset($_GET, 'data');
      e_assert($PM->add($_GET['data']), mysql_error());
      json_output(array('result' => true));
      break;
    default:
      error_output('Unknown action `' . $_GET['action'] . '`');
  }

  function e_is_admin () {
    e_assert(is_admin(), 'Only admins are allowed to perform this action!');
  }

?>
