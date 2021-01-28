<nav class="ui secondary pointing stackable menu">
  <div class="ui container">
    <div class="header item">
      <img src="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(__DIR__)) ?>/images/favicon.svg" alt="" style="margin-right:7px">
      <?php echo $lang->title ?>
    </div>
    <a href="./dashboard.php" class="item" id="nav_dashboard"><?php echo $lang->title_dashboard ?></a>
    <a href="./practices.php" class="item" id="nav_practices"><?php echo $lang->title_practices ?></a>
    <a href="./personal.php" class="item" id="nav_personal"><?php echo $lang->title_personal ?></a>
    <div class="right menu">
      <?php if($_SESSION["Admin"]){
        echo '<div class="ui item"><a href="/theatre_planner/pages/admin/dashboard.php" class="mini ui icon button" title="'.$lang->change_to_admin.'"><i class="chess queen icon"></i></a></div>';
      } ?>
      <div class="ui item">
        <form action="/theatre_planner/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
