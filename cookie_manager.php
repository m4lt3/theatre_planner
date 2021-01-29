<?php
if(isset($_POST["cookie_manager"])){
  if(isset($_POST["cookies"])){
    $_SESSION["cookies_allowed"] = true;
  }
  $_SESSION["show_cookie_dialouge"] = false;
}
?>

<div class="ui floating icon message" style="position:fixed; bottom:-14px; <?php if(!$_SESSION["show_cookie_dialouge"]){echo "display:none";} ?>">
  <i class="icon"><img class="ui mini image" src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__) . "/images/cookie.svg" ?>" alt="A cookie" style="color:blue"></i>
  <div class="content">
    <div class="header">
      <?php echo $lang->cookie_header ?>
    </div>
    <?php echo $lang->cookie_text ?><br/>
    <a href="<?php echo dirname($_SERVER["PHP_SELF"]) . "/imprint.php"; ?>"><?php echo $lang->imprint ?></a> | <a href="<?php echo dirname($_SERVER["PHP_SELF"]) . "/privacy.php"; ?>"><?php echo $lang->privacy ?></a>
    <form id="form_container" action="" method="POST">
      <div>
        <div class="ui disabled checkbox">
          <label><?php echo $lang->necessary ?></label>
          <input type="checkbox" checked>
        </div>
        <div class="ui checkbox">
          <label><?php echo $lang->functional ?></label>
          <input type="checkbox" name="cookies" value="">
        </div>
      </div>
      <input type="submit" class="ui button" name="cookie_manager" value="<?php echo $lang->save ?>">
    </form>
  </div>
</div>
<script type="text/javascript">
  $(".ui.checkbox").checkbox();
</script>
