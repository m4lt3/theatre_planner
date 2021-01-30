<?php
require_once __DIR__ . "/php/utils/loadPreferences.php";
$config = require_once __DIR__ . "/php/config.php";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->imprint ?></title>
    <?php include __DIR__ . "/head.php"; ?>
  </head>
  <body>
    <div class="ui secondary pointing menu">
      <div class="ui container">
        <div class="ui item">
          <a href="/index.php"><?php echo $lang->to_main ?></a>
        </div>
      </div>
    </div>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->imprint ?></h1>
      <?php
      if(!empty($config->contact_info)){
        echo '<h2 class="ui medium header">'.$lang->contact_info.'</h2><p>';
        echo $config->contact_info;
        echo "</p><br/>";
      }
      echo $config->imprint_text;
      ?>

      <br/><br/>
      <h2 class="ui medium header"><?php echo $lang->third_party_content ?></h2>
      <ul>
        <li><a href="https://github.com/xdan/datetimepicker" target="_blank">Datepicker</a></li>
        <li><a href="https://semantic-ui.com" target="_blank">Semantic UI</a></li>
        <li><a href="https://assets.wildbit.com/postmark/templates/dist/password_reset.html" target="_blank">Mail template</a></li>
        <li><a href="https://fontawesome.com/license/free" target="_blank">Cookie & Masks by fontawesome</a></li>
      </ul>
    </main>
    <?php
    include __DIR__ . "/footer.php";
    ?>
  </body>
</html>
