<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Logged-out */
  if ($user !== false) 
  {
    header("Location: view-all-lists.php");
    exit();
  }

  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  //Verify User
  $code = $_GET['verification'];
  if ($code != $_SESSION['vCode'])
  {
    $notvalid = true;
  }
  
  /* Initiate Variables */
  $password1 = "";
  $password2 = "";
  $errors    = array();
  
  if (isset($_POST['submit'])) 
  {
    
    //Password1
    $password1 = $_POST['password1'];
    if(strlen($password1) < 8)
    {
      $errors['password1'] = true;
    }

    //Password2
    $password2 = $_POST['password2'];
    if($password1 !== $password2)
    {
      $errors['password2'] = true;
    }

    //No Erorrs
    if (count($errors) == 0)
    {
      $user = $_SESSION['tempUser'];

      //Rest password 
      $hash = password_hash($password1, PASSWORD_DEFAULT);
      $query = "UPDATE `GiftinatorUserInfo` SET passwd = ? WHERE userId = $user";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$hash]);

      //unset tempory session variables to reset 
      $_SESSION['vCode'] = null;
      $_SESSION['tempUser'] = null;

      //log in user and redirect 
      $_SESSION['userId'] = $user;
      header("Location: view-all-lists.php");
      exit();
    }
  }

?>
<!DOCTYPE html>
<html lang="en">
	<head>
    <?php 
      $PAGE_TITLE = "Gift-inator | Forgot Password";
      include 'includes/metadata.php';
    ?>
    </head>
    
	<body>
    <?php include 'includes/header.php'; ?>
    <main>
      <?php include 'includes/nav.php'; ?>
      <div class="main-content">
        <h2>Reset Password</h2>
        <?php if (isset($notvalid)):?>
          <p>Something went wrong this link is not active please try resting your password again.</p>
        <?php else: ?>
        <form 
          id="reset-password" 
          name="reset-password" 
          action="reset-password.php?verification=<?php echo $code; ?>" 
          method="post"
        >
          <div class="form-item column">
            <label for="password1">New Password</label>
            <input 
              type="password" 
              name="password1" 
              id="password1" 
              value="<?php echo $password1; ?>" 
              required
            />
            <span 
              class="errorMessage <?php echo isset($errors['password1']) ? 'error' : 'noerror'; ?>"
            >Invalid password. Password must be 8 characters or more.</span>
          </div>
          <div class="form-item column">
            <label for="password2">Re-enter New Password</label>
            <input 
              type="password" 
              name="password2" 
              id="password2"
              value="<?php echo $password2; ?>" 
              required
            />
            <span 
              class="errorMessage <?php echo isset($errors['password2']) ? 'error' : 'noerror'; ?>"
            >Passwords do not match.</span>
          </div>
          <button id="submit" type="submit" name="submit">Reset Password</button>
        </form>
        <?php endif; ?>
      </div>
    </main>
    <?php include 'includes/footer.php'; ?>
  </body>
</html>