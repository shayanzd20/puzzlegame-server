<?php
echo "\n---------------------------------START enter room API:-----------------------------\n";

use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id) AND $decode->id != ""
) {
    
    $token = $decode->token;
    $id = $decode->id;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $UserOrg = $this->DBA->Shell("SELECT `Number` ,`Username`,`Credit`,`Prize`,`Token`,`Avatar`
                                    FROM `users`
                                    WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $userID = $User->Username;
            $number = $User->Number;
            $credit = $User->Credit;
            $userPrize = $User->Prize;
            $picOrig = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?number=" . $decoded->number . "&userPic=" . $User->Avatar;


            // then we need to validate with token user
            if ($token == $ValidToken) {

                // check if room is empty or not
                $emptyRoom = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id' AND `Status`='EMPTY'");
                if ($this->DBA->Size($emptyRoom)) {
                    $emptyRoom1 = $this->DBA->Load($emptyRoom);
                    $roomEntry = $emptyRoom1->EntryPrice;
                    $roomPrize = $emptyRoom1->PrizePrice;

                    // check if user already in room or not
                    $alreadyUser = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$userID' AND `RoomID`='$id'");
                    if ($this->DBA->Size($alreadyUser)) {
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 226, "msg" => MSG_226)));
                    } else {
                        $adminUser = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Admin`='Y' AND `RoomID`='$id'");

                        if($this->DBA->Size($adminUser)){
                            $adminUser1 = $this->DBA->Load($adminUser);
                            $Char = $adminUser1->Char;

                            // summation user credit and user prize is more than room entry
                            if ($credit + $userPrize > $roomEntry) {
                                if (($emptyRoom1->Members + 1) == $emptyRoom1->MaxMembers) {
                                    $this->DBA->Run("UPDATE `rooms` SET Members=Members+1,`Status`='FULL' WHERE `ID`='$id'");
                                    $this->DBA->Run("UPDATE `user_room` SET `Status`='1' WHERE `RoomID`='$id'");

                                    // increase credit of user
                                    if($credit>=$roomEntry){

                                        // when credit of user is more than room entry
                                        $creditDecreased=$roomEntry;
                                        $prizeDecreased=0;
                                    }else{

                                        // when credit of user is less than room entry
                                        $creditDecreased=$credit;
                                        $prizeDecreased=$roomEntry-$credit;
                                    }
                                    $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`-" . $creditDecreased . " ,`Prize`=`Prize`-".$prizeDecreased." WHERE `Username`='$userID'");
                                    echo "\n"."UPDATE `users` SET `Credit`=`Credit`-" . $creditDecreased . " ,`Prize`=`Prize`-".$prizeDecreased." WHERE `Username`='$userID'"."\n";


                                    $this->DBA->Run("DELETE FROM `invite` WHERE `Invited`='$userID' AND `RoomID`='" . $id . "'");
                                    $this->DBA->Run("DELETE FROM `invite` WHERE `RoomID`='" . $id . "'");
                                    $this->DBA->Run("ALTER TABLE `user_room` AUTO_INCREMENT=1");
                                    $this->DBA->Run("INSERT INTO `user_room` (`UserID`,`RoomID`,`Status`,`Char`,`EntryPrice`,`PrizePrice`,`Avatar`) VALUES ('" . $userID . "','" . $id . "','1','" . $Char . "','" . $roomEntry . "','" . $roomPrize . "','" . $User->Avatar . "')");
                                    $usersRoom = $this->DBA->Shell("SELECT * FROM `user_room`  WHERE `RoomID`='$id'");
                                    $usersRoom1 = $this->DBA->Buffer($usersRoom);
                                    $users = array();
                                    sleep(1);
                                    foreach ($usersRoom1 as $usersRoom2) {
                                        $userOriginal = new stdClass();
                                        $userOriginal->avatar = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $usersRoom2->UserID . "&userPic=" . $usersRoom2->Avatar;
                                        if ($usersRoom2->UserID) {
                                            $userOriginal->username = $usersRoom2->UserID;
                                        } else {
                                            $userOriginal->username = NULL;
                                        }
                                        $users[] = $userOriginal;
                                    }
                                    // increase credit of user
                                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='room' WHERE `UserID`='$userID'");
                                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='room' WHERE `Username`='$userID'");

                                    // update log game
                                    $this->DBA->Run("UPDATE `users_game_log` SET `Competitor`='".json_encode($users)."' WHERE `RoomID`='$id'");



                                    echo "member resp:"."\n";
                                    var_dump($users);
                                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","id" => $id, "mem" => $users, "roomstatus" => 1, "status" => 200, "msg" => MSG_200)));


                                } else {
                                    $this->DBA->Run("ALTER TABLE `user_room` AUTO_INCREMENT=1");
                                    $this->DBA->Run("INSERT INTO `user_room` (`UserID`,`RoomID`,`Char`,`EntryPrice`,`PrizePrice`,`Avatar`) VALUES ('" . $userID . "','" . $id . "','" . $Char . "','" . $roomEntry . "','" . $roomPrize . "','" . $User->Avatar . "')");
                                    $this->DBA->Run("UPDATE `rooms` SET Members=Members+1 WHERE `ID`='$id'");
                                    $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`-" . $roomEntry . " WHERE `Username`='$userID'");
                                    echo "\n"."UPDATE `users` SET `Credit`=`Credit`-" . $roomEntry . " WHERE `Username`='$userID'"."\n";
                                    $usersRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id'");
                                    $usersRoom1 = $this->DBA->Buffer($usersRoom);
                                    $users = array();
                                    sleep(1);
                                    foreach ($usersRoom1 as $usersRoom2) {
                                        $userOriginal = new stdClass();
                                        $picture = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?number=" . $number . "&userPic=" . $usersRoom2->Avatar;
                                        $userOriginal->avatar = $picture;
                                        if ($usersRoom2->UserID) {
                                            $userOriginal->username = $usersRoom2->UserID;
                                        } else {
                                            $userOriginal->username = NULL;
                                        }
                                        $users[] = $userOriginal;
                                    }

                                    echo "response:"."\n";
                                    var_dump(array("command"=>"enterroomResp","id" => $id, "mem" => $users, "roomstatus" => 0, "status" => 200, "msg" => MSG_200));

                                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='room' WHERE `UserID`='$userID'");
                                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='room' WHERE `Username`='$userID'");


                                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","id" => $id, "mem" => $users, "roomstatus" => 0, "status" => 200, "msg" => MSG_200)));
                                }

                                // send rooms detail to users where in active rooms
                                $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY'");
                                if ($this->DBA->Size($rooms)) {
                                    $rooms1 = $this->DBA->Buffer($rooms);
                                    $showRooms = array();
                                    foreach ($rooms1 as $rooms2) {
                                        $showRooms[] = array("id" => $rooms2->ID,
                                            "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                            "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                            "type" => $rooms2->Type);
                                    }
                                    $UserShowrooms = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserStatus`='showroom'");
                                    $UserShowrooms1=$this->DBA->Buffer($UserShowrooms);
                                    if(count($UserShowrooms1)>0){
                                        foreach($UserShowrooms1 as $UserShowrooms2){
                                            $this->users[$UserShowrooms2->ResourceID]->send(json_encode(array("command" => "showroomResp", "rooms" => $showRooms)));
                                        }
                                    }
                                }

                                // send users detail to users where in a rooms and waiting for others
                                $roomsUser = $this->DBA->Shell("SELECT s1.UserID,s1.Avatar,s1.Admin,s1.`Status`,s1.RoomID,s2.Credit,s1.LastActivity FROM `user_room` s1
                                                                    JOIN `users` s2
                                                                    ON s1.UserID=s2.Username
                                                                    WHERE s1.`RoomID`='$id'");

                                $roomsUser1=$this->DBA->Buffer($roomsUser);
                                if(count($roomsUser1)>0){
                                    foreach($roomsUser1 as $roomsUser2){

                                        $to_time = strtotime(date("Y-m-d H:i:s"));
                                        $from_time = strtotime($roomsUser2->LastActivity);
                                        $lastActive = round(abs($to_time - $from_time) / 60);

                                        if ($lastActive < 60) {
                                            echo "\n\nless than 60 min\n\n";
                                            $Picture = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $roomsUser2->UserID . "&userPic=" . $roomsUser2->Avatar;
                                            $usersOrg1 = $roomsUser2->UserID;
                                            $credit = $roomsUser2->Credit;

                                            $admin=null;
                                            $adminCredit=null;
                                            $adminPicture=null;

                                            if ($roomsUser2->Admin == 'Y') {
                                                $admin = $usersOrg1;
                                                $adminCredit = $credit;
                                                $adminPicture = $Picture;
                                            }

//                                        $adminOrg = array("username" => $admin, "credit" => $adminCredit, "avatar" => $adminPicture);
//                                        $inviteOrg1 = array();
//                                        $user[] = $roomsUser2->UserID;
                                            $usersforsend[] = array("username" => $usersOrg1, "credit" => $credit, "avatar" => $Picture);

                                            $roomOrg = array("id" => $id, "admin" => $admin, "users" => $usersforsend, "roomStatus" => $roomsUser2->Status);


                                        } else {
                                            //if user was idle

                                            // check if user is admin

                                            // check if room will be empty after deleting
//                                        if ($roomsUser2->Admin == 'Y') {
//                                            $this->DBA->Shell("DELETE FROM `rooms` WHERE `ID`='$roomsUser2->RoomID'");
//
//                                        } else {
//                                            $this->DBA->Shell("UPDATE `rooms` SET `Members`=`Members`-1 WHERE `ID`='$roomsUser2->RoomID'");
//                                        }
//                                        $this->DBA->Shell("DELETE FROM `user_room` WHERE `UserID`='$roomsUser2->UserID' AND `RoomID`='$roomsUser2->RoomID'");
//                                        $this->DBA->Shell("DELETE FROM `user_word` WHERE `User`='$roomsUser2->UserID' AND `RoomID`='$roomsUser2->RoomID'");
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


                            } else {
                                $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 227, "msg" => MSG_227)));
                            }
                        }else{
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 209, "msg" => MSG_209)));

                        }

                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 210, "msg" => MSG_210)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"enterroomResp","status" => 102, "msg" => MSG_102)));
}
echo "\n---------------------------------END enter room API:-----------------------------\n";

