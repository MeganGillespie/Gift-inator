<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }

  $listId = $_GET['id'];

  /* Connect to the Database */
  require './includes/library.php';
  $pdo = connectDB();

  $query = "SELECT * FROM `GiftinatorLists` WHERE listId=?";
  $list = $pdo->prepare($query);
  $list->execute([$listId]);
  $list = $list->fetch();

  /* Initiate Variables */
  $title           = $list['title'];
  $description     = $list['decrpt'];
  $expiryDate     = $list['expier'];
  $password        = "";
  $currentPassword = "";
  $confirm         = false;
  $validate        = false;
  $errors          = array();

  if ($user == $list['userId']) { $owned = true; }
  else { $owned = false; }

  //Validate Form on Submission
  if (isset($_POST['edit-list'])) 
  {
    //Title
    $title = $_POST['title'];
    $title = htmlspecialchars(strip_tags($title));
    if(empty($title )){
      $errors['title'] = true;
    }

    //Unique Title
    $query = "SELECT listId, title FROM `GiftinatorLists` WHERE userId=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$user]);
    $stmnt = $stmnt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($stmnt as $key=>$listTitle)
    {
      if($key != $listId && $title == $listTitle)
      {
        $errors['uniqueTitle'] = true;
      }
    }

    //Description
    $description = $_POST['description'];
    $description = htmlspecialchars(strip_tags($description));

    //Expiry Date 
    $expire = $_POST['expiryDate'];

    //Password
    $password = $_POST['password'];
    if (!empty($password))
    {
      $validate = true;
      
      if (strlen($password) < 8)
      {
        $errors['password'] = true;
      }

      //Current Password
      if($validate && isset($_POST['currentPassword']))
      {
        $currentPassword = $_POST['currentPassword'];
        $query = "SELECT passwd FROM `GiftinatorUserInfo` WHERE userId=$user";
        $accountPassword = $pdo->query($query)->fetch();

        if (password_verify($currentPassword, $accountPassword['passwd']))
        {
          //Hash Password
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $confirm = true;

          $query = "UPDATE `GiftinatorLists` SET passwd=? WHERE listId = $listId";
          $stmnt = $pdo->prepare($query);
          $stmnt->execute([$hash]);
        }
        else { $errors['currentPassword'] = true; }
      }
    }

    //no errors 
    if(count($errors) === 0)
    {
      $query = "UPDATE `GiftinatorLists` SET title=?, decrpt=?, expier=?  WHERE listId=?";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$title, $description, $expire, $listId]);

      if (($validate && $confirm) || !$validate)
      {
        header("Location: view-list.php?id=$listId");
        exit();
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator | Edit List";
      include 'includes/metadata.php';
    ?>
    <script defer src="scripts/edit-list.js"></script>
  </head>

  <body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    <main>
      <!-- Navigation -->
      <?php include 'includes/nav.php'; ?>

      <?php if (!$owned):?>
        <div class="main-content">
          <h2>An error has occurred.</h2>
          <p>You do not have access to edit list "<?php echo $title; ?>"</p>
        </div>
      <?php else: ?>
        <div class="main-content">
          <h2>Edit List "<?php echo $title; ?>"</h2>

          <form 
            id="edit-list" 
            name="edit-list" 
            action="edit-list.php?id=<?php echo $listId; ?>" 
            method="post"
          >

            <!-- List Title -->
            <div class="form-item column">
              <label for="title">List Title</label>
              <input 
                type="text" 
                id="title"
                name="title"  
                value="<?php echo $title; ?>" 
                required
              />
              <span class="errorMessage <?php echo isset($errors['title']) ? 'error' : 'noerror'; ?>"
              >List title can not be empty.</span>
              <span class="errorMessage <?php echo isset($errors['uniqueTitle']) ? 'error' : 'noerror'; ?>"
              >You already have a list titled "<?php echo $title; ?>".</span>
            </div>

            <!-- List Description -->
            <div class="form-item column">
		    	    <label for="description">List Description</label>
              <textarea 
                id="description" 
                name="description" 
                required
              ><?php echo $description; ?></textarea>
            </div>
          
            <!-- List Password -->
            <div class="form-item column">
              <label for="password">List Password</label>
              <input 
                type="password" 
                id="password" 
                name="password" 
                value="<?php echo $password; ?>" 
              />
              <span>This password will need to be inputted to be viewed by the public.</span>
              <span class="errorMessage <?php echo isset($errors['password']) ? 'error' : 'noerror'; ?>"
              >Invalid password. Password must be 8 characters or more.</span>
            </div>

            <!-- Account Password -->
            <?php if ($validate):?>
              <div class="form-item column">
                <label for="currentPassword">Account Password</label>
                <input 
                  type="password" 
                  name="currentPassword" 
                  id="currentPassword" 
                  value="<?php echo $currentPassword; ?>" 
                  required
                />
                <span class="error"> You need to input your current account password to change the list password.</span>
                <span class="errorMessage <?php echo isset($errors['currentPassword']) ? 'error' : 'noerror'; ?>"
                >Invalid password.</span>
              </div>
            <?php endif;?>
      
            <!-- Expiry Date -->
            <div class="form-item column">
              <label for="expiryDate">Expiry Date:</label>
              <input 
                type="date" 
                id="expiryDate" 
                name="expiryDate" 
                value="<?php echo $expiryDate; ?>" 
                required
              />
              <span>After this date the list will no longer be visible to the public.</span>
            </div>

            <button type="submit" id="edit-list-button" name="edit-list">Save List Info</button>
          </form>
        </div>
      <?php endif; ?>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>