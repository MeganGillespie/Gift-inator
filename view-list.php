<?php
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }

  /* Connect to database */
  require './includes/library.php';
  $pdo = connectDB();

  /* Initiate Varibles */
  $id = $_GET['id'];
  $today = getdate();

  $query = "SELECT * FROM `GiftinatorLists` WHERE listId = ?";
  $list = $pdo->prepare($query);
  $list->execute([$id]);
  $list = $list->fetch();

  //Make sure user owns list
  if ($list['userId'] === $user + 0) 
  { 
    $owned = true;
    $query = "SELECT * FROM `GiftinatorListItems` WHERE listId = ? ORDER BY `name` ASC";
    $items = $pdo->prepare($query);
    $items->execute([$id]);
    $items = $items->fetchAll();

  }
  else 
  { 
    $owned = false; 
  }

  $expiryDate = date_parse ($list['expier']);
  if ($today['year'] <= $expiryDate['year'] &&
  $today['mon'] <= $expiryDate['month'] &&
  $today['mday'] < $expiryDate['day'])
    $active = true;
  else 
    $active = false;

  if (isset($_POST['addItem']))
  {
    $ID2 = $_POST['addItem'];
    header("Location: add-list-item.php?id=$ID2");
    exit();
  }

  if (isset($_POST['editlist']))
  {
    $ID2 = $_POST['editlist'];
    header("Location: edit-list.php?id=$ID2");
    exit();
  }

  if (isset($_POST['deletelist']))
  {
    delete($_POST['deletelist'], "GiftinatorLists", $pdo);
    header("Location: view-all-lists.php");
    exit();
  }
  
  if (isset($_POST['viewitem']))
  {
    $ID2 = $_POST['viewitem'];
    header("Location: view-list-item.php?id=$ID2");
    exit();
  }

  if (isset($_POST['edititem']))
  {
    $ID2 = $_POST['edititem'];
    header("Location: edit-list-item.php?id=$ID2");
    exit();
  }

  if (isset($_POST['deleteitem']))
  { 
    delete($_POST['deleteitem'], "GiftinatorListItems", $pdo);
    header("Location: view-all-lists.php");
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
        <?php if ($owned == false):
          echo "You do not have access to this list.";
        else: ?>
          <div>
            <div>
              <h2 class="<?php echo $active? 'active' : 'inactive'; ?>"><?php echo $list['title']; ?></h2>
              <form
                class="buttons center"
                action="view-list.php?id=<?php echo $id; ?>" 
                method="post"
              >
                <button type="submit" class="addItem" name="addItem" value="<?php echo $id;?>">
                  <abbr title="Add Item"><i class="fas fa-plus"></i></abbr>
                </button>
                <button type="submit" class="edit" name="editlist" value="<?php echo $id;?>">
                  <abbr title="Edit List"><i class="fas fa-edit"></i></abbr> <!-- Edit -->
                </button>
                <button type="submit" class="delete" name="deletelist" value="<?php echo $id;?>">
                  <abbr title="Delete List"><i class="fas fa-trash-alt"></i></abbr>
                </button>
                <button type="submit" class="link" name="link">
                  <abbr title="Copy link to share with your friends to clipboard."><i class="fas fa-link"></i> <i class="fas fa-clipboard"></i></abbr>
                </button>
                <input type="text" class="hidden" name="url" id="url" value="loki.trentu.ca/~megangillespie/3420/project/public-view-list.php?id=<?php echo $id;?>" />
              </form>
              <span><small>
                <!-- date created -->
                <abbr title="Date Created"><i class="fas fa-calendar"></i></abbr>
                <?php echo $list['dateCreated']; ?>
              </small></span>
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
                  <li class="list">
                    <!--item name -->
                    <div>
                      <h3>
                        <?php echo $row['name']; ?>
                        <span class="quantity">x<?php echo ($row['quantity']); ?></span>
                      </h3>
                    <?php if ($row['picture'] !== null):?>
                        <!-- Image -->
                        <figure>
                          <img src="/~megangillespie<?php echo $row['picture']; ?>" alt=" " />
                        </figure>
                      <?php elseif($row['descrpt'] !== null):?>
                        <!-- description -->
                        <p><?php echo $row['descrpt']; ?></p>
                      <?php elseif ($row['url'] !== null):?>
                        <!-- URL -->
                        <a href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a>
                      <?php endif; ?>
                    </div>
                    <form 
                      class="buttons left"
                      action="view-list.php?<?php echo $id; ?>" 
                      method="post"
                    >
                      <button type="submit" class="viewItem" name="viewitem" value="<?php echo $row['itemId']; ?>">
                        <abbr title="View Item"><i class="far fa-eye"></i></abbr> <!-- View -->
                      </button>
                      <button type="submit" class="edit" name="edititem" value="<?php echo $row['itemId'];?>">
                        <abbr title="Edit Item"><i class="fas fa-edit"></i></abbr> <!-- Edit -->
                      </button>
                      <button type="submit" class="delete" name="deleteitem" value="<?php echo $row['itemId'];?>">
                        <abbr title="Delete Item"><i class="fas fa-trash-alt"></i></abbr>
                      </button>
                    </form>
                    
                    <div class="pop-up" id="<?php echo $row['itemId']; ?>">
                      <div class="main-content pop-up-content">
                        <span class="close">&times;</span>
                        <!-- Name & Quantity -->
                        <h2>
                          <?php $item = $row;
                          $itemId = $item['itemId'];
                          echo $item['name']; ?>
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
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>