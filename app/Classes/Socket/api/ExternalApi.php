<?php
header('Content-Type: application/json');
// delete rooms if rank =1
date_default_timezone_set("Asia/Tehran");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/systemConfig.php";
require_once __DIR__ . "/Terminal.x";

$DBA = new Terminal("balootmo_smooti");
$get = file_get_contents("php://input");
$decode = json_decode($get);
print_r($decode->unknown);
foreach($decode->unknown as $credit){
    $realNumber="0".substr($credit->number,2);
    echo "credit: ".$credit->credit."\n";
    $DBA->Run("INSERT INTO `user_credit_log` (`Number`,`Credit`,`Date`) VALUES ('".$realNumber."','".$credit->credit."','".$credit->lastupdate."')");
    $DBA->Run("UPDATE `users` SET `Credit`=`Credit`+'$credit->credit' WHERE `Number`='".$realNumber."'");
}
//print_r($get);

echo "\n successfull";
?>