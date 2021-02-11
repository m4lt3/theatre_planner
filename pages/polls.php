<?php
  require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
  require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:../index.php");
  }

  $db = new DBHandler();
  if(isset($_GET["poll"])){
    $poll = $db->prepareQuery("SELECT *, DATE_ADD(Start, INTERVAL (Duration - 1 ) DAY) AS Poll_end  FROM POLLS WHERE PollID=?","i", array($_GET["poll"]))[0]??array();
  }

  if(isset($_POST["change_entries"])){
    // Changing or adding entries to a poll
    $entries = "";
    // generating bitmap and converting it to int
    for($i = 0; $i < $poll["Duration"]; $i++){
      $entries .= isset($_POST[(string)$i])?"1":"0";
    }
    $entries = bindec($entries);

    // Inserting or updatong entries
    $relation_id = $db->prepareQuery("SELECT EntryID FROM POLL_ENTRIES WHERE UserID=?","i", array($_POST["uid"]));
    if(count($relation_id??array()) == 0){
      $db->update("INSERT INTO POLL_ENTRIES VALUES(NULL, ?, ?, ?)", "iii", array($poll["PollID"], $_POST["uid"], $entries));
    } else {
      $db->update("UPDATE POLL_ENTRIES SET Entries = ? WHERE UserID=? AND PollID=?", "iii", array($entries, $_POST["uid"], $poll["PollID"]));
    }
  } elseif (isset($_POST["create_practice"])) {
    // Create a practice on a coresponding poll option
    $entries = $db->baseQuery("SELECT USERS.UserID, POLL_ENTRIES.Entries FROM USERS LEFT JOIN POLL_ENTRIES ON USERS.UserID = POLL_ENTRIES.UserID LEFT JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID");
    // Getting all entries of every user and inserting them into a structured table
    $matrix = array();
    for($i = 0; $i < $poll["Duration"]; $i++){
      if(!isset($entries[$i]["Entries"])){
        $matrix[] = str_repeat("0", $poll["Duration"]);
      } else {
        $parsed_entries = decbin($entries[$i]["Entries"]);
        if(strlen($parsed_entries)<$poll["Duration"]){
          $parsed_entries = str_repeat("0", $poll["Duration"] - strlen($parsed_entries)) . $parsed_entries;
        }
        $matrix[] = $parsed_entries;
      }
    }

    // Extracting all User IDs that checked the option in the given column
    $UIDs = array();
    for($i = 0; $i < count($matrix); $i++){
      if($matrix[$i][$_POST["col"]]=="1"){
        $UIDs[] = $entries[$i]["UserID"];
      }
    }

    // Creating new practice (and the coresponding date)
    $date = date_create($poll["Start"]);
    date_add($date, date_interval_create_from_date_string($_POST["col"]." days"));
    $db->update("INSERT INTO PRACTICES VALUES (NULL, NULL, ?)", "s", array(date_format($date, "Y-m-d")));
    $id = $db->getLastID();
    // Automatically attending all users that have checked that option
    foreach ($UIDs as $uid) {
      $db->update("INSERT INTO ATTENDS VALUES(NULL, ?, ?)", "ii", array($id, $uid));
    }
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
      require dirname(__DIR__)."/php/ui/polls.php";
        if(isset($_GET["poll"])){
          if(empty($poll)){
            echo '<div id="notFound"><i class="massive orange question circle outline icon"></i><div class="ui header">'.$lang->poll_not_found.'<div></div>';
          } else {
            echo $poll["Description"];
            $body="";
            $foot ="";
            if($_SESSION["Admin"]){
              // Create admin UI
              $entries = $db->prepareQuery("SELECT USERS.Name, USERS.Informal, USERS.UserID, ENTRIES.*FROM USERS LEFT JOIN (SELECT POLL_ENTRIES.EntryID, POLL_ENTRIES.Entries, POLL_ENTRIES.UserID AS Poll_uid, POLLS.* FROM POLL_ENTRIES LEFT JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID WHERE POLL_ENTRIES.PollID=?) AS ENTRIES ON ENTRIES.Poll_uid = USERS.UserID ORDER BY USERS.UserID ","i",array($poll["PollID"]));
              foreach ($entries as $entry) {
                $body .= createTRow($entry["Name"], $entry["Entries"], (date_create($poll["Poll_end"])>date_create(date("Y-m-d"))&&($entry["Informal"] || $entry["UserID"]==$_SESSION["UserID"])), $poll["Duration"], $entry["UserID"]);
              }
              $body .= createSumRow($entries, $poll["Duration"]);
              if(date_create($poll["Poll_end"])>date_create(date("Y-m-d"))){
                $foot = createButtons($poll["Duration"], $poll["Start"], $db->prepareQuery("SELECT DATE(Start) AS Start FROM PRACTICES WHERE Start >=? ORDER BY Start","s",array($poll["Start"])));
              }
            } else {
              // Create User UI
              if($config->all_poll_entries){
                $entries = $db->prepareQuery("SELECT USERS.Name, USERS.Informal, USERS.UserID, ENTRIES.*FROM USERS LEFT JOIN (SELECT POLL_ENTRIES.EntryID, POLL_ENTRIES.Entries, POLL_ENTRIES.UserID AS Poll_uid, POLLS.* FROM POLL_ENTRIES LEFT JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID WHERE POLL_ENTRIES.PollID=?) AS ENTRIES ON ENTRIES.Poll_uid = USERS.UserID ORDER BY USERS.UserID","i",array($poll["PollID"]));
                foreach ($entries as $entry) {
                  $body .= createTRow($entry["Name"], $entry["Entries"], date_create($poll["Poll_end"])>date_create(date("Y-m-d"))&&$entry["UserID"]==$_SESSION["UserID"], $poll["Duration"], $entry["UserID"]);
                }
                $body .= createSumRow($entries, $poll["Duration"]);
              } else {
                $entry = $db->prepareQuery("SELECT POLL_ENTRIES.Entries, POLLS.* FROM POLL_ENTRIES JOIN POLLS ON POLL_ENTRIES.PollID = POLLS.PollID WHERE POLLS.PollID=? AND POLL_ENTRIES.UserID=?", "ii", array($_GET["poll"], $_SESSION["UserID"]))[0]??array();
                $body .= createTRow($_SESSION["UserName"], $entry["Entries"]??NULL, date_create($poll["Poll_end"])>date_create(date("Y-m-d")), $poll["Duration"], $_SESSION["UserID"]);
              }
            }
            echo '</div><div class="ui container">';
            echo '<div style="overflow-x:auto;padding-top:14px">';
            echo '<table class="ui very basic celled table"><thead>';
            echo createTHead($poll["Start"],$poll["Duration"]);
            echo '</thead><tbody>';
            echo $body;
            echo '</tbody>';
            echo $foot;
            echo '</table>';
            echo '</div>';
          }
        } else {
          //listing active polls
          $polls = $db->baseQuery("SELECT * FROM POLLS WHERE DATE_ADD(Start, INTERVAL (Duration - 1 ) DAY) >= CURDATE() ORDER BY DATE_ADD(Start, INTERVAL (Duration - 1 ) DAY)")??array();
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
