<?php
  require $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  $db = new DBHandler();
  $inserted = true;

 if(isset($_POST["addScene"])){
   $db->update("INSERT INTO SCENES VALUES (NULL, ?, ?)", "ss", array($_POST["sceneName"], $_POST["sceneDescription"]));
 }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Scenes</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <main class="ui text container">
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="sceneName">Scene Name</label>
          <input required="true" type="text" name="sceneName" maxlength="32">
        </div>
        <div class="field">
          <label for="sceneDescription">Scene Description</label>
          <textarea name="sceneDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addScene" value="Create Scene">
      </form>

      <br/>

      <div class="ui two stackable cards">
        <?php
        
        ?>
      </div>
    </main>
    <?php
    include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/footer.html";
    if($inserted){
      echo '<script>document.getElementById("mailError").style.display="none";</script>';
    } else {
      echo '<script>document.getElementById("mailError").style.display="block";</script>';
    }
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
      $('.ui.dropdown').dropdown();
    });
    </script>
  </body>
</html>
