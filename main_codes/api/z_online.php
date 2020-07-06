<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

include '../../dbcon/dbcon.php';
$ctr=0;
$data = json_decode(file_get_contents("php://input"));

$stmt = $DBcon->prepare("SELECT * from sysfile");
$stmt->execute();		

while($rows=$stmt->FETCH(PDO::FETCH_ASSOC)) {     
    $ctr++;
} 
echo $ctr;
//echo "wala";
?>