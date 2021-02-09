<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:/index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }

  $db = new DBHandler();
  if(isset($_POST["start"])){
    $start = date_create($_POST["start"]);
    $end = date_create($_POST["end"]);
    $duration = date_diff($start, $end)->days + 1;
    $db->update("INSERT INTO POLLS VALUES(NULL, ?, ?, ?)", "sis", array($_POST["start"],$duration,$_POST["description"]??""));
  } elseif (isset($_POST["toggleValue"])){
    // Toggling whether past dates are shown or not.
    if(isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"]){
      // Only save preference in cookie if allowed
      setcookie("theatre_past", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
    }
    $_SESSION["theatre_past"] = ($_POST["toggleValue"]=="true");
  } elseif (isset($_POST["rm_poll"])){
    $db->update("DELETE FROM POLL_ENTRIES WHERE PollID=?","i", array($_POST["rm_poll"]));
    $db->update("DELETE FROM POLLS WHERE PollID=?","i", array($_POST["rm_poll"]));
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_date_finder ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_date_finder ?></h1>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="required field">
            <label><?php echo $lang->start_date ?></label>
            <input id="start" type="date" name="start" value="" required onchange="setMinDate()">
          </div>
          <div class="required field">
            <label><?php echo $lang->end_date ?></label>
            <input id="end" type="date" name="end" value="" required min="">
          </div>
        </div>
        <div class="field">
          <label><?php echo $lang->description ?></label>
          <textarea name="description" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <button class="ui primary button" type="submit" name="button"><?php echo $lang->create_poll ?></button>
      </form>
      <br/>
      <form id="toggleForm" action="" method="post">
        <input id="toggleValue" type="hidden" name="toggleValue" value="">
        <div class="ui toggle checkbox">
          <input type="checkbox" name="toggle_past" id="toggle_past"
          <?php
          if(!empty($_SESSION["theatre_past"]) && $_SESSION["theatre_past"]){
            echo "checked";
          }
           ?>
          >
          <label><?php echo $lang->show_past ?></label>
        </div>
      </form>
      <br/>
      <div class="ui two stacked cards">
        <?php
        $pollQuery = "SELECT * FROM POLLS";
        $divided=false;
        if(empty($_SESSION["theatre_past"]) || !$_SESSION["theatre_past"]){
          $pollQuery .= " WHERE Start >= CURDATE()";
          $divided=true;
        }
        $polls = $db->baseQuery($pollQuery)??array();
        foreach ($polls as $poll) {
          $date = date_create($poll["Start"]);
          $startFormat = date_format($date, "d.m.Y");
          date_add($date, date_interval_create_from_date_string(($poll["Duration"]-1)." days"));
          $endFormat = date_format($date, "d.m.Y");

          $card = <<<EOT
          <div class="ui card">
            <div class="content">
              <div class="header">
                {$lang->poll} #{$poll["PollID"]}
              </div>
            </div>
            <div class="content">
              <div class="ui sub header">{$lang->timeframe}</div>
              $startFormat - $endFormat
            </div>
            <div class="content">
              <div class="ui sub header">
                {$lang->description}
              </div>
              {$poll["Description"]}
            </div>
            <form method="POST" action="" style="margin-bottom:0;">
              <input type="hidden" name="rm_poll" value="{$poll["PollID"]}">
              <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
            </form>
          </div>
EOT;
          echo $card;
        }
        ?>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
      function setMinDate(){
        document.getElementById("end").min = document.getElementById("start").value;
      }

      $('#toggle_past').change(function(){
        document.getElementById('toggleValue').value = document.getElementById('toggle_past').checked;
        $('#toggleForm').submit();
      });
    </script>
  </body>
</html>
