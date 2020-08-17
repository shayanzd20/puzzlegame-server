<?php
echo "\n---------------------------------START exit room API:-----------------------------\n";

// when click on exit room
// credit most be back

use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id) AND $decode->id != ""
) {
    $token = $decode->token;
    $id = $decode->id;

    $key = DECODE_KEY;
    $decoded = JWT::decode($token, $key, array('HS256'));

    $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
    if ($this->DBA->Size($User)) {
        $Valid = $this->DBA->Load($User);
        $ValidToken = $Valid->Token;
        $username = $Valid->Username;

        // then we need to validate with token user
        if ($token == $ValidToken) {

            $roomID = $this->DBA->Shell("SELECT s1.`UserID`,s1.`Admin`,s2.`Members`,s2.`EntryPrice` FROM `user_room` s1
                                       JOIN `rooms` s2
                                       ON s1.RoomID=s2.ID
                                       WHERE s1.`RoomID`='$id' AND s1.`UserID`='$username'");
            if ($this->DBA->Size($roomID)) {
                $roomDetail = $this->DBA->Load($roomID);
                $admin = $roomDetail->Admin;
                $EntryPrice = $roomDetail->EntryPrice;
                $UserID = $roomDetail->UserID;
                if ($admin == 'Y') {


                    $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`+" . $EntryPrice . " WHERE `Username`='$UserID'");
                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$UserID'");
                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$UserID'");

                    $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 200, "msg" => MSG_200)));

                    // send other user to exit the room because there is no admin
                    // send other user new data of users
                    $roomsUser = $this->DBA->Shell("SELECT s1.UserID,s1.Avatar,s1.Admin,s1.`Status`,s1.RoomID,s2.Credit,s1.LastActivity FROM `user_room` s1
                                                                    JOIN `users` s2
                                                                    ON s1.UserID=s2.Username
                                                                    WHERE s1.`RoomID`='$id'");

                    $roomsUser1 = $this->DBA->Buffer($roomsUser);

                    if (count($roomsUser1) > 0) {
                        foreach ($roomsUser1 as $roomsUser2) {
                            $UserShowrooms = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserID`='$roomsUser2->UserID'");
                            if ($this->DBA->Size($UserShowrooms)) {
                                $UserShowrooms2 = $this->DBA->Load($UserShowrooms);
                                $this->users[$UserShowrooms2->ResourceID]->send(json_encode(array("command" => "exitRoomResp", "status" => 200, "msg" => MSG_200)));
                                $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`+" . $EntryPrice . " WHERE `Username`='$roomsUser2->UserID' AND `Username`!='$UserID'");

                            }
                        }

                    }
                    $this->DBA->Run("DELETE FROM `user_room` WHERE `RoomID`='$id'");
                    $this->DBA->Run("DELETE FROM `rooms` WHERE `ID`='$id'");
                    /////////////////////////////////////

                    // delete logged game in logging game table
                    $this->DBA->Run("DELETE FROM `users_game_log` WHERE `RoomID`='$id'");


                    // send messages API to users where in messages page
                    $UserInvited = $this->DBA->Shell("SELECT `invite`.`Invited` AS Invited ,
                                                             `socket_user`.`ResourceID` AS ResourceID,
                                                             `socket_user`.`Number` AS Number,
                                                             `users`.`Credit` AS Credit,
                                                             `users`.`Avatar` AS Avatar
                                                             FROM `socket_user`,`invite`,`users`
                                                             WHERE `invite`.`Invited`=`socket_user`.`UserID`
                                                             AND `users`.Username =`socket_user`.`UserID`
                                                             AND `invite`.`RoomID`='$id'");
                    $UserInvited1 = $this->DBA->Buffer($UserInvited);
                    $this->DBA->Run("DELETE FROM `invite` WHERE `RoomID`='$id'");

                    foreach($UserInvited1 as $UserInvited2){

                        $RoomID = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$UserInvited2->Invited'");
                        if ($this->DBA->Size($RoomID) > 0) {
                            $RoomID1 = $this->DBA->Buffer($RoomID);
                            foreach ($RoomID1 as $RoomID2) {
                                $room = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$RoomID2->RoomID'");
                                if ($this->DBA->Size($room)) {
                                    $roomOriginal = $this->DBA->Load($room);
                                    $roomDetail = new stdClass();
                                    $roomDetail->roomid = $roomOriginal->ID;
                                    $roomDetail->message = "دعوت از اتاق " . $roomOriginal->ID . "\n";
                                    $roomDetail->message .= " ورودی " . $roomOriginal->EntryPrice . " - " . " جایزه " . $roomOriginal->PrizePrice . "\n";
                                    $roomDetail->message .= "تعداد اعضا " . $roomOriginal->MaxMembers . "/" . $roomOriginal->Members;
                                    $messages[] = $roomDetail;
                                }

                            }
                            $this->users[$UserInvited2->ResourceID]->send(json_encode(array("command"=>"messages","messages" => $messages, "status" => 200, "msg" => MSG_200)));

                            // call home API
                            $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $UserInvited2->Invited . "&userPic=" . $UserInvited2->Avatar;

                            $this->users[$UserInvited2->ResourceID]->send(json_encode(array(
                                "command" => "homeResp",
                                "username" => $UserInvited2->Invited,
                                "credit" => $UserInvited2->Credit,
                                "avatar" => $userPic,
                                "number" => $UserInvited2->Number,
                                "notifications" => $this->DBA->Size($RoomID),
                                "status" => 200,
                                "msg" => MSG_200)));
                        }else {
                            $this->users[$UserInvited2->ResourceID]->send(json_encode(array("command"=>"messages","messages" => array(), "status" => 200, "msg" => MSG_200)));

                            // call home API
                            $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $UserInvited2->Invited . "&userPic=" . $UserInvited2->Avatar;

                            $this->users[$UserInvited2->ResourceID]->send(json_encode(array(
                                "command" => "homeResp",
                                "username" => $UserInvited2->Invited,
                                "credit" => $UserInvited2->Credit,
                                "avatar" => $userPic,
                                "number" => $UserInvited2->Number,
                                "notifications" => $this->DBA->Size($RoomID),
                                "status" => 200,
                                "msg" => MSG_200)));
                        }
                    }
                    // send home API to users after that delete



                } else {
                    $this->DBA->Run("DELETE FROM `user_room` WHERE `UserID`='$username'");
                    $this->DBA->Run("UPDATE `rooms` SET `Members`=Members-1,`Status`='EMPTY' WHERE `ID`='$id'");
                    $this->DBA->Run("UPDATE `user_room` SET `Status`=0 WHERE `RoomID`='$id'");
                    $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`+" . $EntryPrice . " WHERE `Username`='$UserID'");
                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$UserID'");
                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$UserID'");

                    echo "UPDATE `users` SET `Credit`=`Credit`+" . $EntryPrice . " WHERE `Username`='$UserID'" . "\n";

                    // send other user new data of users
                    $roomsUser = $this->DBA->Shell("SELECT s1.UserID,s1.Avatar,s1.Admin,s1.`Status`,s1.RoomID,s2.Credit,s1.LastActivity FROM `user_room` s1
                                                                    JOIN `users` s2
                                                                    ON s1.UserID=s2.Username
                                                                    WHERE s1.`RoomID`='$id'");
                    $roomsUser1 = $this->DBA->Buffer($roomsUser);
                    if (count($roomsUser1) > 0) {
                        foreach ($roomsUser1 as $roomsUser2) {


                            $to_time = strtotime(date("Y-m-d H:i:s"));
                            $from_time = strtotime($roomsUser2->LastActivity);
                            $lastActive = round(abs($to_time - $from_time) / 60);

                            if ($lastActive < 30) {
                                $Picture = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $roomsUser2->UserID . "&userPic=" . $roomsUser2->Avatar;
                                $usersOrg1 = $roomsUser2->UserID;
                                $credit = $roomsUser2->Credit;
                                if ($roomsUser2->Admin == 'Y') {
                                    $admin = $usersOrg1;
                                    $adminCredit = $credit;
                                    $adminPicture = $Picture;
                                }

                                $user[] = $roomsUser2->UserID;
                                $usersforsend[] = array("username" => $usersOrg1, "credit" => $credit, "avatar" => $Picture);

                                $roomOrg = array("id" => $id, "admin" => $admin, "users" => $usersforsend, "roomStatus" => $roomsUser2->Status);


                            } else {
                                //if user was idle

                                // check if user is admin

                                // check if room will be empty after deleting
//                                if ($roomsUser2->Admin == 'Y') {
//                                    $this->DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$roomsUser2->RoomID'");
//
//                                } else {
//                                    $this->DBA->Shell("UPDATE `rooms` SET `Members`=`Members`-1 WHERE `ID`='$roomsUser2->RoomID'");
//                                }
//                                $this->DBA->Shell("DELETE FROM `user_room` WHERE `UserID`='$roomsUser2->UserID' AND `RoomID`='$roomsUser2->RoomID'");
//                                $this->DBA->Shell("DELETE FROM `user_word` WHERE `User`='$roomsUser2->UserID' AND `RoomID`='$roomsUser2->RoomID'");
                            }

                        }

                        // send users to all competitor who are waiting for user
                        $UserShowrooms = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserID` IN (SELECT `UserID` FROM `user_room` WHERE `RoomID`='$id')");
                        $UserShowrooms1=$this->DBA->Buffer($UserShowrooms);
                        if(COUNT($UserShowrooms1)>0){
                            foreach($UserShowrooms1 as $UserShowrooms2){
                                $this->users[$UserShowrooms2->ResourceID]->send(json_encode(array("command" => "userroomResp", "id" => $id, "admin" => $admin, "users" => $usersforsend, "roomStatus" => $roomsUser2->Status)));
                            }
                        }
                    }

                    /////////////////////////////////////

                    $room = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                    if ($this->DBA->Size($room)) {
                        $room1 = $this->DBA->Load($room);
                        if ($room1->Members == 0) {
                            $this->DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$id'");
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 200, "msg" => MSG_200)));
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 200, "msg" => MSG_200)));
                        }
                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 214, "msg" => MSG_214)));
                    }
                }


            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 214, "msg" => MSG_214)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 203, "msg" => MSG_203)));
        }
    } else {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 209, "msg" => MSG_209)));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "exitRoomResp", "status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------END exit room API:-----------------------------\n";
