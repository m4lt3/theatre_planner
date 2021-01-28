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
   if($_SESSION["UserID"] != $_POST["rm_user"]){
     if(!$db->update("DELETE FROM USERS WHERE UserID=?", "i", array($_POST["rm_user"]))){
       $dependencies = $db->prepareQuery("SELECT PlaysID FROM PLAYS WHERE UserID=?", "i", array($_POST["rm_user"]));
       foreach ($dependencies as $dependency) {
         $db->update("DELETE FROM PLAYS WHERE PlaysID=?","i", array($dependency["PlaysID"]));
       }
       $dependencies = $db->prepareQuery("SELECT AttendsID FROM ATTENDS WHERE UserID=?", "i", array($_POST["rm_user"]));
       foreach ($dependencies as $dependency) {
         $db->update("DELETE FROM ATTENDS WHERE AttendsID=?","i", array($dependency["AttendsID"]));
       }
       $db->update("DELETE FROM USERS WHERE UserID=?", "i", array($_POST["rm_user"]));
     }
   }
 } elseif (isset($_POST["addUser"])) {
   $password = uniqid();
   $inserted = $db->update("INSERT INTO USERS VALUES (NULL, ?, ?, ?, ?)","sssi",array($_POST["userName"], $_POST["userMail"], password_hash($password, PASSWORD_BCRYPT), (($_POST["userAdmin"]??0)==0)?0:1));
   if($inserted){
     $config = require dirname(dirname(__DIR__)) . "/php/config.php";
     $header = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nReply-to: " . $config->admin_mail . "\r\nX-Mailer: PHP " . phpversion();
     $mail_lang = require dirname(dirname(__DIR__)) . "/php/translations/" . $_POST["lang"] . ".php";
     $url = ((!empty($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] !== 'off')?"https://":"http://") . $_SERVER['SERVER_NAME'] . dirname(dirname($_SERVER["PHP_SELF"])).'/index.php';
     require dirname(dirname(__DIR__)) . "/php/ui/mail_template.php";
     $message = createMail($mail_lang, $_POST["userName"], $_SESSION["UserName"], $password, $url, $config->contact_info, "create");
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
          $currentUser = array("UserID" => -1);
          $FreeRoles = $db->baseQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT RoleID FROM PLAYS)");
          foreach ($db->baseQuery("SELECT PLAYS.PlaysID, USERS.UserID, USERS.Name, USERS.Mail, USERS.Admin , ROLES.Name AS Role FROM USERS LEFT JOIN PLAYS ON USERS.UserId = PLAYS.UserID LEFT JOIN ROLES ON ROLES.RoleID = PLAYS.RoleID ORDER BY USERS.UserID") as $user) {
            if($currentUser["UserID"] != $user["UserID"]){
              if($currentUser["UserID"] != -1){
                createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"], $FreeRoles, $currentUser["Admin"]);
              }
              $currentUser = $user;
              $currentUser["Role"] = array($currentUser["Role"]);
              $currentUser["PlaysID"] = array($currentUser["PlaysID"]);
            } else {
              array_push($currentUser["Role"], $user["Role"]);
              array_push($currentUser["PlaysID"], $user["PlaysID"]);
            }
          }
          createCard($currentUser["UserID"], $currentUser["Name"], $currentUser["Mail"], $currentUser["Role"], $currentUser["PlaysID"], $FreeRoles, $currentUser["Admin"]);

          function createCard($UserID, $name, $mail, $roles, $PlaysID, $FreeRoles, $admin){
            global $lang;
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

            $dialog_options = "";
            if(count($FreeRoles==null?array():$FreeRoles)>0){
              foreach($FreeRoles as $freeRole){
                $dialog_options .= '<div class="item" data-value ="' . $freeRole["RoleID"] . '">' . $freeRole["Name"] . '</div>';
              }
            }
            $role_dialog =<<<EOT
            <tr>
              <form action="" method="post">
                <td>
                  <div class="field">
                    <div class="ui selection dropdown">
                      <input type="hidden" name="newPlay">
                      <i class="dropdown icon"></i>
                      <div class="default text">{$lang->role}</div>
                        <div class="menu">
                          $dialog_options
                        </div>
                    </div>
                  </div>
                </td>
                <td>
                  <input type="hidden" name="UserID" value="$UserID">
                  <button class="ui primary icon button" type="submit" name="addPlay"><i class="plus icon"></i></button>
                </td>
              </form>
            </tr>
EOT;
            $button =<<<EOT
            <form method="POST" action="" style="margin-bottom:0;">
              <input type="hidden" name="rm_user" value="$UserID">
              <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
            </form>
EOT;

          $adminColour = "";
          $admin_appendix = $lang->admin_appendix;
          if($admin){
            $adminColour = "orange";
            $admin_appendix = "";
          }

            $card =<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        $name
        <div class="right floated meta">#$UserID</div>
        <form action="" method="post"><input type="hidden" name="toggle_admin" value ="$UserID"><button title="{$lang->admin_prefix}$admin_appendix{$lang->admin}" type="submit" style="cursor:pointer" class="ui right floating $adminColour icon label"><i class="fitted chess queen icon"></i></button></form>
      </div>
      <div class="meta"><a href="mailto:$mail">$mail</a></div>
    </div>
    <div class="content">
      <div class="ui sub header">{$lang->roles}</div>
        <table class="ui very basic table">
          $role_rows
          $role_dialog
        </table>
      </div>
      $button
  </div>
EOT;
          echo $card;
          }
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
    <script type="text/javascript">
    for (let nav_item of document.getElementsByClassName("nav_users item")) {
      nav_item.className = "nav_users active item";
    }

    document.getElementById("hamburger").addEventListener("click",function(){
      
      if (this.className == "bars icon"){
        this.className = "close icon";
      } else {
        this.className = "bars icon";
      }
      document.getElementById("mobile_menu").classList.toggle("expanded");
    });
    </script>
  </body>
</html>
