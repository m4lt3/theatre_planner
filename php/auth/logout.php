<?php
session_start();
unset($_SESSION["UserID"]);
unset($_SESSION["UserName"]);
unset($_SESSION["Admin"]);
unset($_SESSION);
session_destroy();

setcookie("theatreID", "", array("expires"=>time() - 3600, "samesite"=>"Strict", "path"=>"/"));
setcookie("theatre_h1", "", array("expires"=>time() - 3600, "samesite"=>"Strict", "path"=>"/"));
setcookie("theatre_h2", "", array("expires"=>time() - 3600, "samesite"=>"Strict", "path"=>"/"));
setcookie("PHPSESSID","", array("expires"=>time() - 3600, "samesite"=>"Strict", "path"=>"/"));

header("location:../../index.php");
?>
