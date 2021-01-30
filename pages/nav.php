<nav class="ui secondary pointing stackable computer only menu">
  <div class="ui container">
    <div class="header item">
      <img src="<?php echo $config->subfolder ?>/images/favicon.svg" alt="" style="margin-right:7px">
      <?php echo $lang->title ?>
    </div>
    <a href="./dashboard.php" class="nav_dashboard item" ><?php echo $lang->title_dashboard ?></a>
    <a href="./practices.php" class="nav_practices item" ><?php echo $lang->title_practices ?></a>
    <a href="./personal.php" class="nav_personal item" ><?php echo $lang->title_personal ?></a>
    <div class="right menu">
      <?php if($_SESSION["Admin"]){
        echo '<div class="ui item"><a href="admin/dashboard.php" class="mini ui icon button" title="'.$lang->change_to_admin.'"><i class="chess queen icon"></i></a></div>';
      } ?>
      <div class="ui item">
        <form action="<?php echo $config->subfolder ?>/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
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
    <a href="./practices.php" class="nav_practices item" ><?php echo $lang->title_practices ?></a>
    <a href="./personal.php" class="nav_personal item" ><?php echo $lang->title_personal ?></a>
    <div class="right menu">
      <?php if($_SESSION["Admin"]){
        echo '<div class="ui item"><a href="admin/dashboard.php" class="mini ui icon button" title="'.$lang->change_to_admin.'"><i class="chess queen icon"></i></a></div>';
      } ?>
      <div class="ui item">
        <form action="<?php echo $config->subfolder ?>/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
