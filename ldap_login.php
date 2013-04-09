<?php

  /**
   * @param {String} $username
   * @param {String} $password
   * @return {Bool} 
   */
  function ldap_login ($username, $password) {
    $host = "jacobs.jacobs-university.de";
    $port = 389;
    $base_dn = "DC=jacobs,DC=jacobs-university,DC=de";
    $user_dn = "OU=active,OU=Users,OU=CampusNet,DC=jacobs,DC=jacobs-university,DC=de";

    if (!$username || !$password) {
      return false;
    }
//    error_reporting(0);
    $ldap_conn = ldap_connect($host);
    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
    $success = ldap_bind(
      $ldap_conn,
      $username."@jacobs.jacobs-university.de",
      $password
    );
    ldap_unbind($ldap_conn);
//    error_reporting(-1);

    return $success;
  }

?>
