<?php
  require $_SERVER['DOCUMENT_ROOT'] . "/theatre_planner/php/utils/database.php";

  $db = new DBHandler();

 if(isset($_POST["rm_user"])){
   $db->update("DELETE FROM USERS WHERE UserID =?", "i", array($_POST["rm_user"]));
 } elseif (isset($_POST["addUser"])) {
   $password = uniqid();
   if($db->update("INSERT INTO USERS (UserID, Name, Mail, Password, Admin) VALUES (NULL, ?, ?, ?, ?)","sssi",array($_POST["userName"], $_POST["userMail"], md5($password), 0))){
     // TODO mail($_POST["userMail"], "Hello " . $_POST["userName"] . "! Your Password is '" . $password . "'. Please change it after your first login at " . $_SERVER["SERVER_NAME"]);
   } else {
     echo '<div style="color:red;">Oops! That Address is already taken!</div>';
   }
 }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner - Add user</title>
  </head>
  <body>
    <!-- TODO add sidebar / static nav -->
    <form action="" method="post" onSubmit="return checkNewUser()">
      <input required="true" type="text" id="userName" name="userName">
      <input required="true" type="email" id="userMail" name="userMail">
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
          foreach ($db->baseQuery("SELECT UserID, Name, Mail, Admin FROM USERS WHERE Admin = 0") ?? array() as $user) {
            create_row($user["UserID"], $user["Name"], $user["Mail"]);
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
    <script type="text/javascript">
      function checkNewUser(){
        if(document.getElementById("userName").value.length > 32 ){
          alert("The Name mustn't be longer than 32 characters");
          document.getElementById("userName").focus();
          return false;
        }

        if(document.getElementById("userMail").value.length > 64){
          alert("The mail mustn't be longer than 64 characters");
          document.getElementById("userMail").focus();
          return false;
        }

        return true;
      }
    </script>
  </body>
</html>
