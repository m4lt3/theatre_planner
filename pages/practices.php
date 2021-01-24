<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
if(!$loggedIn){
  header("location:/theatre_planner/index.php");
}
require_once dirname(__DIR__) . "/php/utils/database.php";

$db = new DBHandler();

if (isset($_POST["reject"])){
  $db->update("DELETE FROM ATTENDS WHERE AttendsID=?", "i", array($_POST["reqID"]));
} elseif (isset($_POST["accept"])){
  $db->update("INSERT INTO ATTENDS VALUES (NULL, ?, ?)", "ii", array($_POST["reqID"], $_SESSION["UserID"]));
} elseif (isset($_POST["toggleValue"])){
  setcookie("theatre_past", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
  header("location:./practices.php");
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Practices</title>
    <?php include dirname(__DIR__) . "/pages/head.html"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header">Your practices</h1>
      <br/>
      <form id="toggleForm" action="" method="post">
        <input id="toggleValue" type="hidden" name="toggleValue" value="">
        <div class="ui toggle checkbox">
          <input type="checkbox" name="toggle_past" id="toggle_past"
          <?php
          if(!empty($_COOKIE["theatre_past"])){
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
        $practiceQuery = "SELECT PRACTICES.*, ME.AttendsID FROM PRACTICES LEFT JOIN (SELECT * FROM ATTENDS WHERE UserID = ?) AS ME ON ME.PracticeID = PRACTICES.PracticeID";
        $divided = false;
        if (empty($_COOKIE["theatre_past"])) {
          $practiceQuery .= " WHERE PRACTICES.Start > NOW()";

          $divided = true;
        }
        $practiceQuery .= " ORDER BY PRACTICES.Start";

        foreach($db->prepareQuery($practiceQuery, "i", array($_SESSION["UserID"])) as $practice){
          if (!$divided && $practice["Start"] > date("Y-m-d H:i:s")){
            echo '</div><div class="ui horizontal divider">Today</div><div class="ui two stacked cards" style="margin-top:-14px">';
            $divided = !$divided;
          }
          createCard($practice["PracticeID"], $practice["Title"], $practice["Start"], $practice["AttendsID"]);
        }

        function createCard($id, $title, $date, $attends){
          $format = new DateTime($date);
          $disabled = ($date < date("Y-m-d H:i:s"))?"disabled":"";
          $buttons = "";
          $reqID = $id;
          if(!empty($attends)){
            $reqID = $attends;
          }

          if (empty($attends)) {
            $buttons=<<<EOT
            <button type="button" class="ui red $disabled icon button" name="reject"><i class="close icon"></i></button>
            <button type="submit" class="ui basic green $disabled icon button" name="accept"><i class="check icon"></i></button>
EOT;
          } else {
            $buttons=<<<EOT
            <button type="submit" class="ui basic red $disabled icon button" name="reject"><i class="close icon"></i></button>
            <button type="button" class="ui green $disabled icon button" name="accept"><i class="check icon"></i></button>
EOT;
          }

          $card=<<<EOT
          <div class="ui card">
            <div class="content">
              <div class="header">
                $title
                <div class="right floated meta">#$id</div>
              </div>
              <div class="meta">
                {$format->format("d.m.Y H:i")}
              </div>
            </div>
            <div class="extra content">
              <form action="" method="POST">
                <input type="hidden" name="reqID" value ="$reqID">
                <div class="ui two buttons">
                  $buttons
                </div>
              </form>
            </div>
          </div>
EOT;
          echo $card;
        }
        ?>
      </div>
    </main>
    <?php include dirname(__DIR__) . "/pages/footer.html" ?>
    <script type="text/javascript">
      document.getElementById("nav_practices").className="active item";
    </script>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.ui.checkbox').checkbox();

        $('#toggle_past').change(function(){
          document.getElementById('toggleValue').value = document.getElementById('toggle_past').checked;
          $('#toggleForm').submit();
        });
      });
    </script>
  </body>
</html>
