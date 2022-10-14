<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator";
      include 'includes/metadata.php';
    ?>
  </head>

  <body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <main>
      <!-- Navigation -->
      <?php include 'includes/nav.php'; ?>
      <div class="main-content">
        <h3>Welcome to the Gift-inator!!!</h3>
        <p></p>
        <h4> What is Gift-inator?</h4>
        <p>This is a site where you can create wish lists and share them with 
        your friends, kind of like a gift registry for a baby shower or a 
        wedding but this can be for any occasion from any store or site.</p>
        <p>Gift-inator eliminates the need to make several different Christmas 
        or bitthday lists and distribute them amongst family and friends. On 
        Gift-inator you can create one list and then distribute the public link
        to view to family and friends. Gift-inator also eliminates the need for
        said family members and friends to coordinate gifts because Gift-inator 
        does it for you. When browsing the public version of the list friends 
        and family can mark items as bought when they decide to get you that item.</p>
        <h4><strong>Gift-inator ... Gift Giving Made Fun</strong></h4>
      </div>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
  </body>
</html>