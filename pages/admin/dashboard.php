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
$roles = $db->baseQuery("SELECT COUNT(RoleID) AS RoleCount FROM ROLES")[0]["RoleCount"];
$unplayed_roles = $db->baseQuery("SELECT RoleID, Name, Description FROM ROLES WHERE RoleID NOT IN (SELECT RoleID FROM PLAYS) ");
$scenes = $db->baseQuery("SELECT COUNT(SceneID) AS SceneCount FROM SCENES")[0]["SceneCount"];
$actors = $db->baseQuery("SELECT COUNT(UserID) AS UserCount FROM USERS")[0]["UserCount"];
$roleless_actors = $db->baseQuery("SELECT UserID, Name, Mail FROM USERS WHERE UserID NOT IN (SELECT UserID FROM PLAYS)");
?>

<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->admin ?> <?php echo $lang->title_dashboard ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
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
    <main class="ui container">
      <h1 class="ui large header"><?php echo $_SESSION["UserName"] ?>'s <?php echo $lang->admin ?> <?php echo $lang->title_dashboard ?></h1>
      <div class="ui stackable three column grid">
        <div class="column">
          <div class="ui huge statistic">
            <div class="value">
              <?php echo $roles ?>
            </div>
            <div class="label">
              <?php
              if($roles == 1){
                echo $lang->role;
              } else {
                echo $lang->roles;
              }
              ?>
            </div>
          </div>
          <?php
          if(!empty($unplayed_roles)){
            $count = count($unplayed_roles);
            $unplayed_stat = <<<EOT
            <div class="ui medium orange statistic">
              <div class="value">
                $count
              </div>
              <div class="label">
                {$lang->without_roles}
              </div>
            </div>
EOT;
            echo $unplayed_stat;
            echo '<div class="ui cards" style="margin-top:5px">';
            foreach ($unplayed_roles as $unplayed_role) {
              $unplayed_card =<<<EOT
                <div class="ui card">
                  <div class="content">
                    <div class="header">
                      {$unplayed_role["Name"]}
                      <div class="right floated meta">#{$unplayed_role["RoleID"]}</div>
                    </div>
                  </div>
                  <div class="content">
                    <div class="ui sub header">Description</div>
                    {$unplayed_role["Description"]}
                  </div>
                </div>
EOT;
              echo $unplayed_card;
            }
            echo '</div>';
          }
           ?>

        </div>
        <div class="column">
          <div class="ui huge statistic">
            <div class="value">
              <?php echo $scenes ?>
            </div>
            <div class="label">
              <?php
              if($scenes==1){
                echo $lang->scene;
              } else {
                echo $lang->scenes;
              }
              ?>
            </div>
          </div>
        </div>
        <div class="column">
          <div class="ui huge statistic">
            <div class="value">
              <?php echo $actors ?>
            </div>
            <div class="label">
              <?php
              if($actors == 1){
                echo $lang->actor;
              } else {
                echo $lang->actors;
              }
              ?>
            </div>
          </div>
          <?php
          if(!empty($roleless_actors)){
            $count = count($roleless_actors);
            $roleless_stat = <<<EOT
            <div class="ui medium orange statistic">
              <div class="value">
                $count
              </div>
              <div class="label">
                {$lang->roleless}
              </div>
            </div>
EOT;
            echo $roleless_stat;

            echo '<div class="ui cards" style="margin-top:5px">';
            foreach ($roleless_actors as $roleless_actor) {
              $roleless_card =<<<EOT
                <div class="ui card">
                  <div class="content">
                    <div class="header">
                      {$roleless_actor["Name"]}
                      <div class="right floated meta">#{$roleless_actor["UserID"]}</div>
                    </div>
                    <div class="meta"><a href="mailto:{$roleless_actor["Mail"]}">{$roleless_actor["Mail"]}</a></div>
                  </div>
                </div>
EOT;
              echo $roleless_card;
            }
            echo '</div>';
          }
          ?>
        </div>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
     ?>
  </body>
</html>
