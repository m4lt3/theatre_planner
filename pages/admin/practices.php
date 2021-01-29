<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/practice.php";

  $db = new DBHandler();

  if(isset($_POST["addDate"])){
    // A practice date is to be inserted
    $db->update("INSERT INTO PRACTICES VALUES (NULL, ?, ?)", "ss", array($_POST["titleInput"], $_POST["dateInput"]));
  } elseif (isset($_POST["rm_date"])){
    // Remove a practice date
    if(!$db->update("DELETE FROM PRACTICES WHERE PracticeID=?", "i", array($_POST["rm_date"]))){
      // If deletion fails due to foreign key references, delete dependencies first
      $dependencies = $db->prepareQuery("SELECT AttendsID FROM ATTENDS WHERE PracticeID=?", "i", array($_POST["rm_date"]));
      foreach ($dependencies??array() as $dependency) {
        $db->update("DELETE FROM ATTENDS WHERE AttendsID=?","i", array($dependency["AttendsID"]));
      }
      $dependencies = $db->prepareQuery("SELECT PlanID FROM PLANNED_ON WHERE PracticeID=?","i",array($_POST["rm_date"]));
      foreach ($dependencies??array() as $dependency) {
        $db->update("DELETE FROM PLANNED_ON WHERE PlanID=?","i",array($dependency["PlanID"]));
      }
      // delete again
      $db->update("DELETE FROM PRACTICES WHERE PracticeID=?", "i", array($_POST["rm_date"]));
    }
  } elseif (isset($_POST["toggleValue"])){
    // Toggling whether past dates are shown or not.
    if(isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"]){
      // Only save preference in cookie if allowed
      setcookie("theatre_past", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
    }
    $_SESSION["theatre_past"] = ($_POST["toggleValue"]=="true");
  } elseif (isset($_POST["rm_planned"])){
    //Delete scene assignment (admin focused mode only)
    $db->update("DELETE FROM PLANNED_ON WHERE PlanID=?","i", array($_POST["rm_planned"]));
  } elseif(isset($_POST["addPlanned"])){
    $db->update("INSERT INTO PLANNED_ON VALUES (NULL, ?, ?)", "ii", array($_POST["PracticeID"], $_POST["newPlan"]));
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_practice_management ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
    <link rel="stylesheet" href="../../css/jquery.datetimepicker.min.css">
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_practice_management ?></h1>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="field">
            <label for="titleInput"><?php echo $lang->practice_management_title_input ?></label>
            <input type="text" name="titleInput" maxlength="32">
          </div>
          <div class="required field">
            <label for="dateInput"><?php echo $lang->practice_date ?></label>
            <div class="ui action input">
              <input id="dateInput" type="text" name="dateInput" value="">
              <button type="button" class="ui icon button" name="button" onclick="openPicker()"><i class="calendar alternate outline icon"></i></button>
            </div>
          </div>
        </div>
        <input type="submit" class="ui primary button" name="addDate" value="<?php echo $lang->add_date ?>">
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
        require dirname(dirname(__DIR__))."/php/ui/admin/practiceCard.php";

        $practiceQuery = "";
        $card_function = "";
        $sceneQuery = "";

        if($config->user_focused){

          //Query of hell - It actually just misses one relation table until it reaches back to itself

          $practiceQuery = "SELECT PRACTICES.PracticeID, PRACTICES.Title, PRACTICES.Start, USERS.UserID, USERS.Name, ROLES.RoleID, ROLES.Name AS Role FROM PRACTICES LEFT JOIN ATTENDS ON PRACTICES.PracticeID = ATTENDS.PracticeID LEFT JOIN USERS ON USERS.UserID = ATTENDS.UserID LEFT JOIN PLAYS ON PLAYS.UserID = USERS.UserID LEFT JOIN ROLES ON PLAYS.RoleID = ROLES.RoleID";
          $card_function = "generateUserFocusedCardStack";
          $sceneQuery = "SELECT FEATURES.SceneID, FEATURES.RoleID, FEATURES.Mandatory, SCENES.Name FROM FEATURES JOIN SCENES ON FEATURES.SceneID = SCENES.SceneID ORDER BY FEATURES.SceneID";
        } else {
          $practiceQuery = "SELECT PRACTICES.PracticeID, PRACTICES.Title, PRACTICES.Start, PLANNED_ON.PlanID, SCENES.SceneID, SCENES.Name AS Scene FROM PRACTICES LEFT JOIN PLANNED_ON ON PLANNED_ON.PracticeID = PRACTICES.PracticeID LEFT JOIN SCENES ON SCENES.SceneID = PLANNED_ON.SceneID ";
          $card_function = "createAdminFocusedCardStack";
          $sceneQuery = "SELECT SceneID, Name FROM SCENES";
        }


        $divided=false;
        if(empty($_SESSION["theatre_past"]) || !$_SESSION["theatre_past"]){
          $practiceQuery .= " WHERE PRACTICES.Start > NOW()";
          $divided=true;
        }

        if($config->user_focused){
          $practiceQuery .= " ORDER BY PRACTICES.Start, USERS.UserID";
        } else {
          $practiceQuery .= " ORDER BY PRACTICES.Start";
        }
        $practices = $db->baseQuery($practiceQuery);
        $allScenes= $db->baseQuery($sceneQuery);

        $card_function($practices, $allScenes);
        ?>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
     ?>
    <script src="../../js/jquery.datetimepicker.full.min.js" charset="utf-8"></script>
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
    $(".ui.dropdown").dropdown();
    </script>
  </body>
</html>
