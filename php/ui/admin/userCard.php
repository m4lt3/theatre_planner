<?php
/**
* Create a Card that displays actor information and allows to assign roles.
*
* @param string $UserID The user id
* @param string $name The username
* @param string $mail The email-address of the user
* @param array $roles All roles played by the actor
* @param array $PlaysID The Primary keys to the plays relation from actor to role
* @param array $FreeRoles Collection of Role-ID and Role-Name pairs that are not yet assigned to an actor
* @param bool $admin If the current user is an admin
*
* @return string Heredoc string of the card-html.
*/
function createCard($UserID, $name, $mail, $roles, $PlaysID, $FreeRoles, $admin){
  global $lang;

  // generate more complex components
  $role_rows = create_role_rows($roles, $PlaysID);
  $dialogue_options = create_dialogue_options($FreeRoles);
  $role_dialogue = create_role_dialogue($dialogue_options, $UserID);
  $button =<<<EOT
  <form method="POST" action="" style="margin-bottom:0;">
    <input type="hidden" name="rm_user" value="$UserID">
    <button class="ui bottom attached red button" style="width:100%" type="submit"><i class="trash icon"></i></button>
  </form>
EOT;

  // Determine admin-indicating UI
  $adminColour = "";
  $admin_appendix = $lang->admin_appendix;
  if($admin){
    $adminColour = "orange";
    $admin_appendix = "";
  }


  $card = create_actual_card($name, $UserID, $mail, $admin_appendix, $adminColour, $role_rows, $role_dialogue, $button);
  return $card;
}


/**
* Creates a table row containing name of the role and a delete assignment option for each role in the array.
*
* @param array $roles Names of the assigned roles
* @param array $PlaysID Primary keys of the role-actor relation-table in the same order as the roles
*
* @return string Heredoc-template
*/
function create_role_rows($roles, $PlaysID){
  $role_rows = "";
  foreach ($roles as $index => $role) {
    if($role == ""){
      continue;
    }
    $role_rows .= <<<EOT
    <tr>
      <td>$role</td>
      <td>
        <form action="" method="POST">
          <input type="hidden" value="$PlaysID[$index]" name="rm_plays">
            <button type="submit" class="ui red icon button"><i class="trash icon"></i></button>
          </form>
        </td>
    </tr>
EOT;
  }
  return $role_rows;
}

/**
* Creates the selection values for the role-assignment dropdown.
*
* @param array $FreeRoles Array of ID-Name-Pairs of the unassigned roles
*
* @return string Heredoc template
*/
function create_dialogue_options($FreeRoles){
  $dialogue_options = "";
  if(count($FreeRoles??array())>0){
    foreach($FreeRoles as $freeRole){
      $dialogue_options .= '<div class="item" data-value ="' . $freeRole["RoleID"] . '">' . $freeRole["Name"] . '</div>';
    }
  }
  return $dialogue_options;
}


/**
* Creates a dropdown form for assigning a new role using already generated select values.
*
* @see create_dialogue_options()
*
* @param string $dialogue_options The generated select values
* @param int|string $UserID the ID of the current user
*
* @return string Heredoc template
*/
function create_role_dialogue($dialogue_options, $UserID){
  global $lang;

  $role_dialogue =<<<EOT
  <tr>
    <form action="" method="post">
      <td>
        <div class="field">
          <div class="ui selection dropdown">
            <input type="hidden" name="newPlay">
            <i class="dropdown icon"></i>
            <div class="default text">{$lang->role}</div>
              <div class="menu">
                $dialogue_options
              </div>
          </div>
        </div>
      </td>
      <td>
        <input type="hidden" name="UserID" value="$UserID">
        <button class="ui primary icon button" type="submit" name="addPlay"><i class="plus icon"></i></button>
      </td>
    </form>
  </tr>
EOT;
  return $role_dialogue;
}

/**
* Puts all components together and generates the card template.
*
* @see create_role_rows()
* @see create_role_dialogue()
*
* @param string $name Name of the current user
* @param int|string $UserId the ID of the user
* @param string $mail email address of the user
* @param string $admin_appendix String for the indicate-admin UI (empty or no)
* @param string $adminColour Colour for the indicate-admin UI
* @param string $role_rows generated table row template
* @param string $role_dialogue Generated dropdown form template
* @param string $button Generated delete button form
*
* @return string Complete card template ready for printing
*/
function create_actual_card($name, $UserID, $mail, $admin_appendix, $adminColour, $role_rows, $role_dialogue, $button){
  global $lang;

  $card =<<<EOT
<div class="ui card">
<div class="content">
<div class="header">
$name
<div class="right floated meta">#$UserID</div>
<form action="" method="post"><input type="hidden" name="toggle_admin" value ="$UserID"><button title="{$lang->admin_prefix}$admin_appendix{$lang->admin}" type="submit" style="cursor:pointer" class="ui right floating $adminColour icon label"><i class="fitted chess queen icon"></i></button></form>
</div>
<div class="meta"><a href="mailto:$mail">$mail</a></div>
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
?>
