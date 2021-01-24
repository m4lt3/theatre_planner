<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

$loggedIn = false;

if(isset($_SESSION["UserID"])){
  $loggedIn = true;
} elseif (isset($_COOKIE["theatreID"]) && isset($_COOKIE["theatre_h1"]) && isset($_COOKIE["theatre_h2"])){
  $pVerified = false;
  $sVerified = false;
  $eVerified = false;

  $db = new DBHandler();
  $token = $db->prepareQuery("SELECT * FROM TOKENS WHERE TokenID=?","i", array($_COOKIE["theatreID"]))[0];

  if(password_verify($_COOKIE["theatre_h1"], $token["Password"])){
    $pVerified = true;
  }

  if(password_verify($_COOKIE["theatre_h2"], $token["Selector"])){
    $sVerified = true;
  }

  $current_time = time();
  $current_date = date("Y-m-d H:i:s", $current_time);
  if($token["Expires"] >= $current_date){
    $eVerified = true;
  }

  if ($pVerified && $sVerified && $eVerified){
    $loggedIn = true;
    $result = $db->prepareQuery("SELECT Name, Admin FROM USERS WHERE UserID=?","i", array($token["UserID"]))[0];
    $_SESSION["UserID"] = $token["UserID"];
    $_SESSION["UserName"] = $result["Name"];
    $_SESSION["Admin"] = $result["Admin"];
  } else {
    if(!$eVerified){
      $db->update("DELETE FROM TOKENS WHERE TokenID=?","i",array($token["TokenID"]));
    }

    setcookie("theatreID", "", array("expires"=> time() -3600, "samesite"=>"Strict","path"=>"/"));
    setcookie("theatre_h1", "", array("expires"=> time() -3600, "samesite"=>"Strict","path"=>"/"));
    setcookie("theatre_h2", "", array("expires"=> time() -3600, "samesite"=>"Strict","path"=>"/"));
  }
}
?>
