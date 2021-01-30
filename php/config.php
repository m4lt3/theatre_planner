<?php
return (object) array(
  // set to fals if you don't need any help setting up theatre planner
  "setup_guide" => true,
  // Database Connection can be edited here
  "db_server" => "localhost",
  "db_name" => "theatre_planner",
  "db_user" => "planner",
  "db_pwd" => "dC5*nn%phW!LuGiZ",

  // Settings that are actually visible and/or change the behavour of theatre theatre planner

  // If theatre planner is user focused, it means that the admin gets informed
  // which scenes he can practice on which date; If it is set to false,
  // theatre planner is admin focused, which means an admin sets which scenes
  // are practiced when and the acters will get informed on which dates they
  // need to be present
  "user_focused" => true,
  "admin_mail" => "admin@theatre-planner.org",

  "contact_info" => "Contact information to be shown in imprint and privacy declaration",
  "imprint_text" => "Custom text to be shown in the imprint",
  "data_protection_officer" => "Name and address",
  "custom_privacy_text" => "",
  "disable_standard_privacy" => false,

  "header_tags" => "",
  // Getss set automaticallly during setup. If theatre planner is not inside the
  // document root, insert the subfolder here with a leading, bt no trailing /
  // (e.g. if you can find the loginpage at http://example.com/theatre_planner/index.php, then the subfolder is /theatre_planner)
  // This also has to be adjisted in the browserconfig.xml if you're not using the setup
  "subfolder" => ""
);
?>
