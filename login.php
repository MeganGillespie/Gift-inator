<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Logged-out */
  if ($user !== false) {
    header("Location: view-all-lists.php");
    exit();
  }

  /* Initialize Variables */
  $username = "";
  $password = "";
  $error = false;

  //Process Form on Submission
  if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    /* Connect to the Database */
    require './includes/library.php';
    $pdo = connectDB();

    //Find User 
    $query = "SELECT * FROM `GiftinatorUserInfo` WHERE username=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$username]);

    if (!$stmnt)
      $error = true;
    else
    {  
      $results = $stmnt->fetch();

      if (password_verify($password, $results['passwd']))
      { //Login
        $_SESSION['user'] = $username;
        $_SESSION['userId'] = $results['userId'];
        header("Location: view-all-lists.php");
        exit();
      }
     else 
        $error = true;
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php
      $PAGE_TITLE = "Gift-inator | Login";
      include 'includes/metadata.php';
    ?>
  </head>

  <body>
    <?php include 'includes/header.php'; ?>

    <main>
      <?php include 'includes/nav.php'; ?>

      <div class="main-content">
        <h2>Login</h2>
        <form id="login" name="login" action="login.php" method="post">

          <!-- Username -->
          <div class="form-item row">
            <label for="username">Username: </label>
            <input
              type="text" 
              name="username" 
              id="username" 
              value="<?php echo $username; ?>" 
              required
            />
          </div>

          <!-- Password -->
          <div class="form-item row">
            <label for="passwd">Password: </label>
            <input 
              type="password" 
              name="password" 
              id="passwd" 
              value="<?php echo $password; ?>" 
              required
            />
          </div>
          <div><a href="forgot-password.php">Forgot your password?</a></div>
          <button type="submit" name="login" id="loginButton">Login</button>
        </form>
        <span class="<?php echo $error ? 'error' : 'noerror'; ?>">
          Incorrect username or password.</span>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>
  </body>
</html>