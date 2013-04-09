<?php

  require_once 'utils.php';
  require_once 'People_Model.php';

  $PM = new People_Model();

  $people = $PM->get_all();

  foreach ($people as $person) {
    $name = DIR_IMAGES . "/" . $person['eid'] . '.jpg';
    $content = file_get_contents($person['photo_url']);
    if (!$content) {
      echo '<div>Unable to get image for `'.$person['account'].'`</div>';
      continue;
    }
    if (!file_put_contents($name, $content)) {
      echo '<div>Unable to write image file `'.$name.'`</div>';
    }
  }

  echo '<div>DONE</div>';

?>