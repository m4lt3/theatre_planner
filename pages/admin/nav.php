<nav class="ui secondary pointing stackable menu">
  <div class="ui container">
    <div class="header item">
      <img src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(dirname(__DIR__))) ?>/images/favicon.svg" alt="" style="margin-right:7px">
      <?php echo $lang->title ?>
    </div>
    <a href="./dashboard.php" class="item" id="nav_dashboard"><?php echo $lang->title_dashboard ?></a>
    <a href="./practices.php" class="item" id="nav_practices"><?php echo $lang->title_practice_management ?></a>
    <a href="./scenes.php" class="item" id="nav_scenes"><?php echo $lang->title_scene_management ?></a>
    <a href="./roles.php" class="item" id="nav_roles"><?php echo $lang->title_role_management ?></a>
    <a href="./actors.php" class="item" id="nav_users"><?php echo $lang->title_actor_management ?></a>
    <a href="./config.php" class="item" id="nav_config"><?php echo $lang->title_server_configuration ?></a>
    <div class="right menu">
      <div class="ui item">
          <a href="/theatre_planner/pages/dashboard.php" class="mini ui icon button" title="<?php echo $lang->change_to_actor ?>"><i class="user icon"></i></a>
      </div>
      <div class="ui item">
        <form action="/theatre_planner/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
