<?php
  require_once 'pdo.php';
  require_once 'util.php';
  session_start();

  if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
  }

  $salt = '*********';

  if (isset($_POST['email']) && isset($_POST['pass'])) {
    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
      $_SESSION['error'] = 'Email and Password are Required';
      header('Location: login.php');
      return;
    } else if (strpos($_POST['email'], '@') === false) {
      $_SESSION['error'] = 'Email Must Have an At-Sign (@)';
      header('Location: login.php');
      return;
    } else {
      $check = hash('md5', $salt.$_POST['pass']);
      $stmt = $pdo -> prepare('SELECT user_id, name FROM User WHERE email = :em AND password = :pw');
      $stmt -> execute(array(':em' => $_POST['email'], ':pw' => $check));
      $row = $stmt -> fetch(PDO::FETCH_ASSOC);
      if ($row !== false) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['success'] = 'Logged In';
        error_log("Login success".$_POST['email']);
        header('Location: index.php');
        return;
      } else {
        $_SESSION['error'] = 'Incorrect Email/Password';
        error_log("Login fail".$_POST['email']."$check");
        header('Location: login.php');
        return;
      }
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
          <h2>Log In</h2>
        </div>
        <div class="col col-sm-8 main-area push-down-sm">
          <?php flashMessages(); ?>
          <form method="POST">
            <p>Email<br /><input type="text" name="email" size="30" id="email" /></p>
            <p>Password<br /><input type="text" name="pass" size="30" id="id_1723" /></p>
            <p><input type="submit" name="login" value="Log In" onclick="return doValidate();" class="btn" /> <input type="submit" name="cancel" value="Cancel" class="btn" /></p>
          </form>
          <script>
          function doValidate() {
            try {
              addr = document.getElementById('email').value;
              pw = document.getElementById('id_1723').value;
              if (addr == null || addr == "" || pw == null || pw == "") {
                alert("Both Fields Must Be Filled Out");
                return false;
              }
              if (addr.indexOf('@') == -1) {
                alert("Invalid Email Address");
                return false;
              }
              return true;
            } catch(e) {
              return false;
            }
            return false;
          }
          </script>
        </div>
      </div>
    </div>
  </div>
  <?php require_once 'footer.php'; ?>
</body>
</html>
