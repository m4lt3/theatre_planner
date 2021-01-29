<?php
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
      foreach ($dependencies as $dependency) {
        $db->update("DELETE FROM ATTENDS WHERE AttendsID=?","i", array($dependency["AttendsID"]));
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
        //Query of hell; It actually just misses one relation table until it reaches back to itself.
        $practiceQuery = "SELECT PRACTICES.PracticeID, PRACTICES.Title, PRACTICES.Start, USERS.UserID, USERS.Name, ROLES.RoleID, ROLES.Name AS Role FROM PRACTICES LEFT JOIN ATTENDS ON PRACTICES.PracticeID = ATTENDS.PracticeID LEFT JOIN USERS ON USERS.UserID = ATTENDS.UserID LEFT JOIN PLAYS ON PLAYS.UserID = USERS.UserID LEFT JOIN ROLES ON PLAYS.RoleID = ROLES.RoleID";

        $divided=false;
        if(empty($_SESSION["theatre_past"]) || !$_SESSION["theatre_past"]){
          $practiceQuery .= " WHERE PRACTICES.Start > NOW()";
          $divided=true;
        }
        $practiceQuery .= " ORDER BY PRACTICES.Start, USERS.UserID";
        $practices = $db->baseQuery($practiceQuery);
        $practice_collection = new Practice(-1,"","");
        $allScenes= $db->baseQuery("SELECT FEATURES.SceneID, FEATURES.RoleID, FEATURES.Mandatory, SCENES.Name FROM FEATURES JOIN SCENES ON FEATURES.SceneID = SCENES.SceneID ORDER BY FEATURES.SceneID");
        if(!empty($practices)){
          foreach ($practices as $practice) {
            // as with the user, due to the massive joins, a practice can (and most likely will) appear multiple times, so the values are stored together until the are ready to display.
            if($practice["PracticeID"] != $practice_collection->id){
              if($practice_collection->id != -1){
                // Detect all practiceable scenes before displaying
                $practice_collection->detectScenes($allScenes);

                echo createPracticeCard($practice_collection);
                if (!$divided && $practice["Start"] > date("Y-m-d H:i:s")){
                  // If new practice is in the future and past dates are enabled and not yet separated, draw a line
                  echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards" style="margin-top:-14px">';
                  $divided = !$divided;
                }
              }
              // initialize new Collection for the next practice
              $practice_collection = new Practice($practice["PracticeID"], $practice["Title"], $practice["Start"]);
            }
            if(count($practice_collection->attendees) == 0){
              // if array is empty, then simply add user
              array_push($practice_collection->attendees, array("id"=>$practice["UserID"], "name"=>$practice["Name"]));
            } elseif($practice["UserID"] != $practice_collection->attendees[count($practice_collection->attendees)-1]["id"]){
              // if a new user attends the practice, add it to the list
              array_push($practice_collection->attendees, array("id"=>$practice["UserID"], "name"=>$practice["Name"]));
            }
            // add the Role to the list
            array_push($practice_collection->roles, $practice["RoleID"]);
          }
          // print last practice as it didn't get triggered
          $practice_collection->detectScenes($allScenes);
          echo createPracticeCard($practice_collection);
          if(!$divided){
            // If all datas have been in the past, at least indicate that now
            echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards">';
          }
        }
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
    </script>
  </body>
</html>
