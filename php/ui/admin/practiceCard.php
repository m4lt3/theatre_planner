<?php
/**
* This function collects all data from the query, groups it accordingly and creates a card for each practice.
*
* @param array $practices Array containing all results from the according query
* @param array $allScenes Array containing all Scenes in the system
*/
function generateUserFocusedCardStack($practices, $allScenes){
  global $divided;
  global $lang;
  $practice_collection = new Practice(-1,"","");
  if(!empty($practices)){
    foreach ($practices as $practice) {
      // as with the user, due to the massive joins, a practice can (and most likely will) appear multiple times, so the values are stored together until the are ready to display.
      if($practice["PracticeID"] != $practice_collection->id){
        if($practice_collection->id != -1){
          // Detect all practiceable scenes before displaying
          $practice_collection->detectScenes($allScenes);

          echo createUserFocusedPracticeCard($practice_collection);
          if (!$divided && $practice["Start"] > date("Y-m-d H:i:s")){
            // If new practice is in the future and past dates are enabled and not yet separated, draw a line
            echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards" style="margin-top:-14px">';
            $divided = !$divided;
          }
        }
        // initialize new Collection for the next practice
        $practice_collection = new Practice($practice["PracticeID"], $practice["Title"], $practice["Start"]);
      }
      if(count($practice_collection->attendees) == 0){
        // if array is empty, then simply add user
        $practice_collection->attendees[] = array("id"=>$practice["UserID"], "name"=>$practice["Name"]);
      } elseif($practice["UserID"] != $practice_collection->attendees[count($practice_collection->attendees)-1]["id"]){
        // if a new user attends the practice, add it to the list
        $practice_collection->attendees[] = array("id"=>$practice["UserID"], "name"=>$practice["Name"], "informal"=>$practice["Informal"]);
      }
      // add the Role to the list
      $practice_collection->roles[] = $practice["RoleID"];
    }
    // print last practice as it didn't get triggered
    $practice_collection->detectScenes($allScenes);
    echo createUserFocusedPracticeCard($practice_collection);
    if(!$divided){
      // If all dates have been in the past, at least indicate that now
      echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards">';
    }
  }
}

/**
* Creates a card template for displaying information about a practice date.
*
* @param object $practice_collection The Practice object containing all necessary information
*
* @return string The template
*/
function createUserFocusedPracticeCard($practice_collection){
    global $lang;
    $format = new DateTime($practice_collection->date);
    // Creating delete button
    $button=<<<EOT
    <form method="POST" action="" style="margin-bottom:0;">
      <input type="hidden" name="rm_date" value="$practice_collection->id">
      <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
    </form>
EOT;

    // Creating table roles containing name and id of each ettending actor
    $attendee_rows = "";
    if(!empty($practice_collection->attendees[0]["id"]))
    foreach ($practice_collection->attendees as $attendee) {
      $attendee_rows .= '<tr><td>' . $attendee["name"] .'<div class="right floated meta">#' . $attendee["id"] . '</div></td><td>'.generateRemoveButton($attendee["id"],$practice_collection->id, $practice_collection->date).'</td></tr>';
    }

    $add_attendee_dialogue = generateAddAttendeeDialogue($practice_collection->id, $practice_collection->date);

    // Creating table rows containing name and id of each practiceable scene
    $scene_rows = "";
    foreach ($practice_collection->scenes as $scene) {
      $scene_rows .= '<tr><td>' . $scene["Name"] .'<div class="right floated meta">#' . $scene["SceneID"] . '</div></td></tr>';
    }

    //Generating card
    $card=<<<EOT
    <div class="ui card">
      <div class="content">
        <div class="header">
          $practice_collection->title
          <div class="right floated meta">
            #$practice_collection->id
          </div>
        </div>
        <div class="meta">
          {$format->format("d.m.Y H:i")}
        </div>
      </div>
      <div class="content">
        <div class="sub header">{$lang->attendees}</div>
        <table class="ui very basic table">
          $attendee_rows
          $add_attendee_dialogue
        </table>
      </div>
      <div class="content">
        <div class="sub header">{$lang->available_scenes}</div>
        <table class="ui very basic table">
          $scene_rows
        </table>
      </div>
      $button
    </div>
EOT;
    return $card;
}

/**
* Creates  a dialogue to add actors which allow having their attendance status set by admins
*
* @param int|string $pid ID of the Practice to add to
* @param object $date the date of the practice. If it is in the past, no button is generated
*
* @return string template of the selection dialogue
*/
function generateAddAttendeeDialogue($pid, $date){
  global $db;
  global $lang;

  if($date < date("Y-m-d H:i:s")){
    return "";
  }
  $assignable = $db->prepareQuery("SELECT u.UserID, u.Name FROM USERS u WHERE u.Informal = true AND NOT EXISTS (SELECT u.UserID FROM PRACTICES p LEFT JOIN ATTENDS a ON p.PracticeID = a.PracticeID WHERE a.UserID = u.UserID AND p.PracticeID = ?)", "i", array($pid));
  $options = "";
  foreach ($assignable??array() as $actor) {
    $options .= '<div class="item" data-value="'.$actor["UserID"].'">'.$actor["Name"].'</div>';
  }

  $form = <<<EOT
  <tr>
    <form action="" method="POST">
      <td>
        <div class="ui selection dropdown">
          <input type="hidden" name="attend_actor">
          <i class="dropdown icon"></i>
          <div class="default text">{$lang->actor}</div>
          <div class="menu">
            $options
          </div>
        </div>
      </td>
      <td>
        <input type="hidden" name="PracticeID" value="$pid">
        <button type="submit" class="ui primary icon button" style="float:right"><i class="plus icon"></i></button>
      </td>
    </form>
  </tr>
EOT;
  return $form;
}

/**
* Generates a delete button to remove attending actors from practices
*
* @param int|string $UserID ID of the user to remove
* @param int|string $PracticeID ID of the Practice to remove the user from
* @param object $date the date of the practice. If it is in the past, no button is generated
*
* @return string button template
*/
function generateRemoveButton($UserID, $PracticeID, $date){
  global $db;

  if($date < date("Y-m-d H:i:s")){
    return "";
  }
  $informal = $db->prepareQuery("SELECT Informal FROM USERS Where UserID=?","i",array($UserID))[0]["Informal"];
  if($informal){
    $button = <<<EOT
    <form action="" method="POST" style="width:min-content;float:right">
    <input type="hidden" name="rm_UserID" value="$UserID">
    <input type="hidden" name="PracticeID" value="$PracticeID">
    <button type="submit" class="ui red icon button"><i class="fitted trash icon"></i></button>
    </form>
EOT;
  return $button;
  } else {
    return "";
  }
}

// =============== Admin focused UI ============

/**
* Loops through all given practices, cumulates coherent data and forwards it to crete a suitable card.
*
* @param array $practices list of all practices, coresponding scenes and the associated PlanID (redundant)
* @param array $allScenes all available scenes
*/
function createAdminFocusedCardStack($practices, $allScenes){
  global $divided;
  global $lang;

  if(!empty($practices)){
    $currentPractice = array(array_shift($practices));

    foreach($practices as $practice){
      if($currentPractice[0]["PracticeID"]!=$practice["PracticeID"]){
        //print old practice before overwriting
        echo createAdminFocusedPracticeCard($currentPractice, $allScenes);
        if (!$divided && $practice["Start"] > date("Y-m-d H:i:s")){
          // If new practice is in the future and past dates are enabled and not yet separated, draw a line
          echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards" style="margin-top:-14px">';
          $divided = !$divided;
        }
        $currentPractice = array($practice);

      } else {
        $currentPractice[] = $practice;
      }
    }
    echo createAdminFocusedPracticeCard($currentPractice, $allScenes);
    if(!$divided){
      // If all datas have been in the past, at least indicate that now
      echo '</div><div class="ui horizontal divider">' . $lang->today .'</div><div class="ui two stacked cards">';
    }
  }
}

/**
* Creates a card view that displays name, id, start and assigned scenes, as well as a dropdown form to assign another
*
* @param array $scenes an array of playsID-SceneID and Scene Name pairs
* @param array $allScenes all available scenes
*
* @return string Card template
*/
function createAdminFocusedPracticeCard($scenes, $allScenes){
  global $lang;
  $format = new DateTime($scenes[0]["Start"]);

  $scene_rows = createSceneRows($scenes);

  $scene_dialogue = "";
  if($scenes[0]["Start"] > date("Y-m-d H:i:s")){
    $scene_dialogue = createSceneDialogue(getFreeScenes($scenes, $allScenes), $scenes[0]["PracticeID"]);
  }

$button =<<<EOT
<form method="POST" action="" style="margin-bottom:0;">
  <input type="hidden" name="rm_date" value="{$scenes[0]["PracticeID"]}">
  <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
</form>
EOT;

$card =<<<EOT
<div class="ui card">
  <div class="content">
    <div class="header">
      {$scenes[0]["Title"]}
      <div class="right floated meta">#{$scenes[0]["PracticeID"]}</div>
    </div>
    <div class="meta">{$format->format("d.m.Y H:i")}</div>
  </div>
  <div class="content">
    <div class="ui sub header">{$lang->scenes}</div>
    <table class="ui very basic table">
      $scene_rows
      $scene_dialogue
    </table>
  </div>
  $button
</div>
EOT;
return $card;
}

/**
* Creates a table row with name and delete-relationship button for each scene.
*
* @param array $scenes an array of PracticeID-SceneID and Scene Name pairs
*
* @return string Template string for table rows
*/
function createSceneRows($scenes){
  global $lang;
  $scene_rows = "";
  $createForm = true;
  if($scenes[0]["Start"] < date("Y-m-d H:i:s")){
    $createForm = false;
  }
  foreach ($scenes as $scene) {
    if(empty($scene["PlanID"])){
      continue;
    }
    $scene_rows .= <<<EOT
    <tr>
      <td><span class="meta">{$scene["Sequence"]}.</span> {$scene["Scene"]}</td>
  EOT;
  if($createForm){
    $scene_rows .= <<<EOT
    <td>
      <form action="" method="POST">
        <input type="hidden" value="{$scene["PlanID"]}" name="rm_planned">
          <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
        </form>
      </td>
EOT;
  }
  $scene_rows .= "</tr>";

  }
  return $scene_rows;
}

/**
* Detects unassigned scenes by comparing all scenes to the assigned scenes
*
* @param array $assignedScenes array of name and ID of all assigned scenes
* @param array $allScenes array of name and ID of all scenes
*
* @return array all unassigned scenes
*/
function getFreeScenes($assignedScenes, $allScenes){
  $freeScenes = array();
  $assignedMap = array();
  //making it ino a hasmap results in a runtime complexity of n+k instead of n*k
  foreach ($assignedScenes as $scene) {
    $assignedMap[$scene["SceneID"]] = "assigned";
  }

  foreach ($allScenes??array() as $scene) {
    if(!isset($assignedMap[$scene["SceneID"]])){
      $freeScenes[] = $scene;
    }
  }
  return $freeScenes;
}

/**
* Creates a Dropdown Dialogue to add scenes to the practice.
*
* @param array $freeScenes Array of Scene ID and Name of Scemes that have not yet been assigned to the Practice
* @param int|string $PracticeID ID of the Practice
*
* @return string template for dialogue
*/
function createSceneDialogue($freeScenes, $PracticeID){
  global $lang;

  $dialogue_options = "";
  if(count($freeScenes??array())>0){
    foreach($freeScenes as $freeScene){
      $dialogue_options .= '<div class="item" data-value ="' . $freeScene["SceneID"] . '">' . $freeScene["Name"] . '</div>';
    }
  }

$scene_dialogue =<<<EOT
<tr>
  <form action="" method="post">
    <td>
      <div class="field">
        <div class="ui selection dropdown">
          <input type="hidden" name="newPlan">
          <i class="dropdown icon"></i>
          <div class="default text">{$lang->practices}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            <div class="menu">
              $dialogue_options
            </div>
        </div>
      </div>
    </td>
    <td>
      <input type="hidden" name="PracticeID" value="$PracticeID">
      <button class="ui primary icon button" type="submit" name="addPlanned"><i class="plus icon"></i></button>
    </td>
  </form>
</tr>
EOT;
  return $scene_dialogue;
}

?>
