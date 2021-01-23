<?php
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

  function detectScenes($allScenes){
    $currentScene = $allScenes[0];
    $possible = true;
    foreach ($allScenes as $scene) {
      if($currentScene["SceneID"] != $scene["SceneID"]){
        if($possible){
          array_push($this->scenes, array("SceneID"=> $currentScene["SceneID"], "Name"=> $currentScene["Name"]));
        }
        $possible = true;
        $currentScene = $scene;
      }
      if(!in_array($scene["RoleID"], $this->roles) && $scene["Mandatory"]){
        $possible = false;
      }
    }
    if($possible){
      array_push($this->scenes, array("SceneID"=> $currentScene["SceneID"], "Name"=> $currentScene["Name"]));
    }
  }
}
?>
