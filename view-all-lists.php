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

  $query = "SELECT * FROM `GiftinatorLists` WHERE userId =? ORDER BY expier DESC";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$_SESSION['userId']]);
  $results = $stmnt->fetchAll();

  //Get Current Date
  $today = getdate();

  if (isset($_POST['createList']))
  {
    header("Location: create-list.php");
    exit();
  }
 
  if (isset($_POST['view']))
  {
    $ID2 = $_POST['view'];
    header("Location: view-list.php?id=$ID2");
    exit();
  }

  if (isset($_POST['edit']))
  {
    $ID2 = $_POST['edit'];
    header("Location: edit-list.php?id=$ID2");
    exit();
  }

  if (isset($_POST['delete']))
  {
    delete($_POST['delete'], "GiftinatorLists", $pdo);
    header("Location: view-all-lists.php");
  }

  if (isset($_POST['disable']))
  {
    $ID2 = $_POST['disable'];
    disable($ID2, $pdo); 
  }

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php 
      $PAGE_TITLE = "Gift-inator | My Wish Lists";
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
        <h2>My Wish Lists</h2>
        <form 
          class="buttons center"
          action="view-all-lists.php"
          method="post"
        >
          <button type="submit" id="createList" name="createList" value="">
            <abbr title="Create New List"><i class="fas fa-plus"></i></abbr>
          </button>
        </form>
        <ul>
          <?php foreach ($results as $row):
            // convert the string date taken in by the form into an array of its parts
            $expiryDate = date_parse ($row['expier']);
            // decide if it is active 
            if ($today['year'] <= $expiryDate['year'] &&
            $today['mon'] <= $expiryDate['month'] &&
            $today['mday'] < $expiryDate['day'])
              $active = true;
            else 
              $active = false;?>
            <li class="list <?php echo $active ? 'active' : 'inactive'; ?>">
              <div>
                <!-- list title -->
                <h3><?php echo $row['title']; ?></h3>
                <!-- date created -->
                <span><small>
                  <abbr title="Date Created"><i class="fas fa-calendar"></i></abbr>
                  <?php echo $row['dateCreated']; ?>
                </small></span>
                <!-- expiery date -->
                <span><small>
                  <abbr title="Expiry Date"><i class="fas fa-calendar-times"></i></abbr>
                  <?php echo $row['expier']; ?>
                </small></span>
                <!-- description -->
                <p><?php echo $row['decrpt']; ?></p>
              </div>
              <form 
                class="buttons left"
                action="view-all-lists.php" 
                method="post"
              >
                <button type="submit" class="view" name="view" value="<?php echo $row['listId'];?>">
                  <abbr title="View List"><i class="far fa-eye"></i></abbr>
                </button>
                <button type="submit" class="edit" name="edit" value="<?php echo $row['listId'];?>">
                  <abbr title="Edit List"><i class="fas fa-edit"></i></abbr> <!-- Edit -->
                </button>
                <button type="submit" class="delete" name="delete" value="<?php echo $row['listId'];?>">
                  <abbr title="Delete List"><i class="fas fa-trash-alt"></i></abbr>
                </button>
                <?php if($active): ?>
                  <button type="submit" class="disable" name="disable" value="<?php echo $row['listId'];?>">
                    <abbr title="Disable List"><i class="fas fa-calendar-times"></i></abbr>
                  </button>
                <?php endif ?>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </main>
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
  </body>
</html>