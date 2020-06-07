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

  $stmt = $pdo -> prepare('SELECT * FROM Profile WHERE profile_id = :pid AND user_id = :uid');
  $stmt -> execute(array(
    ':pid' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']));
  $row = $stmt -> fetch(PDO::FETCH_ASSOC);
  if ($row === false) {
    $_SESSION['error'] = 'Bad Entry';
    header('Location: index.php');
    return;
  }

  if (isset($_POST['edit'])) {
    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
      $msg = validateProfile();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
      }

      $msg = validatePos();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
      }

      $msg = validateEdu();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
      }

      $stmt = $pdo -> prepare('UPDATE Profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :su WHERE profile_id = :pid AND user_id = :uid');
      $stmt -> execute(array(
        ':pid' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']));

      $stmt = $pdo -> prepare('DELETE FROM Position WHERE profile_id = :pid');
      $stmt -> execute(array(':pid' => $_REQUEST['profile_id']));

      insertPositions($pdo, $_REQUEST['profile_id']);

      $stmt = $pdo -> prepare('DELETE FROM Education WHERE profile_id = :pid');
      $stmt -> execute(array(':pid' => $_REQUEST['profile_id']));

      insertEducations($pdo, $_REQUEST['profile_id']);

      $_SESSION['success'] = 'Profile Edited';
      header('Location: index.php');
      return;
    }
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
          <h2>Edit Profile</h2>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php
            flashMessages();
            $f = htmlentities($row['first_name']);
            $l = htmlentities($row['last_name']);
            $e = htmlentities($row['email']);
            $h = htmlentities($row['headline']);
            $s = htmlentities($row['summary']);
            $profile_id = $row['profile_id'];
          ?>
          <form method="post">
            <p>First Name<br /><input type="text" name="first_name" size="30" value="<?= $f ?>" /></p>
            <p>Last Name<br /><input type="text" name="last_name" size="30" value="<?= $l ?>" /></p>
            <p>Email<br /><input type="text" name="email" size="30" value="<?= $e ?>" /></p>
            <p>Headline<br /><input type="text" name="headline" size="30" value="<?= $h ?>" /></p>
            <p>Summary<br /><textarea name="summary" rows="6" cols="30"><?= $s ?></textarea></p>
            <?php
              $countEdu = 0;
              echo('<p>Education <input type="submit" id="addEdu" value="+" class="btn" /></p>'."\n");
              echo('<div id="edu_fields">'."\n");
              if (count($schools) > 0) {
                foreach ($schools as $school) {
                  $countEdu++;
                  echo('<div id="edu'.$countEdu.'">'."\n");
                  echo('<p>Year<br /><input type="text" name="edu_year'.$countEdu.'" size="30" ');
                  echo('value="'.$school['year'].'" />'."\n");
                  echo('<input type="button" value="-" ');
                  echo('onclick="$(\'#edu'.$countEdu.'\').remove(); return false;" class="btn" /></p>'."\n");
                  echo('<p>School<br /><input type="text" name="edu_school'.$countEdu.'" size="30" ');
                  echo('class="school" value="'.htmlentities($school['name']).'" /></p>'."\n");
                  echo("\n</div>\n");
                }
              }
              echo("</div>\n");
              $countPos = 0;
              echo('<p>Position <input type="submit" id="addPos" value="+" class="btn" /></p>'."\n");
              echo('<div id="position_fields">'."\n");
              if (count($positions) > 0) {
                foreach ($positions as $position) {
                  $countPos++;
                  echo('<div class="position" id="position'.$countPos.'">'."\n");
                  echo('<p>Year<br /><input type="text" name="year'.$countPos.'" size="30" ');
                  echo('value="'.htmlentities($position['year']).'" />'."\n");
                  echo('<input type="button" value="-" ');
                  echo('onclick="$(\'#position'.$countPos.'\').remove(); return false;" class="btn" /></p>'."\n");
                  echo('<p><textarea name="desc'.$countPos.'" rows="6" cols="30">'."\n");
                  echo(htmlentities($position['description'])."\n");
                  echo("\n</textarea></p>\n</div>\n");
                }
              }
              echo("</div>\n");
            ?>
            <input type="hidden" name="profile_id" value="<?= $profile_id ?>" />
            <p><input type="submit" name="edit" value="Save" class="btn" /> <input type="submit" name="cancel" value="Cancel" class="btn" /></p>
          </form>
          <script>
            countPos = <?= $countPos ?>;
            countEdu = <?= $countEdu ?>;
            $(document).ready(function() {
              $('#addPos').click(function(event) {
                event.preventDefault();
                if (countPos >= 9) {
                  alert('Maximum of nine position entries exceeded');
                  return;
                }
                countPos++;
                $('#position_fields').append(
                  '<div id="position'+countPos+'"> \
                  <p>Year<br /><input type="text" name="year'+countPos+'" size="30" value="" /> \
                  <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove(); return false;" class="btn" /></p> \
                  <p><textarea name="desc'+countPos+'" rows="6" cols="30"></textarea></p> \
                  </div>');
              });
              $('#addEdu').click(function(event) {
                event.preventDefault();
                if (countEdu >= 9) {
                  alert('Maximum of nine education entries exceeded');
                  return;
                }
                countEdu++;
                var source = $("#edu-template").html();
                $('#edu_fields').append(source.replace(/@COUNT@/g, countEdu));
                $('.school').autocomplete({source: 'school.php'});
              });
              $('.school').autocomplete({source: 'school.php'});
            });
          </script>
          <script id="edu-template" type="text">
            <div id="edu@COUNT@">
              <p>Year<br /><input type="text" name="edu_year@COUNT@" size="30" value="" /> <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;" class="btn" /></p>
              <p>School<br /><input type="text" name="edu_school@COUNT@" size="30" class="school" value="" /></p>
            </div>
          </script>
        </div>
      </div>
    </div>
  </div>
  <?php require_once 'footer.php'; ?>
</body>
</html>
