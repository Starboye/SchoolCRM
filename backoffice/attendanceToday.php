<?php
session_start();
if($_SESSION["access"] !=null && $_SESSION["name"]!=null && $_SESSION["id"]!=null && $_SESSION["access"]==0) { 
  
  $username = $_SESSION["name"];
  $access = $_SESSION["access"];
  $id = $_SESSION["id"];

  $servername = "localhost";
  $username_db = "root";
  $password_db = "";
  $dbname = "asimos";
  $row ="";
  $conn = new mysqli($servername, $username_db, $password_db, $dbname);

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }


  $sql = "SELECT * FROM attendance WHERE name='$username' AND id='$id' AND access='$access' AND status='0'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc())

  }




  
$result;

// Return the result as JSON
header('Content-Type: application/json');
echo json_encode(['result' => $result]);

 
$conn->close();

}
?>
