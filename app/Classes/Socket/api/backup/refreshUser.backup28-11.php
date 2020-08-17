<?php
date_default_timezone_set("Asia/Tehran");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../config/systemConfig.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/SignatureInvalidException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/ExpiredException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/BeforeValidException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/JWT.php";
require_once __DIR__ . "/../config/Terminal.x";
use \Firebase\JWT\JWT;


$DBA = new Terminal("balootmo_smooti");
$get = file_get_contents("php://input");
$decode = json_decode($get);

if (is_object($decode) && (count(get_object_vars($decode)) > 0)) {

// we have to get http header token
    if ($decode->token AND $decode->token != ""
//        $decode->id AND $decode->id != ""
    ) {
        $token = $decode->token;
//        $id = $decode->id;

        try {
            $key=DECODE_KEY;
            $decoded = JWT::decode($token, $key, array('HS256'));
            $UserOrg = $DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
            if ($DBA->Size($UserOrg)) {
                $User = $DBA->Load($UserOrg);
                $ValidToken = $User->Token;
                if ($User->Username) {
                    $userID = $User->Username;
                } else {
                    $userID = null;
                }

                // then we need to validate with token user
                if ($token == $ValidToken) {

                    // update user last activity
                    $DBA->Run("UPDATE `user_room` SET `LastActivity`=NOW() WHERE `UserID`='$userID'");

                    // check last activity of users to delete them
                    $LastActivity = $DBA->Shell("SELECT * FROM `user_room` WHERE TIMEDIFF(NOW(),`LastActivity`)>'00:01:00' AND `UserID`!='$userID' ");
                    $LastActivity1 = $DBA->Buffer($LastActivity);

                    if (COUNT($LastActivity1) > 0) {
                        foreach ($LastActivity1 as $LastActivity2) {
//                            $LastActivity3 = $LastActivity2->LastActivity;
//                            $to_time = strtotime(date("Y-m-d H:i:s"));
//                            $from_time = strtotime($LastActivity3);
//                            $lastActive = round(abs($to_time - $from_time) / 60);
//                            if ($lastActive >3) {
                            if ($LastActivity2->Admin == 'Y') {
                                sleep(3);
                                $DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$LastActivity2->RoomID' AND `Status`!='RUNNING'");
                            } else {
                                $DBA->Run("UPDATE `rooms` SET `Members`=`Members`-1 WHERE `ID`='$LastActivity2->RoomID' AND `Members`>0  AND `Status`!='RUNNING'");
                            }
                            sleep(3);
                            $DBA->Run("DELETE FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `Status`!=2");
                            $DBA->Run("DELETE FROM `user_word` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `UserID` NOT IN (SELECT `UserID` FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `Status`=2 )");
//                            }
                            $RoomId = $DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$LastActivity2->RoomID'");
                            if (!$DBA->Size($RoomId)) {
                                sleep(3);
                                $DBA->Run("DELETE FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID'");
                                $DBA->Run("DELETE FROM `user_word` WHERE `UserID`='$LastActivity2->UserID'");
                            }
                        }
                    }

                    // remove rooms with rank 1
                    $stoppedRoom = $DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='STOPPED'");
                    $stoppedRoom1 = $DBA->Buffer($stoppedRoom);
                    if(COUNT($stoppedRoom1)>0){
                        foreach($stoppedRoom1 as $stoppedRoom2){
                            $rankRoom = $DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='".$stoppedRoom2->ID."' AND `Rank`=0");
                            if(!$DBA->Size($rankRoom)){
                                sleep(3);
                                $DBA->Run("DELETE FROM `user_room` WHERE `RoomID`='$stoppedRoom2->ID'");
                                $DBA->Run("DELETE FROM `user_word` WHERE `RoomID`='$stoppedRoom2->ID'");
                                $DBA->Run("DELETE FROM `rooms` WHERE `ID`='$stoppedRoom2->ID'");

                            }
                        }
                    }

                    // delete user room that there is no room for them
                    $deleteRoom = $DBA->Shell("SELECT `RoomID`  FROM `user_room` WHERE `RoomID` NOT IN (SELECT ID FROM `rooms`)");
                    $deleteRoom1 = $DBA->Buffer($deleteRoom);
                    if(COUNT($deleteRoom1)>0){
                        foreach($deleteRoom1 as $deleteRoom2){
                            sleep(2);
                            $DBA->Run("DELETE FROM `user_room` WHERE `RoomID`='$deleteRoom2->RoomID'");
                            $DBA->Run("DELETE FROM `user_word` WHERE `RoomID`='$deleteRoom2->RoomID'");
                        }
                    }

                    // get information of token user
                    $usersOrg = $DBA->Shell("SELECT `Number` ,`Username`,`Credit`,`Token`,`Avatar`
                                             FROM `users`
                                             WHERE `Number`= '$decoded'");

                    $usersOrg1 = $DBA->Load($usersOrg);
                    $credit = $usersOrg1->Credit;
                    $number = $usersOrg1->Number;
                    $picOrig = $usersOrg1->Avatar;
                    $picture = "http://smooti.balootmobile.org/api/userAvatar.php?number=" . $decoded . "&userPic=" . $picOrig;
                    $inviteOrg = $DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                    $inviteOrg1 = $DBA->Buffer($inviteOrg);


                    echo json_encode(array("username" => $userID, "credit" => $credit, "avatar" => $picture, "number" => $number, "notifications" => count($inviteOrg1), "status" => 200, "msg" => MSG_200));
                }
            } else {
                echo json_encode(array("status" => 209, "msg" => MSG_209));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 500, "msg" => $e->getMessage()));
        }
    } else {
        echo json_encode(array("status" => 202, "msg" => MSG_202));
    }
} else {
    echo json_encode(array("status" => 202, "msg" => MSG_202));
}