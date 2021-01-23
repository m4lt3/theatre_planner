<?php
session_start();
unset($_SESSION["UserID"]);
unset($_SESSION["UserName"]);
unset($_SESSION["Admin"]);
setcookie("theatreID", "", time() - 3600);
setcookie("theatre_h1", "", time() - 3600);
setcookie("theatre_h2", "", time() - 3600);
header("location:/theatre_planner/index.php");
?>
