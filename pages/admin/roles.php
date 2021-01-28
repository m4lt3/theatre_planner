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
    // Remove a role
    if(!$db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]))){
      // If delete fails due to foreign key references, delete all dependencies first
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
      // delete role again
      $db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]));
    }
  } elseif (isset($_POST["addRole"])) {
    // Add a Role
    $db->update("INSERT INTO ROLES VALUES (NULL, ?, ?)", "ss", array($_POST["roleName"], $_POST["roleDescription"]));
  } elseif (isset($_POST["newPlay"])){
    //(Re)Assign a role to an actor
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
        require dirname(dirname(__DIR__))."/php/ui/admin/createRoleCard.php";
        foreach ($roles as $role) {
          echo createRoleCard($role["RoleID"], $role["Name"], $role["Description"], $actors);
        }
        ?>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
     ?>
    <script type="text/javascript">
    $(".ui.dropdown").dropdown();
    </script>

    <script type="text/javascript">
      <?php
      $actors = $db->baseQuery("SELECT USERS.UserID, USERS.Name, PLAYS.RoleID FROM USERS LEFT JOIN PLAYS ON PLAYS.UserID = USERS.UserID ") ?? array();
      foreach ($actors as $actor) {
        if(isset($actor["RoleID"])){
          // Set the dropdown forms to the current value, if any (no, this was not possible in the generation of the forms)
          echo '$("#dropdown_'. $actor["RoleID"] .'").dropdown("set selected", "'.$actor["UserID"].'");'.PHP_EOL;
        }
      }

      foreach($roles as $role){
        // Add a change listener toisntantly react on selections
        echo 'document.getElementById("input_'.$role["RoleID"].'").addEventListener("change", function(){document.getElementById("form_'.$role["RoleID"].'").submit();});';
      }
      ?>
    </script>
  </body>
</html>
