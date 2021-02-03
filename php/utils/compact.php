<?php
class compactScene{
  public $id;
  public $title;
  public $sequence;
  // consists of: FeaturesID, RoleID, RoleName, PlaysID, UserID, UserName
  public $parties;

  function __construct($scene){
    if(!empty($scene)){
      $this->id = $scene["SceneID"];
      $this->title = $scene["Scene"];
      $this->sequence = $scene["Sequence"];
      $this->parties = array();
      $this->parties[] = array("FeatureID"=>$scene["FeatureID"], "RoleID"=>$scene["RoleID"], "RoleName"=>$scene["Role"], "PlaysID" => $scene["PlaysID"], "UserID"=>$scene["UserID"], "UserName"=>$scene["Name"]);
    } else {
      $this->parties = array();
    }
  }

  public function getActors(){
    $actors = array();
    foreach ($this->parties as $actor) {
      $actors[] = array("ID"=>$actor["UserID"], "Name"=>$actor["UserName"], "Relation"=>$actor["PlaysID"]);
    }
    return $actors;
  }

  public function getRoles(){
    $roles = array();
    foreach ($this->parties as $role) {
      $roles[] = array("ID"=>$role["RoleID"], "Name"=>$role["RoleName"], "Relation"=>$role["FeatureID"]);
    }
    return $roles;
  }

  public function addRelations($scene){
    $this->parties[] = array("FeatureID"=>$scene["FeatureID"], "RoleID"=>$scene["RoleID"], "RoleName"=>$scene["Role"], "PlaysID" => $scene["PlaysID"], "UserID"=>$scene["UserID"], "UserName"=>$scene["Name"]);
  }

  public function getRelationCount(){
    return count($this->parties??array());
  }
}
?>
