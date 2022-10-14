<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Logged-out */
  if ($user !== false) {
    header("Location: view-all-lists.php");
    exit();
  }

  /* Initiate Variables */
  $email  = "";
  $sent   = false;
  $errors = array();

  //Validate Form on Submission
  if (isset($_POST['send-email'])) {

    //Email
    $email = $_POST['email'];
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
      $errors['not-eamil'] = true;
    }

    if (!isset($errors['not-eamil']))
    {
      /* Connect to Database */
      require './includes/library.php';
      $pdo = connectDB();

      $query = "SELECT userId FROM `GiftinatorUserInfo` WHERE email=?";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$email]);
      $stmnt = $stmnt->fetch();
      if ($stmnt === false)
      {
        $errors['noAccount'] = true;
      }
    }

    if(count($errors) == 0)
    {
      $_SESSION['tempUser'] = $stmnt['userId'];
      $_SESSION['vCode'] = sendEmail($email);
      $sent = true;
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
		    <h2>Forgot Password</h2>
        <?php if ($sent): ?>
        <p>An email with a link to reset your password has been sent to your account.</p>
        <?php else: ?>
		    <form id="forgot-password" name="forgot-password" action="forgot-password.php" method="post">
          <!-- Email -->
          <div class="form-item column">
            <label for="email">Email Address</label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              placeholder="example@gamil.com" 
              value="<?php echo $email; ?>" 
              required
            />
            <span 
             class="errorMessage <?php echo isset($errors['noAccount']) ? 'error' : 'noerror'; ?>"
            >Email is not associated with any accounts.</span>
            <span 
              class="errorMessage <?php echo isset($errors['not-email']) ? 'error' : 'noerror'; ?>"
            >Please enter a valid email.</span>
          </div>
		      <button id="send-email" type="submit" name="send-email">Send Email</button>
		    </form>
        <?php endif; ?>
      </div>
    </main>
    <?php include 'includes/footer.php'; ?>
  </body>
</html>