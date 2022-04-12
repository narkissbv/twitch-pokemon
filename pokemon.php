<?php
  header("Access-Control-Allow-Origin: *");
  header("Expires: on, 01 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  include_once('db_connect.php');
  include_once('config.php');

  if (!isset($_GET['username'])) {
    die('Error: missing username');
  }
  $username = mysqli_real_escape_string($link, $_GET['username']);

  // create new record for new users
  $sql = "SELECT * FROM `points` WHERE username={$username}";
  $user_rs = mysqli_query($link, $sql);
  if (mysqli_num_rows($user_rs) == 0) {
    // create new user record
    $sql = "INSERT INTO `points` (username, amount) VALUES ('$username', $daily_reward)";
    mysqli_query($link, $sql);
  }    

  // try to catch Pokemon
  $win_probabilty = 50;
  $bet = rand(0,100);
  if ($bet <= $win_probabilty) {
    // catch success
  } else {
    // catch fail
  }

  if (!isset($_GET['query'])) {
    die()
  }
  
  $query = mysqli_real_escape_string($link, $_GET['query']);

  switch($action) {
    case 'list':

      break;
    case 'release':

      break;
    default:
      die('Error: unfamiliar query command');
      break;
  }

?>