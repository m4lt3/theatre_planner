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
  $SceneOrder = $db->baseQuery("SELECT SceneID, Sequence FROM SCENES ORDER BY Sequence");
  $sceneCount = count($SceneOrder??array());
  $inserted = true;

 if(isset($_POST["addScene"])){
   //Checking the new order
   if($_POST["order"] < $sceneCount + 1){
     // Shifting up according sequences by one, if scene is inserted into an existing order - but backwards due to the uniquenes of the sequence column
     for($i = $sceneCount - 1; $i >= $_POST["order"] -1; $i--){
       $db->update("UPDATE SCENES SET SEQUENCE = ? WHERE SceneID = ?", "ii", array($SceneOrder[$i]["Sequence"]+1, $SceneOrder[$i]["SceneID"]));
     }
   }
   // Add a new scene
   $db->update("INSERT INTO SCENES VALUES (NULL, ?, ?, ?)", "ssi", array($_POST["sceneName"], $_POST["sceneDescription"], $_POST["order"]));
 } elseif (isset($_POST["rm_scene"])){
   // Delete a scene
   if(!$db->update("DELETE FROM SCENES WHERE SceneID=?", "i", array($_POST["rm_scene"]))){
     // If delete fails due to foreign key references, delete dependencies first
     $dependencies = $db->prepareQuery("SELECT FeatureID FROM FEATURES WHERE SceneID=?", "i", array($_POST["rm_scene"]));
     foreach ($dependencies??array() as $dependency) {
       $db->update("DELETE FROM FEATURES WHERE FEATUREID=?","i", array($dependency["FeatureID"]));
     }
     $dependencies = $db->prepareQuery("SELECT PlanID FROM PLANNED_ON WHERE SceneID = ?", "i", array($_POST["rm_scene"]));
     foreach ($dependencies??array() as $dependency) {
       $db->update("DELETE FROM PLANNED_ON WHERE PlanID=?","i", array($dependency["PlanID"]));
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
        <div class="two fields">
          <div class="required field">
            <label for="sceneSeq"><?php echo $lang->order ?></label>
            <input required type="number" name="order" value="<?php echo ($sceneCount+1) ?>" min="1", max="<?php echo ($sceneCount+1) ?>">
          </div>
          <div class="required field">
            <label for="sceneName"><?php echo $lang->scene_name ?></label>
            <input required="true" type="text" name="sceneName" maxlength="32">
          </div>
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
          $scenes = $db->baseQuery("SELECT SCENES.*, FEATURES.FeatureID, FEATURES.Mandatory, ROLES.RoleID, ROLES.Name AS Role FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID ORDER BY SCENES.Sequence");
          if(!empty($scenes)){
            foreach ($scenes as $scene) {
              // Due to joins, a scene can (and probably will) appear multiple times with multiple roles; This is stored here until it is ready to display
              if($currentScene["SceneID"] != $scene["SceneID"]){
                if($currentScene["SceneID"] != -1){
                  // Print card before overwriting with new scene
                  $FreeRoles = $db->prepareQuery("SELECT RoleID, Name FROM ROLES WHERE RoleID NOT IN (SELECT ROLES.RoleID FROM ROLES, FEATURES WHERE ROLES.RoleID = FEATURES.RoleID AND FEATURES.SceneID = ?)", "i", array($currentScene["SceneID"]));
                  echo createSceneCard($currentScene["SceneID"], $currentScene["Sequence"] ,$currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);
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
            echo createSceneCard($currentScene["SceneID"], $currentScene["Sequence"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["FeatureID"], $currentScene["Mandatory"], $FreeRoles);
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
