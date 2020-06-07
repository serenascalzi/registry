<?php
  if (!isset($_GET['term'])) {
    die('MISSING REQUIRED PARAMETER');
    return;
  }

  if (!isset($_COOKIE[session_name()])) {
    die('MUST BE LOGGED IN');
    return;
  }

  session_start();

  if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
    return;
  }

  require_once 'pdo.php';

  header("Content-type: application/json; charset=utf-8");

  $term = $_GET['term'];
  error_log("Looking up typeahead term=".$term);

  $stmt = $pdo -> prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
  $stmt -> execute(array(':prefix' => $term."%"));

  $retval = array();
  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
    $retval[] = $row['name'];
  }

  echo(json_encode($retval, JSON_PRETTY_PRINT));
?>
