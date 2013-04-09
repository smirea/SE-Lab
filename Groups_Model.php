<?php

  require_once 'Model.php';
  require_once 'People_Model.php';

  class Groups_Model extends Model {
    protected $people;

    public function __construct ($people_model, $table_name = 'Groups') {
      parent::__construct($table_name);
      $this->people = $people_model;
    }

    public function get_group_number ($eid, $phase) {
      return $this->to_array(
        $this->select('number', "WHERE phase='$phase' AND eid='$eid'"),
        'number'
      );
    }

    public function get_group_numbers ($phase) {
      return $this->to_array(
        $this->select('DISTINCT number', "WHERE phase='$phase'"),
        'number'
      );
    }

    public function get_eids_from_group ($phase, $number) {
      return $this->to_array(
        $this->select('eid', "WHERE phase='$phase' AND number='$number'"),
        'eid'
      );
    }

    public function get_people_not_in_phase ($phase) {
      return $this->to_array(
        $this->query("SELECT p.* FROM ".$this->people->get_table()." p WHERE p.eid NOT IN (SELECT eid FROM Groups g WHERE g.phase='$phase')")
      );
    }

    public function get_people_from_phase ($phase) {
      return $this->to_array(
        $this->query("SELECT g.number as group_number, p.* FROM ".$this->get_table()." g, ".$this->people->get_table()." p WHERE g.phase='$phase' AND g.eid=p.eid")
      );
    }

    public function get_people_from_group ($phase, $group) {
      return $this->to_array(
        $this->query("SELECT p.* FROM ".$this->get_table()." g, ".$this->people->get_table()." p WHERE g.phase='$phase' AND g.number='$group' AND g.eid=p.eid")
      );
    }

    public function add_to_group ($eid, $phase, $number) {
      return $this->insert(array(
        'phase' => $phase,
        'number' => $number,
        'eid' => $eid
      ));
    }

    public function get_all () {
      return $this->to_array($this->select('*'));
    }

    public function set_phase ($phase, array $groups) {
      foreach ($groups as $group_number => $members) {
        foreach ($members as $eid) {
          $insert = $this->insert(array(
            'phase' => $phase,
            'number' => $group_number,
            'eid' => $eid
          ));
          if (!$insert) {
            return null;
          }
        }
      }
      return true;
    }

    public function delete_phase ($phase) {
      return $this->delete("WHERE phase='$phase'");
    }

    /**
     * Remove a user from a group. A user can only be in only one group in a phase
     * @param  {String} $phase
     * @param  {Int} $eid
     * @return {Bool}
     */
    public function remove_from_group ($eid, $phase, $number = null) {
      $number_condition = $number !== null ? "AND $number='$number'" : '';
      return $this->delete("WHERE phase='$phase' AND eid='$eid' $number_condition");
    }

  }

?>