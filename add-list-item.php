<?php 
  /* Check User Status */
  session_start();
  $user = $_SESSION['userId'] ?? false;

  /* Require Login */
  if ($user === false) {
    header("Location: login.php");
    exit();
  }

  /* Initiate Values */
  $name ="";
  $description = "";
  $link = "";
  $quantity = 1;
  $listId = $_GET['id'];
  $errors = array();
  $newname = null;

  //Validate Form on Submittion
  if (isset($_POST['addItem'])) 
  {
    /* Conect to Database */
    require './includes/library.php';
    $pdo = connectDB();

    //List Id 
    $list = $_POST['listTitle'];
    $query = "SELECT userId FROM `GiftinatorLists` WHERE listId=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$list]);
    $listOwner = $stmnt->fetch();
    //Check user owns list they are trying to add to 
    if($listOwner['userId'] != $user){
      $errors['listNotOwned'] = true;
    }

    //Name 
    $name = $_POST['name'];
    $name = htmlspecialchars(strip_tags($name));
    if(empty($name)) { $errors['name'] = true; }

    //Uniques Name
    $query = "SELECT `name` FROM `GiftinatorListItems` WHERE listId=?";
    $stmnt = $pdo->prepare($query);
    $stmnt->execute([$list]);
    $stmnt = $stmnt->fetchAll();
    foreach ($stmnt as $list2)
    {
      if(strcasecmp($list2['name'], $name) == 0){
        $errors['uniqueName'] = true;
      }
    }

    //Description
    $description = $_POST['description'];
    $description = htmlspecialchars(strip_tags($description));
    if (empty($description)){ $description = null; } 

    //URL
    $link = strip_tags($_POST['link']);
    if (empty($link)) { $link = null; }
    elseif (filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === false) {
      $errors['link'] = true;
    }

    //Quantity
    $quantity = $_POST['quantity'];
    if(strlen($quantity) <= 3)
    {
      $quantity = $quantity + 0;
      if (!is_int($quantity) || $quantity < 1) {$errors['quantity'] = true; } 
    }

    //No Errors 
    if(count($errors) == 0)
    {
      //Insert into the database
      $query = "INSERT INTO `GiftinatorListItems` (`name`, descrpt, `url`, bought, listId, userId, quantity)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
      $stmnt = $pdo->prepare($query);
      $stmnt->execute([$name, $description, $link, 0, $list, $user, $quantity]);

      $itemId = $pdo->lastInsertId();
      
      //Picture
      if(is_uploaded_file($_FILES['picture']['tmp_name']))
      {
        echo "Hello";
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
      
        //if still no errors
        if(count($errors) == 0)
        {//update the database with picture info
          $query = "UPDATE `GiftinatorListItems` SET picture = ? WHERE itemId = ?";
          $stmnt = $pdo->prepare($query);
          $stmnt->execute([$filename, $itemId]);  
        }
      }

      //if still no errors
      if(count($errors) == 0){
        //redirect to the lit they added the item to 
        header("Location: view-list.php?id=$list");
        exit();
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php 
			$PAGE_TITLE = "Gift-inator | Add List Item";
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
        <h2>Add Item to List</h2>
        <span class="<?= isset($errors['listNotOwned']) ? 'error' : 'noerror'; ?>">
          An error occurred, please try again.</span>
        <form 
          id="create-item" 
          name="create-item" 
          action="add-list-item.php?id=<?php echo $listId; ?>" 
          method="post"
				  enctype="multipart/form-data"
        >
          <div class="form-item column">
            <!-- Select the list they wish to add item to 
            have list preselected if it is one of their lists -->
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

          <div class="form-item column">
            <!-- Item Name -->
            <label for="name">Item Name</label>
            <input type="text" id="name" name="name" value="<?php echo $name; ?>" required />
            <span class="errorMessage <?php echo isset($errors['name']) ? 'error' : 'noerror'; ?>"
            >Item name can not be empty.</span>
            <span class="errorMessage <?php echo isset($errors['uniqueName']) ? 'error' : 'noerror'; ?>"
            >You already have an item named <?php echo $name; ?> in list this list.</span>
          </div>

          <div class="form-item column">
		    	  <label for="description">Item Description</label>
            <textarea id="description" name="description" ><?php echo $description; ?></textarea>
          </div>

          <div class="form-item column">		
					  <input type="hidden" name="MAX_FILE_SIZE" value="12400" />
					  <label for="picture">Upload Picture</label>
            <input type="file" id="picture" name="picture" />
            <span class="errorMessage <?php echo isset($errors['picture']) ? 'error' : 'noerror'; ?>"
            >Something went wrong with the photo. Please try again. Possibly ty a smaller file or a diffrent file extension.</span>
		  		</div>

          <div class="form-item column">
            <label for="link">URL</label>
            <input 
              type="text"
              id="link" 
              name="link" 
              value="<?php echo $link; ?>" 
            />
            <span class="errorMessage <?php echo isset($errors['link']) ? 'error' : 'noerror'; ?>"
            >Please enter a valid URL.</span>
          </div>

          <div class="form-item column">
            <label for="quantity">Quantity</label>
            <input 
              type="number" 
              id="quantity" 
              name="quantity" 
              value="<?php echo $quantity; ?>" 
              required 
            />
            <span class="errorMessage <?= isset($errors['quantity']) ? 'error' : 'noerror'; ?>">
            Please enter an integer.</span>
          </div>

          <button type="submit" id="addItem" name="addItem">Add Item to List</button>
          </form>
        </div>
		</main>
		<?php include "includes/footer.php"; ?>
	</body>
</html>