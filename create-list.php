<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }

  /* Initiate Variables */
  $title       = "";
  $description = "";
  $password    = "";
  $expire      = "";
  $errors      = array();
  $today       = getdate();

  //Validate Form on Submission
  if (isset($_POST['create-list'])) 
  {
    /* Connect to Database */
    require './includes/library.php';
    $pdo = connectDB();

    //Title
    $title = $_POST['title'];
    $title = htmlspecialchars(strip_tags($title));
    if(empty($title )){
      $errors['title'] = true;
    }

    //Unique Title
    $query = "SELECT title FROM `GiftinatorLists` WHERE userId=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$user]);
    $stmnt = $stmnt->fetchAll();
    //Check that title is unique to user
    foreach ($stmnt as $list)
    {
      if($list['title'] == $title){
        $errors['uniqueTitle'] = true;
      }
    }

    //Password
    $password = $_POST['password'];
    if(strlen($password) < 8){
      $errors['password'] = true;
    }

    //Description
    $description = $_POST['description'];
    $description = htmlspecialchars(strip_tags($description));

    //Expiry Date 
    $expire = $_POST['expiryDate'];

    //No Errors 
    if(count($errors) === 0)
    {
      //hash password to store
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $query = "INSERT INTO `GiftinatorLists` (title, decrpt, passwd, expier, userId, dateCreated) 
        VALUES (?, ?, ?, ?, ?, NOW())";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$title, $description, $hash, $expire, $user]);

      //get the user id 
      $id = $pdo->lastInsertId();

      header("Location: view-list.php?id=$id");
      exit();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator";
      include 'includes/metadata.php';
    ?>
    <script defer src="scripts/create-list.js"></script>
  </head>

  <body>
    <?php include 'includes/header.php'; ?>
    <main>
      <?php include 'includes/nav.php'; ?>
      <div class="main-content">
        <h2>Create New List</h2>

        <form id="create-list" name="create-list" action="create-list.php" method="post">

          <div class="form-item column">
            <label for="title">List Title</label>
            <input 
              type="text" 
              name="title" 
              id="title" 
              placeholder="" 
              value="<?php echo $title; ?>" 
              required
            />
            <span class="errorMessage <?php echo isset($errors['title']) ? 'error' : 'noerror'; ?>"
            >List title can not be empty.</span>
            <span 
              class="errorMessage <?php echo isset($errors['uniqueTitle']) ? 'error' : 'noerror'; ?>"
            >You already have a list titled "<?php echo $title; ?>".</span>
          </div>

          <!-- Description -->
          <div class="form-item column">
		    	  <label for="description">List Description</label>
            <textarea 
              id="description" 
              name="description" 
              required
            ><?php echo $description;?></textarea>
          </div>
          
          <!-- List Password -->
          <div class="form-item column">
            <label for="passwd">List Password</label>
            <input 
              type="password" 
              name="password" 
              id="passwd" 
              value="<?php echo $password; ?>" 
              required
            />
            <span>This password will need to be inputted to be viewed by the public.</span>
            <span 
              class="errorMessage <?php echo isset($errors['password']) ? 'error' : 'noerror'; ?>"
            >Invalid password. Password must be 8 characters or more.</span>
          </div>

          <!-- Expiry Date -->
          <div class="form-item column">
            <label for="expiryDate">Expiry Date:</label>
            <input 
              type="text" 
              name="expiryDate" 
              id="expiryDate" 
              value="<?php echo $expire; ?>" 
              required
            />
            <span>After this date the list will no longer be visible to the public.</span>
          </div>

          <button id="create-list-button" type="submit" name="create-list">Create List</button>
        </form>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>

  </body>
</html>