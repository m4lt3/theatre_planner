<?php
/**
* Creates a card to display basic information about a practice date; Based on the planning mode a user can either manage his attending-status or read if he's required and for which scenes.
*
* @param array $practice A pracctice array; If multiple scenes are assiged, there is one element for each scene.
*
* @return string card template
*/
function createCard($practice){
  global $config;

  $format = new DateTime($practice[0]["Start"]);

  $extra = "";
  $extraContent = "";

  if($config->user_focused){
    $extraContent = generateUserContent($practice[0]["PracticeID"], $practice[0]["Start"], $practice[0]["AttendsID"]);
    $extra = "extra ";
  } else {
    $extraContent = generateAdminContent($practice);
  }

  $card=<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        {$practice[0]["Title"]}
        <div class="right floated meta">#{$practice[0]["PracticeID"]}</div>
      </div>
      <div class="meta">
        {$format->format("d.m.Y H:i")}
      </div>
    </div>
    <div class="{$extra}content">
      $extraContent
    </div>
  </div>
EOT;
  echo $card;
}

/**
* Generates the Accept/Decline buttons for user focused mode.
*
* @param int $id ID of the practice
* @param string $date Date of the practice decides whether the buttons are disabled or not
* @param int $attends AttendsID, if existing; If not, NULL is evaluated as not attending
*
* @return string button template
*/
function generateUserContent($id, $date, $attends){
  $disabled = ($date < date("Y-m-d H:i:s"))?"disabled":"";
  $buttons = "";
  $reqID = $id;
  if(!empty($attends)){
    $reqID = $attends;
  }

  if (empty($attends)) {
    $buttons=<<<EOT
    <button type="button" class="ui red $disabled icon button" name="reject"><i class="close icon"></i></button>
    <button type="submit" class="ui basic green $disabled icon button" name="accept"><i class="check icon"></i></button>
EOT;
  } else {
    $buttons=<<<EOT
    <button type="submit" class="ui basic red $disabled icon button" name="reject"><i class="close icon"></i></button>
    <button type="button" class="ui green $disabled icon button" name="accept"><i class="check icon"></i></button>
EOT;
  }

  $form = <<<EOT
  <form action="" method="POST">
    <input type="hidden" name="reqID" value ="$reqID">
    <div class="ui two buttons">
      $buttons
    </div>
  </form>
EOT;
  return $form;
}

/**
* Generates a list of scenes for this practice and and displays information about whether the actor is required.
*
*
* @param array $practice A pracctice array; If multiple scenes are assiged, there is one element for each scene.
*
* @return string template for the admin content
*/
function generateAdminContent($practice){
  global $lang;

  $label = makeLabel($practice[0]["AttendsID"], $practice[0]["Start"]);
  $rows = makeRows($practice);
  $content=<<<EOT
  <div class="ui sub header">{$lang->scenes}</div>
    <table class="ui very basic table">
      $rows
    </table>
  </div>
  <div class="extra content" style="text-align:center">
  $label
EOT;
  return $content;
}

/**
* Generates a table row containing name and required status for each scene
*
* @param array $practice A pracctice array; If multiple scenes are assiged, there is one element for each scene.
*
* @return string template for the table rows
*/
function makeRows($practice){
  global $lang;

  $rows = "";
  if(!empty($practice[0]["Name"])){
    foreach ($practice as $scene) {
      $requiredIndicator = makeRequiredIndicator($scene["AttendsID"], $scene["Start"]);
      $rows .= '<tr><td>'.$scene["Name"].'</td><td>'.$requiredIndicator.'</td></tr>';
    }
  }
  return $rows;
}

/**
* generates a label indicating if the actor is required, requested or not requred.
*
* @param bool $required Required status (1=required,0=requested,NULL=not required)
* @param string date date to show if the label is urgent or not
*
* @return string label template
*/
function makeRequiredIndicator($required, $date){
  global $lang;

  $basic = ($date < date("Y-m-d H:i:s"))?" basic":"";
  // Detecting required status
  $mandatoryColour = "";
  $mandatoryText = "";
  $mandatoryIcon = "";
  if($required === NULL){
    // Empty means he is neither requested nor required for the scene
    $mandatoryIcon = '<i class="fitted question icon"></i>';
    $mandatoryColour = "green";
    $mandatoryText = $lang->actor_not_required;
  } else {
    // Not empty means role is part of a scene
    $mandatoryIcon = '<i class="fitted exclamation icon"></i>';
    if($required){
      // Role is mandatory
      $mandatoryColour = "red";
      $mandatoryText = $lang->actor_required;
    } else {
      // Role is requested but not mandatory
      $mandatoryColour = "orange";
      $mandatoryText = $lang->actor_requested;
    }
  }
  return '<div class="ui '.$mandatoryColour. $basic. ' label" title="'.$mandatoryText.'" style="width:26px; height:26px;display:flex;justify-content:center">'.$mandatoryIcon.'</div>';
}

/**
* Generates a label indicating the required-status in admin focused mode
*
* @param bool $required the required status (1=required,0=requested,NULL=not required)
* @param string $date Date of the practice determines the look of the label
*
* @return string label template
*/
function makeLabel($required, $date){
  global $lang;

  $keyword = "";
  $colour = "";
  $basic = ($date < date("Y-m-d H:i:s"))?"basic":"";
  if(isset($required)){
    if($required == 1){
      $keyword = $lang->actor_required;
      $colour = "red";
    } else {
      $keyword = $lang->actor_requested;
      $colour = "orange";
    }
  } else {
    $keyword = $lang->actor_not_required;
    $colour = "green";
  }
  $label =<<<EOT
    <div class="ui $colour $basic label">
      {$lang->your_presence} $keyword
    </div>
EOT;
  return $label;
}
?>
