<?php
class DBHandler {
  private $config;
  private $conn;

  function __construct(){
    $this->conn = $this->connectDB();
  }

  function __destruct(){
    $this->conn->close();
  }

  private function connectDB(){
    global $config;
    $temp_conn = new mysqli($config->db_server, $config->db_user, $config->db_pwd, $config->db_name);

    if ($temp_conn->connect_error) {
      die("Connection failed: " . $temp_conn->connect_error);
    } else {
      return $temp_conn;
    }
  }

  public function baseQuery($sql){
    $result = $this->conn->query($sql);
    $resultset = array();
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        $resultset[] = $row;
      }
      return $resultset;
    }
  }

  public function prepareQuery($sql, $param_type, $param_values){
    if($stmt = $this->conn->prepare($sql)){
      $this->bindQueryParams($stmt, $param_type, $param_values);
      $stmt->execute();
      $result = $stmt->get_result();
      $resultset = array();
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $resultset[] = $row;
          }
          return $resultset;
      }
    }
  }

  public function update($query, $param_type, $param_value_array) {
    if(empty($param_type) && empty($param_value_array)){
      return $this->conn->query($query);
    }
    $sql = $this->conn->prepare($query);
    $this->bindQueryParams($sql, $param_type, $param_value_array);
    return $sql->execute();
  }

  private function bindQueryParams($sql, $param_type, $param_value_array) {
  $param_value_reference[] = & $param_type;
  for($i=0; $i<count($param_value_array); $i++) {
    $param_value_reference[] = & $param_value_array[$i];
  }
  call_user_func_array(array(
      $sql,
      'bind_param'
    ), $param_value_reference);
  }

  public function getLastID(){
    return $this->conn->insert_id;
  }
}
?>
