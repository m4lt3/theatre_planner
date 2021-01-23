<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/php/auth/sessionValidate.php";
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Users</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui container">

    </main>
    <?php include $_SERVER["DOCUMENT_ROOT"] . "/theatre_planner/pages/footer.html" ?>
    <script type="text/javascript">
      document.getElementById("nav_dashboard").className="active item";
    </script>
  </body>
</html>
