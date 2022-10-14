<?php   
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }
  
  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();
 
  $itemId = $_GET['id'];
  $query = "SELECT * FROM `GiftinatorListItems` WHERE itemId = ?";
  $item = $pdo->prepare($query);
  $item->execute([$itemId]);
  $item = $item->fetch();
 
  if($item['userId'] === $user) { $owned = true; }
  else { $owned = false; }
 
  if (isset($_POST['edititem']))
  {
    header("Location: edit-list-item.php?id=$itemId");
    exit();
  }

  if (isset($_POST['deleteitem']))
  { 
    $listId = $item['listId'];
    delete($itemId, "GiftinatorListItems", $pdo); 
    header("Location: view-list.php?id=$listId");
    exit();
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator | ".$item['name'];
      include 'includes/metadata.php';
    ?>
  </head>

  <body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    <main>
      <!-- Nav -->
      <?php include 'includes/nav.php'; ?>
      <div class="main-content">
        <!-- Name & Quantity -->
        <h2>
          <?php echo $item['name']; ?>
          <span class="quantity">x<?php echo ($item['quantity']); ?></span>
        </h2>

        <form 
          class="buttons center"
          action="view-list-item.php?id=<?php echo $itemId; ?>" 
          method="post"
        >
          <!-- Edit -->
          <button type="submit" class="edit" name="edititem" value="<?php echo $itemId; ?>">
            <abbr title="Edit Item"><i class="fas fa-edit"></i></abbr>
          </button>
          <!-- Delete -->
          <button type="submit" class="delete" name="deleteitem" value="<?php echo $itemId; ?>">
            <abbr title="Delete Item"><i class="fas fa-trash-alt"></i></abbr>
          </button>
        </form>

        <!-- Image -->
        <?php if ($item['picture'] !== null):?>
          <figure>
            <img src="/~megangillespie<?php echo $item['picture']; ?>" alt=" " />
          </figure>
        <?php endif; 

        // Description
        if ($item['descrpt'] !== null):?>
          <p><?php echo $item['descrpt']; ?></p>
        <?php endif; 

        // URL
        if ($item['url'] !== null):?>
          <a href="<?php echo $item['url']; ?>"><?php echo $item['url']; ?></a>
        <?php endif; ?>

      </div>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>