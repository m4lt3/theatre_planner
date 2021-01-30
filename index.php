<?php
require_once __DIR__ . "/php/auth/sessionValidate.php";
require_once __DIR__ . "/php/utils/loadPreferences.php";
require_once __DIR__ . "/php/utils/database.php";

if($loggedIn){
  header("location:./pages/dashboard.php");
}

$config = require __DIR__ . "/php/config.php";
if($config->setup_guide){
  header("location:./pages/utils/setup.php");
}

 if(isset($_POST["login"])){

   $db= new DBHandler();
   $creds = $db->prepareQuery("SELECT * FROM USERS WHERE Mail=?", "s", array($_POST["email"]));
   if((!empty($creds)) && password_verify($_POST["password"], $creds[0]["Password"])){
     $creds = $creds[0];
     $_SESSION["UserID"] = $creds["UserID"];
     $_SESSION["UserName"] = $creds["Name"];
     $_SESSION["Admin"] = $creds["Admin"];

     if(isset($_POST["rememberMe"]) && isset($_SESSION["cookies_allowed"]) && $_SESSION["cookies_allowed"] == true){
       $cookie_expiration_time = time() + 2592000;


       $h1 = random_bytes(16);


       $h2 = random_bytes(32);

       $h1_hash = password_hash($h1, PASSWORD_BCRYPT);
       $h2_hash = password_hash($h2, PASSWORD_BCRYPT);
       $expiry_date = date("Y-m-d H:i:s", $cookie_expiration_time);

       $db->update("INSERT INTO TOKENS VALUES (NULL, ?, ?, ?, ?)", "isss", array($_SESSION["UserID"], $h1_hash, $h2_hash, $expiry_date));
       $tokenID = $db->prepareQuery("SELECT TokenID FROM TOKENS WHERE Selector=?","s", array($h2_hash))[0]["TokenID"];

       $db->update("DELETE FROM TOKENS WHERE Expires < NOW()", "", array());

       setcookie("theatre_h1", $h1, array("expires"=>$cookie_expiration_time, "samesite"=>"Lax", "path"=>"/"));
       setcookie("theatre_h2", $h2, array("expires"=>$cookie_expiration_time, "samesite"=>"Lax", "path"=>"/"));
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
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->login ?></title>
    <?php include __DIR__ . "/head.php"; ?>
    <style type="text/css">
    body{
      background-image: url("/images/login.jpg");
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
          <h2 class="ui image header" style="color:white">
            <img class="image" src="./images/favicon.svg" alt="Masks">
            <div class="content">
              <?php echo $lang->title ?>
            </div>
          </h2>
          <form class="ui large form" action="" method="POST">
            <div class="ui error message" <?php if(!empty($message)){echo 'style="display:block"';} ?> >
              <?php echo $lang->invalid_login ?>
            </div>
            <div class="ui stacked  segment">
              <div class="field">
                <div class="ui left icon input">
                  <i class="user icon"></i>
                  <input required type="email" name="email" placeholder="<?php echo $lang->email ?>">
                </div>
              </div>
              <div class="field" style="text-align:right">
                <div class="ui left icon input">
                  <i class="lock icon"></i>
                  <input required type="password" name="password" placeholder="<?php echo $lang->password ?>">
                </div>
                <a href="pages/utils/resetPassword.php"><?php echo $lang->forgot_password ?></a>
              </div>
              <div class="field" style="text-align:left;">
                <div class="ui checkbox">
                  <label for="rememberMe"><?php echo $lang->remember_me ?></label>
                  <input type="checkbox" name="rememberMe">
                </div>
              </div>
              <input class="ui fluid large teal submit button" type="submit" name="login" value="Login"></input>
            </div>
          </form>
          <div>
            <span><?php echo $lang->photo_by ?> <a href="https://unsplash.com/@kilyan_s?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Kilyan Sockalingum</a> <?php echo $lang->on ?> <a href="https://unsplash.com/s/photos/theatre?utm_source=unsplash&amp;utm_medium=referral&amp;utm_content=creditCopyText">Unsplash</a></span>
          </div>
        </div>
      </div>
    </main>
    <?php
    include __DIR__ . "/footer.php";
    require __DIR__ . "/cookie_manager.php";
     ?>

    <script type="text/javascript">
      $(document).ready(function(){
        $('.ui.checkbox').checkbox();
      });
    </script>
  </body>
</html>
