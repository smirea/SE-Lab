<?php

  require_once 'config.php';
  require_once 'utils.php';
//  require_once 'ldap_login.php'; // ONLY WORKS WITHIN JACOBS NETWORK
  require_once 'campusnet.php';

  if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == 'logout') {
      unset($_SESSION['user']);
      session_destroy();
      unset($_REQUEST['action']);
      $new_query = http_build_query($_REQUEST);
      header('Location: ' . $_SERVER['SCRIPT_NAME'] . (strlen($new_query) > 0 ? '?' . $query_string : ''));
      exit();
    } else if ($_REQUEST['action'] == 'login') {
      $username = mysql_real_escape_string($_REQUEST['username']);
      $password = mysql_real_escape_string($_REQUEST['password']);
//      if (ldap_login($username, $password)) {

      /**
       * MAJOR HAAAAAAAAAAAAAAAAAAAAAAAACKKKKKKKKKKKKKKKKKKKKKK
       * SECURY RIIIIIIIIIIIISK!!!!!!!!!!!!!!
       */
      if (strlen(loginToCampusNet($username, $password)) > 10000) {
        $_SESSION['user'] = strtolower($username);
      } else {
        echo '<div class="error">Invalid username/password</div>';
      }
    }
  }

  if (!isset($_SESSION['user']) || !$_SESSION['user']) {
    require_once 'login-form.php';
    exit();
  }

  echo '
    <div class="login">Logged in as <b>' . $_SESSION['user'] .'<span class="phase">['.$phase.' phase]</span></b>.
        <a class="logout" href="?action=logout">log out</a></div>
  ';

?>
