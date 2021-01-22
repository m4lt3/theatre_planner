<?php
  require $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  $db = new DBHandler();

  if(isset($_POST["rm_role"])){
    $db->update("DELETE FROM ROLES WHERE RoleID=?", "i", array($_POST["rm_role"]));
  } elseif (isset($_POST["addRole"])) {
    $db->update("INSERT INTO ROLES VALUES (NULL, ?, ?)", "ss", array($_POST["roleName"], $_POST["roleDescription"]));
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Roles</title>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/head.html"; ?>
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <main class="ui text container">
      <form action="" method="post" class="ui form">
        <div class="required field">
          <label for="roleName">Role Name</label>
          <input required="true" type="text" name="roleName" maxlength="32">
        </div>
        <div class="field">
          <label for="roleDescription">Role Description</label>
          <textarea name="roleDescription" rows="8" cols="64" maxlength="512"></textarea>
        </div>
        <input class="ui primary button" type="submit" name="addRole" value="Create Role">
      </form>

      <br/>

      <table class="ui celled table">
        <thead>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th></th>
        </thead>
        <tbody>
          <?php
            foreach ($db->baseQuery("SELECT * FROM ROLES") ?? array() as $role) {
              create_row($role["RoleID"], $role["Name"], $role["Description"]);
            }

            function create_row($id, $name, $description){
              $button =<<<EOT
              <form method="POST" action="">
                <input type="hidden" name="rm_role" value="$id">
                <input class="ui button" type="submit" value="Delete Role">
              </form>
EOT;

              $message = <<<EOT
              <tr>
                <td>$id</td>
                <td>$name</td>
                <td>$description</td>
                <td>$button</td>
              </tr>
EOT;
            echo $message;
            }
          ?>
        </tbody>
      </table>
    </main>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/pages/footer.html"; ?>
  </body>
</html>
