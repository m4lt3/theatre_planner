<?php
/**
* Generates a card contatining information about an unassigned role.
*
* @param array $unplayedRole An array contianing name, id and description of the role
*
* @return string card template
*/
function generateUnplayedCard($unplayedRole){
  global $lang;
  $unplayed_card =<<<EOT
    <div class="ui card">
      <div class="content">
        <div class="header">
          {$unplayedRole["Name"]}
          <div class="right floated meta">#{$unplayedRole["RoleID"]}</div>
        </div>
      </div>
      <div class="content">
        <div class="ui sub header">{$lang->description}</div>
        {$unplayedRole["Description"]}
      </div>
    </div>
EOT;
  return $unplayed_card;
}

/**
* Generates a card to display basic information about an roleless actor.
*
* @param array $roleless_actor Name, ID and mail of the roleless actor
*
* @return string card template
*/
function generateRolelessCard($roleless_actor){
  $roleless_card =<<<EOT
    <div class="ui card">
      <div class="content">
        <div class="header">
          {$roleless_actor["Name"]}
          <div class="right floated meta">#{$roleless_actor["UserID"]}</div>
        </div>
        <div class="meta"><a href="mailto:{$roleless_actor["Mail"]}">{$roleless_actor["Mail"]}</a></div>
      </div>
    </div>
EOT;
  return $roleless_card;
}
?>
