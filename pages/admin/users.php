<?php
  require $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  $db = new DBHandler();
  $inserted = true;

 if(isset($_POST["rm_user"])){
   $db->update("DELETE FROM USERS WHERE UserID =?", "i", array($_POST["rm_user"]));
 } elseif (isset($_POST["addUser"])) {
   $password = uniqid();
   $inserted = $db->update("INSERT INTO USERS VALUES (NULL, ?, ?, ?, ?)","sssi",array($_POST["userName"], $_POST["userMail"], md5($password), ($_POST["userAdmin"] == "on") ? 1 : 0));
   if($inserted){
     // TODO mail($_POST["userMail"], "Hello " . $_POST["userName"] . "! Your Password is '" . $password . "'. Please change it after your first login at " . $_SERVER["SERVER_NAME"]);
   }
 } elseif (isset($_POST["rm_plays"])){
   $db->update("DELETE FROM PLAYS WHERE PlaysID=?", "i", array($_POST["rm_plays"]));
 }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Users</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <main class="ui text container">
      <form class="ui form" action="" method="post">
        <div class="three fields">
          <div class="required field">
            <label for="userName">Name</label>
            <input required="true" type="text" name="userName" maxlength="32">
          </div>
          <div class="required field">
            <label for="userMail">E-Mail</label>
            <input required="true" type="email" name="userMail" maxlength="64">
          </div>
          <div class="field">
            <label>&nbsp;</label>
            <div class="ui toggle checkbox">
              <input type="checkbox" name="userAdmin">
              <label for="userAdmin">Admin</label>
            </div>
          </div>
        </div>
        <div class="ui error message" id="mailError" style="">
          <div class="header">
            Oops! That Address is already taken.
          </div>
        </div>
        <input class="ui primary button" type="submit" name="addUser" value="Create User">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
          $currentUser = array("UserID" => -1);

          foreach ($db->baseQuery("SELECT PLAYS.PlaysID, USERS.UserID, USERS.Name, USERS.Mail, USERS.ADMIN , ROLES.Name AS Role FROM USERS LEFT JOIN PLAYS ON USERS.UserId = PLAYS.UserID LEFT JOIN ROLES ON ROLES.RoleID = PLAYS.RoleID ORDER BY USERS.UserID") as $user) {
            if($currentUser["UserID"] != $user["UserID"]){
              if($currentUser["UserID"] != -1){
                createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"]);
              }
              $currentUser = $user;
              $currentUser["Role"] = array($currentUser["Role"]);
              $currentUser["PlaysID"] = array($currentUser["PlaysID"]);
            } else {
              array_push($currentUser["Role"], $user["Role"]);
              array_push($currentUser["PlaysID"], $user["PlaysID"]);
            }
          }
          createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"]);

          function createCard($UserID, $name, $mail, $roles, $PlaysID){
            /*print_r($roles);
            echo "<br/>";
            print_r($PlaysID);
            echo "<br/>";*/
            $role_rows = "";
            foreach ($roles as $index => $role) {
              if($role == ""){
                continue;
              }
              $role_rows .= <<<EOT
              <tr>
              <td>$role</td>
              <td>
              <form action="" method="POST">
              <input type="hidden" value="$PlaysID[$index]" name="rm_plays">
              <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
              </form>
              </td>
              </tr>
EOT;
            }

            $message =<<<EOT
  <div class="ui card">
  <div class="content">
  <div class="header">
  $name
  <div class="right floated meta">#$UserID</div>
  </div>
  <div class="meta"><a href="mailto:$mail">$mail</a></div>
  </div>
  <div class="content">
  <div class="ui sub header">Roles</div>
  <table class="ui very basic table">
  $role_rows
  </table>
  </div>
  </div>
EOT;
          echo $message;
          }
        ?>
      </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/footer.html";
    if($inserted){
      echo '<script>document.getElementById("mailError").style.display="none";</script>';
    } else {
      echo '<script>document.getElementById("mailError").style.display="block";</script>';
    }
    ?>
  </body>
</html>
