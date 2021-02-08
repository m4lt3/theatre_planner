<?php
require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
if(!$loggedIn){
  header("location:../index.php");
}
require_once dirname(__DIR__) . "/php/utils/database.php";

$db = new DBHandler();

if (isset($_POST["reject"])){
  $db->update("DELETE FROM ATTENDS WHERE AttendsID=?", "i", array($_POST["reqID"]));
} elseif (isset($_POST["accept"])){
  $db->update("INSERT INTO ATTENDS VALUES (NULL, ?, ?)", "ii", array($_POST["reqID"], $_SESSION["UserID"]));
} elseif (isset($_POST["toggleValue"])){
  if (isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"]) {
    setcookie("theatre_past", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
  }
    $_SESSION["theatre_past"] = ($_POST["toggleValue"]=="true");
}

?>

<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_practices ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_practices ?></h1>
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
        $practiceQuery = "";
        if($config->user_focused){
          $practiceQuery = "SELECT PRACTICES.*, ME.AttendsID FROM PRACTICES LEFT JOIN (SELECT * FROM ATTENDS WHERE UserID = ?) AS ME ON ME.PracticeID = PRACTICES.PracticeID";
        } else {
          // Another query from hell, man I love SQL.

          $practiceQuery = "SELECT PRACTICES.*, SCENES.Name, ME.Mandatory AS AttendsID FROM PRACTICES LEFT JOIN PLANNED_ON ON PRACTICES.PracticeID = PLANNED_ON.PracticeID LEFT JOIN SCENES ON PLANNED_ON.SceneID = SCENES.SceneID LEFT JOIN (SELECT FEATURES.SceneID, FEATURES.Mandatory FROM FEATURES LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID LEFT JOIN PLAYS ON PLAYS.RoleID = ROLES.RoleID LEFT JOIN USERS ON USERS.UserID = PLAYS.UserID WHERE USERS.UserID = ?) AS ME ON ME.SceneID = SCENES.SceneID";
        }
        $divided = false;
        if (empty($_SESSION["theatre_past"]) || !$_SESSION["theatre_past"]) {
          $practiceQuery .= " WHERE PRACTICES.Start > NOW()";

          $divided = true;
        }
        $practiceQuery .= " ORDER BY PRACTICES.Start";

        if(!$config->user_focused){
          $practiceQuery .= ", ME.Mandatory DESC";
        }

        require dirname(__DIR__) . "/php/ui/practiceCards.php";

        $practices = $db->prepareQuery($practiceQuery, "i", array($_SESSION["UserID"]));


        $samePractice = array();

        foreach($practices??array() as $practice){
          if(empty($samePractice) || $samePractice[0]["PracticeID"]==$practice["PracticeID"]){
            $samePractice[] = $practice;
          } else {
            createCard($samePractice);
            $samePractice = array();
            $samePractice[] = $practice;
          }
          if (!$divided && $practice["Start"] > date("Y-m-d H:i:s")){
            echo '</div><div class="ui horizontal divider">'.$lang->today.'</div><div class="ui two stacked cards" style="margin-top:-14px">';
            $divided = !$divided;
          }
        }
        if (!empty($samePractice)){
          createCard($samePractice);
        }
        if(!$divided){
          echo '</div><div class="ui horizontal divider">'.$lang->today.'</div><div class="ui two stacked cards">';
        }
        ?>
      </div>
    </main>
    <?php
    include dirname(__DIR__) . "/footer.php";
    require dirname(__DIR__) . "/cookie_manager.php";
     ?>
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
