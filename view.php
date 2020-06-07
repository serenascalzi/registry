<?php
  require_once 'pdo.php';
  require_once 'util.php';
  session_start();

  if (isset($_POST['done'])) {
    header('Location: index.php');
    return;
  }

  if (!isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = 'Missing Entry';
    header('Location: index.php');
    return;
  }

  $stmt = $pdo -> prepare('SELECT first_name, last_name, email, headline, summary, profile_id FROM Profile WHERE profile_id = :pid');
  $stmt -> execute(array(':pid' => $_REQUEST['profile_id']));
  $row = $stmt -> fetch(PDO::FETCH_ASSOC);
  if ($row === false) {
    $_SESSION['error'] = 'Bad Entry';
    header('Location: index.php');
    return;
  }

  $positions = loadPos($pdo, $_REQUEST['profile_id']);
  $schools = loadEdu($pdo, $_REQUEST['profile_id']);
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
          <h2>Profile Information</h2>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php flashMessages(); ?>
          <p><span class="title">First Name</span><br /><?= htmlentities($row['first_name']) ?></p>
          <p><span class="title">Last Name</span><br /><?= htmlentities($row['last_name']) ?></p>
          <p><span class="title">Email</span><br /><?= htmlentities($row['email']) ?></p>
          <p><span class="title">Headline</span><br /><?= htmlentities($row['headline']) ?></p>
          <p><span class="title">Summary</span><br /><?= htmlentities($row['summary']) ?></p>
          <p><span class="title">Education</span></p>
          <?php
            foreach ($schools as $school) {
              echo('<p>- ');
              echo(htmlentities($school['year']));
              echo(': ');
              echo(htmlentities($school['name']));
              echo('</p>');
            }
          ?>
          <p><span class="title">Positions</span></p>
          <?php
            foreach ($positions as $position) {
              echo('<p>- ');
              echo(htmlentities($position['year']));
              echo(': ');
              echo(htmlentities($position['description']));
              echo('</p>');
            }
          ?>
          <form method="post">
            <p><input type="submit" name="done" value="Done" class="btn" /></p>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php require_once 'footer.php'; ?>
</body>
</html>
