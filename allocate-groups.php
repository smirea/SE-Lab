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

  $string = "ltudor + mmekonnen
ggyurchev + btagaev
lodiaconu + vchiwome
vbeleuta + ksapkota
nkolev + ymengesha
oenache + npentrel
amushumb01 + ssaeed
mbaltac + cperticas
vschneider + tpllaha
atoader + gbesleaga
dhasegan + dkundel
ppoudel + ssayenju
angiurgiu + gmerticari
cacevedo + mbalcegadi + mmiteva
rkarna + trupi
egonzalesh + fstankovsk
necheverri + alezza
svaidya + vungureanu
uagha + btiwaree
musaeed + amilitaru
jbachhuber + dcucleschi
jkohlhase + anpopa
erodriguez + amalik
";
  
  $groups = explode("\n", $string);
  foreach ($groups as $index => $str) {
    $members = explode(" + ", $str);
    foreach ($members as $account) {
      $person = $PM->get_by_account($account);
      if (!$GM->add_to_group($person['eid'], 'xp2', $index + 1)) {
        echo '<div>Error: '.$person['account'].' : '.mysql_error(),'</div>';
      }
    }
  }
  echo '<div>DONE</div>';

?>