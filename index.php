<?php
  require_once 'pdo.php';
  require_once 'util.php';
  session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <?php require_once 'head.php'; ?>
</head>
<body>
  <?php require_once 'header.php'; ?>
  <div class="content-area group section">
    <div class="container">
      <div class="row">
        <div class="col col-sm-4 sidebar">
          <h2>Welcome</h2>
          <?php
            if (!isset($_SESSION['name'])) {
              echo('<p><a href="login.php" class="btn">Log In</a></p>');
            } else {
              echo('<p><a href="add.php" class="btn">Add New Entry</a> <a href="logout.php" class="btn">Log Out</a></p>');
            }
          ?>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php
            flashMessages();
            $stmt = $pdo -> query('SELECT profile_id, first_name, last_name, headline FROM Profile');
            $rows = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
              echo('<p class="error">No Rows Found</p>');
            } else {
              foreach ($rows as $row) {
                echo('<p>');
                echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']." ".$row['last_name']).'</a>, '.htmlentities($row['headline']));
                if (isset($_SESSION['name'])) {
                  echo('</p><p>');
                  echo('<a href="edit.php?profile_id='.$row['profile_id'].'" class="btn">Edit</a> <a href="delete.php?profile_id='.$row['profile_id'].'" class="btn">Delete</a>');
                }
                echo('</p>');
              }
            }
          ?>
        </div>
      </div>
    </div>
  </div>
  <?php require_once 'footer.php'; ?>
</body>
</html>
