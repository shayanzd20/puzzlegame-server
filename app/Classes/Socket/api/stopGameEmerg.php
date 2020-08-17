<?php
//include __DIR__ . "/../config/gameFunctions.php";

echo "stop game emergency API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

//$Check = new Game();
// we have to get http header token
if (isset($decode->token)
) {
    $token = $decode->token;


    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            // then we need to validate with token user
            if ($token == $ValidToken) {

                $hard = 0;
                // check if room is stopped or not
                $roomDetail = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username'");
                if ($this->DBA->Size($roomDetail)) {
                    $roomDetail1 = $this->DBA->Load($roomDetail);
                    $id = $roomDetail1->RoomID;

                    $room = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                    if ($this->DBA->Size($room)) {
                        $roomORG = $this->DBA->Load($room);
                        $Char = $roomORG->Char;
                        $Type = $roomORG->Type;

                        // if room stopped
                        // must not validate other parameters from empty
                        if ($roomORG->Status == 'STOPPED') {
                            sleep(1);

                            $this->DBA->Shell("UPDATE `user_room` SET `Status`='3' WHERE `RoomID`='$id'");


                            $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 200, "msg" => MSG_200)));


                        } else {

                            $this->DBA->Shell("UPDATE `user_room` SET `Status`='3',`Stop`='1' WHERE `RoomID`='$id'");
                            $this->DBA->Shell("UPDATE `rooms` SET `Status`='STOPPED',`StopDate`=NOW() WHERE `ID`='$id'");


                            // response for token user
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 200, "msg" => MSG_200)));

/*
                            // send words of hanged user
                            $hangedUser=$this->DBA->Shell("SELECT `UserID`,TIMEDIFF(NOW(),`LastActivity`) FROM `user_room`
                                         WHERE TIMEDIFF(NOW(),`LastActivity`)>'00:01:50'
                                         AND  `RoomID`='$id'");
                            $hangedUser1=$this->DBA->Buffer($hangedUser);
                            if(COUNT($hangedUser1)>0){
                                foreach($hangedUser1 as $hangedUser2){
                                    $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'اسم'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'گل'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'کشور')");
                                    $this->DBA->Run("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$hangedUser2->UserID' AND `RoomID`='$id'");
                                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$hangedUser2->UserID'");

                                }
                            }*/



                            // insert word of offline user
                            $offlineSocketUser = $this->DBA->Shell("SELECT `UserID` FROM `user_room` WHERE `RoomID`='$id' AND
                                                           `UserID` NOT IN (SELECT `UserID` FROM `socket_user`)");
                            $offlineSocketUser1 = $this->DBA->Buffer($offlineSocketUser);
                            echo "offline users:\n";
                            var_dump($offlineSocketUser1);
                            echo "count:".count($offlineSocketUser1);
                            if(COUNT($offlineSocketUser1)>0){
                                foreach($offlineSocketUser1 as $offlineSocketUser2){
                                     $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'اسم'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'گل'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $offlineSocketUser2->UserID . "','$id',NULL,0,'کشور')");
                                    $this->DBA->Run("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$offlineSocketUser2->UserID' AND `RoomID`='$id'");
                                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$offlineSocketUser2->UserID'");

                                }
                            }

                            // for each user you must send massage
                            $roomUser = $this->DBA->Shell("SELECT s1.ResourceID,s2.UserID FROM `socket_user` s1
                                                             JOIN `user_room` s2
                                                             ON s1.UserID=s2.UserID
                                                             WHERE s2.RoomID='$id'");

                            $roomUser1 = $this->DBA->Buffer($roomUser);
                            echo "this is size :".count($roomUser1)."\n";
                            if (count($roomUser1) > 0) {
                                foreach ($roomUser1 as $roomUser2) {

                                    if (isset($roomUser2->ResourceID)) {
                                        $this->users[$roomUser2->ResourceID]->send(json_encode(array("command" => "stopGameResp", "status" => 200, "msg" => MSG_200)));
                                    } else {
                                        $ranking = $this->DBA->Shell("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'اسم'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'گل'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'کشور')");
                                        $ranking = $this->DBA->Shell("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `RoomID`='$id'");
                                    }
                                }
                            } else {

                                $offlineUser = $this->DBA->Shell("SELECT `UserID` FROM `user_room` WHERE `RoomID`='$id' AND `UserID` NOT IN (SELECT `UserID` FROM `socket_user`)");
                                $offlineUser1 = $this->DBA->Buffer($offlineUser);
                                if (count($offlineUser1) > 0) {
                                    echo "\nstop offline users\n";
                                    echo "UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$offlineUser2->UserID' AND `RoomID`='$id'\n";
                                    foreach ($offlineUser1 as $offlineUser2) {
                                        $this->DBA->Run("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$offlineUser2->UserID' AND `RoomID`='$id'");
                                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'اسم'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'گل'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'کشور')");
                                    }
                                }
                            }

                        }
                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 110, "msg" => MSG_110)));
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 109, "msg" => MSG_109)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 107, "msg" => MSG_107)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 109, "msg" => MSG_109)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 100, "msg" => $e->getMessage())));
    }

} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "stopGameEmergResp", "status" => 102, "msg" => MSG_102)));
}


echo "\n---------------------------------------------------------------------------------\n";
