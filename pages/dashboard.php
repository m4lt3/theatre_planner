<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
require_once dirname(__DIR__) . "/php/utils/database.php";
if(!$loggedIn){
  header("location:../index.php");
}
$db = new DBHandler();
$roles = $db->prepareQuery("SELECT ROLES.RoleID, ROLES.Name, ROLES.Description FROM ROLES, USERS, PLAYS WHERE PLAYS.UserID = ? AND PLAYS.RoleID = ROLES.RoleID AND PLAYS.UserID = USERS.UserID", "s", array($_SESSION["UserID"]));
$query = "";
if($config->user_focused){
  $query = "SELECT PRACTICES.Start, ME.AttendsID FROM PRACTICES LEFT JOIN (SELECT * FROM ATTENDS WHERE UserID = ?) AS ME ON PRACTICES.PracticeID = ME.PracticeID WHERE PRACTICES.Start > NOW() ORDER BY PRACTICES.Start LIMIT 1";
} else {
  $query = "SELECT PRACTICES.*, ME.Mandatory FROM PRACTICES LEFT JOIN PLANNED_ON ON PRACTICES.PracticeID = PLANNED_ON.PracticeID LEFT JOIN SCENES ON PLANNED_ON.SceneID = SCENES.SceneID LEFT JOIN (SELECT FEATURES.SceneID, FEATURES.Mandatory FROM FEATURES LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID LEFT JOIN PLAYS ON PLAYS.RoleID = ROLES.RoleID LEFT JOIN USERS ON USERS.UserID = PLAYS.UserID WHERE USERS.UserID = ?) AS ME ON ME.SceneID = SCENES.SceneID WHERE PRACTICES.Start > NOW() ORDER BY PRACTICES.Start, ME.Mandatory DESC LIMIT 1";
}
$date = $db->prepareQuery($query, "i", array($_SESSION["UserID"]));
?>

<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_dashboard ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
    <style media="screen">
      main > .grid > .column {
        display: flex!important;
        justify-content: center;
        flex-direction: column;
      }
      main > .grid{
        height:100%;
      }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
        <h1 class="ui large header"><?php echo $_SESSION["UserName"] ?><?php echo $lang->her_his ?> <?php echo $lang->title_dashboard ?></h1>
        <div class="ui mobile reversed stackable two column grid">
          <div class="column">
            <div class="ui huge statistic">
              <div class="value">
                <?php if(empty($roles)){
                  echo "0";
                } else {
                  echo count($roles);
                } ?>
              </div>
              <div class="label">
                <?php if (empty($roles)||count($roles)>1) {
                  echo $lang->roles;
                } else {
                  echo $lang->role;
                } ?>
              </div>
            </div>
            <div class="ui cards">
              <?php
              if(!empty($roles)){
                foreach ($roles as $role) {
                  $card =<<<EOT
                    <div class="ui card">
                      <div class="content">
                        <div class="header">
                          {$role["Name"]}
                          <div class="right floated meta">#{$role["RoleID"]}</div>
                        </div>
                      </div>
                      <div class="content">
                        <div class="ui sub header">{$lang->description}</div>
                        {$role["Description"]}
                      </div>
                    </div>
EOT;
                  echo $card;
                }
              }

              ?>
            </div>
          </div>
          <div class="column">
            <div class="ui large statistic">
              <div class="label">
                <?php echo $lang->next_practice ?>
              </div>
              <div class="value">
                <?php
                if(empty($date)){
                  echo $lang->unknown_date;
                } else {
                  $format = new DateTime($date[0]["Start"]);
                  echo $format->format("d.m.Y");
                }
                ?>
              </div>
              <div class="label">
                <?php
                if(!empty($date)){
                  echo $format->format("H:i") . "<br/>";
                  if($config->user_focused){
                    if(empty($date[0]["AttendsID"])){
                      echo ' <span class="ui red label">'. $lang->declined .'</span>';
                    } else {
                      echo ' <span class="ui green label">'. $lang->accepted .'</span>';
                    }
                  } else {
                    require dirname(__DIR__) . "/php/ui/practiceCards.php";
                    echo makeLabel($date[0]["Mandatory"], $date[0]["Start"]);
                  }
                }
                 ?>
              </div>
            </div>
          </div>
        </div>
    </main>
    <?php
    include dirname(__DIR__) . "/footer.php";
    require dirname(__DIR__) . "/cookie_manager.php";
    ?>
  </body>
</html>
