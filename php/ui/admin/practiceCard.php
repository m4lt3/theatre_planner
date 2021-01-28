<?php
/**
* Creates a card template for displaying information about a practice date.
*
* @param object $practice_collection The PRactice object ontaining all necessary information
*
* @return string The template
*/
function createPracticeCard($practice_collection){
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
      $attendee_rows .= '<tr><td>' . $attendee["name"] .'<div class="right floated meta">#' . $attendee["id"] . '</div></td></tr>';
    }

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
?>
