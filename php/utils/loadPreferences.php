<?php
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
?>
