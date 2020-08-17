<?php

// search user in invite page
echo "search user API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if (isset($decode->token) AND $decode->token != ""

) {
    $token = $decode->token;


    try {
        $key = DECODE_KEY;
        $decoded = JWT::decode($token, $key, array('HS256'));


        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {
                $OnlineUser = array();
                $OfflineUser = array();
                $room = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username'");
                if ($this->DBA->Size($room)) {
                    $roomOrg = $this->DBA->Load($room);
                    $roomID = $roomOrg->RoomID;


                    if (isset($decode->username) AND $decode->username != "") {
                        $invitedOrg = $decode->username;

                        // online user
                        $online = $this->DBA->Shell("SELECT `UserID`,`Avatar` FROM `socket_user` s1
                                                     JOIN `users` s2
                                                     ON s1.UserID=s2.Username
                                                     WHERE s1.`UserID`!='" . $username . "' AND
                                                     s1.`UserID` NOT IN (SELECT `Invited` FROM `invite` WHERE `RoomID`='$roomID') AND
                                                     s1.`UserID` LIKE '%$invitedOrg%' LIMIT 5");

//                            echo "SELECT `UserID` FROM `user_room` WHERE `UserID` LIKE '%$invitedOrg%' LIMIT 5";
                        if ($this->DBA->Size($online)) {
                            $online1 = $this->DBA->Buffer($online);
                            foreach ($online1 as $online2) {
                                $OnlineUserObj = new stdClass();
                                $OnlineUserObj->username = $online2->UserID;
                                $OnlineUserObj->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $online2->UserID . "&userPic=" . $online2->Avatar;
                                $OnlineUser[] = $OnlineUserObj;
                            }
                        } else {
                            $OnlineUser = array();
                        }

                        // offline user
                        $offline = $this->DBA->Shell("SELECT `Username`,`Avatar` FROM `users`
                                                      WHERE `Username`!='" . $username . "' AND
                                                      `Username` NOT IN (SELECT `Number` FROM `user_room`) AND
                                                      `Username` NOT IN (SELECT `UserID` FROM `socket_user`) AND
                                                      `Username` NOT IN (SELECT `Invited` FROM `invite` WHERE `RoomID`='$roomID') AND
                                                      `UserName` LIKE '%$invitedOrg%' LIMIT 5");


                        if ($this->DBA->Size($offline)) {
                            $offline1 = $this->DBA->Buffer($offline);
                            foreach ($offline1 as $offline2) {
                                $OfflineUserObj = new stdClass();
                                $OfflineUserObj->username = $offline2->Username;
                                $OfflineUserObj->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $offline2->Username . "&userPic=" . $offline2->Avatar;

                                $OfflineUser[] = $OfflineUserObj;
                            }
                        } else {
                            $OfflineUser = array();
                        }
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","online" => $OnlineUser, "offline" => $OfflineUser, "status" => 200, "msg" => MSG_200)));


                    } else {

                        echo "not mention any user:\n";

                        // online user
                        $online = $this->DBA->Shell("SELECT `UserID`,`Avatar` FROM `socket_user` s1
                                                     JOIN `users` s2
                                                     ON s1.UserID=s2.Username
                                                     WHERE s1.`UserID`!='" . $username . "' AND
                                                     s1.`UserID` NOT IN (SELECT `Invited` FROM `invite` WHERE `RoomID`='$roomID')  ORDER BY RAND() LIMIT 5");

                        if ($this->DBA->Size($online)) {
                            $online1 = $this->DBA->Buffer($online);
                            foreach ($online1 as $online2) {
                                $OnlineUserObj = new stdClass();
                                $OnlineUserObj->username = $online2->UserID;
                                $OnlineUserObj->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $online2->UserID . "&userPic=" . $online2->Avatar;
                                $OnlineUser[] = $OnlineUserObj;
                            }
                        } else {
                            $OnlineUser = array();
                        }

                        // offline user
                        $offline = $this->DBA->Shell("SELECT `Username`,`Avatar` FROM `users` WHERE `Username`!='" . $username . "' AND
                        `Username` NOT IN (SELECT `UserID` FROM `user_room`)  AND
                        `Username` NOT IN (SELECT `Invited` FROM `invite` WHERE `RoomID`='$roomID') AND
                        `Username` NOT IN (SELECT `UserID` FROM `socket_user` WHERE `UserID` IS NOT NULL) ORDER BY RAND() LIMIT 5");
                        $offline1 = $this->DBA->Buffer($offline);
                        if (COUNT($offline1)>0) {
                            foreach ($offline1 as $offline2) {
                                $OfflineUserObj = new stdClass();
                                $OfflineUserObj->username = $offline2->Username;
                                $OfflineUserObj->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $offline2->Username . "&userPic=" . $offline2->Avatar;

                                $OfflineUser[] = $OfflineUserObj;
                            }
                        } else {
                            $OfflineUser = array();
                        }
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","online" => $OnlineUser, "offline" => $OfflineUser, "status" => 200, "msg" => MSG_200)));

                    }


                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","status" => 238, "msg" => MSG_238)));

                }


            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","status" => 100, "msg" => $e->getMessage())));
    }


} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"searchUserResp","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
