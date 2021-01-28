<?php

/**
* The Practice class simply is a more structured container for practice data with the abaility to detect playable scened.
*
* @property int|string $id ID of the practice
* @property string $title Optional title of the practice
* @property string $date Date string of the practice
* @property array $attendees All attending actors (only the ID)
* @property array $roles All roles of the attending actors (id only)
* @property array $scenes Practiceable scenes, to be detected at the end
*
* @see Practice::detectScenes()
*/
class Practice{
  public $id;
  public $title;
  public $date;
  public $attendees;
  public $roles;
  public $scenes;

  function __construct($pID, $pTitle, $pDate){
    $this->id = $pID;
    $this->title = $pTitle;
    $this->date = $pDate;
    $this->attendees = array();
    $this->roles = array();
    $this->scenes = array();
  }

  /**
  * Detects all playable scenes based on given scenes and stored actors and roles.
  *
  * @param array $allScenes An Array containing All existing scenes with ID and name and their corresponding Roles and whether they are necessary or not
  */
  function detectScenes($allScenes){
    if(empty($allScenes)){
      $this->scenes = array();
    } else {
      $currentScene = $allScenes[0];
      $possible = true;
      foreach ($allScenes as $scene) {
        if($currentScene["SceneID"] != $scene["SceneID"]){
          // New scene is inspected
          if($possible){
            // Store old scene if it is possible
            array_push($this->scenes, array("SceneID"=> $currentScene["SceneID"], "Name"=> $currentScene["Name"]));
          }
          // reset values
          $possible = true;
          $currentScene = $scene;
        }
        if(!in_array($scene["RoleID"], $this->roles) && $scene["Mandatory"]){
          // Scene is not possible if the role is not in the attending roles and the role is mandatory
          $possible = false;
        }
      }
      if($possible){
        // triggering the last check
        array_push($this->scenes, array("SceneID"=> $currentScene["SceneID"], "Name"=> $currentScene["Name"]));
      }
    }
  }
}
?>
