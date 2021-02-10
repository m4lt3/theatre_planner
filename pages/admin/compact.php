<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  $db = new DBHandler();
  if(isset($_POST["add_scene"])){
    $db->update("INSERT INTO SCENES VALUES(NULL, ?, NULL, ?)", "si", array($_POST["add_scene"], $_POST["sequence"]));
  } elseif (isset($_POST["rm_features"])){
    $db->update("DELETE FROM FEATURES WHERE FeatureID=?","i",array($_POST["rm_features"]));
  } elseif (isset($_POST["add_role"])){
    if(is_numeric($_POST["add_role"])){
      // assigning an existing role
      $db->update("INSERT INTO FEATURES VALUES (NULL, ?, ?, ?)", "iii", array($_POST["id"],$_POST["add_role"], (isset($_POST["isMandatory"])?1:0)));
    } else {
      // Create new role
      $db->update("INSERT INTO ROLES VALUES (NULL,?,NULL)","s",array($_POST["add_role"]));
      $id = $db->prepareQuery("SELECT RoleID FROM ROLES WHERE Name=?","s",array($_POST["add_role"]))[0]["RoleID"];
      $db->update("INSERT INTO FEATURES VALUES (NULL, ?, ?, ?)", "iii", array($_POST["id"],$id, (isset($_POST["isMandatory"])?1:0)));
    }
  } elseif (isset($_POST["add_actor"])) {
    $db->update("INSERT INTO PLAYS VALUES (NULL,?,?)", "ii", array($_POST["add_actor"],$_POST["id"]));
  } elseif (isset($_POST["change_actor"])){
    $db->update("DELETE FROM PLAYS WHERE PlaysID=?","i",array($_POST["relation_id"]));
    $db->update("INSERT INTO PLAYS VALUES (NULL, ?, ?)", "ii", array($_POST["change_actor"],$_POST["role_id"]));
  } elseif(isset($_POST["toggle_mandatory"])){
    //Toggle whether the role is mandatory for the scene or not
    $isMandatory = $db->prepareQuery("SELECT Mandatory FROM FEATURES WHERE FeatureID=?","i", array($_POST["toggle_mandatory"]))[0]["Mandatory"];
    $db->update("UPDATE FEATURES SET Mandatory=? WHERE FeatureID=?", "ii", array(!$isMandatory,$_POST["toggle_mandatory"]));
  } elseif(isset($_POST["rm_plays"])){
    $db->update("DELETE FROM PLAYS WHERE PlaysID=?","i",array($_POST["rm_plays"]));
  }

  $everything = $db->baseQuery("SELECT SCENES.Name AS Scene, SCENES.SceneID, SCENES.Sequence, FEATURES.FeatureID, FEATURES.Mandatory, ROLES.RoleID, ROLES.Name AS Role, PLAYS.PlaysID, USERS.UserID, USERS.Name FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID LEFT JOIN PLAYS ON ROLES.RoleID = PLAYS.RoleID LEFT JOIN USERS ON PLAYS.UserID = USERS.UserID ORDER BY SCENES.Sequence, ROLES.RoleID");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_compact_view ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
    <style media="screen">
      .roleCell{
        display:flex;
        justify-content:space-between;
        align-items: center;
      }
      .roleCell > *:first-child{
        flex-grow:1;
        margin-right: 5px;
      }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui container">
      <h1 class="ui large header"><?php echo $lang->title_compact_view ?></h1>
      <table class="ui table">
        <thead>
          <th><?php echo $lang->scenes ?></th>
          <th><?php echo $lang->roles ?></th>
          <th><?php echo $lang->actors ?></th>
        </thead>
        <tbody>
          <?php
          require dirname(dirname(__DIR__))."/php/ui/admin/createCompactTable.php";
          echo createCompactTable($everything);
          ?>
        </tbody>
      </table>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
      $(".ui.change.dropdown").change(function(){
        this.parentNode.submit();
      });
      $(".ui.dropdown").dropdown();
      $(".ui.search.dropdown").dropdown({
        allowAdditions: true
      });
    </script>
  </body>
</html>
