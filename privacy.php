<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$config = require_once __DIR__ . "/php/config.php";
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Privacy</title>
    <?php include __DIR__ . "/pages/head.html"; ?>
  </head>
  <body>
    <div class="ui secondary pointing menu">
      <div class="ui container">
        <div class="ui item">
          <a href="index.php">Back to Main Page</a>
        </div>
      </div>
    </div>
    <main class="ui text container">
      <?php if(!$config->disable_standard_privacy){
        //TODO insert switch for language as soon as translation is supported
        include "php/translations/privacy/de.php";
      } ?>
      <br/><br/>
      <?php echo $config->custom_privacy_text ?>
    </main>
    <?php
    include __DIR__ . "/pages/footer.html";
    ?>
  </body>
</html>
