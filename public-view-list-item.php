<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  $itemId = $_GET['id'];

  $query = "SELECT * FROM `GiftinatorListItems` WHERE itemId = ?";
  $item = $pdo->prepare($query);
  $item->execute([$itemId]);
  $item = $item->fetch();

  $listId = $item['listId'];
  

  if (!isset($_SESSION['publicAccess']))
  {
    header("Location: public-login.php?id=$listId");
    exit();
  }
  elseif($_SESSION['publicAccess'] != $listId)
  {
    header("Location: public-login.php?id=$listId");
    exit();
  }

  if (isset($_POST['bought']))
  {
    BoughtOne($_POST['bought'], $pdo);
    header("Location: public-view-list-item.php?id=$itemId");
    exit();
  }

  if (isset($_POST['boughtAll']))
  {
    BoughtAll($_POST['boughtAll'], $pdo);
    header("Location: public-view-list-item.php?id=$itemId");
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
          <span class="quantity">x<?php echo ($item['quantity'] - $item['bought']); ?></span>
        </h2>

        <form  
          class="buttons center"
          action="public-view-list-item.php?id=<?php echo $itemId; ?>" 
          method="post"
        >
          <?php if ($item['bought'] < $item['quantity'])
            $itemActive = true;
          else 
            $itemActive = false; 
          ?>

          <!-- Bought 1 -->
          <?php if($itemActive): ?>
            <button type="submit" class="bought" name="bought" value="<?php echo $itemId; ?>">
              <abbr title="Mark 1 as Bought"><i class="fas fa-shopping-bag"></i> x1</abbr>
            </button>
          <?php else: ?> 
            <button class="inactive">
              <abbr title="Mark 1 as Bought"><i class="fas fa-shopping-bag"></i></abbr>
            </button>
          <?php endif; ?>
          
          <!-- Bought All -->
          <?php if($itemActive): ?>
            <button type="submit" class="boughtAll" name="boughtAll" value="<?php echo $itemId; ?>">
              <abbr title="Mark bought"><i class="fas fa-check"></i> <i class="fas fa-shopping-bag"></i></abbr>
            </button>
          <?php else: ?> 
            <button class="inactive">
            <abbr title="Mark bought"><i class="fas fa-check"></i> <i class="fas fa-shopping-bag"></i></abbr>
            </button>
          <?php endif; ?>
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