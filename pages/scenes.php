<?php
  require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
  require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
  require_once dirname(__DIR__) . "/php/utils/database.php";
  if(!$loggedIn){
    header("location:../index.php");
  }

  if (isset($_POST["toggleValue"])){
    if (isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"]) {
      setcookie("theatre_me", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
    }
      $_SESSION["theatre_me"] = ($_POST["toggleValue"]=="true");
  }

  $db = new DBHandler();

  $query = "SELECT SCENES.*, FEATURES.Mandatory, ROLES.Name AS Role, ROLES.RoleID, ME.Plays FROM SCENES LEFT JOIN FEATURES ON FEATURES.SceneID = SCENES.SceneID LEFT JOIN ROLES ON ROLES.RoleID = FEATURES.RoleID LEFT JOIN (SELECT ROLES.RoleID AS Plays FROM ROLES LEFT JOIN PLAYS ON PLAYS.RoleID = ROLES.RoleID LEFT JOIN USERS ON USERS.UserID = PLAYS.UserID WHERE USERS.UserID=?) AS ME ON ME.Plays = ROLES.RoleID";
  $queryParams = array("i", array($_SESSION["UserID"]));
  if(isset($_SESSION["theatre_me"]) && $_SESSION["theatre_me"]){
    $query .= "  WHERE SCENES.SceneID IN (SELECT SCENES.SceneID FROM SCENES LEFT JOIN FEATURES ON FEATURES.SceneID = SCENES.SceneID LEFT JOIN ROLES ON ROLES.RoleID = FEATURES.RoleID LEFT JOIN PLAYS ON PLAYS.RoleID = ROLES.RoleID LEFT JOIN USERS ON USERS.UserID = PLAYS.UserID WHERE USERS.UserID=?)";
    $queryParams = array("ii", array($_SESSION["UserID"],$_SESSION["UserID"]));
  }
  $query .= " ORDER BY SCENES.Sequence, ME.Plays DESC";

  $scenes = $db->prepareQuery($query, $queryParams[0], $queryParams[1]);
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
      <br/>
      <form id="toggleForm" action="" method="post">
        <input id="toggleValue" type="hidden" name="toggleValue" value="">
        <div class="ui toggle checkbox">
          <input type="checkbox" name="toggle_me" id="toggle_me"
          <?php
          if(!empty($_SESSION["theatre_me"]) && $_SESSION["theatre_me"]){
            echo "checked";
          }
           ?>
          >
          <label><?php echo $lang->show_my . " " . $lang->scenes?></label>
        </div>
      </form>
      <br/>
      <div class="ui two stackable cards">
        <?php
        require dirname(__DIR__)."/php/ui/createSceneCard.php";
          $currentScene = array("SceneID"=>-1);
          if(!empty($scenes)){
            foreach ($scenes as $scene) {
              // Due to joins, a scene can (and probably will) appear multiple times with multiple roles; This is stored here until it is ready to display

              if($currentScene["SceneID"] != $scene["SceneID"]){
                if($currentScene["SceneID"] != -1){
                  // Print card before overwriting with new scene
                  echo createSceneCard($currentScene["SceneID"], $currentScene["Sequence"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["Mandatory"], $currentScene["Plays"]);
                }
                // Overwrite with new values
                $currentScene = $scene;
                $currentScene["Role"] = array($currentScene["Role"]);
                $currentScene["Mandatory"] = array($currentScene["Mandatory"]);
                $currentScene["Plays"] = array($currentScene["Plays"]);
              } else {
                // Add values to the existing scene
                $currentScene["Role"][] = $scene["Role"];
                $currentScene["Mandatory"][] = $scene["Mandatory"];
                $currentScene["Plays"][] = $scene["Plays"];
              }
            }
            // Print the last card since it didn't ge triggered
            echo createSceneCard($currentScene["SceneID"], $currentScene["Sequence"], $currentScene["Name"], $currentScene["Description"], $currentScene["Role"], $currentScene["Mandatory"], $currentScene["Plays"]);
          }
        ?>
      </div>
    </main>
    <?php
    include dirname(__DIR__) . "/footer.php";
    require dirname(__DIR__) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.ui.checkbox').checkbox();

        $('#toggle_me').change(function(){
          document.getElementById('toggleValue').value = document.getElementById('toggle_me').checked;
          $('#toggleForm').submit();
        });
      });
    </script>
  </body>
  </body>
</html>
