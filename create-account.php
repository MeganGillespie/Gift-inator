<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Logged-out */
  if ($user !== false) {
    header("Location: view-all-lists.php");
    exit();
  }

  /* Initialize Variables*/
  $email = "";
  $username = "";
  $password = "";
  $passwordCheck = "";
  $errors = array();

  //Validate form information
  if (isset($_POST['submit'])) 
  {
    /* Conect to Database */
    require './includes/library.php';
    $pdo = connectDB();

    //Email
    $email = $_POST['email'];
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false){
      $errors['email'] = true;
    }

    //Unique Email
    $query = "SELECT userId FROM `GiftinatorUserInfo` WHERE email=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$email]);
    $stmnt = $stmnt->fetch();
    if($stmnt != false){
      $errors['emailNotUnique'] = true;
    }

    //Username 
    $username = $_POST['username'];
    $query = "SELECT userId FROM `GiftinatorUserInfo` WHERE username=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$username]);
    $stmnt = $stmnt->fetch();
    if($stmnt != false){
      $errors['username'] = true;
    }

    //Password
    $password = $_POST['password'];
    if(strlen($password) < 8){
      $errors['password'] = true;
    }

    //Password Check
    $passwordCheck = $_POST['check-password'];
    if($password != $passwordCheck){
      $errors['passwordCheck'] = true;
    }

    //No Errors 
    if(count($errors) == 0){
      //hash pasword to store
      $hash = password_hash($password, PASSWORD_DEFAULT);

      //insert info into the database to create the account 
      $query = "INSERT INTO `GiftinatorUserInfo` (username, email, passwd) VALUES (?, ?, ?)";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$username, $email, $hash]);

      //get the user id 
      $id = $pdo->lastInsertId();

      //Login 
      session_start();
      $_SESSION['user'] = $username;
      $_SESSION['userId'] = $id;
      header("Location: view-all-lists.php");
      exit();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <?php
      $PAGE_TITLE = "Gift-inator | Create Account";
      include 'includes/metadata.php';
    ?>
    <script defer src="scripts/create-account.js"></script>
  </head>

  <body>

    <?php include 'includes/header.php'; ?>

    <main>
      <?php include 'includes/nav.php'; ?>

      <div class="main-content">
        <h2>Create Account</h2>

        <form id="create-account" name="create-account" action="create-account.php" method="post">

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
              class="errorMessage <?php echo isset($errors['email']) ? 'error' : 'noerror'; ?>"
            >Enter a valid email address.</span>
            <span 
              class="errorMessage  <?php echo isset($errors['emailNotUnique']) ? 'error' : 'noerror'; ?>"
            >This email is already associated with an account.</span>
          </div>

          <!-- Username -->
          <div class="form-item column">
            <label for="username">Username</label>
            <input 
              type="text" 
              name="username" 
              id="username" 
              value="<?php echo $username; ?>" 
              required
            />
            <span 
              class="errorMessage <?php echo isset($errors['username']) ? 'error' : 'noerror'; ?>"
            >Username already taken. Please enter a unique username.</span>
          </div>

          <!-- Password -->
          <div class="form-item column">
            <label for="passwd">Password</label>
            <input 
              type="password" 
              name="password" 
              id="passwd" 
              value="<?php echo $password; ?>" 
              required
            />
            <span 
              class="errorMessage <?php echo isset($errors['password']) ? 'error' : 'noerror'; ?>"
            >Invalid password. Password must be 8 characters or more.</span>
          </div>

          <!-- Check Password -->
          <div class="form-item column">
            <label for="check-password">Re-enter Password</label>
            <input 
              type="password" 
              name="check-password" 
              id="check-password" 
              value="<?php echo $passwordCheck; ?>" 
              required
            />
            <span 
              class="errorMessage <?php echo isset($errors['passwordCheck']) ? 'error' : 'noerror'; ?>"
            >Passwords do not match.</span>
          </div>

          <button id="submit" type="submit" name="submit">Create Account</button>
        </form>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>
  </body>
</html>
