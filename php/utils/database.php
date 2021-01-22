<?php
class DBHandler {
  private $servername = "localhost";
  private $username = "planner";
  private $password = "dC5*nn%phW!LuGiZ";
  private $dbname = "theatre_planner";
  private $conn;

  function __construct(){
    $this->conn = $this->connectDB();
  }

  function __destruct(){
    $this->conn->close();
  }

  private function connectDB(){
    $temp_conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

    if ($temp_conn->connect_error) {
      die("Connection failed: " . $temp_conn->connect_error);
    } else {
      return $temp_conn;
    }
  }

  public function baseQuery($sql){
    $result = $this->conn->query($sql);
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

      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $resultset[] = $row;
          }
          return $resultset;
      }
    }
  }

  public function update($query, $param_type, $param_value_array) {
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
}
?>
