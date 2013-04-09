<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"/>
  <link rel="stylesheet" type="text/css" href="css/style.css"/>
  <style>
    .roulette {
      border-radius: 1000px;
      border: 1px solid #000;
      background: #fff;
      margin: 10px;
        overflow: hidden;
    }
    .face {
      position: relative;
      display: inline-block;
      padding: 5px;
    }
    .face.chosen {
      background: red;
    }
    .face img {
      max-width: 80px;
      max-height: 80px;
    }
    .face .name {
      position: absolute;
      right: 5px;
      bottom: 5px;
      left: 5px;
      background: rgba(0, 0, 0, 0.7);
      font-size: 8pt;
      text-align: center;
      color: #fff;
    }
    .roulette-wrapper {
      position: relative;
      float: left;
    }
    .outer-center {
      position: absolute;
      top: 50%;
      left: 50%;
      height: 100px;
    }
    .inner-center {
      position: relative;
      top: -50%;
      left: -50%;
    }
    .roulette-wrapper .info-panel {
      border: 1px solid #ccc;
      background: #fff;
      padding: 10px;
    }
    .roulette-wrapper .separator {
      font-size: 120pt;
      line-height: 100px;
    }
    .roulette-wrapper .wrapper-face img {
      max-width: 120px!important;
      max-height: 120px!important;
    }
  </style>
  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
  <script type="text/javascript" src="js/shared.js"></script>
</head>
<body>
  <?php require_once 'login.php'; ?>
  <?php
    if (!is_admin()) {
      echo '<div class="alert alert-error">You are not an admin, so no :)</div>';
      exit();
    }
  ?>

  <div id="people"></div>

  <script>
    var admins = '<?php echo ADMINS; ?>'.split(',');
    var DIR_PHOTOS = '<?php echo DIR_PHOTOS; ?>';
    var DIR_IMAGES = '<?php echo DIR_IMAGES; ?>';
    var unknown_face_url = DIR_IMAGES + '/unknown.jpg';
    var arrow_url = DIR_IMAGES + '/roulette-arrow-down.png';

    var spin_time = 15 * 1000;
    var select_time = 5 * 1000;
    var result_time = 4 * 1000;

    var code_allocations = '1,20|2,17|3,5|4,10|5,6|6,7|7,11|8,3|9,8|10,4|11,9|12,22|13,18|14,19|15,14|16,1|17,16|18,15|19,13|20,12|21,21|22,23|23,2';
    code_allocations = code_allocations.split('|').map(function(v){ return v.split(',');});

    // get_advanced_people(admins, function (people) {
    //   var new_groups = randomly_allocate_in_groups(2, people);
    //   var new_codes = randomly_allocate_codes(new_groups, code_allocations, people);
    //   console.log(new_codes);
    //   // for (var i=0; i<new_codes.length; ++i) {
    //   //   var str;
    //   //   str = (i+1)+' -> '+j;
    //   //   console.log(str);
    //   // }
    // });
  </script>
  <script src="js/group-roulette.js"></script>

</body>
</html>