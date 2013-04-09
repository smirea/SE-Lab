<?php

  require_once 'Model.php';

  class Log_Model extends Model {
    public function __construct ($table_name = 'Log') {
      parent::__construct($table_name);
    }

    public function log ($account, $action, $details) {
      $this->insert(array(
        'time' => date('H:i:s Y.m.d'),
        'account' => $account,
        'action' => $action,
        'details' => $details
      ));
    }
  }

?>