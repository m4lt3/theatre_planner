<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/php/auth/sessionValidate.php";
if(!$loggedIn){
  header("location:/theatre_planner/index.php");
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Admin Dashboard</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
    <style media="screen">
      main > .grid > .column {
        display: flex!important;
        justify-content: center;
        flex-direction: column;
      }
      main > .grid{
        height:100%;
      }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui container">
      <h1 class="ui large header"><?php echo $_SESSION["UserName"] ?>'s Admin Dashboard</h1>
    </main>
    <?php include $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/pages/footer.html" ?>
    <script type="text/javascript">
      document.getElementById("nav_dashboard").className="active item";
    </script>
  </body>
</html>
