<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  $listId = $_GET['id'];
 
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
 
  $query = "SELECT * FROM `GiftinatorLists` WHERE listId = ?";
  $list=$pdo->prepare($query);
  $list->execute([$listId]);
  $list = $list->fetch();

  $query = "SELECT * FROM `GiftinatorListItems` WHERE listId =? ORDER BY `name` ASC";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$listId]);
  $items = $stmnt->fetchAll();

  if (isset($_POST['viewItem']))
  {
    $ID2 = $_POST['viewItem'];
    header("Location: public-view-list-item.php?id=$ID2");
    exit();
  }

  if (isset($_POST['bought']))
  {
    $ID2 = $_POST['bought'];
    BoughtOne($ID2, $pdo);
    header("Location: public-view-list.php?id=$listId");
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator | ".$list['title'];
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
        <div>
          <!-- Title -->
          <h2><?php echo $list['title']; ?></h2>
          <span><small>
            <!-- expiery date -->
            <abbr title="Expiry Date"><i class="fas fa-calendar-times"></i></abbr>
            <?php echo $list['expier']; ?>
          </small></span>
          <p><?php echo $list['decrpt']; ?></p>
        </div>
        <div>
          <ul>
            <?php foreach ($items as $row):
              if ($row['bought'] < $row['quantity'])
                $itemActive = true;
              else 
                $itemActive = false; ?>
              <li class="list <?php $itemActive ? 'active' : 'inactive' ?>">
                <div>
                  <!--item name & quantity-->
                  <h3>
                    <?php echo $row['name']; 
                    $quantity = $row['quantity'] - $row['bought'];?>
                    <span class="quantity">x<?php echo $quantity; ?></span>
                  </h3>
                  <!-- Image -->
                  <?php if ($row['picture'] !== null):?>
                    <figure>
                      <img src="/~megangillespie<?php echo $row['picture']; ?>" alt=" " />
                    </figure>
                  <?php elseif($row['descrpt'] !== null):?>
                    <!-- Description -->
                    <p><?php echo $row['descrpt']; ?></p>
                  <?php elseif ($row['url'] !== null):?>
                    <!-- URL -->
                    <a href="<?php echo $item['url']; ?>"><?php echo $item['url']; ?></a>
                  <?php endif; ?>
                </div>
                <form  
                  class="buttons left"
                  action="public-view-list.php?id=<?php echo $listId; ?>" 
                  method="post"
                >
                  <button type="submit" class="viewItem" name="viewitem" value="<?php echo $row['itemId']; ?>">
                    <abbr title="View Item"><i class="far fa-eye"></i></abbr> <!-- View -->
                  </button>
                  <?php if($itemActive): ?>
                    <button type="submit" class="bought" name="bought" value="<?php echo $row['itemId'];?>">
                      <abbr title="Mark 1 as Bought"><i class="fas fa-shopping-bag"></i> x1</abbr>
                    </button>
                  <?php else: ?> 
                    <button class="inactive">
                      <abbr title="Mark 1 as Bought"><i class="fas fa-shopping-bag"></i></abbr>
                    </button>
                  <?php endif; ?>
                </form>
              
                <div class="pop-up" id="<?php echo $row['itemId']; ?>">
                  <div class="main-content pop-up-content">
                    <span class="close">&times;</span>
                    <!-- Name & Quantity -->
                    <h2>
                      <?php $item = $row;
                      $itemId = $row['itemId'];
                      echo $item['name']; ?>
                      <span class="quantity">x<?php echo ($item['quantity'] - $item['bought']); ?></span>
                    </h2>

                    <form  
                      class="buttons center"
                      action="public-view-list-item.php?itemid=<?php echo $itemId; ?>&listid=<?php echo $listId; ?>" 
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
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>