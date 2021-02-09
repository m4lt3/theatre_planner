<nav class="ui secondary pointing stackable computer only menu" style="padding-left:25px;">
  <div class="header item">
    <img src="<?php echo $config->subfolder ?>/images/favicon.svg" alt="" style="margin-right:7px">
    <?php echo $lang->title ?>
  </div>
  <a href="./dashboard.php" class="nav_dashboard item"><?php echo $lang->title_dashboard ?></a>
  <a href="./compact.php" class="nav_compact item"><?php echo $lang->title_compact_view ?></a>
  <a href="./practices.php" class="nav_practices item"><?php echo $lang->title_practice_management ?></a>
  <a href="./scenes.php" class="nav_scenes item"><?php echo $lang->title_scene_management ?></a>
  <a href="./roles.php" class="nav_roles item" ><?php echo $lang->title_role_management ?></a>
  <a href="./actors.php" class="nav_actors item" ><?php echo $lang->title_actor_management ?></a>
  <a href="./dates.php" class="nav_date item" ><?php echo $lang->title_date_finder ?></a>
  <a href="./config.php" class="nav_config item" ><?php echo $lang->title_server_configuration ?></a>
  <div class="right menu">
    <div class="ui item">
        <a href="../dashboard.php" class="mini ui icon button" title="<?php echo $lang->change_to_actor ?>"><i class="user icon"></i></a>
    </div>
    <div class="ui item">
      <form action="<?php echo $config->subfolder ?>/php/auth/logout.php" method="post">
        <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
      </form>
    </div>
  </div>
</nav>
<nav class="ui secondary pointing stackable computer hidden menu" id="mobile_menu">
  <div class="ui container">
    <div class="header item">
      <img src="<?php echo $config->subfolder ?>/images/favicon.svg" alt="" style="margin-right:7px">
      <?php echo $lang->title ?><span style="flex-grow:1"></span>
      <i class="bars icon" id="hamburger"></i>
    </div>
    <a href="./dashboard.php" class="nav_dashboard item" ><?php echo $lang->title_dashboard ?></a>
    <a href="./compact.php" class="nav_compact item"><?php echo $lang->title_compact_view ?></a>
    <a href="./practices.php" class="nav_practices item" ><?php echo $lang->title_practice_management ?></a>
    <a href="./scenes.php" class="nav_scenes item" ><?php echo $lang->title_scene_management ?></a>
    <a href="./roles.php" class="nav_roles item" ><?php echo $lang->title_role_management ?></a>
    <a href="./actors.php" class="nav_actors item" ><?php echo $lang->title_actor_management ?></a>
    <a href="./dates.php" class="nav_date item" ><?php echo $lang->title_date_finder ?></a>
    <a href="./config.php" class="nav_config item" ><?php echo $lang->title_server_configuration ?></a>
    <div class="right menu">
      <div class="ui item">
          <a href="../dashboard.php" class="mini ui icon button" title="<?php echo $lang->change_to_actor ?>"><i class="user icon"></i></a>
      </div>
      <div class="ui item">
        <form action="<?php echo $config->subfolder ?>/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
