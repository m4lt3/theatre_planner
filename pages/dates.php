<?php
  require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
  require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:../index.php");
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_date_finder ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
    <style media="screen">
      #notFound{
        height: 100%;
        width: 100%;
        display:flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
      }
      main > div > div > table.ui.table > tbody > tr > td{
        text-align:center;
      }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main>
      <div class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_date_finder ?></h1>
      <?php
      $db = new DBHandler();
      require dirname(__DIR__)."/php/ui/polls.php";
        if(isset($_GET["poll"])){
          $poll = $db->prepareQuery("SELECT * FROM POLLS WHERE PollID=?","i", array($_GET["poll"]))[0]??array();
          if(empty($poll)){
            echo '<div id="notFound"><i class="massive orange question circle outline icon"></i><div class="ui header">'.$lang->poll_not_found.'<div></div>';
          } else {
            echo $poll["Description"];
            $body="";
            if($_SESSION["Admin"]){
              // Create admin UI
              $entries = $db->baseQuery("SELECT USERS.UserID, USERS.Name, POLL_ENTRIES.EntryID, POLL_ENTRIES.Entries, POLLS.* FROM USERS LEFT JOIN POLL_ENTRIES ON USERS.UserID = POLL_ENTRIES.UserID LEFT JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID");
              foreach ($entries as $entry) {
                $body .= createTRow($entry["Name"], $entry["Entries"], $entry["UserID"]==$_SESSION["UserID"], $poll["Duration"], $entry["UserID"]);
              }
            } else {
              // Create User UI
              $entry = $db->prepareQuery("SELECT POLL_ENTRIES.Entries, POLLS.* FROM POLL_ENTRIES JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID WHERE POLLS.PollID=? AND POLL_ENTRIES.UserID=?", "ii", array($_GET["poll"], $_SESSION["UserID"]))[0]??array();
              $body .= createTRow($_SESSION["UserName"], $entry["Entries"]??NULL, true, $poll["Duration"], $_SESSION["UserID"]);
            }
            echo '</div><div class="ui container">';
            echo '<div style="overflow-x:auto;padding-top:14px">';
            echo '<table class="ui very basic celled table"><thead>';
            echo createTHead($poll["Start"],$poll["Duration"]);
            echo '</thead><tbody>';
            echo $body;
            echo '</tbody></table>';
            echo '</div>';
          }
        } else {
          //listing active polls
          $polls = $db->baseQuery("SELECT * FROM POLLS WHERE DATE_ADD(Start, INTERVAL (Duration - 1 ) DAY) >= CURDATE()")??array();
          echo '<div class="ui two stacked cards">';
          foreach ($polls as $poll) {
            echo createPollCard($poll);
          }
          echo "</div>";
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
