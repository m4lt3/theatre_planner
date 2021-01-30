<?php
require_once __DIR__ . "/php/utils/loadPreferences.php";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->privacy ?></title>
    <?php include __DIR__ . "/head.php"; ?>
  </head>
  <body>
    <div class="ui secondary pointing menu">
      <div class="ui container">
        <div class="ui item">
          <a href="<?php echo $config->subfolder ?>/index.php"><?php echo $lang->to_main ?></a>
        </div>
      </div>
    </div>
    <main class="ui text container">
      <?php if(!$config->disable_standard_privacy){
        switch($lang->lang){
          case "de":
            include "php/translations/privacy/de.php";
            break;
          default:
            include "php/translations/privacy/en.php";
        }
      } ?>
      <br/><br/>
      <?php echo $config->custom_privacy_text ?>
    </main>
    <?php
    include __DIR__ . "/footer.php";
    ?>
  </body>
</html>
