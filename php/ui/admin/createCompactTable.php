<?php
require dirname(dirname(__DIR__))."/utils/compact.php";

/**
* Creates the body of the overview table
*
* @param array $everything query results of all scenes containing ScenID, Scene name, scene sequence and all associated roles and actors with corresponding relation IDs
*
* @return string template of the body
*/
function createCompactTable($everything){
  $body = "";
  $sceneObject = new compactScene(NULL);
  foreach ($everything??array() as $scene) {
    // Aggregation of coherent data for each scene
    if($sceneObject->id != $scene["SceneID"]){
      // Aggregation complete, generating table row(s) and starting new aggregation
      if(isset($sceneObject->id)){
        $body .= generateSceneRow($sceneObject);
      }
      $sceneObject = new compactScene($scene);
    } else {
      // Aggregating data
      $sceneObject->addRelations($scene);
    }
  }
  // Triggering last scene
  $body .= generateSceneRow($sceneObject);
  // Generating "add scene" dialogue
  $body .= generateAddSceneRow($sceneObject->sequence+1);
  return $body;
}

/**
* Generate tablerow(s) for a scene
*
* @param array $scene the compactScene object of the scene to print
*
* @return string template for scene row(s)
*/
function generateSceneRow($scene){
  $rowspan = $scene->getRelationCount()+1;
  $sceneCell = $sceneCell = <<<EOT
  <td rowspan="$rowspan"> <span class="meta">{$scene->sequence}.</span> {$scene->title}</td>
EOT;
  $roleActorCells = "";
  $sceneRow = "";
  if(!empty($scene->parties[0]["FeatureID"])){
    // If there is at least one role associated, print it in the next cell
    $actorDropdown = "";
    if(empty($scene->parties[0]["PlaysID"])){
      // If there is no actor associated with the role, generate a form to add an actor
      $actorDropdown = generateAddActorDropdown($scene->parties[0]["RoleID"]);
    } else {
      // ... or generate form to change the existing assignment
      $actorDropdown = generateChangeActorDropdown($scene->parties[0]);
    }

    $roleActorCells = '<td>'.$scene->parties[0]["RoleName"].generateDeleteRoleButton($scene->parties[0]).'</td><td>'.$actorDropdown.'</td>';
    $sceneRow = "<tr>".$sceneCell.$roleActorCells."</tr>";
    if($scene->getRelationCount()>1){
      // If there are more than one roles associated, print coresponding roles
      for ($i=1; $i < $scene->getRelationCount(); $i++) {
        if(empty($scene->parties[$i]["PlaysID"])){
          // If there is no actor associated with the role, generate a form to add an actor
          $actorDropdown = generateAddActorDropdown($scene->parties[$i]["RoleID"]);
        } else {
          // ... or generate form to change the existing assignment
          $actorDropdown = generateChangeActorDropdown($scene->parties[$i]);
        }
        $sceneRow .= "<tr><td>".$scene->parties[$i]["RoleName"].generateDeleteRoleButton($scene->parties[$i])."</td><td>".$actorDropdown."</td></tr>";
      }
    }
    // Printing form to add role to scene, indifferent of whether there already are other actors
    $sceneRow .= '<tr><td>'.generateAddRoleDropdown($scene->id, $scene->getRoles()).'</td><td></td><tr>';
  } else {
    $sceneRow .= '<tr>'.$sceneCell.'<td>'.generateAddRoleDropdown($scene->id, $scene->getRoles()).'</td><td></td><tr>';
  }

  return $sceneRow;
}

/**
* Generates a dropdown to change an associated actor with pre-selection of the current actor
*
* @param array $selected associative array of the selected array including name, id and relation-id
*
* @return string form template
*/
function generateChangeActorDropdown($selected){
  global $db;
  $options = $db->baseQuery("SELECT UserID AS ID, Name FROM USERS")??array();

  $selections = "";
  foreach ($options as $option) {
    // Determine the selected option and generate options template
    $active = $option["ID"]==$selected["UserID"]?"active selected ":"";
    $selections .= '<div class="'.$active.'item" data-value="'.$option["ID"].'">'.$option["Name"].'</div>';
  }
  $form = <<<EOT
  <form action="" method="post" style="display: inline-block">
  <input type="hidden" name="relation_id" value="{$selected["PlaysID"]}">
  <input type="hidden" name="role_id" value="{$selected["RoleID"]}">
    <div class="ui change selection dropdown">
      <input type="hidden" name="change_actor" value="">
      <i class="dropdown icon"></i>
      <div class="text">{$selected["UserName"]}</div>
      <div class="menu">
        $selections
      </div>
    </div>
  </form>
EOT;
  $form .= <<<EOT
  <form method="POST" action="" style="display: inline-block; float:right">
    <input type="hidden" name="rm_plays" value="{$selected["PlaysID"]}">
    <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
  </form>
EOT;
  return $form;
}

/**
* Generates a dropdown to change an associated role with pre-selection of the current role
*
* @param array $selected associative array of the selected role including name, id and relation-id
*
* @return string form template
*/
function generateDeleteRoleButton($selected){
  $form = <<<EOT
  <form method="POST" action="" style="display: inline-block; float:right">
    <input type="hidden" name="rm_features" value="{$selected["FeatureID"]}">
    <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
  </form>
EOT;
  return $form;
}

/**
* Generates a dropdown to assign an existing actor to a role
*
* @param int|string $RoleID ID of the role to assign to
*
* @return string form template
*/
function generateAddActorDropdown($RoleID){
  global $lang;
  global $db;
  $users = $db->baseQuery("SELECT UserID, Name FROM USERS");
  $selections = "";
  foreach ($users as $option) {
    $selections .= '<div class="item" data-value="'.$option["UserID"].'">'.$option["Name"].'</div>';
  }
  $form = <<<EOT
  <form action="" method="post">
  <input type="hidden" name="id" value="$RoleID">
    <div class="ui search selection dropdown">
      <input type="hidden" name="add_actor" value="">
      <i class="dropdown icon"></i>
      <div class="default text">{$lang->actor}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
      <div class="menu">
        $selections
      </div>
    </div>
    <button type="submit" class="ui blue icon button" style="float:right"><i class="plus icon"></i></button>
  </form>
EOT;
  return $form;
}

/**
* Generates a dropdown to assign an existing or a new role to a scene
*
* @param int|string $SceneID of the scene to assign to
* @param array $excludeRoles array containing IDs of the roles that are already assigned to the scene
*
* @return string form template
*/
function generateAddRoleDropdown($SceneID, $excludeRoles){
  global $lang;
  global $db;
  $excludeIDs = array();
  foreach ($excludeRoles as $excludeRole) {
    // Generating associative array for better performance later on
    $excludeIDs[$excludeRole["ID"]]=true;
  }
  $roles = $db->baseQuery("SELECT RoleID, Name FROM ROLES");
  $selections = "";
  foreach ($roles as $option) {
    if(empty($excludeIDs[$option["RoleID"]])){
      // generating only options for roles that are not excluded
      $selections .= '<div class="item" data-value="'.$option["RoleID"].'">'.$option["Name"].'</div>';
    }
  }

  $form = <<<EOT
  <form action="" method="post">
  <input type="hidden" name="id" value="$SceneID">
    <div class="ui search selection dropdown">
      <input type="hidden" name="add_role" value="">
      <i class="dropdown icon"></i>
      <div class="default text">{$lang->role}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
      <div class="menu">
        $selections
      </div>
    </div>
    <button type="submit" class="ui blue icon button" style="float:right"><i class="plus icon"></i></button>
  </form>
EOT;
  return $form;
}

/**
* Generates a table row with a form to add a new scene
*
* @param int|string $sequence Sequence of the new role
*
* @return string row template
*/
function generateAddSceneRow($sequence){
  global $lang;
  $form = <<<EOT
  <form method="post" action ="">
  <input type="hidden" name="sequence" value="$sequence">
    <div class="ui input">
      <input type="text" placeholder="{$lang->scene}" name="add_scene" value="">
    </div>
    <button type="submit" class="ui blue icon button" style="float:right"><i class="plus icon"></i></button>
  </form>
EOT;
  $row = "<tr><td>".$form."</td><td></td><td></td></tr>";
  return $row;
}

?>
