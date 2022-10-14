<?php
/*
 * Got connectDB, checkAndMoveFile, and sendEmail functions from the course notes
 *
*/

// Get the acutal document and webroot path for virtual directories
$direx = explode('/', getcwd());
define('DOCROOT', "/$direx[1]/$direx[2]/"); // /home/username/
define('WEBROOT', "/$direx[1]/$direx[2]/$direx[3]/"); //home/username/public_html

function connectDB() {
  // Load configuration as an array.
  $config = parse_ini_file(DOCROOT."pwd/config.ini");
  $dsn = "mysql:host=$config[domain];dbname=$config[dbname];charset=utf8mb4";

  try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
  } catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
  }

  return $pdo;
}

function checkAndMoveFile($filekey, $sizelimit, $newname){
  //modified from http://www.php.net/manual/en/features.file-upload.php
  //stolen from lecture notes
  try
  {
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if(!isset($_FILES[$filekey]['error']) || is_array($_FILES[$filekey]['error'])) 
    {
      throw new RuntimeException('Invalid parameters.');
    }
    // Check Error value.
    switch ($_FILES[$filekey]['error']) 
    {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new RuntimeException('No file sent.');
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new RuntimeException('Exceeded filesize limit.');
      default:
        throw new RuntimeException('Unknown errors.');
    }
    // You should also check filesize here.
    if ($_FILES[$filekey]['size'] > $sizelimit) {
      throw new RuntimeException('Exceeded filesize limit.');
    }
    // Check the File type  Note: this example assumes image upload
    if (exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_GIF
      and exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_JPEG
      and exif_imagetype( $_FILES[$filekey]['tmp_name']) != IMAGETYPE_PNG)
    {
      throw new RuntimeException('Invalid file format.');
    }
    // $newname should be unique and tested
    if (!move_uploaded_file($_FILES[$filekey]['tmp_name'], $newname))
    {
      throw new RuntimeException('Failed to move uploaded file.');
    }
    return false;
  } 
  catch (RuntimeException $e) 
  {
    return $e->getMessage();
  } 
}

function delete($id, $table, $pdo)
{
  if ($table === "GiftinatorUserInfo")
  {
    $query = "DELETE FROM `GiftinatorUserInfo` WHERE userId=?";
    $delete = $pdo->prepare($query);
    $delete->execute([$id]);
  }
  elseif ($table === "GiftinatorLists")
  {
    $query = "DELETE FROM `GiftinatorLists` WHERE listId=?";
    $delete = $pdo->prepare($query);
    $delete->execute([$id]);
  }
  elseif ($table === "GiftinatorListItems")
  {
    $query = "DELETE FROM `GiftinatorListItems` WHERE itemId=?";
    $delete = $pdo->prepare($query);
    $delete->execute([$id]);
  }
}

function disable($listId, $pdo)
{
  $today = getdate();
  $today = date(Y)."-".date(m)."-".date(d);
  $query = "UPDATE `GiftinatorLists` SET expier=?  WHERE listId=?";
  $disable = $pdo->prepare($query);
  $disable->execute([$today, $listId]);
}

function BoughtOne($Id, $pdo)
{
  $query = "SELECT bought FROM `GiftinatorListItems` WHERE itemId=?";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$Id]);
  $bought = $stmnt->fetch();
  $bought = $bought['bought'] +1;
  $query = "UPDATE `GiftinatorListItems` SET bought=? WHERE itemId=?";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$bought, $Id]);
}

function BoughtAll($Id, $pdo)
{
  $query = "SELECT quantity FROM `GiftinatorListItems` WHERE itemId=?";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$Id]);
  $quantity = $stmnt->fetch();
  $quantity = $quantity['quantity'] +0;
  $query = "UPDATE `GiftinatorListItems` SET bought=? WHERE itemId=?";
  $stmnt = $pdo->prepare($query);
  $stmnt->execute([$quantity, $Id]);
}

function sendEmail($email)
{
  require_once "Mail.php";  //this includes the pear SMTP mail library
  $from = "Gift-inator Password Reset System <noreply@loki.trentu.ca>";
  $to = $email;  //put user's email here
  $subject = "Giftinator Password Reset";
  $passCode = verificationCode();
  $link = "https://loki.trentu.ca/~megangillespie/3420/project/reset-password.php?verification=".$passCode;
  $body = "If you did not request a password reset please ignore this email. If you did request a 
  password reset please visit ".$link;
  $host = "smtp.trentu.ca";
  $headers = array ('From' => $from,
    'To' => $to,
    'Subject' => $subject);
  $smtp = Mail::factory('smtp',
    array ('host' => $host));
    
  $mail = $smtp->send($to, $headers, $body);
  if (PEAR::isError($mail)) 
    echo("<p>" . $mail->getMessage() . "</p>");
 // else 
   // echo("<p>Message successfully sent!</p>");
  return $passCode;
}

function verificationCode() {
  $characters = "0123456789_$@!ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  $randomString = "";
  for ($i = 0; $i < 15; $i++) 
  {
    $randomString .= $characters[rand(0, 39)];
  }
  return $randomString;
}
?>