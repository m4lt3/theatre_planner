<?php
  require_once dirname(dirname(__DIR__)) . "/php/auth/sessionValidate.php";
  require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
  if($loggedIn){
    header("location:../dashboard.php");
  }
  $reason="request";
  if(isset($_POST["request_reset"])){
    // Password reset rrequested, validating information
    require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
    $db = new DBHandler();
    $pwd = $db->prepareQuery("SELECT UserID, Name, Password FROM USERS WHERE Mail=?","s",array($_POST["email"]));
    if(!empty($pwd)){
      // User exists, generating and storing reset token

      $expiration_time = time() + 86400;
      $secret = password_hash(uniqid(), PASSWORD_BCRYPT);
      $token = password_hash($_POST["email"].$expiration_time.$pwd[0]["Password"].$secret, PASSWORD_BCRYPT);

      $expiry_date = date("Y-m-d H:i:s", $expiration_time);
      $db->update("INSERT INTO TOKENS VALUES(NULL, ?,?,?,?)","isss", array($pwd[0]["UserID"], $secret, $token, $expiry_date));

      //Sending password reset mail
      require dirname(dirname(__DIR__))."/php/ui/mail_template.php";
      $config = require dirname(dirname(__DIR__))."/php/config.php";

      $header = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8\r\nFrom: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\nReply-to: " . $config->admin_mail . "\r\nX-Mailer: PHP " . phpversion();
      $base_url = ((!empty($_SERVER["HTTPS"]) && $_SERVER['HTTPS'] !== 'off')?"https://":"http://") . $_SERVER['SERVER_NAME'] . dirname(dirname(dirname($_SERVER["PHP_SELF"])));
      $action_url = '/pages/utils/resetPassword.php?token='.$token;
      $message =  createMail($lang, $pwd[0]["Name"], "", "", $base_url, $action_url, $config->contact_info, "password");
      mail($_POST["email"], $lang->reset_title, $message, $header);
    }
  } elseif (isset($_POST["perform_reset"])){
    require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
    $db = new DBHandler();
    $token = $db->prepareQuery("SELECT TokenID, UserID FROM TOKENS WHERE Selector=? AND Expires > NOW()","s",array($_GET["token"]));
    if(!empty($token)){
      $db->update("UPDATE USERS SET PASSWORD=? WHERE UserID=?","si", array(password_hash($_POST["password"], PASSWORD_BCRYPT), $token[0]["UserID"]));
      $db->update("DELETE FROM TOKENS WHERE TokenID=?","i",array($token[0]["TokenID"]));
      $reason = "success";
    }
  } elseif(isset($_GET["token"])){
    require_once dirname(dirname(__DIR__)) . "/php/utils/database.php";
    $db = new DBHandler();
    if(empty($db->prepareQuery("SELECT Selector FROM TOKENS WHERE Selector=? AND Expires > NOW()","s",array($_GET["token"])))){
      $reason = "invalid";
    } else {
      $reason = "reset";
    }

  }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->reset_password ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
    <style type="text/css">
    main > .grid {
      height: 100%;
    }
    .image {
      margin-top: -100px;
    }
    .column {
      max-width: 450px;
    }
  </style>
  </head>
  <body>
    <div class="ui secondary pointing menu">
      <div class="ui container">
        <div class="ui item">
          <a href="<?php echo str_replace($_SERVER["DOCUMENT_ROOT"], "", dirname(dirname(__DIR__))) . "/index.php" ?>"><?php echo $lang->to_main ?></a>
        </div>
      </div>
    </div>
    <main>
      <div class="ui middle aligned center aligned grid">
  <div class="column">
    <h2 class="ui teal image header">
        <?php echo $lang->reset_password ?>
    </h2>
    <form class="ui large form" action="" method="post">
      <div class="ui stacked segment" <?php if($reason=="invalid" || $reason == "success"){echo 'style="display:none"';} ?>>
        <div class="field" <?php if($reason!="request"){echo 'style="display:none"';} ?>>
          <div class="ui left icon input">
            <i class="user icon"></i>
            <input type="email" name="email" placeholder="<?php echo $lang->email ?>">
          </div>
        </div>
        <div class="field" <?php if($reason!="reset"){echo 'style="display:none"';} ?>>
          <div class="ui left icon input">
            <i class="lock icon"></i>
            <input <?php if($reason!="reset"){echo "required";} ?> type="password" name="password" placeholder="<?php echo $lang->password ?>" minlength="8">
          </div>
        </div>
        <input type="submit" class="ui fluid large teal button" name="<?php if($reason=="request"){echo 'request_reset';} else {echo 'perform_reset';} ?>" value="<?php echo $lang->reset_password ?>">
      </div>

      <div class="ui error message" <?php if($reason=="invalid"){echo 'style="display:block"';} ?>><?php echo $lang->invalid_token ?></div>
      <div class="ui success message"<?php if($reason=="success"){echo 'style="display:block"';} ?>><?php echo $lang->reset_success ?></div>

    </form>

    <div class="ui message" <?php if(!isset($_POST["request_reset"])){echo 'style="display:none"';} ?>>
      <?php echo $lang->reset_link_sent ?>
    </div>
  </div>
</div>

    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    require dirname(dirname(__DIR__)) . "/cookie_manager.php";
    ?>
  </body>
</html>
