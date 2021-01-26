<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
require_once dirname(__DIR__) . "/php/utils/database.php";
if(!$loggedIn){
  header("location:../index.php");
}
$db = new DBHandler();
$roles = $db->prepareQuery("SELECT ROLES.RoleID, ROLES.Name, ROLES.Description FROM ROLES, USERS, PLAYS WHERE PLAYS.UserID = ? AND PLAYS.RoleID = ROLES.RoleID AND PLAYS.UserID = USERS.UserID", "s", array($_SESSION["UserID"]));
$date = $db->prepareQuery("SELECT PRACTICES.Start, ME.AttendsID FROM PRACTICES LEFT JOIN (SELECT * FROM ATTENDS WHERE UserID = ?) AS ME ON PRACTICES.PracticeID = ME.PracticeID WHERE PRACTICES.Start > NOW() ORDER BY PRACTICES.Start LIMIT 1", "i", array($_SESSION["UserID"]));
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Dashboard</title>
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
        <h1 class="ui large header"><?php echo $_SESSION["UserName"] ?>'s Dashboard</h1>
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
                Role<?php if (empty($roles)||count($roles)>1) {
                  echo "s";
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
                        <div class="ui sub header">Description</div>
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
                Next Practice on
              </div>
              <div class="value">
                <?php
                if(empty($date)){
                  echo "Hold";
                } else {
                  $format = new DateTime($date[0]["Start"]);
                  echo $format->format("d.m.Y");
                }
                ?>
              </div>
              <div class="label">
                <?php

                if(!empty($date)){
                  echo $format->format("H:i");
                  if(empty($date[0]["AttendsID"])){
                    echo ' <span class="ui red label">Declined</span>';
                  } else {
                    echo ' <span class="ui green label">Accepted</span>';
                  }
                }
                 ?>
              </div>
            </div>
          </div>
        </div>
    </main>
    <?php include dirname(__DIR__) . "/footer.php" ?>
    <script type="text/javascript">
      document.getElementById("nav_dashboard").className="active item";
    </script>
  </body>
</html>
