<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner - Add user</title>
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <?php
      $servername = "localhost";
      $username = "planner";
      $password = "dC5*nn%phW!LuGiZ";
      $dbname = "theatre_planner";

      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);

      // Check connection
      if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
     }

     if(isset($_POST["rm_user"])){
       $sql = "DELETE FROM USERS WHERE UserID = " . $_POST["rm_user"] . ";";
       $conn->query($sql);
     } elseif (isset($_POST["addUser"])) {
       $password = uniqid();
       $sql = "INSERT INTO USERS (UserID, Name, Mail, Password, Admin) VALUES (NULL, '" . $_POST["userName"] . "', '" . $_POST["userMail"] . "', '" . md5($password) . "', 0);";
       $result = $conn->query($sql);
       if($result == true){
         // TODO mail($_POST["userMail"], "Hello " . $_POST["userName"] . "! Your Password is '" . $password . "'. Please change it after your first login at " . $_SERVER["SERVER_NAME"]);
       } else {
         echo '<div style="color:red;">Oops! That Address is already taken!</div>';
       }

     }
    ?>

    <form action="" method="post">
      <input required="true" type="text" name="userName">
      <input required="true" type="email" name="userMail">
      <input type="submit" name="addUser" value="Nutzer Anlegen">
    </form>

    <br/>

    <table>
      <thead>
        <th>ID</th>
        <th>Name</th>
        <th>E-Mail</th>
        <th></th>
      </thead>
      <tbody>
        <?php
          $sql = "SELECT UserID, Name, Mail, Admin FROM USERS WHERE Admin = 0";
          $result = $conn->query($sql);
          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              create_row($row["UserID"], $row["Name"], $row["Mail"]);
            }
          }

          function create_row($id, $name, $mail){
            $button =<<<EOT
            <form method="POST" action="">
              <input type="hidden" name="rm_user" value="$id">
              <input type="submit" value="Delete User">
            </form>
EOT;
            $message = <<<EOT
            <tr>
              <td>$id</td>
              <td>$name</td>
              <td><a href="mailto:$mail">$mail</a></td>
              <td>$button</td>
            </tr>
EOT;
          echo $message;
          }
        ?>
      </tbody>
    </table>
  </body>
</html>
