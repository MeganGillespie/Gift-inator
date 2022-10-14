<?php 
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: index.php");
    exit();
  }
  
  //Destroy Session
  session_destroy();
  $user = false;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator | Log out";
      include 'includes/metadata.php';
    ?>
  </head>

  <body>

    <?php include 'includes/header.php'; ?>

    <main>
      <?php include 'includes/nav.php'; ?>
      <div class="main-content">
        <h3>You have successfully logged out</h3>
      </div>
    </main>

    <?php include 'includes/footer.php'; ?>

  </body>
</html>