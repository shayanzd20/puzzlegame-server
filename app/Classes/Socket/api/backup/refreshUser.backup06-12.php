<?php
echo "refreshUser API:\n\n";
use \Firebase\JWT\JWT;
$key = DECODE_KEY;


// we have to get http header token
if ($decode->token AND $decode->token != ""
) {
    $token = $decode->token;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $UserNumber = $User->Number;
            if ($User->Username) {
                $userID = $User->Username;
            } else {
                $userID = null;
            }

            // then we need to validate with token user
            if ($token == $ValidToken) {

                // update user last activity
                $this->DBA->Run("UPDATE `socket_user` SET `Token`='$token',`UserID`='$userID',`Number`='$UserNumber' WHERE `ResourceID`='$from->resourceId'");
                $this->DBA->Run("UPDATE `user_room` SET `LastActivity`=NOW() WHERE `UserID`='$userID'");

                // check last activity of users to delete them
                $LastActivity = $this->DBA->Shell("SELECT * FROM `user_room` WHERE TIMEDIFF(NOW(),`LastActivity`)>'00:01:00' AND `UserID`!='$userID' ");
                $LastActivity1 = $this->DBA->Buffer($LastActivity);

                if (COUNT($LastActivity1) > 0) {
                    foreach ($LastActivity1 as $LastActivity2) {
//                            $LastActivity3 = $LastActivity2->LastActivity;
//                            $to_time = strtotime(date("Y-m-d H:i:s"));
//                            $from_time = strtotime($LastActivity3);
//                            $lastActive = round(abs($to_time - $from_time) / 60);
//                            if ($lastActive >3) {
                        if ($LastActivity2->Admin == 'Y') {
                            sleep(3);
                            $this->DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$LastActivity2->RoomID' AND `Status`!='RUNNING'");
                        } else {
                            $this->DBA->Run("UPDATE `rooms` SET `Members`=`Members`-1 WHERE `ID`='$LastActivity2->RoomID' AND `Members`>0  AND `Status`!='RUNNING'");
                        }
                        sleep(3);
                        $this->DBA->Run("DELETE FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `Status`!=2");
                        $this->DBA->Run("DELETE FROM `user_word` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `UserID` NOT IN (SELECT `UserID` FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID' AND `Status`=2 )");
//                            }
                        $RoomId = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$LastActivity2->RoomID'");
                        if (!$this->DBA->Size($RoomId)) {
                            sleep(3);
                            $this->DBA->Run("DELETE FROM `user_room` WHERE `UserID`='$LastActivity2->UserID' AND `RoomID`='$LastActivity2->RoomID'");
                            $this->DBA->Run("DELETE FROM `user_word` WHERE `UserID`='$LastActivity2->UserID'");
                        }
                    }
                }

                // remove rooms with rank 1
                $stoppedRoom = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='STOPPED'");
                $stoppedRoom1 = $this->DBA->Buffer($stoppedRoom);
                if (COUNT($stoppedRoom1) > 0) {
                    foreach ($stoppedRoom1 as $stoppedRoom2) {
                        $rankRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='" . $stoppedRoom2->ID . "' AND `Rank`=0");
                        if (!$this->DBA->Size($rankRoom)) {
                            sleep(3);
                            $this->DBA->Run("DELETE FROM `user_room` WHERE `RoomID`='$stoppedRoom2->ID'");
                            $this->DBA->Run("DELETE FROM `user_word` WHERE `RoomID`='$stoppedRoom2->ID'");
                            $this->DBA->Run("DELETE FROM `rooms` WHERE `ID`='$stoppedRoom2->ID'");

                        }
                    }
                }

                // delete user room that there is no room for them
                $deleteRoom = $this->DBA->Shell("SELECT `RoomID`  FROM `user_room` WHERE `RoomID` NOT IN (SELECT ID FROM `rooms`)");
                $deleteRoom1 = $this->DBA->Buffer($deleteRoom);
                if (COUNT($deleteRoom1) > 0) {
                    foreach ($deleteRoom1 as $deleteRoom2) {
                        sleep(2);
                        $this->DBA->Run("DELETE FROM `user_room` WHERE `RoomID`='$deleteRoom2->RoomID'");
                        $this->DBA->Run("DELETE FROM `user_word` WHERE `RoomID`='$deleteRoom2->RoomID'");
                    }
                }

                // remove invalid socket id
                $UserOrg = $this->DBA->Shell("SELECT * FROM `socket_user`");
                $UserOrg1=$this->DBA->Buffer($UserOrg);
                if(count($UserOrg1)>0){
                    foreach($UserOrg1 as $UserOrg2){
                        if(!isset($this->users[$UserOrg2->ResourceID])){
                            echo $UserOrg2->ResourceID." is not set\n";
                            $UserOrg = $this->DBA->Shell("DELETE FROM `socket_user` WHERE `ResourceID`='".$UserOrg2->ResourceID."'");
                        }
                    }
                }


                // get information of token user
                $usersOrg = $this->DBA->Shell("SELECT `Number` ,`Username`,`Credit`,`Token`,`Avatar`
                                             FROM `users`
                                             WHERE `Number`= '$decoded->number'");

                $usersOrg1 = $this->DBA->Load($usersOrg);
                $credit = $usersOrg1->Credit;
                $number = $usersOrg1->Number;
                $picOrig = $usersOrg1->Avatar;
                $picture = "http://smooti.balootmobile.org/api/userAvatar.php?number=" . $decoded->number . "&userPic=" . $picOrig;
                $inviteOrg = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                $inviteOrg1 = $this->DBA->Buffer($inviteOrg);


                $this->users[$from->resourceId]->send(json_encode(array("username" => $userID, "credit" => $credit, "avatar" => $picture, "number" => $number, "notifications" => count($inviteOrg1), "status" => 200, "msg" => MSG_200)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 202, "msg" => MSG_202)));
}
echo "\n---------------------------------------------------------------------------------\n";
