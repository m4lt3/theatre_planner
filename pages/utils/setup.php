<?php
   require_once dirname(dirname(__DIR__)) . "/php/utils/loadPreferences.php";
   $config = require dirname(dirname(__DIR__)) . "/php/config.php";
   $step = 1;
   $error = "";

   if (isset($_POST["db_changed"])) {
     $temp_conn = new mysqli($_POST["db_server"], $_POST["db_user"], $_POST["db_pwd"], $_POST["db_name"]);

     if ($temp_conn->connect_error) {
       $error = $lang->invalid_login;
     } else {
       $config->db_server = $_POST["db_server"];
       $config->db_name = $_POST["db_name"];
       $config->db_user = $_POST["db_user"];
       $config->db_pwd = $_POST["db_pwd"];
       file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");

       $setup_sql = file_get_contents(dirname(dirname(__DIR__))."/php/utils/create_db.sql");
       if($temp_conn->multi_query($setup_sql)){
         $temp_conn->close();
         $step = 2;
       } else {
         $error = "Structuring of database failed: " . $temp_conn->error;
       }


     }
   } elseif (isset($_POST["create_user"])){
     require dirname(dirname(__DIR__))."/php/utils/database.php";
     $db = new DBHandler();
     $db->update("INSERT INTO USERS VALUES (NULL, ?, ?, ?, ?)", "sssi", array($_POST["name"], $_POST["email"], password_hash($_POST["password"], PASSWORD_BCRYPT), 1));
     session_start();
     $_SESSION["UserID"] = 1;
     $_SESSION["UserName"] = $_POST["name"];
     $_SESSION["Admin"] = true;

     $config->setup_guide = false;
     file_put_contents("../../php/config.php", "<?php\n\nreturn " . var_export($config, true) . "\n\n?>");
   }

   if(!$config->setup_guide){
     $step = 3;
   }
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->lang ?>" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title><?php echo $lang->title ?> | <?php echo $lang->setup ?></title>
    <?php include dirname(dirname(__DIR__)) . "/head.php"; ?>
  </head>
  <body>
    <main class="ui container" style="padding-top:14px; text-align: center">
      <div class="ui steps">
        <div class="<?php if($step==1){echo "active ";} else { echo "disabled ";} ?>step">
          <i class="database icon"></i>
          <div class="content">
            <div class="title">
              <?php echo $lang->setup_db ?>
            </div>
            <div class="description">
              <?php echo $lang->setup_db_text ?>
            </div>
          </div>
        </div>
        <div class="<?php if($step==2){echo "active ";} elseif($step>2){echo "disabled ";} ?>step">
          <i class="user plus icon"></i>
          <div class="content">
            <div class="title">
              <?php echo $lang->setup_user ?>
            </div>
            <div class="description">
              <?php echo $lang->setup_user_text ?>
            </div>
          </div>
        </div>
        <div class=" <?php if($step == 3){echo "active ";} ?>step">
          <i class="sliders horizontal icon"></i>
          <div class="content">
            <div class="title">
              <?php echo $lang->setup_settings ?>
            </div>
            <div class="description">
              <?php echo $lang->setup_settings_text ?>
            </div>
          </div>
        </div>
      </div>
      <div class="ui text container">
        <form class="ui form" action="" method="post" <?php if($step!=1){echo 'style="display:none"';} ?>>
          <div class="two fields">
            <div class="field">
              <label for="db_server"><?php echo $lang->db_server ?></label>
              <input required type="text" name="db_server" value="<?php echo $_POST["db_server"]??"" ?>">
            </div>
            <div class="field">
              <label for="db_name"><?php echo $lang->db_name ?></label>
              <input required type="text" name="db_name" value="<?php echo $_POST["db_name"]??"" ?>">
            </div>
          </div>
          <div class="two fields">
            <div class="field">
              <label for="db_user"><?php echo $lang->db_user ?></label>
              <input required type="text" name="db_user" value="<?php echo $_POST["db_user"]??"" ?>">
            </div>
            <div class="field">
              <label for="db_pwd"><?php echo $lang->db_pwd ?></label>
              <div class="ui action input">
                <input required type="password" name="db_pwd" value="<?php echo $_POST["db_pwd"]??"" ?>" id="db_pwd" minlength="8">
                <button class="ui icon button" type="button" name="button" id="show_pwd"><i class="low vision icon"></i></button>
              </div>
            </div>
          </div>
          <input class="ui primary button" type="submit" name="db_changed" value="<?php echo $lang->save ?>">
        </form>
        <form class="ui form" action="" method="POST" <?php if($step!=2){echo 'style="display:none"';} ?>>
          <div class="ui  segment">
            <div class="field">
              <div class="ui left icon input">
                <i class="comment icon"></i>
                <input required type="text" name="name" placeholder="<?php echo $lang->name ?>">
              </div>
            </div>
            <div class="field">
              <div class="ui left icon input">
                <i class="user icon"></i>
                <input required type="email" name="email" placeholder="<?php echo $lang->email ?>">
              </div>
            </div>
            <div class="field">
              <div class="ui left icon input">
                <i class="lock icon"></i>
                <input required type="password" name="password" placeholder="<?php echo $lang->password ?>" minlength="8">
              </div>
            </div>
            <input class="ui fluid large teal submit button" type="submit" name="create_user" value="Login"></input>
          </div>
        </form>
        <div class="ui huge icon success message" <?php if($step!=3){echo 'style="display:none"';} ?>>
          <i class="check icon"></i>
          <div class="content">
            <div class="header">
              <?php echo $lang->setup_done ?>
            </div>
            <p><?php echo $lang->setup_done_text ?></p>
            <a href="../admin/config.php"><button type="button" name="button" class="ui large green fluid button"><?php echo $lang->get_started ?></button></a>
          </div>
        </div>
        <div class="ui error message" <?php if(empty($error)){echo 'style="display:none"';} ?>>
          <?php echo $error ?>
        </div>
      </div>
    </main>
    <?php
    include dirname(dirname(__DIR__)) . "/footer.php";
    ?>
    <script type="text/javascript">
    //Password display functionality
    document.getElementById("show_pwd").addEventListener("mousedown", function(){
      document.getElementById('db_pwd').type='text';
    });
    document.getElementById("show_pwd").addEventListener("mouseup", function(){
      document.getElementById('db_pwd').type='password'
    });
    document.getElementById("show_pwd").addEventListener("mouseout", function(){
      document.getElementById('db_pwd').type='password'
    });
    </script>
  </body>
</html>
