<?php
require dirname(dirname(__DIR__))."/utils/compact.php";
function createCompactTable($everything){
  $body = "";
  $sceneObject = new compactScene(NULL);
  foreach ($everything??array() as $scene) {
    if($sceneObject->id != $scene["SceneID"]){
      if(isset($sceneObject->id)){
        $body .= generateSceneRow($sceneObject);
      }
      $sceneObject = new compactScene($scene);
    } else {
      $sceneObject->addRelations($scene);
    }
  }
  $body .= generateSceneRow($sceneObject);
  $body .= generateAddSceneRow($sceneObject->sequence+1);
  return $body;
}

function generateSceneRow($scene){
  $rowspan = $scene->getRelationCount()+1;
  $sceneCell = $sceneCell = <<<EOT
  <td rowspan="$rowspan"> <span class="meta">{$scene->sequence}.</span> {$scene->title}</td>
EOT;
  $roleActorCells = "";
  $sceneRow = "";
  if(!empty($scene->parties[0]["FeatureID"])){
    $actorDropdown = "";
    if(empty($scene->parties[0]["PlaysID"])){
      $actorDropdown = generateAddActorDropdown($scene->parties[0]["RoleID"]);
    } else {
      $actorDropdown = generateChangeActorDropdown($scene->parties[0]);
    }

    $roleActorCells = '<td>'.generateChangeRoleDropdown($scene->parties[0]).'</td><td>'.$actorDropdown.'</td>';
    $sceneRow = "<tr>".$sceneCell.$roleActorCells."</tr>";
    if($scene->getRelationCount()>1){
      for ($i=1; $i < $scene->getRelationCount(); $i++) {
        if(empty($scene->parties[$i]["PlaysID"])){
          $actorDropdown = generateAddActorDropdown();
        } else {
          $actorDropdown = generateChangeActorDropdown($scene->parties[$i]);
        }
        $sceneRow .= "<tr><td>".generateChangeRoleDropdown($scene->parties[$i])."</td><td>".$actorDropdown."</td></tr>";
      }
    }
    $sceneRow .= '<tr><td>'.generateAddRoleDropdown($scene->id).'</td><td></td><tr>';
  } else {
    $sceneRow .= '<tr>'.$sceneCell.'<td>'.generateAddRoleDropdown($scene->id).'</td><td></td><tr>';
  }

  return $sceneRow;
}


function generateChangeActorDropdown($selected){
  global $db;
  $options = $db->baseQuery("SELECT UserID AS ID, Name FROM USERS")??array();

  $selections = "";
  foreach ($options as $option) {
    $active = $option["ID"]==$selected["UserID"]?"active selected ":"";
    $selections .= '<div class="'.$active.'item" data-value="'.$option["ID"].'">'.$option["Name"].'</div>';
  }
  $form = <<<EOT
  <form action="" method="post" style="display: inline-block">
  <input type="hidden" name="relation_id" value="{$selected["FeatureID"]}">
    <div class="ui selection dropdown">
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
    <input type="hidden" name="rm_relation" value="{$selected["FeatureID"]}">
    <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
  </form>
EOT;
  return $form;
}

function generateChangeRoleDropdown($selected){
  global $db;
  $options = $db->baseQuery("SELECT RoleID AS ID, Name FROM ROLES")??array();


  $selections = "";
  foreach ($options as $option) {
    $active = $option["ID"]==$selected["RoleID"]?"active selected ":"";
    $selections .= '<div class="'.$active.'item" data-value="'.$option["ID"].'">'.$option["Name"].'</div>';
  }
  $form = <<<EOT
  <form action="" method="post" style="display: inline-block">
  <input type="hidden" name="relation_id" value="{$selected["FeatureID"]}">
    <div class="ui search selection dropdown">
      <input type="hidden" name="change_role" value="">
      <i class="dropdown icon"></i>
      <div class="text">{$selected["RoleName"]}</div>
      <div class="menu">
        $selections
      </div>
    </div>
  </form>
EOT;
  $form .= <<<EOT
  <form method="POST" action="" style="display: inline-block; float:right">
    <input type="hidden" name="rm_relation" value="{$selected["FeatureID"]}">
    <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
  </form>
EOT;
  return $form;
}

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
      <input type="hidden" name="add_role" value="">
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

function generateAddRoleDropdown($SceneID){
  global $lang;
  global $db;
  $roles = $db->baseQuery("SELECT RoleID, Name FROM ROLES");
  $selections = "";
  foreach ($roles as $option) {
    $selections .= '<div class="item" data-value="'.$option["RoleID"].'">'.$option["Name"].'</div>';
  }

  $form = <<<EOT
  <form action="" method="post">
  <input type="hidden" name="id" value="$SceneID">
    <div class="ui search selection dropdown">
      <input type="hidden" name="add_actor" value="">
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
