<?php
  // header("Access-Control-Allow-Origin: *");
  header("Expires: on, 01 Jan 1970 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");
  $http_origin = $_SERVER['HTTP_ORIGIN'];
  header("Access-Control-Allow-Origin: $http_origin");
  include_once('db_connect.php');

  if (!isset($_GET['username'])) {
    die('Error: missing username');
  }
  $username = mysqli_real_escape_string($link, $_GET['username']);

  // create new record for new users
  $sql = "SELECT * FROM `pokemons` WHERE username='$username'";
  $user_rs = mysqli_query($link, $sql);
  if (mysqli_num_rows($user_rs) == 0) {
    // create new user record
    $sql = "INSERT INTO `pokemons` (username, data) VALUES ('$username', '[]')";
    mysqli_query($link, $sql);
  }
  $user_data = mysqli_fetch_assoc($user_rs);
  $user_data = json_decode($user_data['data'], true);
  if (!strlen($_GET['query'])) {
    // try to catch Pokemon
    $win_probabilty = 50;
    $try = rand(0,100);
    if ($try <= $win_probabilty) {
      // catch success
      // check whether user has an empty slot
      if (count($user_data) >= 10) {
        die("You already have 10 pokemons. Release some and try again...");
      }
      $pokemons_list = json_decode(file_get_contents('pokemons.json'), true);
      $pokemon_caught = rand(0,count($pokemons_list['pokemons']) - 1);
      array_push($user_data, $pokemons_list['pokemons'][$pokemon_caught]);
      save_data($user_data, $username, $link);
      if (isset($_GET['mode']) && $_GET['mode'] == 'json') {
        header('Content-Type: application/json');
        $resp = array(
          'success' => true,
          'name' => $pokemons_list['pokemons'][$pokemon_caught]
        );
        die(json_encode($resp));
      }
      die("Yeah, $username! You caught " . $pokemons_list['pokemons'][$pokemon_caught] . '! :)');
    }
    // catch fail
    if (isset($_GET['mode']) && $_GET['mode'] == 'json') {
      header('Content-Type: application/json');
      $resp = array(
        'success' => false
      );
      die(json_encode($resp));
    }
    die("Oh snap, $username! Pokemon got away... :(");
  }

  $query = explode(" ", $_GET['query']);
  switch($query[0]) {
    case 'list':
      if (isset($_GET['mode']) && $_GET['mode'] == 'json') {
        header('Content-Type: application/json');
        die(json_encode($user_data));
      }
      echo "$username's pokemons: ";
      if (count($user_data) <= 0) {
        die('No pokemons. Try catching some!');
      }
      foreach($user_data as $key => $value) {
        echo $key + 1 . ": $value, ";
      }
      die();

    case 'release':
      if (!$query[1]) {
        die('Error: Missing pokemon number...');
      }
      if ($query[1] < 1 || $query[1] > count($user_data)) {
        die("Error: out of range");
      }
      $pokemon_index = $query[1] - 1;
      $pokemon_name = $user_data[$pokemon_index];
      array_splice($user_data, $pokemon_index, 1);
      save_data($user_data, $username, $link);
      die("$username released $pokemon_name. Farewell cute pokemon!");

    case 'release_name':
      if (!$query[1]) {
        http_response_code(400);
        die('Error: Missing pokemon name...');
      }
      $pokemon_name = $query[1];
      $pokemon_index = array_search($pokemon_name, $user_data);
      if ($pokemon_index === false) {
        http_response_code(404);
        die("Error: Pokemon $pokemon_name not found");
      }
      array_splice($user_data, $pokemon_index, 1);
      save_data($user_data, $username, $link);
      die("$username released $pokemon_name. Farewell cute pokemon!");

    default:
      die('Error: unfamiliar pokemon command: ' . $query[0]);
  }

  function save_data($user_data, $username, $link) {
    if ($user_data == null) {
      $user_data = [];
    }
    $user_data = json_encode($user_data);
    $sql = "UPDATE `pokemons` SET `data` = '$user_data' WHERE username = '$username'";
    mysqli_query($link, $sql);
  }
?>