<?php
$servername = "localhost";
$username = "i6_vxwnhjupfc";
$password = "YDR9p5QDZbp3B@!";
$dbname = "i6_vxwnhjupfc";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
if(!empty($_REQUEST['email'])){
$sql = "SELECT * FROM vtiger_contactdetails WHERE email='".$_REQUEST['email']."'";
$result = $conn->query($sql);
echo $result->num_rows;
}

if(!empty($_REQUEST['phone'])){
$sql = "SELECT * FROM vtiger_contactdetails WHERE phone='".$_REQUEST['phone']."'";
$result = $conn->query($sql);
echo $result->num_rows;
}
$conn->close();
?>