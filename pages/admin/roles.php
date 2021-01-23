<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/php/auth/sessionValidate.php";
  require_once $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  $db = new DBHandler();

  if(isset($_POST["rm_role"])){

    if(!$db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]))){
      $dependencies = $db->prepareQuery("SELECT PlaysID FROM PLAYS WHERE RoleID=?", "i", array($_POST["rm_role"]));
      foreach ($dependencies as $dependency) {
        $db->update("DELETE FROM PLAYS WHERE PlaysID=?","i", array($dependency["PlaysID"]));
      }
      $db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]));
    }
  } elseif (isset($_POST["addRole"])) {
    $db->update("INSERT INTO ROLES VALUES (NULL, ?, ?)", "ss", array($_POST["roleName"], $_POST["roleDescription"]));
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Roles</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="roleName">Role Name</label>
          <input required="true" type="text" name="roleName" maxlength="32">
        </div>
        <div class="field">
          <label for="roleDescription">Role Description</label>
          <textarea name="roleDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addRole" value="Create Role">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
        foreach ($db->baseQuery("SELECT * FROM ROLES") ?? array() as $role) {
          create_card($role["RoleID"], $role["Name"], $role["Description"]);
        }
        function create_card($id, $name, $description){
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
    <div class="ui sub header">Description</div>
    $description
  </div>
  $button
</div>
EOT;
        echo $card;
        }
        ?>
      </div>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/footer.html"; ?>
    <script type="text/javascript">
      document.getElementById("nav_roles").className="active item";
    </script>
  </body>
</html>
