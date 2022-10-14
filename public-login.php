<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  /* Initialize Variables */
  $listId   = $_GET['id'];
  $password = "";
  $error    = false;
  $active   = false;

  $query = "SELECT passwd, title, expier FROM `GiftinatorLists` WHERE listId = ?";
  $list=$pdo->prepare($query);
  $list->execute([$listId]);
  $list = $list->fetch();

  //Get Current Date and see if the list has already expired
  $today = getdate();
  $expiryDate = date_parse ($list['expier']);
  if ($today['year'] < $expiryDate['year']) { $active = true; }
  elseif ($today['year'] == $expiryDate['year'])
  {
    if ($today['mon'] < $expiryDate['month']) { $active = true; }
    elseif ($today['mon'] == $expiryDate['month'])
    {
      if ($today['mday'] < $expiryDate['day'])  { $active = true; }
    }
  }

  //Process Form on Submission
  if (isset($_POST['login'])) 
  {
    $password = $_POST['password'];

    if (password_verify($password, $list['passwd']))
    { //log in
        $_SESSION['publicAccess'] = $listId;
        header("Location: public-view-list.php?id=$listId");
        exit();
    }
    else {
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
        <?php if ($list == false): ?>
          <h2>The list you are trying to access dose not exist.</h2>
        <?php elseif (!$active):?>
          <h2>This list is no longer active.</h2>
          <p>If you believe this is a mistake please contact the owner of the list.</p>
        <?php else: ?>
        <h2>Login to View <?php echo $list['title']; ?> List</h2>
        <form id="public-login" name="public-login" action="public-login.php?id=<?php echo $listId; ?>" method="post">
          <!--Password -->
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

          <button type="submit" name="login" id="login">View Wish List</button>
        </form>
        <span class="<?php echo $error ? 'error' : 'noerror'; ?>">
          Incorrect password.</span>
        <?php endif; ?>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>
  </body>
</html>