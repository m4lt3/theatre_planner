<link rel="stylesheet" href="/theatre_planner/css/semantic.min.css">
<link rel="stylesheet" href="/theatre_planner/css/custom.css">
<meta name="viewport" content="width=device-width, inital-scale=1">
<meta name="author" content="Malte Jakob Informatik">
<!--- favicon stuff by ralfavicongenerator.net -->
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/favicon-16x16.png">
<link rel="manifest" href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__ . "/php/translations/webmanifest/" . $lang->lang)?>.webmanifest">
<link rel="mask-icon" href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__)?>/safari-pinned-tab.svg" color="#00b5ad">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="theme-color" content="#00b5ad">

<!-- opengraph stuff by realfavicongenerator.net -->
<meta property="og:image:width" content="279">
<meta property="og:image:height" content="279">
<meta property="og:title" content="<?php echo $lang->title ?>">
<meta property="og:description" content="<?php echo $lang->og_description ?>">
<meta property="og:url" content="<?php echo ((!empty($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] !== 'off')?"https://":"http://") . $_SERVER['SERVER_NAME'] . str_replace($_SERVER["DOCUMENT_ROOT"], '', __DIR__) ?>/index.php">
<meta property="og:image" content="<?php echo ((!empty($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] !== 'off')?"https://":"http://") . $_SERVER['SERVER_NAME'] . str_replace($_SERVER["DOCUMENT_ROOT"], '', __DIR__) ?>/images/og-image.jpg">

<?php
$config = require "php/config.php";
echo $config->header_tags;
?>
