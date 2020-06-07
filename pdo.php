<?php
  $pdo = new PDO('mysql:host=Database Host; port=Database Port; dbname=Database Name', 'Database User', 'Database Password');
  $pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
