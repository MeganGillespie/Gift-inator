<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false)
  {
    header("Location: login.php");
    exit();
  }
  
  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  $query = "SELECT * FROM `GiftinatorUserInfo` WHERE userId=?";
  $userInfo = $pdo->prepare($query);
  $userInfo->execute([$user]);
  $userInfo = $userInfo->fetch();

  /* Initialize Variables */
  $email           = $userInfo['email'];
  $username        = $userInfo['username'];
  $accountPassword = $userInfo['passwd'];
  $currentPassword = "";
  $newPassword     = "";
  $confirm         = false;
  $validate        = false;
  $errors          = array();

  //Validate Form on Submission 
  if (isset($_POST['submit'])) 
  {
    //email
    $email = $_POST['email'];
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
    {
      $errors['email'] = true;
    }

    //Unique Email
    $query = "SELECT userId FROM `GiftinatorUserInfo` WHERE email=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$email]);
    if($stmnt === false){
      $errors['emailNotUnique'] = true;
    }

    //New Password
    $newPassword = $_POST['newPassword'];
    if (!empty($newPassword))
    {
      $validate = true;
  
      if (strlen($newPassword) < 8)
      {
        $errors['password'] = true;
      }

      //Current Password
      if($validate && isset($_POST['currentPassword']))
      {
        $currentPassword = $_POST['currentPassword'];
        if (password_verify($currentPassword, $accountPassword))
        {
          //Hash password
          $hash = password_hash($newPassword, PASSWORD_DEFAULT);
          $confirm = true;

          $query = "UPDATE `GiftinatorUserInfo` SET passwd=? WHERE userId = $user";
          $stmnt = $pdo->prepare($query);
          $stmnt->execute([$hash]);
        }
        else { $errors['currentPassword'] = true; }
      }
    }

    //No Errors 
    if(count($errors) === 0)
    {
      //Update Email
      $query = "UPDATE `GiftinatorUserInfo` SET email=? WHERE userId=$user";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$email]);

      if (($validate && $confirm) || !$validate)
      {
        header("Location: view-all-lists.php");
        exit();
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php
      $PAGE_TITLE = "Gift-inator | Edit Account";
      include 'includes/metadata.php';
    ?>
    <script defer src="scripts/edit-account.js"></script>
  </head>

  <body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main>
      <!-- Nav -->
      <?php include 'includes/nav.php'; ?>

      <div class="main-content">
      <h2>Edit Account Info</h2>
        <!-- Username -->
        <div class="input">Username: <span class="inactive"><?php echo $username; ?></span></div>

        <form id="edit-account" name="edit-account" action="edit-account.php" method="post">
          <!-- Email -->
          <div class="form-item column">
            <label for="email">Update Email Address</label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              placeholder="example@gamil.com" 
              value="<?php echo $email; ?>" 
            />
            <span class="errorMessage <?php echo isset($errors['email']) ? 'error' : 'noerror'; ?>"
            >Enter a valid email address.</span>
            <span class="errorMessage <?php echo isset($errors['emailNotUnique']) ? 'error' : 'noerror'; ?>"
            >This email is already associated with an account.</span>
          </div>
  
          <!-- New Password -->
          <div class="form-item column">
            <label for="newPassword">Update Password (optional)</label>
            <input 
              type="password" 
              name="newPassword" 
              id="newPassword" 
              value="<?php echo $newPassword; ?>" 
            />
            <span class="errorMessage <?php echo isset($errors['password']) ? 'error' : 'noerror'; ?>"
            >Invalid password. Password must be 8 characters or more.</span>
          </div>

          <!-- Current Password -->
          <?php if ($validate):?>
            <div class="form-item column">
              <label for="currentPasswrd">Current Account Password</label>
              <input 
                type="password" 
                name="currentPassword" 
                id="currentPassword" 
                value="<?php echo $currentPassword; ?>" 
                required
              />
              <span class="errorMessage <?php echo isset($errors['currentPassword']) ? 'error' : 'noerror'; ?>"
              >Invalid password.</span>
            </div>
          <?php endif; ?>

          <button type="submit" id="submit" name="submit">Save Account Info</button>
        </form>
      </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>