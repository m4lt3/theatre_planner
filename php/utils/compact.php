<?php
/**
* A wrapper class for scenes to display in the compact table.
*
* @property int|string $id ID of the Scene
* @property string $title Title of the scene
* @property int $sequence Sequence of the scene
* @property array $parties All relations of the scene containing Name, ID and Relation-ID of role (role + mandatory) and actor (if present)
*/
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
      $this->parties[] = array("FeatureID"=>$scene["FeatureID"], "RoleID"=>$scene["RoleID"], "RoleName"=>$scene["Role"], "Mandatory"=> $scene["Mandatory"],"PlaysID" => $scene["PlaysID"], "UserID"=>$scene["UserID"], "UserName"=>$scene["Name"]);
    } else {
      $this->parties = array();
    }
  }

  /**
  * Returns all associated actors with Name, ID and Relation-ID
  *
  * @return array all associated actors with Name, ID and Relation-ID
  */
  public function getActors(){
    $actors = array();
    foreach ($this->parties as $actor) {
      $actors[] = array("ID"=>$actor["UserID"], "Name"=>$actor["UserName"], "Relation"=>$actor["PlaysID"]);
    }
    return $actors;
  }

  /**
  * Returns all associated roles with Name, ID and Relation-ID
  *
  * @return array all associated roles with Name, ID and Relation-ID
  */
  public function getRoles(){
    $roles = array();
    foreach ($this->parties as $role) {
      $roles[] = array("ID"=>$role["RoleID"], "Name"=>$role["RoleName"], "Relation"=>$role["FeatureID"]);
    }
    return $roles;
  }

  /**
  * Adds Roles and Actors form a given query result to the local array
  *
  * @param array $scene query reults with all required information stored in the local array
  *
  * @see $parties
  */
  public function addRelations($scene){
    $this->parties[] = array("FeatureID"=>$scene["FeatureID"], "RoleID"=>$scene["RoleID"], "RoleName"=>$scene["Role"], "Mandatory"=> $scene["Mandatory"], "PlaysID" => $scene["PlaysID"], "UserID"=>$scene["UserID"], "UserName"=>$scene["Name"]);
  }

  /**
  * Returns the amount of associated roles
  *
  * @return int Amount of associated roles
  */
  public function getRelationCount(){
    return count($this->parties??array());
  }
}
?>
