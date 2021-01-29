<?php
/**
* Creates a card to display basic information about a practice date; Based on the planning mode a user can either manage his attending-status or read if he's required.
*
* @param int|string $id ID of the practice
* @param string $title The title of the practice
* @param string $date The date of the practice
* @param int|bool $relationship Is the AttendsID, if user focused (value=attending, NULL=not attending), or the required status (1=required,0=requested,NULL=not required)
*
* @return string card template
*/
function createCard($id, $title, $date, $relationship){
  global $config;

  $format = new DateTime($date);

  $extraContent = "";

  if($config->user_focused){
    $extraContent = generateUserContent($id, $date, $relationship);
  } else {
    $extraContent = generateAdminContent($relationship, $date);
  }

  $card=<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        $title
        <div class="right floated meta">#$id</div>
      </div>
      <div class="meta">
        {$format->format("d.m.Y H:i")}
      </div>
    </div>
    <div class="extra content">
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
* Generates a label indicating the required-status in admin focused mode
*
* @param bool $required the required status (1=required,0=requested,NULL=not required)
* @param string $date Date of the practice determines the look of the label
*
* @return string label template
*/
function generateAdminContent($required, $date){
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
    <div class="ui centered $colour $basic label">
      {$lang->your_presence} $keyword
    </div>
EOT;
  return $label;
}
?>
