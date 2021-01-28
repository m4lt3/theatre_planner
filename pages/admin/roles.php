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

  if(isset($_POST["rm_role"])){

    if(!$db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]))){
      $dependencies = $db->prepareQuery("SELECT PlaysID FROM PLAYS WHERE RoleID=?", "i", array($_POST["rm_role"]));
      if(!empty($dependencies)){
        foreach ($dependencies as $dependency) {
          $db->update("DELETE FROM PLAYS WHERE PlaysID=?","i", array($dependency["PlaysID"]));
        }
      }
      $dependencies = $db->prepareQuery("SELECT FeatureID FROM FEATURES WHERE RoleID=?","i", array($_POST["rm_role"]));
      if(!empty($dependencies)){
        foreach ($dependencies as $dependency) {
          $db->update("DELETE FROM FEATURES WHERE FeatureID=?","i", array($dependency["FeatureID"]));
        }
      }
      $db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]));
    }
  } elseif (isset($_POST["addRole"])) {
    $db->update("INSERT INTO ROLES VALUES (NULL, ?, ?)", "ss", array($_POST["roleName"], $_POST["roleDescription"]));
  } elseif (isset($_POST["newPlay"])){
    $db->update("DELETE FROM PLAYS WHERE RoleID = ?", "i", array($_POST["RoleID"]));
    $db->update("INSERT INTO PLAYS VALUES(NULL, ?, ?)", "ii", array($_POST["newPlay"], $_POST["RoleID"]));
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_role_management ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_role_management ?></h1>
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="roleName"><?php echo $lang->role_name ?></label>
          <input required="true" type="text" name="roleName" maxlength="32">
        </div>
        <div class="field">
          <label for="roleDescription"><?php echo $lang->role_description ?></label>
          <textarea name="roleDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addRole" value="<?php echo $lang->create_role ?>">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
        $actors = $db->baseQuery("SELECT USERS.UserID, USERS.Name FROM USERS") ?? array();
        $roles = $db->baseQuery("SELECT * FROM ROLES") ?? array();
        foreach ($roles as $role) {
          create_card($role["RoleID"], $role["Name"], $role["Description"], $actors);
        }
        function create_card($id, $name, $description, $actors){
          global $lang;

          $dialog_options = "";
          if(count($actors)>0){
            foreach($actors as $actor){
              $dialog_options .= '<div class="item" data-value ="' . $actor["UserID"] . '">' . $actor["Name"] . '</div>';
            }
          }
          $actor_dialog =<<<EOT
            <form action="" method="post" id="form_$id">
              <input type="hidden" name="RoleID" value="$id">
              <div class="field">
                <div class="ui selection dropdown" id="dropdown_$id">
                  <input type="hidden" name="newPlay" id="input_$id">
                  <i class="dropdown icon"></i>
                  <div class="default text">{$lang->actor}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
                    <div class="menu">
                      $dialog_options
                    </div>
                </div>
              </div>
            </form>
EOT;

          $button =<<<EOT
          <form method="POST" action="" style="margin-bottom:0;">
            <input type="hidden" name="rm_role" value="$id">
            <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
          </form>
EOT;
          $card =<<<EOT
<div class="ui card">
  <div class="content">
    <div class="header">
      $name
      <div class="right floated meta">#$id</div>
    </div>
  </div>
  <div class="content">
    <div class="ui sub header">{$lang->description}</div>
    $description
  </div>
  <div class="content">
    <div class="ui sub header">{$lang->actor}</div>
    $actor_dialog
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
    for (let nav_item of document.getElementsByClassName("nav_roles item")) {
      nav_item.className = "nav_roles active item";
    }

    document.getElementById("hamburger").addEventListener("click",function(){

      if (this.className == "bars icon"){
        this.className = "close icon";
      } else {
        this.className = "bars icon";
      }
      document.getElementById("mobile_menu").classList.toggle("expanded");
    });
    $(".ui.dropdown").dropdown();
    </script>

    <script type="text/javascript">
      <?php
      $actors = $db->baseQuery("SELECT USERS.UserID, USERS.Name, PLAYS.RoleID FROM USERS LEFT JOIN PLAYS ON PLAYS.UserID = USERS.UserID ") ?? array();
      foreach ($actors as $actor) {
        if(isset($actor["RoleID"])){
          echo '$("#dropdown_'. $actor["RoleID"] .'").dropdown("set selected", "'.$actor["UserID"].'");'.PHP_EOL;
        }
      }

      foreach($roles as $role){
        echo 'document.getElementById("input_'.$role["RoleID"].'").addEventListener("change", function(){document.getElementById("form_'.$role["RoleID"].'").submit();});';
      }
      ?>
    </script>
  </body>
</html>
