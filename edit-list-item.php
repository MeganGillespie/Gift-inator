<?php 
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }

  $itemId      = $_GET['id'];

  /* Connect to Database */
  require './includes/library.php';
  $pdo = connectDB();

  $query = "SELECT * FROM `GiftinatorListItems` WHERE itemId = ?";
  $item = $pdo->prepare($query);
  $item->execute([$itemId]);
  $item = $item->fetch();

  /* Initiate Values */
  $name        = $item['name'];
  $picture     = $item['picture'];
  $description = $item['descrpt'];
  $link        = $item['url'];
  $listId      = $item['listId'];
  $userId      = $item['userId'];
  $quantity    = $item['quantity'];
  $errors      = array();
 

  //Validate Form on Submission
  if (isset($_POST['saveItem'])) 
  {
    //List Id 
    $listId = $_POST['listTitle'];
    $query = "SELECT userId FROM `GiftinatorLists` WHERE listId=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$listId]);
    $listOwner = $stmnt->fetch();
    //Check user owns list they are trying to add to 
    if($listOwner['userId'] != $user){
      $errors['listNotOwned'] = true;
    }

    //Name 
    if ($name !=  $_POST['name'])
    {
      $name = $_POST['name'];
      $name = htmlspecialchars(strip_tags($name));
      if(empty($name)) { $errors['name'] = true; }

      //Unique Name
      $query = "SELECT name FROM `GiftinatorListItems` WHERE listId=?";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$listId]);
      $stmnt = $stmnt->fetchAll();
      foreach ($stmnt as $list2)
      {
        if(strcasecmp($list2['name'], $name) == 0){
          $errors['uniqueName'] = true;
        }
      }
   }

    //Description
    $description = $_POST['description'];
    $description = htmlspecialchars(strip_tags($description));
    if (empty($description)){ $description = null; }

    //Picture
    if(is_uploaded_file($_FILES['picture']['tmp_name']))
    {
      $uniqueID = "/item".$itemId;
      $path = '/home/megangillespie/public_html';
      $fileroot = '/www_data';
      $filename = $_FILES['picture']['name']; //get the original file name for extension
      $exts = explode(".", $filename); // split based on period
      $ext = $exts[count($exts)-1]; //take the last split (contents after last period)
      $filename = $fileroot.$uniqueID.".".$ext;  //build new filename
      $newname = $path.$filename; //add path the file name
      $errorMessage = checkAndMoveFile('picture', 10240, $newname);
      if ($errorMessage !== false) { $errors['picture'] = true; }
      else
      {
        $query = "UPDATE `GiftinatorListItems` SET picture=? WHERE itemId=$itemId";
        $stmnt = $pdo->prepare($query);
        $stmnt->execute([$filename]);
      }
    } 

    //URL
    $link = strip_tags($_POST['link']);
    if (empty($link)) { $link = null; }
    elseif (filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === false) {
      $errors['link'] = true;
    }

    //Quantity
    $quantity = $_POST['quantity'];
    if(strlen($quantity) <= 3 )
    {
      $quantity = $quantity + 0;
      if (!is_int($quantity)) {$errors['quantity'] = true; } 
    }

    //No Errors 
    if(count($errors) == 0)
    { //insert into the database
      $query = "UPDATE `GiftinatorListItems` SET `name`=?, descrpt = ?, `url`=?, listId=?, quantity=?
        WHERE itemId=$itemId";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$name, $description, $link, $listId, $quantity]);

      //redirect to the lit they added the item to 
      header("Location: view-list.php?id=$listId");
      exit();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php 
			$PAGE_TITLE = "Gift-inator | Edit List Item";
			include "includes/metadata.php";
    ?>
    <script defer src="scripts/add-list-item.js"></script>
  </head>
    
	<body>
  	<!-- Header -->
		<?php include "includes/header.php"; ?>
		
		<main>
      <!-- Nav -->
      <?php include "includes/nav.php"; ?>
      <div class="main-content"> 
        <?php //Validate user is owner of item 
        if ($user != $userId):?>
          <h2>You do not have access to edit this item</h2>
        <?php else: ?>
          <h2>Edit Item</h2>
          <form 
            id="edit-list-item" 
            name="edit-list-item" 
            action="edit-list-item.php?id=<?php echo $itemId; ?>" 
            method="post"
				    enctype="multipart/form-data"
          >

            <!-- Select the list they wish to add item to 
            have list preselected if it is one of their lists -->
            <div class="form-item column">
              <label for="listTitle">List</label>
              <select name="listTitle" id="listTitle" required>
                <option value="" >-- Select --</option>
                <?php foreach ($titles as $id=>$opp): 
                  if ($id === $listId):?>
                  <option value="<?php echo $id; ?>" selected><?php echo $opp; ?></option>
                  <?php else: ?>
                    <option value="<?php echo $id; ?>"><?php echo $opp; ?></option>
                  <?php endif;
                endforeach; ?>
              </select>
            </div>

            <!-- Item Name -->
            <div class="form-item column">
              <label for="name">Item Name</label>
              <input type="text" id="name" name="name" value="<?php echo $name; ?>" required />
              <span class="<?php echo isset($errors['name']) ? 'error' : 'noerror'; ?>"
              >Item name can not be empty.</span>
              <span class="<?php echo isset($errors['uniqueName']) ? 'error' : 'noerror'; ?>"
              >You already have an item named <?php echo $name; ?> in list <?php echo $titles[$listId]; ?>.</span>
            </div>

            <!-- Description -->
            <div class="form-item column">
		    	    <label for="description">Item Description</label>
              <textarea id="description" name="description"><?php echo $description; ?></textarea>
            </div>

            <!-- Picture -->
            <div class="form-item column">		
              <label for="picture">Picture</label>
              <?php if ($item['picture'] != null): ?>
                <figure>
                  <img src="/~megangillespie<?php echo $picture; ?>" alt=" " />
                </figure>
              <?php endif;?>
              <input type="hidden" name="MAX_FILE_SIZE" value="12400" />
              <input type="file" id="picture" name="picture" />
              <span class="<?php echo isset($errors['picture']) ? 'error' : 'noerror'; ?>"
              >Something went wrong with the photo. Please try again. Possibly ty a smaller file or a diffrent file extension.</span>
		  		  </div>

            <!-- URL -->
            <div class="form-item column">
              <label for="link">URL</label>
              <input type="text" id="link" name="link" value="<?php echo $link; ?>" />
              <span class="<?php echo isset($errors['link']) ? 'error' : 'noerror'; ?>"
              >Please enter a valid URL.</span>
            </div>

            <!-- Quantity -->
            <div class="form-item column">
              <label for="quantity">Quantity</label>
              <input type="number" id="quantity" name="quantity" value="<?php echo $quantity; ?>" required />
              <span class="<?= isset($errors['quantity']) ? 'error' : 'noerror'; ?>">
              Please enter an integer.</span>
            </div>

            <button type="submit" id="saveItem" name="saveItem">Save Changes Made To Item</button>
          </form>
        <?php endif; ?>
      </div>
    </main>
    <!-- Footer -->
		<?php include "includes/footer.php"; ?>
	</body>
</html>