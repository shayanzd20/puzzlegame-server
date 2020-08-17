<?php
echo "refresh room API:\n\n";
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
            $userID = $User->Username;
            $userCredit = $User->Credit;
            $userAvatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userID . "&userPic=" . $User->Avatar;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                $this->DBA->Run("UPDATE `user_room` SET `LastActivity`=NOW() WHERE `UserID`='$userID'");
                $RoomIDOrg = $this->DBA->Shell("SELECT `RoomID` FROM `user_room` WHERE `UserID`='$userID'");
                if ($this->DBA->Size($RoomIDOrg)) {
                    $idRoom = $this->DBA->Load($RoomIDOrg)->RoomID;
                    $usersRoom = $this->DBA->Shell("SELECT s1.`UserID`,s1.`Score`,s1.`Admin`,s1.`LastActivity`,s1.`RoomID`,s1.`Status`,s1.`Avatar`,s2.`Credit`,s2.`Number`,s3.`Members`,s3.`Status` AS Stop FROM `user_room` s1
                                                    JOIN `users` s2
                                                    JOIN `rooms` s3
                                                    ON s1.`UserID`=s2.`Username` AND s3.ID=s1.RoomID
                                                    WHERE s1.`RoomID`='$idRoom'");
                    $usersRoom1 = $this->DBA->Buffer($usersRoom);

                    $user = array();
                    $users = array();
                    $admin = null;
                    $adminCredit = null;
                    $adminPicture = null;
                    $roomOrg = NULL;
                    foreach ($usersRoom1 as $usersRoom2) {

                        $to_time = strtotime(date("Y-m-d H:i:s"));
                        $from_time = strtotime($usersRoom2->LastActivity);
                        $lastActive = round(abs($to_time - $from_time) / 60);

                        if ($lastActive < 3) {
                            $Picture = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $usersRoom2->UserID . "&userPic=" . $usersRoom2->Avatar;
                            $usersOrg1 = $usersRoom2->UserID;
                            $credit = $usersRoom2->Credit;
                            if ($usersRoom2->Admin == 'Y') {
                                $admin = $usersOrg1;
                                $adminCredit = $credit;
                                $adminPicture = $Picture;
                            }

                            $adminOrg = array("username" => $admin, "credit" => $adminCredit, "avatar" => $adminPicture);
                            $inviteOrg1 = array();
                            $user[] = $usersRoom2->UserID;
                            $users[] = array("username" => $usersOrg1, "credit" => $credit, "avatar" => $Picture);

                            // room status = 0 => empty and not running
                            // room status = 1 => full or running
                            // room status = 2 => running
                            // room status = 3 => stopped

                            switch ($usersRoom2->Stop) {
                                case "EMPTY":
                                    $usersRoom2->Stop = 0;
                                    break;
                                case "FULL":
                                    $usersRoom2->Stop = 1;
                                    break;
                                case "RUNNING":
                                    $usersRoom2->Stop = 2;
                                    break;
                                case "STOPPED":
                                    $usersRoom2->Stop = 3;
                                    break;
                                default:

                            }
                            $roomOrg = array("id" => $idRoom, "admin" => $admin, "users" => $users, "roomStatus" => $usersRoom2->Stop);


                        } else {
                            //if user was idle

                            // check if user is admin

                            // check if room will be empty after deleting
                            if ($usersRoom2->Admin == 'Y') {
                                $this->DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$usersRoom2->RoomID'");

                            } else {
                                $this->DBA->Shell("UPDATE `rooms` SET `Members`=`Members`-1 WHERE `ID`='$usersRoom2->RoomID'");
                            }
                            $this->DBA->Shell("DELETE FROM `user_room` WHERE `UserID`='$usersRoom2->UserID' AND `RoomID`='$usersRoom2->RoomID'");
                            $this->DBA->Shell("DELETE FROM `user_word` WHERE `User`='$usersRoom2->UserID' AND `RoomID`='$usersRoom2->RoomID'");
                        }
                    }

                    // username => username of token
                    // credit => credit of token
                    // avatar => avatar of token
                    // notification => notification of token

//                            echo "test1";
                    $this->users[$from->resourceId]->send(json_encode(array("username" => $userID, "credit" => $userCredit, "avatar" => $userAvatar, "room" => $roomOrg, "status" => 200, "msg" => MSG_200)));
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 222, "msg" => MSG_222)));
                }
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
