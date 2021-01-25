<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";

  $db = new DBHandler();
  $inserted = true;

 if(isset($_POST["addScene"])){
   $db->update("INSERT INTO SCENES VALUES (NULL, ?, ?)", "ss", array($_POST["sceneName"], $_POST["sceneDescription"]));
 } elseif (isset($_POST["rm_scene"])){
   if(!$db->update("DELETE FROM SCENES WHERE SceneID=?", "i", array($_POST["rm_scene"]))){
     $dependencies = $db->prepareQuery("SELECT FeatureID FROM FEATURES WHERE SceneID=?", "i", array($_POST["rm_scene"]));
     foreach ($dependencies as $dependency) {
       $db->update("DELETE FROM FEATURES WHERE FEATUREID=?","i", array($dependency["FeatureID"]));
     }
     $db->update("DELETE FROM SCENES WHERE SceneID=?", "i", array($_POST["rm_scene"]));
   }
 } elseif (isset($_POST["rm_features"])) {
   $db->update("DELETE FROM FEATURES WHERE FeatureID=?", "i", array($_POST["rm_features"]));
 } elseif (isset($_POST["addFeature"])) {
   $db->update("INSERT INTO FEATURES VALUES (NULL, ?, ?, ?)", "iii", array($_POST["SceneID"], $_POST["newFeature"], (isset($_POST["isMandatory"])?1:0)));
 } elseif(isset($_POST["toggle_mandatory"])){
   $isMandatory = $db->prepareQuery("SELECT Mandatory FROM FEATURES WHERE FeatureID=?","i", array($_POST["toggle_mandatory"]))[0]["Mandatory"];
   $db->update("UPDATE FEATURES SET Mandatory=? WHERE FeatureID=?", "ii", array(!$isMandatory,$_POST["toggle_mandatory"]));
 }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Scene Management</title>
    <?php include dirname(dirname(__DIR__)) . "/pages/head.html"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header">Scene management</h1>
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="sceneName">Scene Name</label>
          <input required="true" type="text" name="sceneName" maxlength="32">
        </div>
        <div class="field">
          <label for="sceneDescription">Scene Description</label>
          <textarea name="sceneDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addScene" value="Create Scene">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
          $currentScene = array("SceneID"=>-1);
          $FreeRoles = array();
          $scenes = $db->baseQuery("SELECT SCENES.*, FEATURES.FeatureID, FEATURES.Mandatory, ROLES.RoleID, ROLES.Name AS Role FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID ORDER BY SCENES.SceneID");
          if(!empty($scenes)){
            foreach ($scenes as $scene) {
              if($currentScene["SceneID"] != $scene["SceneID"]){
                if($currentScene["SceneID"] != -1){
                  $FreeRoles = $db->prepareQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT ROLES.RoleID FROM ROLES, FEATURES WHERE ROLES.RoleID = FEATURES.RoleID AND FEATURES.SceneID = ?)", "i", array($currentScene["SceneID"]));
                  createCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);
                }
                $currentScene = $scene;
                $currentScene["Role"] = array($currentScene["Role"]);
                $currentScene["Mandatory"] = array($currentScene["Mandatory"]);
                $currentScene["FeatureID"] = array($currentScene["FeatureID"]);
              } else {
                array_push($currentScene["Role"], $scene["Role"]);
                array_push($currentScene["Mandatory"], $scene["Mandatory"]);
                array_push($currentScene["FeatureID"], $scene["FeatureID"]);
              }
            }
            $FreeRoles = $db->prepareQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT ROLES.RoleID FROM ROLES, FEATURES WHERE ROLES.RoleID = FEATURES.RoleID AND FEATURES.SceneID = ?)", "i", array($currentScene["SceneID"]));
            createCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);

          }

          function createCard($SceneID, $Name, $Description, $Roles, $Features, $Mandatory, $FreeRoles){
            $role_rows = "";
            foreach ($Roles as $index => $role) {
              if($role == ""){
                continue;
              }
              $mandatoryColour = "";
              $mandatory_appendix = " not";
              if($Mandatory[$index]){
                $mandatoryColour = "orange";
                $mandatory_appendix = "";
              }
              $role_rows .= <<<EOT
              <tr>
                <td>$role</td>
                <td>
                  <form action="" method="post">
                    <input type="hidden" name="toggle_mandatory" value ="$Features[$index]">
                    <button title="Is$mandatory_appendix mandatory" type="submit" style="cursor:pointer" class="ui $mandatoryColour label">
                      <i class="fitted exclamation icon"></i>
                    </button>
                  </form>
                </td>
                <td>
                  <form action="" method="POST">
                    <input type="hidden" value="$Features[$index]" name="rm_features">
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
                    <input type="hidden" name="newFeature">
                    <i class="dropdown icon"></i>
                    <div class="default text">Features</div>
                      <div class="menu">
                        $dialog_options
                      </div>
                  </div>
                </div>
              </td>
              <td>
              <div class="ui toggle checkbox">
                <input type="checkbox" name="isMandatory" checked="true">
              </div>
              </td>
              <td>
                <input type="hidden" name="SceneID" value="$SceneID">
                <button class="ui primary icon button" type="submit" name="addFeature"><i class="plus icon"></i></button>
              </td>
            </form>
          </tr>
EOT;

          $button =<<<EOT
          <form method="POST" action="" style="margin-bottom:0;">
            <input type="hidden" name="rm_scene" value="$SceneID">
            <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
          </form>
EOT;

          $card =<<<EOT
          <div class="ui card">
            <div class="content">
              <div class="header">
                $Name
                <div class="right floated meta">#$SceneID</div>
              </div>
            </div>
            <div class="content">
              <div class="ui sub header">Description</div>
              $Description
            </div>
            <div class="content">
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
    include dirname(dirname(__DIR__)) . "/pages/footer.html";
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
      $('.ui.dropdown').dropdown();
      $('.ui.checkbox').checkbox();
    });
    </script>
    <script type="text/javascript">
      document.getElementById("nav_scenes").className="active item";
    </script>
  </body>
</html>
