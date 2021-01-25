<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  if(!$loggedIn){
    header("location:../../index.php");
  }
  if(!$_SESSION["Admin"]){
    header("location:../dashboard.php");
  }
  $config = require dirname(dirname(__DIR__))."/php/config.php";
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
  } elseif(isset($_POST["tags_changed"])){
    $config->header_tags = $_POST["header_tags"];
    file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Server Configuration</title>
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
      <h1 class="ui large header">Server Configuration</h1>
      <h2 class="ui medium header">Database Connection</h2>
      <form class="ui form" action="" method="post">
        <div class="two fields">
          <div class="field">
            <label for="db_server">Databse Server</label>
            <input type="text" name="db_server" value="<?php echo $config->db_server ?>">
          </div>
          <div class="field">
            <label for="db_name">Database Name</label>
            <input type="text" name="db_name" value="<?php echo $config->db_name ?>">
          </div>
        </div>
        <div class="two fields">
          <div class="field">
            <label for="db_user">Database User</label>
            <input type="text" name="db_user" value="<?php echo $config->db_user ?>">
          </div>
          <div class="field">
            <label for="db_pwd">Database User Password</label>
            <div class="ui action input">
              <input type="password" name="db_pwd" value="<?php echo $config->db_pwd ?>" id="db_pwd">
              <button class="ui icon button" type="button" name="button" id="show_pwd"><i class="low vision icon"></i></button>
            </div>
          </div>
        </div>
        <input class="ui primary button" type="submit" name="db_changed" value="Save">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header">Planning Mode</h2>
      <div class="ui horizontal segments">
        <div class="ui <?php if($config->user_focused){echo "teal";} ?> segment" id="actor_segment">
          <h3 class="ui small sub header">Actor Focused</h3>
          If theatre planner is actor focused, it means that every actor can reject or decline a certain date. Based on the attendees, admins can look up which scenes they can practice.
        </div>
        <div class="ui vertical divider">
          Or
        </div>
        <div class="ui right aligned <?php if(!$config->user_focused){echo "teal";} ?> segment" id="admin_segment">
          <h3 class="ui small right aligned sub header">Admin Focused</h3>
          If theatre planner is admin focused, it means that admins can decide which scenes are practiced when. Actors can then look up when they are required.
        </div>
      </div>
      <form action="" method="post" id="focus_form">
        <input type="hidden" name="mode_changed" id="focus_value" value="undefined">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header">Privacy & Imprint</h2>
      <div class="ui info icon message">
        <i class="code icon"></i>
        All text below is in stored as HTML. This means, if you want to display a line break on the web, you need to write &lt;br/&gt; here
      </div>
      <form class="ui form" action="" method="post">
        <div class="field">
          <label for="contact_info">Contact information <div class="ui circular label" id="contact_help"><i class="fitted question icon"></i></div></label>
          <textarea name="contact_info" rows="2" cols="80"><?php echo $config->contact_info ?></textarea>
        </div>
        <div class="field">
          <label for="imprint_text">Imprint text <div class="ui circular label" id="imprint_help"><i class="fitted question icon"></i></div></label>
          <textarea name="imprint_text" rows="8" cols="80"><?php echo $config->imprint_text ?></textarea>
        </div>
        <div class="field">
          <label for="data_protection_officer">Name and Address of Data protection officer <div class="ui circular label" id="officer_help"><i class="fitted question icon"></i></div></label>
          <textarea name="data_protection_officer" rows="2" cols="80"><?php echo $config->data_protection_officer ?></textarea>
        </div>
        <div class="field">
          <label for="custom_privacy_text">
            Custom privacy text
            <div class="ui circular label" id="custom_help"><i class="fitted question icon"></i></div>
          </label>
          <textarea name="custom_privacy_text" rows="8" cols="80"><?php echo $config->custom_privacy_text ?></textarea>
        </div>
        <div class="ui toggle checkbox">
          <label for="disable_standard_privacy">Show only the custom privacy text</label>
          <input type="checkbox" name="disable_standard_privacy" <?php if($config->disable_standard_privacy){echo "checked";} ?>>
        </div><br/><br/>
        <input class="ui primary button" type="submit" name="privacy_imprint_changed" value="Save Privacy & Imprint settings">
      </form>
      <div class="ui divider"></div>
      <h2 class="ui medium header">Miscellaneous</h2>
      <form class="ui form" action="" method="post">
        <div class="ui field">
          <label for="header_tags">Header Tags <div class="ui circular label" id="header_help"><i class="fitted question icon"></i></div></label>
          <textarea name="header_tags" rows="8" cols="80"><?php echo $config->header_tags ?></textarea>
        </div>
        <input class="ui primary button" type="submit" name="tags_changed" value="Save information">
      </form>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    ?>
    <script type="text/javascript">
      document.getElementById("nav_scenes").className="active item";
    </script>
    <script type="text/javascript">
      document.getElementById("show_pwd").addEventListener("mousedown", function(){
        document.getElementById('db_pwd').type='text';
      });
      document.getElementById("show_pwd").addEventListener("mouseup", function(){
        document.getElementById('db_pwd').type='password'
      });
      document.getElementById("show_pwd").addEventListener("mouseout", function(){
        document.getElementById('db_pwd').type='password'
      });
      document.getElementById("actor_segment").addEventListener("click", function(){
        document.getElementById("focus_value").value="true";
        document.getElementById("focus_form").submit();
      });
      document.getElementById("admin_segment").addEventListener("click", function(){
        document.getElementById("focus_value").value="false";
        document.getElementById("focus_form").submit();
      });
      document.getElementById("custom_help").addEventListener("click", function(){
        if(this.id=="custom_help"){
          this.className = "ui left pointing label";
          this.innerHTML="Text to be displayed after the standard privacy text";
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
          this.innerHTML="To be shown in the standard privacy notice";
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
          this.innerHTML="Text to be displayed in the imprint after the contact information";
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
          this.innerHTML="Contact information to be displayed in the privacy notice and the imprint";
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
          this.innerHTML="The tags you enter here will be inserted into every page header";
          this.id="header_help_expanded";
        } else {
          this.className="ui circular label";
          this.innerHTML='<i class="fitted question icon"></i>';
          this.id="header_help";
        }
      });

      $(".ui.checkbox").checkbox();
    </script>
  </body>
</html>
