<?php
/**
* Creates a card that displays information about a scene
*
* @param int|string $SceneID ID of the Scene
* @param string $Name name of the Scene
* @param string $Description Description of the Scene
* @param array $Roles Array of names of  featured roles
* @param array $Features Array of Primary keys of role-scene relationships in the same orrder as roles
* @param array $Mandatory Array of booleans wheter the role is mandatory for the scene or not - in the same order as roles
*
* @return string Card template
*/
function createSceneCard($SceneID, $Name, $Description, $Roles, $Mandatory){
  global $lang;

  $role_rows = createRoleRows($Roles, $Mandatory);

  $card =<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        $Name
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
      </table>
    </div>
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
function createRoleRows($Roles, $Mandatory){
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
          <div title="{$lang->admin_prefix}$mandatory_appendix{$lang->mandatory}" type="submit" style="cursor:pointer" class="ui $mandatoryColour label">
            <i class="fitted exclamation icon"></i>
          </div>
      </td>
    </tr>
EOT;
  }
  return $role_rows;
}
?>
