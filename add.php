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

  if (isset($_POST['add'])) {
    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
      $msg = validateProfile();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
      }

      $msg = validatePos();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
      }

      $msg = validateEdu();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
      }

      $stmt = $pdo -> prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES (:uid, :fn, :ln, :em, :he, :su)');
      $stmt -> execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']));

      $profile_id = $pdo -> lastInsertId();

      insertPositions($pdo, $profile_id);
      insertEducations($pdo, $profile_id);

      $_SESSION['success'] = 'Profile Added';
      header('Location: index.php');
      return;
    }
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
          <h2>Add Profile</h2>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php flashMessages(); ?>
          <form method="post">
            <p>First Name<br /><input type="text" name="first_name" size="30" /></p>
            <p>Last Name<br /><input type="text" name="last_name" size="30" /></p>
            <p>Email<br /><input type="text" name="email" size="30" /></p>
            <p>Headline<br /><input type="text" name="headline" size="30" /></p>
            <p>Summary<br /><textarea name="summary" rows="6" cols="30"></textarea></p>
            <p>Education <input type="submit" id="addEdu" value="+" class="btn" /></p>
            <div id="edu_fields"></div>
            <p>Position <input type="submit" id="addPos" value="+" class="btn" /></p>
            <div id="position_fields"></div>
            <p><input type="submit" name="add" value="Add" class="btn" /> <input type="submit" name="cancel" value="Cancel" class="btn" /></p>
          </form>
          <script>
            countPos = 0;
            countEdu = 0;
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
