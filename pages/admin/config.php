<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  $config = require dirname(dirname(__DIR__))."/php/config.php";
  //save the config form data to file if anything has been changed
  if (isset($_POST["db_changed"])) {
    $config->db_server = $_POST["db_server"];
    $config->db_name = $_POST["db_name"];
    $config->db_user = $_POST["db_user"];
    $config->db_pwd = $_POST["db_pwd"];
    file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
  } elseif (isset($_POST["mode_changed"])){
    $config->user_focused =  ($_POST["mode_changed"]=="true")?true:false;
    file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
  } elseif(isset($_POST["privacy_imprint_changed"])){
    $config->contact_info = $_POST["contact_info"];
    $config->imprint_text = $_POST["imprint_text"];
    $config->data_protection_officer = $_POST["data_protection_officer"];
    $config->custom_privacy_text = $_POST["custom_privacy_text"];
    $config->disable_standard_privacy = isset($_POST["disable_standard_privacy"]);
    file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
  } elseif(isset($_POST["misc_changed"])){
    $config->header_tags = $_POST["header_tags"];
    $config->admin_mail = $_POST["admin_mail"];
    file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->title_server_configuration ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
    <style media="screen">
      .segment {
        width: 50%!important;
        border: none!important;
      }
      .segment:hover{
        background-color: #f3f4f5!important;
      }
      .label{
        cursor:help;
      }
    </style>
  </head>
  <body>
    <?php include "nav.php" ?>
    <main class="ui text container">
      <h1 class="ui large header"><?php echo $lang->title_server_configuration ?></h1>
      <h2 class="ui medium header"><?php echo $lang->db_connection ?></h2>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="field">
            <label for="db_server"><?php echo $lang->db_server ?></label>
            <input type="text" name="db_server" value="<?php echo $config->db_server ?>">
          </div>
          <div class="field">
            <label for="db_name"><?php echo $lang->db_name ?></label>
            <input type="text" name="db_name" value="<?php echo $config->db_name ?>">
          </div>
        </div>
        <div class="two fields">
          <div class="field">
            <label for="db_user"><?php echo $lang->db_user ?></label>
            <input type="text" name="db_user" value="<?php echo $config->db_user ?>">
          </div>
          <div class="field">
            <label for="db_pwd"><?php echo $lang->db_pwd ?></label>
            <div class="ui action input">
              <input type="password" name="db_pwd" value="<?php echo $config->db_pwd ?>" id="db_pwd">
              <button class="ui icon button" type="button" name="button" id="show_pwd"><i class="low vision icon"></i></button>
            </div>
          </div>
        </div>
        <input class="ui primary button" type="submit" name="db_changed" value="<?php echo $lang->save ?>">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header"><?php echo $lang->planning_mode ?></h2>
      <div class="ui horizontal segments">
        <div class="ui <?php if($config->user_focused){echo "teal";} ?> segment" id="actor_segment">
          <h3 class="ui small sub header"><?php echo $lang->title_actor_focused ?></h3>
          <?php echo $lang->description_actor_focused ?>
        </div>
        <div class="ui vertical divider">
          <?php echo $lang->or ?>
        </div>
        <div class="ui right aligned <?php if(!$config->user_focused){echo "teal";} ?> segment" id="admin_segment">
          <h3 class="ui small right aligned sub header"><?php echo $lang->title_admin_focused ?></h3>
          <?php echo $lang->description_admin_focused ?>
        </div>
      </div>
      <form action="" method="post" id="focus_form">
        <input type="hidden" name="mode_changed" id="focus_value" value="undefined">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header"><?php echo $lang->privacy_imprint ?></h2>
      <div class="ui info icon message">
        <i class="code icon"></i>
        <?php echo $lang->html_notice ?>
      </div>
      <form class="ui form" action="" method="post">
        <div class="field">
          <label for="contact_info"><?php echo $lang->contact_info ?> <div class="ui circular label" id="contact_help"><i class="fitted question icon"></i></div></label>
          <textarea name="contact_info" rows="2" cols="80"><?php echo $config->contact_info ?></textarea>
        </div>
        <div class="field">
          <label for="imprint_text"><?php echo $lang->imprint_text ?> <div class="ui circular label" id="imprint_help"><i class="fitted question icon"></i></div></label>
          <textarea name="imprint_text" rows="8" cols="80"><?php echo $config->imprint_text ?></textarea>
        </div>
        <div class="field">
          <label for="data_protection_officer"><?php echo $lang->contact_gdpr ?> <div class="ui circular label" id="officer_help"><i class="fitted question icon"></i></div></label>
          <textarea name="data_protection_officer" rows="2" cols="80"><?php echo $config->data_protection_officer ?></textarea>
        </div>
        <div class="field">
          <label for="custom_privacy_text">
            <?php echo $lang->custom_privacy ?>
            <div class="ui circular label" id="custom_help"><i class="fitted question icon"></i></div>
          </label>
          <textarea name="custom_privacy_text" rows="8" cols="80"><?php echo $config->custom_privacy_text ?></textarea>
        </div>
        <div class="ui toggle checkbox">
          <label for="disable_standard_privacy"><?php echo $lang->disable_privacy ?></label>
          <input type="checkbox" name="disable_standard_privacy" <?php if($config->disable_standard_privacy){echo "checked";} ?>>
        </div><br/><br/>
        <input class="ui primary button" type="submit" name="privacy_imprint_changed" value="<?php echo $lang->save_imprint_privacy ?>">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header"><?php echo $lang->misc ?></h2>
      <form class="ui form" action="" method="post">
        <div class="field">
          <label for="header_tags"><?php echo $lang->header_tags ?> <div class="ui circular label" id="header_help"><i class="fitted question icon"></i></div></label>
          <textarea name="header_tags" rows="8" cols="80"><?php echo $config->header_tags ?></textarea>
        </div>
        <div class="field">
          <label for="admin_mail"><?php echo $lang->admin . " " . $lang->email ?> <div class="ui circular label" id="admail_help"><i class="fitted question icon"></i></div></label>
          <input type="email" name="admin_mail" value="<?php echo $config->admin_mail ?>">
        </div>
        <input class="ui primary button" type="submit" name="misc_changed" value="<?php echo $lang->save ?>">
      </form>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
    <script type="text/javascript">
      //Password display functionality
      document.getElementById("show_pwd").addEventListener("mousedown", function(){
        document.getElementById('db_pwd').type='text';
      });
      document.getElementById("show_pwd").addEventListener("mouseup", function(){
        document.getElementById('db_pwd').type='password'
      });
      document.getElementById("show_pwd").addEventListener("mouseout", function(){
        document.getElementById('db_pwd').type='password'
      });
      //Special form implementation of planning mode
      document.getElementById("actor_segment").addEventListener("click", function(){
        document.getElementById("focus_value").value="true";
        document.getElementById("focus_form").submit();
      });
      document.getElementById("admin_segment").addEventListener("click", function(){
        document.getElementById("focus_value").value="false";
        document.getElementById("focus_form").submit();
      });

      //Code responsible for tooltips
      document.getElementById("custom_help").addEventListener("click", function(){
        if(this.id=="custom_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->custom_privacy_help ?>";
          this.id="custom_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="custom_help";
        }
      });
      document.getElementById("officer_help").addEventListener("click", function(){
        if(this.id=="officer_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->contact_gdpr_help ?>";
          this.id="officer_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="officer_help";
        }
      });
      document.getElementById("imprint_help").addEventListener("click", function(){
        if(this.id=="imprint_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->imprint_help ?>";
          this.id="imprint_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="imprint_help";
        }
      });
      document.getElementById("contact_help").addEventListener("click", function(){
        if(this.id=="contact_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->contact_info_help ?>";
          this.id="contact_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="contact_help";
        }
      });
      document.getElementById("header_help").addEventListener("click", function(){
        if(this.id=="header_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->header_help ?>";
          this.id="header_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="header_help";
        }
      });
      document.getElementById("admail_help").addEventListener("click", function(){
        if(this.id=="admail_help"){
          this.className = "ui left pointing label";
          this.innerHTML="<?php echo $lang->admail_help ?>";
          this.id="admail_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="admail_help";
        }
      });

      $(".ui.checkbox").checkbox();
    </script>
  </body>
</html>
