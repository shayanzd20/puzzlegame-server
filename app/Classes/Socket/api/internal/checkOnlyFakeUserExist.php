<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once "../../vendor/firebase/php-jwt/src/JWT.php";
use \Firebase\JWT\JWT;

$key = "smootikey_v_1";

require_once "../../config/Terminal.x";
$DBA = new Terminal("balootmo_smoothy");


$token = $_GET['token'];


$decoded = JWT::decode($token, $key, array('HS256'));
$User = $DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
if ($DBA->Size($User)) {
    $Valid = $DBA->Load($User);
    $ValidToken = $Valid->Token;
    $username = $Valid->Username;
    $userCredit = $Valid->Credit;

    // then we need to validate with token user
    if ($token == $ValidToken) {

        // check item is exist or not
        $charRoom = $DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='" . $username . "'");
        if ($DBA->Size($charRoom)) {
            $charRoom1=$DBA->Load($charRoom)->RoomID;
            $fakeExist = $DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='".$charRoom1."' AND 
                                     `UserID` NOT IN (SELECT `Username` FROM `users_fake`)");
            if ($DBA->Size($fakeExist)) {
                echo json_encode(array("status"=>0));
            }else{
                echo json_encode(array("status"=>1,"id"=>$charRoom1));
            }
        } else {
            echo json_encode(array("status"=>1));
        }
    }
}


