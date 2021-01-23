<?php session_start(); ?>
<nav class="ui secondary pointing stackable menu">
  <div class="ui container">
    <div class="header item">Theatre Planner</div>
    <a href="./dashboard.php" class="item" id="nav_dashboard">Dashboard</a>
    <a href="./practices.php" class="item" id="nav_practices">Practices</a>
    <a href="./scenes.php" class="item" id="nav_scenes">Scenes</a>
    <a href="./roles.php" class="item" id="nav_roles">Roles</a>
    <a href="./users.php" class="item" id="nav_users">Users</a>
    <div class="right menu">
      <div class="ui item">
          <a href="/theatre_planner/dashboard.php" class="mini ui icon button" title="Change to User Dashboard"><i class="user icon"></i></a>
      </div>
      <div class="ui item">
        <form action="/theatre_planner/logout.php" method="post">
          <button type="submit" name="logout" class="mini ui right labeled icon button"><i class="sign-out icon"></i><?php echo $_SESSION["UserName"]; ?></button>
        </form>
      </div>
    </div>
  </div>
</nav>
