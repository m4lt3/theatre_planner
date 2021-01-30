<?php
// loading language preference
$langs_available = array('de', 'en');
$lang = "";
if(isset($_COOKIE["theatre_lang"])){
  $lang = $_COOKIE["theatre_lang"];
} else {
  $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
  $lang = in_array($lang, $langs_available) ? $lang : 'en';
}

$lang = include dirname(__DIR__) . "/translations/" . $lang . ".php";
if(empty($lang)){
  $lang = include dirname(__DIR__) . "/translations/en.php";
}

// loading past dates / my roles preferences
if(isset($_COOKIE["theatre_past"])){
  $_SESSION["theatre_past"] = true;
}
if(isset($_COOKIE["theatre_me"])){
  $_SESSION["theatre_me"] = true;
}

// handling cookie menagement
if(!isset($_SESSION["cookies_allowed"])){
  if(isset($_COOKIE["theatre_cookies"])){
    $_SESSION["show_cookie_dialouge"] = false;
    $_SESSION["cookies_allowed"] = true;
  } else {
    $_SESSION["show_cookie_dialouge"] = isset($_SESSION["show_cookie_dialouge"])?$_SESSION["show_cookie_dialouge"]:true;
  }
} else {
  if($_SESSION["cookies_allowed"]){
    if(!isset($_COOKIE["theatre_cookies"])){
      setcookie("theatre_cookies", "1", array("expires"=>time() + 2592000, "samesite"=>"Lax", "path"=>"/"));
    }
  }
}

$config = require dirname(__DIR__) . "/config.php";
?>
