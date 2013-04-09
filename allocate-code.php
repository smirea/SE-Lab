<?php
  require_once 'config.php';
  require_once 'utils.php';
  require_once 'Log_Model.php';
  require_once 'People_Model.php';
  require_once 'Groups_Model.php';
  require_once 'Evaluations_Model.php';

  e_assert(is_admin(), 'Only admins are allowed to perform this action!');

  $Log = new Log_Model();
  $PM = new People_Model();
  $GM = new Groups_Model($PM);
  $EM = new Evaluations_Model();

  recursive_escape($_GET);

  $accounts = explode(' ', 'mbalcegadi cgurau akebdani smirea bmahleko mturcu cacevedo ibalce vbeleuta gbesleaga jchaudhary vchiwome lodiaconu oenache egonzaleshuaman ggyurchev dhasegan jkohlhase nkolev dkundel amalik mmekonnen ymengesha gmerticariu mmiteva amushumbusi cperticas tpllaha erodriguez trupi musaeed ssaeed ksapkota vschneider btagaev btiwaree svaidya mbaltac vungureanu dcucleschin jbachhuber amilitaru fstankovski ltudor alezza angiurgiu anpopa ppoudel npentrel ssayenju uagha atoader necheverria rkarna');

  // format: xp_N -> xp_N-1
  $string = "1  ->  20
2  ->  17
3  ->  5
4  ->  10
5  ->  6
6  ->  7
7  ->  11
8  ->  3
9  ->  8
10  ->  4
11  ->  9
12  ->  22
13  ->  18
14  ->  19
15  ->  14
16  ->  1
17  ->  16
18  ->  15
19  ->  13
20  ->  12
21  ->  21
22  ->  23
23  ->  2";
  
  $tmp_people = $PM->get_all();
  $people = array();
  foreach ($tmp_people as $row) {
    $people[$row['eid']] = $row;
  }

  $allocations = explode("\n", $string);
  $to = $from = array_flip(range(1, count($allocations)));

  foreach ($allocations as $str) {
    $groups = explode("  ->  ", $str);
    $xp1 = $GM->get_eids_from_group('xp1', $groups[1]);
    $xp2 = $GM->get_eids_from_group('xp2', $groups[0]);
    unset($from[$groups[0]]);
    unset($to[$groups[0]]);
    echo '<div>'.group_string($groups[0], $xp2)." -> ".group_string($groups[1], $xp1).'</div>';
    if (count(array_intersect($xp1, $xp2)) !== 0) {
      echo error('ERROR: '.$str);
    }
  }
  if (count($from) + count($to) > 0) {
    echo error("Not all groups allocated");
    v_export($from);
    v_export($to);
  }
  echo '<div>DONE</div>';


  foreach ($people as $eid => $person) {
    if (array_search($person['account'], $accounts) === false) {
      $suggestions = array_keys(get_suggestions($person['account'], $accounts));
      echo '<div><span class="error">Wrong account: '.$person['account'].'</span>, how about: <b>'.implode(', ', $suggestions).'</b></div>';
    }
  }

  function get_suggestions ($needle, $haystack) {
    $score = array();
    foreach ($haystack as $string) {
      $score[$string] = levenshtein($needle, $string);
    }
    asort($score);
    return array_slice($score, 0, 3);
  }

  function error ($str) {
    return '<div class="error">'.$str.'</div>';
  }

  function group_string ($number, array $group) {
    global $people;
    global $GM;
    global $accounts;
    $arr = array();
    foreach ($group as $eid) {
      $account = $people[$eid]['account'];
      if (array_search($account, $accounts) === false) {
        $suggestions = array_keys(get_suggestions($account, $accounts));
        $suggestions = array_slice($suggestions, 0, 3);
        $account = $suggestions[0];
      }
      $arr[] = $account;
    }
    return "[$number] ".implode(',', $arr);
  }

?>
<style>
  .error {color:red;}
</style>