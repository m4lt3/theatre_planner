<?php
/**
* Creates a card that displays information about a scene and a form to add, remove and priorize roles.
*
* @param int|string $SceneID ID of the Scene
* @param int $order The order of the scene
* @param string $Name name of the Scene
* @param string $Description Description of the Scene
* @param array $Roles Array of names of  featured roles
* @param array $Features Array of Primary keys of role-scene relationships in the same orrder as roles
* @param array $Mandatory Array of booleans wheter the role is mandatory for the scene or not - in the same order as roles
* @param array $FreeRoles Array of Role ID and Name of Roles that have not yet been assigned to the scene
*
* @return string Card template
*/
function createSceneCard($SceneID, $order, $Name, $Description, $Roles, $Features, $Mandatory, $FreeRoles){
  global $lang;

  $role_rows = createRoleRows($Roles, $Mandatory, $Features);

  $role_dialogue = createRoleDialogue($FreeRoles, $SceneID);

$button =<<<EOT
<form method="POST" action="" style="margin-bottom:0;">
  <input type="hidden" name="rm_scene" value="$SceneID">
  <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
</form>
EOT;

$card =<<<EOT
<div class="ui card">
  <div class="content">
    <div class="header">
      <span class="meta">$order.</span> $Name
      <div class="right floated meta">#$SceneID</div>
    </div>
  </div>
  <div class="content">
    <div class="ui sub header">{$lang->description}</div>
    $Description
  </div>
  <div class="content">
    <div class="ui sub header">{$lang->roles}</div>
    <table class="ui very basic table">
      $role_rows
      $role_dialogue
    </table>
  </div>
  $button
</div>
EOT;
return $card;
}

/**
* Creates a table row with name, mandatory-toggle-form and delete-relationship button for each role.
*
* @param array $Roles Array of names of  featured roles
* @param array $Features Array of Primary keys of role-scene relationships in the same orrder as roles
* @param array $Mandatory Array of booleans wheter the role is mandatory for the scene or not - in the same order as roles
*
* @return string Template string for table rows
*/
function createRoleRows($Roles, $Mandatory, $Features){
  global $lang;

  $role_rows = "";
  foreach ($Roles as $index => $role) {
    if($role == ""){
      continue;
    }
    $mandatoryColour = "";
    $mandatory_appendix = $lang->mandatory_appendix;
    if($Mandatory[$index]){
      $mandatoryColour = "orange";
      $mandatory_appendix = "";
    }
    $role_rows .= <<<EOT
    <tr>
      <td>$role</td>
      <td>
        <form action="" method="post">
          <input type="hidden" name="toggle_mandatory" value ="$Features[$index]">
          <button title="{$lang->admin_prefix}$mandatory_appendix{$lang->mandatory}" type="submit" style="cursor:pointer" class="ui $mandatoryColour label">
            <i class="fitted exclamation icon"></i>
          </button>
        </form>
      </td>
      <td>
        <form action="" method="POST">
          <input type="hidden" value="$Features[$index]" name="rm_features">
            <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
          </form>
        </td>
    </tr>
EOT;
  }
  return $role_rows;
}

/**
* Creates a Dropdown Dialogue to add roles to the scene.
*
* @param array $FreeRoles Array of Role ID and Name of Roles that have not yet been assigned to the scene
* @param int|string $SceneID ID of the Scene
*
* @return string template for dialogue
*/
function createRoleDialogue($FreeRoles, $SceneID){
  global $lang;
  $dialogue_options = "";
  if(count($FreeRoles??array())>0){
    foreach($FreeRoles as $freeRole){
      $dialogue_options .= '<div class="item" data-value ="' . $freeRole["RoleID"] . '">' . $freeRole["Name"] . '</div>';
    }
  }

$role_dialogue =<<<EOT
<tr>
  <form action="" method="post">
    <td>
      <div class="field">
        <div class="ui selection dropdown">
          <input type="hidden" name="newFeature">
          <i class="dropdown icon"></i>
          <div class="default text">{$lang->features}</div>
            <div class="menu">
              $dialogue_options
            </div>
        </div>
      </div>
    </td>
    <td>
    <div class="ui toggle checkbox">
      <input type="checkbox" name="isMandatory" checked="true">
    </div>
    </td>
    <td>
      <input type="hidden" name="SceneID" value="$SceneID">
      <button class="ui primary icon button" type="submit" name="addFeature"><i class="plus icon"></i></button>
    </td>
  </form>
</tr>
EOT;
  return $role_dialogue;
}
?>
