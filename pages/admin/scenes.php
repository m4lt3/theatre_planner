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

 if(isset($_POST["addScene"])){
   // Add a new scene
   $db->update("INSERT INTO SCENES VALUES (NULL, ?, ?)", "ss", array($_POST["sceneName"], $_POST["sceneDescription"]));
 } elseif (isset($_POST["rm_scene"])){
   // Delete a scene
   if(!$db->update("DELETE FROM SCENES WHERE SceneID=?", "i", array($_POST["rm_scene"]))){
     // If delete fails due to foreign key references, delete dependencies first
     $dependencies = $db->prepareQuery("SELECT FeatureID FROM FEATURES WHERE SceneID=?", "i", array($_POST["rm_scene"]));
     foreach ($dependencies as $dependency) {
       $db->update("DELETE FROM FEATURES WHERE FEATUREID=?","i", array($dependency["FeatureID"]));
     }
     // Delete scene again
     $db->update("DELETE FROM SCENES WHERE SceneID=?", "i", array($_POST["rm_scene"]));
   }
 } elseif (isset($_POST["rm_features"])) {
   // Remove a role-scene relation
   $db->update("DELETE FROM FEATURES WHERE FeatureID=?", "i", array($_POST["rm_features"]));
 } elseif (isset($_POST["addFeature"])) {
   // add a role-scene relation
   $db->update("INSERT INTO FEATURES VALUES (NULL, ?, ?, ?)", "iii", array($_POST["SceneID"], $_POST["newFeature"], (isset($_POST["isMandatory"])?1:0)));
 } elseif(isset($_POST["toggle_mandatory"])){
   //Toggle whether the role is mandatory for the scene or not
   $isMandatory = $db->prepareQuery("SELECT Mandatory FROM FEATURES WHERE FeatureID=?","i", array($_POST["toggle_mandatory"]))[0]["Mandatory"];
   $db->update("UPDATE FEATURES SET Mandatory=? WHERE FeatureID=?", "ii", array(!$isMandatory,$_POST["toggle_mandatory"]));
 }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title  ?> | <?php echo $lang->title_scene_management ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_scene_management ?></h1>
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="sceneName"><?php echo $lang->scene_name ?></label>
          <input required="true" type="text" name="sceneName" maxlength="32">
        </div>
        <div class="field">
          <label for="sceneDescription"><?php echo $lang->scene_description ?></label>
          <textarea name="sceneDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addScene" value="<?php echo $lang->create_scene ?>">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
        require dirname(dirname(__DIR__))."/php/ui/admin/createSceneCard.php";
          $currentScene = array("SceneID"=>-1);
          $FreeRoles = array();
          $scenes = $db->baseQuery("SELECT SCENES.*, FEATURES.FeatureID, FEATURES.Mandatory, ROLES.RoleID, ROLES.Name AS Role FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID ORDER BY SCENES.SceneID");
          if(!empty($scenes)){
            foreach ($scenes as $scene) {
              // DUe to joins, a scene can (and probably will) appear multiple times with multiple roles; This is stored here until it is ready to display
              if($currentScene["SceneID"] != $scene["SceneID"]){
                if($currentScene["SceneID"] != -1){
                  // Print card before overwriting with new scene
                  $FreeRoles = $db->prepareQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT ROLES.RoleID FROM ROLES, FEATURES WHERE ROLES.RoleID = FEATURES.RoleID AND FEATURES.SceneID = ?)", "i", array($currentScene["SceneID"]));
                  echo createSceneCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);
                }
                // Overwrite with new values
                $currentScene = $scene;
                $currentScene["Role"] = array($currentScene["Role"]);
                $currentScene["Mandatory"] = array($currentScene["Mandatory"]);
                $currentScene["FeatureID"] = array($currentScene["FeatureID"]);
              } else {
                // Add values to the existing scene
                $currentScene["Role"][] = $scene["Role"];
                $currentScene["Mandatory"][] = $scene["Mandatory"];
                $currentScene["FeatureID"][] = $scene["FeatureID"];
              }
            }
            // Print the last card since it didn't ge triggered
            $FreeRoles = $db->prepareQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT ROLES.RoleID FROM ROLES, FEATURES WHERE ROLES.RoleID = FEATURES.RoleID AND FEATURES.SceneID = ?)", "i", array($currentScene["SceneID"]));
            echo createSceneCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);
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
      $('.ui.checkbox').checkbox();
    });
    </script>
  </body>
</html>
