<?php

  define('DEBUG', false);

  define('ADMINS', 'smirea,akebdani,mturcu,cgurau,bmahleko');

  define('DB_USER', 'jPerson');
  define('DB_PASS', 'jacobsRulz');
  define('DB_NAME', 'SE_Lab');

  define('DS', '/');

  define('DIR_PHOTOS', 'photos');
  define('DIR_IMAGES', 'images');

  dbConnect(DB_USER, DB_PASS, DB_NAME);

  session_start();

  $admins = explode(',', ADMINS);


  $phases = array('design', 'xp1', 'xp2', 'xp3');

  $phase = $phases[2];

  $questions = array(
    'Did a full share of the work ?',
    'Took the initiative in helping the group get organized ?',
    'Provided many ideas for the development of the presentation ?',
    'Work was ready on time or sometimes ahead of time ?'
  );

  $options = array(
    '[0] Did not work at all',
    '[1] Strongly Disagree',
    '[2] Disagree',
    '[3] Not Sure',
    '[4] Agree',
    '[5] Strongly Agree'
  );

  /**
   * Checks to see if a username is an admin
   * @param {String} $username If set to null, $_SESSION['user'] will be used
   * @return {Bool}
   */
  function is_admin ($username = null) {
    global $admins;
    if (!$username) {
      if (!isset($_SESSION['user'])) {
        return false;
      }
      $username = $_SESSION['user'];
    }
    return in_array($username, $admins);
  }

  /**
   * @brief Perform a database connection
   * @warning Dies if it is unable to make a connection
   * @param {string} $user
   * @param {string} $pass
   * @param {string} $name
   * @param {string} $host
   */
  function dbConnect($user, $pass, $name = null, $host = 'localhost'){
    $connexion = mysql_connect( $host, $user, $pass ) or die ("Could not connect to Data Base!");
    if( $name ) mysql_select_db( $name, $connexion ) or die ("Failed to select Data Base");
  }
?>
