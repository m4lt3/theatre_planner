<nav class="ui secondary pointing stackable menu">
  <div class="ui container">
    <div class="header item">Theatre Planner</div>
    <a href="./dashboard.php" class="item" id="nav_dashboard">Dashboard</a>
    <a href="./practices.php" class="item" id="nav_practices">Practices</a>
    <a href="./personal.php" class="item" id="nav_personal">Personal Data</a>
    <div class="right menu">
      <?php if($_SESSION["Admin"]){
        echo '<div class="ui item"><a href="/theatre_planner/pages/admin/dashboard.php" class="mini ui icon button" title="Change to Admin Dashboard"><i class="chess queen icon"></i></a></div>';
      } ?>
      <div class="ui item">
        <form action="/theatre_planner/php/auth/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
