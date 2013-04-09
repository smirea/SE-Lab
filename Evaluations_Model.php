<?php

  require_once 'Model.php';

  class Evaluations_Model extends Model {

    const MIN_SCORE = 0;
    const MAX_SCORE = 5;

    public function __construct ($table_name = 'Evaluations') {
      parent::__construct($table_name);
    }

    public function get_last_evaluations_from_phase ($phase, $start = 0, $number = 5) {
      $number_cap = '';
      if ($number) {
        $number_cap = ',' . $number;
      }
      return $this->to_array(
        $this->select('DISTINCT from_eid', "WHERE phase='$phase' ORDER BY time DESC LIMIT $start$number_cap"),
        'from_eid'
      );
    }

    public function get_evaluations_from_phase ($phase) {
      return $this->to_array($this->select('*', "WHERE phase='$phase'"));
    }

    public function get_evaluation ($eid, $phase) {
      return $this->to_array($this->select('*', "WHERE from_eid='$eid' AND phase='$phase'"));
    }

    public function remove_evaluation ($from_eid, $phase) {
      return $this->delete("WHERE from_eid='$from_eid' AND phase='$phase'");
    }

    public function add_evaluation ($from_eid, $data) {
      foreach ($data['questions'] as $eid => $questions) {
        $fields = array(
          'from_eid' => $from_eid,
          'to_eid' => $eid,
          'time' => time(),
          'phase' => $data['phase'],
          'comment' => $data['comment']
        );
        foreach ($questions as $question_number => $score) {
          $score = max(self::MIN_SCORE, min(self::MAX_SCORE, $score));
          $fields['question_' . $question_number] = $score;
        }
        if (!$this->insert($fields)) {
          return null;
        }
      }
      return true;
    }
  }

?>