<?php
  require_once dirname(__DIR__) . "/php/auth/sessionValidate.php";
  require_once dirname(__DIR__) . "/php/utils/loadPreferences.php";
  require_once dirname(__DIR__) . "/php/utils/database.php";
  if(!$loggedIn){
    header("location:../index.php");
  }

  if (isset($_POST["toggleValue"])){
    if (isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"]) {
      setcookie("theatre_me", ($_POST["toggleValue"]=="true"), array("expires"=>time() + 2592000, "samesite"=>"Strict", "path"=>"/"));
    }
      $_SESSION["theatre_me"] = ($_POST["toggleValue"]=="true");
  }

  $db = new DBHandler();
  $query = "SELECT ROLES.*, USERS.UserID FROM ROLES LEFT JOIN PLAYS ON ROLES.RoleID = PLAYS.RoleID LEFT JOIN USERS ON PLAYS.UserID = USERS.UserID";
  if(isset($_SESSION["theatre_me"]) && $_SESSION["theatre_me"]){
    $query .= " WHERE USERS.UserID=?";
  }
  $query .= " ORDER BY ROLES.RoleID";

  $roles = array();
  if(isset($_SESSION["theatre_me"]) && $_SESSION["theatre_me"]){
    $roles = $db->prepareQuery($query, "i", array($_SESSION["UserID"]));
  } else {
    $roles = $db->baseQuery($query);
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->roles ?></title>
    <?php include dirname(__DIR__) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->roles ?></h1>
      <br/>
      <form id="toggleForm" action="" method="post">
        <input id="toggleValue" type="hidden" name="toggleValue" value="">
        <div class="ui toggle checkbox">
          <input type="checkbox" name="toggle_me" id="toggle_me"
          <?php
          if(!empty($_SESSION["theatre_me"]) && $_SESSION["theatre_me"]){
            echo "checked";
          }
           ?>
          >
          <label><?php echo $lang->show_my . " " . $lang->roles?></label>
        </div>
      </form>
      <br/>
      <div class="ui two stackable cards">
        <?php
        require dirname(__DIR__)."/php/ui/createRoleCard.php";
          if(!empty($roles)){
            foreach ($roles as $role) {
              echo createRoleCard($role["RoleID"], $role["Name"], $role["Description"], $role["UserID"]);
            }
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

        $('#toggle_me').change(function(){
          document.getElementById('toggleValue').value = document.getElementById('toggle_me').checked;
          $('#toggleForm').submit();
        });
      });
    </script>
  </body>
  </body>
</html>
