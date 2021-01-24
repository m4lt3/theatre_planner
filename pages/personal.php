<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
require_once dirname(__DIR__) . "/php/utils/database.php";

if(!$loggedIn){
  header("location:/theatre_planner/index.php");
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
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Personal Data</title>
    <?php include dirname(__DIR__) . "/pages/head.html"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h3 class="ui medium header">Change Password</h3>
      <form class="ui form" action="" method="post" id="passForm">
        <div class="ui error message" id="passEqual" style="">
          Passwords do not match.
        </div>
        <div class="two fields">
          <div class="required field">
            <label for="newPassword">New Password</label>
            <input required type="password" name="newPassword" id="newPassword" minlength="8">
          </div>
          <div class="required field">
            <label for="repeatPassword">Confirm Password</label>
            <input required type="password" name="repeatPassword" id="repeatPassword" minlength="8">
          </div>
        </div>
        <div class="required field">
          <label for="oldPassword">Old Password</label>
          <input required type="password" name="oldPassword">
        </div>
        <div class="ui error message"
        <?php
        if($failure=="pass"){echo 'style="display:block"';}?> >
          Password incorrect.
        </div>
        <div class="ui success message"
        <?php
        if($success=="pass"){echo 'style="display:block"';}?> >
          Password successfully changed!
        </div>
        <input class="ui primary button" type="submit" name="changePassword" value="Change Password">
      </form>
      <div class="ui divider"></div>
      <h3 class="ui medium header">Change E-Mail</h3>
      <form class="ui form" action="" method="post" id="mailForm">
        <div class="ui error message" id="mailEqual" style="">
          Mails do not match.
        </div>
        <div class="two fields">
          <div class="required field">
            <label for="newMail">New E-Mail</label>
            <input required type="email" name="newMail" id="newMail">
          </div>
          <div class="required field">
            <label for="repeatMail">Confirm E-Mail</label>
            <input required type="email" name="repeatMail" id="repeatMail">
          </div>
        </div>
        <div class="required field">
          <label for="mailPassword">Password</label>
          <input required type="password" name="mailPassword">
        </div>
        <div class="ui error message"
        <?php
        if($failure == "mail"){echo 'style="display:block"';}?> >
          Password incorrect.
        </div>
        <div class="ui success message"
        <?php
        if($success=="mail"){echo 'style="display:block"';}?> >
          Mail successfully changed!
        </div>
        <input class="ui primary button" type="submit" name="changeMail" value="Change E-Mail-Address">
      </form>
      <div class="ui divider"></div>
      <h3 class="ui medium header">Change display name</h3>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="required field">
            <label for="nameInput">New display name:</label>
            <input required type="text" name="newName" maxlength="32">
          </div>
          <div class="field">
            <label>&nbsp;</label>
            <input type="submit" name="changeName" value="Change Name" class="ui primary button">
          </div>
        </div>
        <div class="ui success message"
        <?php
        if($success=="name"){echo 'style="display:block"';}?> >
          Name successfully changed!
        </div>
      </form>
    </main>
    <?php include dirname(__DIR__) . "/pages/footer.html" ?>
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
