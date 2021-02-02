<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }



  $db = new DBHandler();
  $everything = $db->baseQuery("SELECT SCENES.Name AS Scene, SCENES.SceneID, SCENES.Sequence, FEATURES.FeatureID, FEATURES.Mandatory, ROLES.RoleID, ROLES.Name AS Role, PLAYS.PlaysID, USERS.UserID, USERS.Name FROM SCENES LEFT JOIN FEATURES ON SCENES.SceneID = FEATURES.SceneID LEFT JOIN ROLES ON FEATURES.RoleID = ROLES.RoleID LEFT JOIN PLAYS ON ROLES.RoleID = PLAYS.RoleID LEFT JOIN USERS ON PLAYS.UserID = USERS.UserID ORDER BY SCENES.Sequence, ROLES.RoleID");
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_compact_view ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui container">
      <h1 class="ui large header"><?php echo $lang->title_compact_view ?></h1>
      <table class="ui table">
        <thead>
          <th><?php echo $lang->scenes ?></th>
          <th><?php echo $lang->roles ?></th>
          <th><?php echo $lang->actors ?></th>
        </thead>
        <tbody>
          <?php
          require dirname(dirname(__DIR__))."/php/ui/admin/createCompactTable.php";
          echo createCompactTable($everything);
          ?>
        </tbody>
      </table>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
      $(".ui.search.dropdown").dropdown({
        allowAdditions: true
      });
      $(".ui.dropdown").dropdown();
    </script>
  </body>
</html>
