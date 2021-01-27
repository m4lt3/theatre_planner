<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
require_once dirname(__DIR__) . "/php/utils/database.php";

if(!$loggedIn){
  header("location:../index.php");
}
$failure = "";
$success = "";
if(isset($_POST["newPassword"])){
  $db = new DBHandler();
  $pw = $db->prepareQuery("SELECT Password FROM USERS WHERE UserID=?","i", array($_SESSION["UserID"]))[0]["Password"];
  if (password_verify($_POST["oldPassword"], $pw)) {
    $db->update("UPDATE USERS SET Password=? WHERE UserID=?", "si", array(password_hash($_POST["newPassword"], PASSWORD_BCRYPT),$_SESSION["UserID"]));
    $success = "pass";
  } else {
    $failure = "pass";
  }
} elseif(isset($_POST["newMail"])){
  $db = new DBHandler();
  $pw = $db->prepareQuery("SELECT Password FROM USERS WHERE UserID=?","i", array($_SESSION["UserID"]))[0]["Password"];
  if (password_verify($_POST["mailPassword"], $pw)) {
    $db->update("UPDATE USERS SET Mail=? WHERE UserID=?", "si", array($_POST["newMail"],$_SESSION["UserID"]));
    $success = "mail";
  } else {
    $failure = "mail";
  }
} elseif (isset($_POST["changeName"])) {
  $db = new DBHandler();
  $db->update("UPDATE USERS SET Name=? WHERE UserID=?", "si", array($_POST["newName"], $_SESSION["UserID"]));
  $success = "name";
  $_SESSION["UserName"] = $_POST["newName"];
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_personal ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_personal ?></h1>
      <h3 class="ui medium header"><?php echo $lang->change_pwd ?></h3>
      <form class="ui form" action="" method="post" id="passForm">
        <div class="ui error message" id="passEqual" style="">
          <?php echo $lang->pwd_mismatch ?>
        </div>
        <div class="two fields">
          <div class="required field">
            <label for="newPassword"><?php echo $lang->new_pwd ?></label>
            <input required type="password" name="newPassword" id="newPassword" minlength="8">
          </div>
          <div class="required field">
            <label for="repeatPassword"><?php echo $lang->confirm_pwd ?></label>
            <input required type="password" name="repeatPassword" id="repeatPassword" minlength="8">
          </div>
        </div>
        <div class="required field">
          <label for="oldPassword"><?php echo $lang->old_pwd ?></label>
          <input required type="password" name="oldPassword">
        </div>
        <div class="ui error message"
        <?php
        if($failure=="pass"){echo 'style="display:block"';}?> >
          <?php echo $lang->wrong_pwd ?>
        </div>
        <div class="ui success message"
        <?php
        if($success=="pass"){echo 'style="display:block"';}?> >
          <?php echo $lang->pwd_changed ?>
        </div>
        <input class="ui primary button" type="submit" name="changePassword" value="<?php echo $lang->change_pwd ?>">
      </form>
      <div class="ui divider"></div>
      <h3 class="ui medium header"><?php echo $lang->change_email ?></h3>
      <form class="ui form" action="" method="post" id="mailForm">
        <div class="ui error message" id="mailEqual" style="">
          <?php echo $lang->mail_mismatch ?>
        </div>
        <div class="two fields">
          <div class="required field">
            <label for="newMail"><?php echo $lang->new_email ?></label>
            <input required type="email" name="newMail" id="newMail">
          </div>
          <div class="required field">
            <label for="repeatMail"><?php echo $lang->confirm_email ?></label>
            <input required type="email" name="repeatMail" id="repeatMail">
          </div>
        </div>
        <div class="required field">
          <label for="mailPassword"><?php echo $lang->old_pwd ?></label>
          <input required type="password" name="mailPassword">
        </div>
        <div class="ui error message"
        <?php
        if($failure == "mail"){echo 'style="display:block"';}?> >
          <lang->wrong_pwd ?>
        </div>
        <div class="ui success message"
        <?php
        if($success=="mail"){echo 'style="display:block"';}?> >
          <?php echo $lang->email_changed ?>
        </div>
        <input class="ui primary button" type="submit" name="changeMail" value="<?php echo $lang->change_email ?>">
      </form>
      <div class="ui divider"></div>
      <h3 class="ui medium header"><?php echo $lang->change_name ?></h3>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="required field">
            <label for="nameInput"><?php echo $lang->new_name ?></label>
            <input required type="text" name="newName" maxlength="32">
          </div>
          <div class="field">
            <label>&nbsp;</label>
            <input type="submit" name="changeName" value="<?php echo $lang->change_name ?>" class="ui primary button">
          </div>
        </div>
        <div class="ui success message"
        <?php
        if($success=="name"){echo 'style="display:block"';}?> >
          <?php echo $lang->name_changed ?>
        </div>
      </form>
    </main>
    <?php include dirname(__DIR__) . "/footer.php" ?>
    <script type="text/javascript">
      document.getElementById("nav_personal").className="active item";
    </script>
    <script type="text/javascript">
      document.getElementById("passForm").onsubmit = function(){
        if(document.getElementById("newPassword").value == document.getElementById("repeatPassword").value){
          document.getElementById("passEqual").style.display = "none";
          return true;
        } else {
          document.getElementById("passEqual").style.display = "block";
          return false;
        }
        return false;
      };

      document.getElementById("mailForm").onsubmit = function(){
        if(document.getElementById("newMail").value == document.getElementById("repeatMail").value){
          document.getElementById("mailEqual").style.display = "none";
          return true;
        } else {
          document.getElementById("mailEqual").style.display = "block";
          return false;
        }
        return false;
      };
    </script>
  </body>
</html>
