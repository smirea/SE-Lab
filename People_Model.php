<?php

  require_once 'Model.php';

  class People_Model extends Model {

    public function __construct ($table_name = 'People') {
      parent::__construct($table_name);
    }

    public function get_all () {
      return $this->to_array($this->select('*'));
    }

    public function get_by_eid ($eid) {
      return $this->to_array($this->select('*', "WHERE eid='$eid'"));
    }

    public function get_by_account ($account) {
      return Model::get_first_row($this->select('*', "WHERE account='$account'"));
    }

    public function add (array $data) {
      if (isset($data['id'])) {
        unset($data['id']);
      }
      return $this->insert($data);
    }

  }
?>