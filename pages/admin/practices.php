<?php
  require $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  $db = new DBHandler();
  $past = false;

  if(isset($_POST["addDate"])){
    $db->update("INSERT INTO PRACTICES VALUES (NULL, ?, ?)", "ss", array($_POST["titleInput"], $_POST["dateInput"]));
  } elseif (isset($_POST["rm_date"])){
    if(!$db->update("DELETE FROM PRACTICES WHERE PracticeID=?", "i", array($_POST["rm_date"]))){
      $dependencies = $db->prepareQuery("SELECT AttendsID FROM ATTENDS WHERE PracticeID=?", "i", array($_POST["rm_date"]));
      foreach ($dependencies as $dependency) {
        $db->update("DELETE FROM ATTENDS WHERE AttendsID=?","i", array($dependency["AttendsID"]));
      }
      $db->update("DELETE FROM PRACTICES WHERE PracticeID=?", "i", array($_POST["rm_date"]));
    }
  } elseif (isset($_POST["toggleValue"])){
    $past = $_POST["toggleValue"];
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Practices</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
    <link rel="stylesheet" href="/theatre_planner/css/jquery.datetimepicker.min.css">
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <main class="ui text container">
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="field">
            <label for="titleInput">Practice Title</label>
            <input type="text" name="titleInput" maxlength="32">
          </div>
          <div class="required field">
            <label for="dateInput">Date of the practice</label>
            <div class="ui action input">
              <input id="dateInput" type="text" name="dateInput" value="">
              <button type="button" class="ui icon button" name="button" onclick="openPicker()"><i class="calendar alternate outline icon"></i></button>
            </div>
          </div>
        </div>
        <input type="submit" class="ui primary button" name="addDate" value="Add Practice">
      </form>
      <br/>
      <form id="toggleForm" action="" method="post">
        <input id="toggleValue" type="hidden" name="toggleValue" value="">
        <div class="ui toggle checkbox">
          <input type="checkbox" name="toggle_past" id="toggle_past"
          <?php
          if($past == "true"){
            echo "checked";
          }
           ?>
          >
          <label>Show past dates</label>
        </div>
      </form>
      <br/>
      <div class="ui two stacked cards">
        <?php
        $practices = $db->baseQuery("SELECT * FROM PRACTICES" . (($past=="true")?"":" WHERE Start > NOW()") ." ORDER BY Start");
        $divided = !($past == "true");
        foreach ($practices as $practice) {
          if ($practice["Start"] > date("Y-m-d H:i:s") && !$divided){
            echo '</div><div class="ui horizontal divider">Today</div><div class="ui two stacked cards" style="margin-top:-14px">';
            $divided = !$divided;
          }
          createCard($practice["PracticeID"], $practice["Title"],$practice["Start"]);
        }

        function createCard($id, $title, $date){
          $format = new DateTime($date);
          $button=<<<EOT
          <form method="POST" action="" style="margin-bottom:0;">
            <input type="hidden" name="rm_date" value="$id">
            <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
          </form>
EOT;
          $card=<<<EOT
          <div class="ui card">
            <div class="content">
              <div class="header">
                $title
                <div class="right floated meta">
                  #$id
                </div>
              </div>
              <div class="meta">
                {$format->format("d.m.Y H:i")}
              </div>
            </div>
            $button
          </div>
EOT;
          echo $card;
        }
        ?>
      </div>
    </main>
    <?php include $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/pages/footer.html" ?>
    <script src="/theatre_planner/js/jquery.datetimepicker.full.min.js" charset="utf-8"></script>
    <script type="text/javascript">
    $(document).ready(function(){
      $('#dateInput').datetimepicker({
        minDate: Date.now(),
        minTime: Date.now(),
        dayOfWeekStart: 1,
        openOnFocus: false,
        closeOnWithoutClick: false,
        closeOnTimeSelect: false,
        closeOnInputClick: false,
        showApplyButton: true
      });

      $('#toggle_past').change(function(){
        document.getElementById('toggleValue').value = document.getElementById('toggle_past').checked;
        $('#toggleForm').submit();
      });
    });

    function openPicker(){
      $('#dateInput').datetimepicker('show');
    }

    </script>
  </body>
</html>
