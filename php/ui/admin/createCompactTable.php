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
  return $body;
}

function generateSceneRow($scene){
  $rowspan = $scene->getRelationCount()+1;
  $sceneCell = $sceneCell = <<<EOT
  <td rowspan="$rowspan"> <span class="meta">{$scene->sequence}.</span> {$scene->title}</td>
EOT;
  $roleActorCells = "";
  if($scene->getRelationCount() > 0){

    $roleActorCells = '<td>'.generateRoleDropdown($scene->parties[0]).'</td><td>'.generateActorDropdown($scene->parties[0]).'</td>';
    $sceneRow = "<tr>".$sceneCell.$roleActorCells."</tr>";
    if($scene->getRelationCount()>1){
      for ($i=1; $i < $scene->getRelationCount(); $i++) {
        $sceneRow .= "<tr><td>".generateRoleDropdown($scene->parties[$i])."</td><td>".generateActorDropdown($scene->parties[$i])."</td></tr>";
      }
    }
  }
  $add_form = generateAddForm($for, $id);

  return $sceneRow;
}


function generateActorDropdown($selected){
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
function generateRoleDropdown($selected){
  global $db;
  $options = $db->baseQuery("SELECT RoleID AS ID, Name FROM ROLES")??array();


  $selections = "";
  foreach ($options as $option) {
    $active = $option["ID"]==$selected["UserID"]?"active selected ":"";
    $selections .= '<div class="'.$active.'item" data-value="'.$option["ID"].'">'.$option["Name"].'</div>';
  }
  $form = <<<EOT
  <form action="" method="post" style="display: inline-block">
  <input type="hidden" name="relation_id" value="{$selected["FeatureID"]}">
    <div class="ui search selection dropdown">
      <input type="hidden" name="change_role" value="">
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

function generateAddForm($for, $id){
  global $lang;

  $form = <<<EOT
  <form action="" method="post">
  <input type="hidden" name="id" value="$id">
    <div class="ui search selection dropdown">
      <input type="hidden" name="add_$display" value="">
      <i class="dropdown icon"></i>
      <div class="default text">{$lang->$for}</div>
      <div class="menu">
        $selections
      </div>
    </div>
    <button type="submit" class="ui blue icon button"><i class="plus icon"></i></button>
  </form>
EOT;
}

?>
