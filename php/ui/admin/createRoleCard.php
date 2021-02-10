<?php
/**
* Creates a card to display information about a role and the ability to (re)assign an actor.
*
* @param int|string $id ID of the Role
* @param string $name Name of the Role
* @param string $description Description of the role
* @param array $actors List of all ACtor names and corresponding IDs
*
* @return string card template
*/
function createRoleCard($id, $name, $description, $actors){
  global $lang;

  $actor_dialogue = createActorDialogue($actors, $id);

  $button =<<<EOT
  <div class="ui buttons">
  <form method="POST" action="" style="margin-bottom:0;width:50%">
    <input type="hidden" name="rm_role" value="$id">
    <button class="ui bottom attached red icon button" type="submit" style="width:100%;border-radius:0;border-bottom-left-radius: .28571429rem;"><i class="trash icon"></i></button>
  </form>
  <form method="POST" action="" style="margin-bottom:0;width:50%" id="edit_form_$id">
    <input type="hidden" name="edit_role" value="$id">
    <button class="ui bottom attached blue icon button" type="submit" style="width:100%;border-radius:0;border-bottom-right-radius: .28571429rem;"><i class="edit icon"></i></button>
  </form>
  </div>
EOT;

  return createActualCard($name, $id, $description, $actor_dialogue, $button);

}

/**
* Function creates a dropdown form with all actors as options.
*
* @param array $actors Array of Name-ID PAirs of actors
* @param int|string $id id to identify the forms
*/
function createActorDialogue($actors, $id){
  global $lang;

  $dialogue_options = "";
  if(count($actors)>0){
    foreach($actors as $actor){
      $dialogue_options .= '<div class="item" data-value ="' . $actor["UserID"] . '">' . $actor["Name"] . '</div>';
    }
  }

  $actor_dialogue =<<<EOT
    <form action="" method="post" id="form_$id">
      <input type="hidden" name="RoleID" value="$id">
      <div class="field">
        <div class="ui selection dropdown" id="dropdown_$id">
          <input type="hidden" name="newPlay" id="input_$id">
          <i class="dropdown icon"></i>
          <div class="default text">{$lang->actor}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>
            <div class="menu">
              $dialogue_options
            </div>
        </div>
      </div>
    </form>
EOT;
  return $actor_dialogue;
}

/**
* Creates the actual card template.
*
* @param string $name Name of the Role
* @param int|string $id ID of the Role
* @param string $description Description of the Role
* @param string Generated template of the role assignment form
*
* @see createActorDialogue()
*
* @return string Card template
*/
function createActualCard($name, $id, $description, $actor_dialogue, $button){
  global $lang;

  $card =<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        $name
        <div class="right floated meta">#$id</div>
      </div>
    </div>
    <div class="content">
      <div class="ui sub header">{$lang->description}</div>
        $description
    </div>
    <div class="content">
      <div class="ui sub header">{$lang->actor}</div>
      $actor_dialogue
    </div>
    $button
  </div>
EOT;
  return $card;
}
?>
