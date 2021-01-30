<?php
/**
* Creates a card to display information about a role and the ability to (re)assign an actor.
*
* @param int|string $id ID of the Role
* @param string $name Name of the Role
* @param string $description Description of the role
* @param int|string $uid the UserID of the assigned User
*
* @return string card template
*/
function createRoleCard($id, $name, $description, $uid){
  global $lang;

  $youLabel = "";
  if($uid == $_SESSION["UserID"]){
    $youLabel = '<div class="floating ui teal label" title="'.$lang->thats_you.'"><i class="fitted user icon"></i></div>';
  }


  $card =<<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        $name
        <div class="right floated meta">#$id</div>
        $youLabel
      </div>
    </div>
    <div class="content">
      <div class="ui sub header">{$lang->description}</div>
      $description
    </div>
  </div>
EOT;
  return $card;

}
?>
