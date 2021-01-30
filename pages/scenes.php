<?php
  require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
  require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
  require_once dirname(__DIR__) . "/php/utils/database.php";
  if(!$loggedIn){
    header("location:../index.php");
  }
  $db = new DBHandler();
  $scenes = $db->baseQuery("SELECT SCENES.*, FEATURES.Mandatory, ROLES.Name AS Role FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID ORDER BY SCENES.SceneID");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->scenes ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->scenes ?></h1>
      <div class="ui two stackable cards">
        <?php
        require dirname(__DIR__)."/php/ui/createSceneCard.php";
          $currentScene = array("SceneID"=>-1);
          if(!empty($scenes)){
            foreach ($scenes as $scene) {
              // DUe to joins, a scene can (and probably will) appear multiple times with multiple roles; This is stored here until it is ready to display
              if($currentScene["SceneID"] != $scene["SceneID"]){
                if($currentScene["SceneID"] != -1){
                  // Print card before overwriting with new scene
                  echo createSceneCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["Mandatory"]);
                }
                // Overwrite with new values
                $currentScene = $scene;
                $currentScene["Role"] = array($currentScene["Role"]);
                $currentScene["Mandatory"] = array($currentScene["Mandatory"]);
              } else {
                // Add values to the existing scene
                $currentScene["Role"][] = $scene["Role"];
                $currentScene["Mandatory"][] = $scene["Mandatory"];
              }
            }
            // Print the last card since it didn't ge triggered
            echo createSceneCard($currentScene["SceneID"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["Mandatory"]);
          }
        ?>
      </div>
    </main>
    <?php
    include dirname(__DIR__) . "/footer.php";
    require dirname(__DIR__) . "/cookie_manager.php";
    ?>
  </body>
</html>
