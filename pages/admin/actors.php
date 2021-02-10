<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";

  $db = new DBHandler();
  $inserted = true;

 if(isset($_POST["rm_user"])){
   // User shall be deleted
   if($_SESSION["UserID"] != $_POST["rm_user"]){
     if(!$db->update("DELETE FROM USERS WHERE UserID=?", "i", array($_POST["rm_user"]))){
       // If deleting fails, resolve all possible foreign key reference issues
       $db->update("DELETE FROM ATTENDS WHERE UserID=?","i",array($_POST["rm_user"]));
       $db->update("DELETE FROM PLAYS WHERE UserID=?","i",array($_POST["rm_user"]));
       $db->update("DELETE FROM POLL_ENTRIES WHERE UserID=?","i",array($_POST["rm_user"]));
       $db->update("DELETE FROM TOKENS WHERE UserID=?","i",array($_POST["rm_user"]));
       // delete again
       $db->update("DELETE FROM USERS WHERE UserID=?", "i", array($_POST["rm_user"]));
     }
   }
 } elseif (isset($_POST["addUser"])) {
   // user shall be created with a random password
   $password = uniqid();
   $inserted = $db->update("INSERT INTO USERS VALUES (NULL, ?, ?, ?, ?)","sssi",array($_POST["userName"], $_POST["userMail"], password_hash($password, PASSWORD_BCRYPT), (($_POST["userAdmin"]??0)==0)?0:1));
   if($inserted){
     // generating a mail to notify the new user
     $header = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nFrom: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\nReply-to: " . $config->admin_mail . "\r\nX-Mailer: PHP " . phpversion();
     $mail_lang = require dirname(dirname(__DIR__)) . "/php/translations/" . $_POST["lang"] . ".php";
     $base_url = ((!empty($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] !== 'off')?"https://":"http://") . $_SERVER['SERVER_NAME'] . dirname(dirname(dirname($_SERVER["PHP_SELF"])));
     $action_url = 'index.php';
     require dirname(dirname(__DIR__)) . "/php/ui/mail_template.php";
     $message = createMail($mail_lang, $_POST["userName"], $_SESSION["UserName"], $password, $base_url, $action_url, $config->contact_info, "create");
     mail($_POST["userMail"], $_SESSION["UserName"] . " " . $mail_lang->create_title, $message, $header);
   }
 } elseif (isset($_POST["rm_plays"])){
   $db->update("DELETE FROM PLAYS WHERE PlaysID=?", "i", array($_POST["rm_plays"]));
 } elseif(isset($_POST["addPlay"])){
   $db->update("INSERT INTO PLAYS VALUES (NULL, ?, ?)", "ii", array($_POST["UserID"], $_POST["newPlay"]));
 } elseif(isset($_POST["toggle_admin"])){
   if($_SESSION["UserID"] != $_POST["toggle_admin"]){
     $adminStatus = $db->prepareQuery("SELECT Admin FROM USERS WHERE UserID=?","i", array($_POST["toggle_admin"]))[0]["Admin"];
     $db->update("UPDATE USERS SET Admin=? WHERE UserID=?", "ii", array(!$adminStatus,$_POST["toggle_admin"]));
   }
 }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_actor_management ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php";?>
    <style media="screen">
    .label{
      cursor:help;
    }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_actor_management ?></h1>
      <form class="ui form" action="" method="post">
        <div class="four fields">
          <div class="required field">
            <label for="userName"><?php echo $lang->name ?><div class="ui circular label" style="visibility:hidden;"><i class="fitted question icon"></i></div></label>
            <input required="true" type="text" name="userName" maxlength="32">
          </div>
          <div class="required field">
            <label for="userMail"><?php echo $lang->email ?><div class="ui circular label" style="visibility:hidden;"><i class="fitted question icon"></i></div></label>
            <input required="true" type="email" name="userMail" maxlength="64">
          </div>
          <div class="field">
            <label for="lang"><?php echo $lang->mail_lang ?> <div class="ui circular label" id="lang_help"><i class="fitted question icon"></i></div></label>
            <div class="ui selection dropdown" id="mail_lang">
              <input type="hidden" name="lang">
              <i class="dropdown icon"></i>
              <div class="default text"></div>
              <div class="menu">
                <?php
                foreach ($langs_available as $lang_available) {
                  echo '<div class="item" data-value="' . $lang_available . '">' . $lang->$lang_available . '</div>';
                }
                ?>
              </div>
            </div>
          </div>
          <div class="field">
            <label for="userAdmin"><?php echo $lang->admin ?><div class="ui circular label" style="visibility:hidden;"><i class="fitted question icon"></i></div></label>
            <div class="ui toggle checkbox">
              <input type="checkbox" name="userAdmin">
              <label>&nbsp;</label>
            </div>
          </div>
        </div>
        <div class="ui error message" id="mailError" <?php if(!$inserted){echo 'style="display:block"';} ?>>
          <div class="header">
            <?php echo $lang->email_taken ?>
          </div>
        </div>
        <input class="ui primary button" type="submit" name="addUser" value="<?php echo $lang->create_actor ?>">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
          require dirname(dirname(__DIR__)) . "/php/ui/admin/userCard.php";
          $currentUser = array("UserID" => -1);
          $FreeRoles = $db->baseQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT RoleID FROM PLAYS)");
          foreach ($db->baseQuery("SELECT PLAYS.PlaysID, USERS.UserID, USERS.Name, USERS.Mail, USERS.Admin , ROLES.Name AS Role FROM USERS LEFT JOIN PLAYS ON USERS.UserId = PLAYS.UserID LEFT JOIN ROLES ON ROLES.RoleID = PLAYS.RoleID ORDER BY USERS.UserID") as $user) {
            // Due to multiple roles assigned, an User can appear multiple times with different roles. This is aggregated here
            if($currentUser["UserID"] != $user["UserID"]){
              if($currentUser["UserID"] != -1){
                // If the user has changed (not for the first time), print card
                echo createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"], $FreeRoles, $currentUser["Admin"]);
              }
              // initialize aggregation variables
              $currentUser = $user;
              $currentUser["Role"] = array($currentUser["Role"]);
              $currentUser["PlaysID"] = array($currentUser["PlaysID"]);
            } else {
              // Add information to the array on existing actor
              $currentUser["Role"][] = $user["Role"];
              $currentUser["PlaysID"][] = $user["PlaysID"];
            }
          }
          // Creat the last card since it didn't get triggered
          echo createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"], $FreeRoles, $currentUser["Admin"]);
        ?>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
      $('.ui.dropdown').dropdown();
      $("#mail_lang").dropdown('set selected', "<?php echo $lang->lang ?>");
    });
    document.getElementById("lang_help").addEventListener("click", function(){
      if(this.id=="lang_help"){
        this.className = "ui left pointing label";
        this.innerHTML="<?php echo $lang->email_lang_help ?>";
        this.id="lang_help_expanded";
      } else {
        this.className="ui circular label";
        this.innerHTML='<i class="fitted question icon"></i>';
        this.id="lang_help";
      }
    });
    </script>
  </body>
</html>
