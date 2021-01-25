<?php
require_once __DIR__ . "/php/auth/sessionValidate.php";
require_once __DIR__ . "/php/utils/database.php";

if($loggedIn){
  header("location:./pages/dashboard.php");
}
 if(isset($_POST["login"])){

   $db= new DBHandler();
   $creds = $db->prepareQuery("SELECT * FROM USERS WHERE Mail=?", "s", array($_POST["email"]));
   if((!empty($creds)) && password_verify($_POST["password"], $creds[0]["Password"])){
     $creds = $creds[0];
     $_SESSION["UserID"] = $creds["UserID"];
     $_SESSION["UserName"] = $creds["Name"];
     $_SESSION["Admin"] = $creds["Admin"];

     if(isset($_POST["rememberMe"])){
       $cookie_expiration_time = time() + (30 * 24 * 60 * 60);


       $h1 = random_bytes(16);
       setcookie("theatre_h1", $h1, array("expires"=>$cookie_expiration_time, "samesite"=>"Lax", "path"=>"/"));

       $h2 = random_bytes(32);
       setcookie("theatre_h2", $h2, array("expires"=>$cookie_expiration_time, "samesite"=>"Lax", "path"=>"/"));

       $h1_hash = password_hash($h1, PASSWORD_BCRYPT);
       $h2_hash = password_hash($h2, PASSWORD_BCRYPT);
       $expiry_date = date("Y-m-d H:i:s", $cookie_expiration_time);

       $db->update("INSERT INTO TOKENS VALUES (NULL, ?, ?, ?, ?)", "isss", array($creds["UserID"], $h1_hash, $h2_hash, $expiry_date));

       $tokenID = $db->prepareQuery("SELECT TokenID FROM TOKENS WHERE Selector=?","s", array($h2_hash))[0]["TokenID"];
       setcookie("theatreID", $tokenID, array("expires"=>$cookie_expiration_time, "samesite"=>"Lax", "path"=>"/"));
     } else {
       setcookie("theatreID", "", array("expires"=>time() - 3600, "samesite"=>"Lax", "path"=>"/"));
       setcookie("theatre_h1", "", array("expires"=>time() - 3600, "samesite"=>"Lax", "path"=>"/"));
       setcookie("theatre_h2", "", array("expires"=>time() - 3600, "samesite"=>"Lax", "path"=>"/"));
     }
     header("location:./pages/dashboard.php");
   } else {
     $message = "Invalid login credentials";
   }
 }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Theatre Planner | Login</title>
    <?php include __DIR__ . "/head.php"; ?>
    <style type="text/css">
    body{
      background-image: url("/theatre_planner/images/login.jpg");
      background-size: cover;
    }
    main > .grid {
      height: 100%;
    }
    .column {
      max-width: 450px;
    }
  </style>
  </head>
  <body>
    <main id="loginScreen">
      <div class="ui middle aligned center aligned grid">
        <div class="column">
          <h2 class="ui header" style="color:white">
            <div class="content">
              Theatre Planner
            </div>
          </h2>
          <form class="ui large form" action="" method="POST">
            <div class="ui error message" <?php if(!empty($message)){echo 'style="display:block"';} ?> >
              Invalid login credentials
            </div>
            <div class="ui stacked  segment">
              <div class="field">
                <div class="ui left icon input">
                  <i class="user icon"></i>
                  <input required type="email" name="email" placeholder="E-mail address">
                </div>
              </div>
              <div class="field">
                <div class="ui left icon input">
                  <i class="lock icon"></i>
                  <input required type="password" name="password" placeholder="Password">
                </div>
              </div>
              <div class="ui checkbox">
                <label for="rememberMe">Keep me logged in</label>
                <input type="checkbox" name="rememberMe">
              </div>
              <input class="ui fluid large teal submit button" type="submit" name="login" value="Login"></input>
            </div>
          </form>
          <div>
            <span>Photo by <a href="https://unsplash.com/@kilyan_s?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Kilyan Sockalingum</a> on <a href="https://unsplash.com/s/photos/theatre?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Unsplash</a></span>
          </div>
        </div>
      </div>
    </main>
    <?php include __DIR__ . "/footer.php" ?>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.ui.checkbox').checkbox();
      });
    </script>
  </body>
</html>
