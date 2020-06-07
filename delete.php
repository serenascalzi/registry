<?php
  require_once 'pdo.php';
  require_once 'util.php';
  session_start();

  if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
    return;
  }

  if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
  }

  if (!isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = 'Missing Entry';
    header('Location: index.php');
    return;
  }

  if (isset($_POST['delete']) && isset($_POST['profile_id'])) {
    $stmt = $pdo -> prepare('DELETE FROM Profile WHERE profile_id = :profile_id');
    $stmt -> execute(array(':profile_id' => $_POST['profile_id']));
    $stmt = $pdo -> prepare('DELETE FROM Position WHERE profile_id = :profile_id');
    $stmt -> execute(array(':profile_id' => $_REQUEST['profile_id']));      
    $stmt = $pdo -> prepare('DELETE FROM Education WHERE profile_id = :profile_id');
    $stmt -> execute(array(':profile_id' => $_REQUEST['profile_id']));
    $_SESSION['success'] = 'Profile Deleted';
    header('Location: index.php');
    return;
  }

  $stmt = $pdo -> prepare('SELECT first_name, last_name, profile_id FROM Profile WHERE profile_id = :pid');
  $stmt -> execute(array(
    ':pid' => $_REQUEST['profile_id']));
  $row = $stmt -> fetch(PDO::FETCH_ASSOC);
  if ($row === false) {
    $_SESSION['error'] = 'Bad Entry';
    header('Location: index.php');
    return;
  }
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
          <h2>Delete Profile</h2>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php flashMessages(); ?>
          <p><span class="title">First Name</span><br /><?= htmlentities($row['first_name']) ?></p>
          <p><span class="title">Last Name</span><br /><?= htmlentities($row['last_name']) ?></p>
          <form method="post">
            <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
            <p><input type="submit" name="delete" value="Delete" class="btn" /> <input type="submit" name="cancel" value="Cancel" class="btn" /></p>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php require_once 'footer.php'; ?>
</body>
</html>
