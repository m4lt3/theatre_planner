<?php
return (object) array(
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
  "imprint_text" => "Text to be shown in the imprint",
  "data_protection_officer" => "Name and address",
  "custom_privacy_text" => "",
  "disable_standard_privacy" => false
);
?>
