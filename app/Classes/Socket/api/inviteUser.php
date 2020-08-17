<?php
// click on invite button
// token , username
echo "invite user API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;
require_once __DIR__."/../push_notification/class.push.php";

// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->username) AND $decode->username != ""
) {
    $token = $decode->token;
    $invitedOrg = $decode->username;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                echo "\ntoken is ok\n";

                $room = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username'");
                if ($this->DBA->Size($room)) {
                    $roomOrg = $this->DBA->Load($room);
                    var_dump($roomOrg);
                    $RoomID = $roomOrg->RoomID;
                    $EntryPrice = $roomOrg->EntryPrice;
                    $PrizePrice = $roomOrg->PrizePrice;

                    $invited = $this->DBA->Shell("SELECT `Invited` FROM `invite` WHERE `UserName`='$username'");
                    if ($this->DBA->Size($invited)) {
                        $invited1 = $this->DBA->Load($invited)->Invited;
                        if ($invited1 != $invitedOrg) {
                            $invited = $this->DBA->Shell("INSERT INTO  `invite` (`UserName`,`Invited`,`RoomID`)VALUES('" . $username . "','" . $invitedOrg . "','" . $RoomID . "')");

                            // socket response
                            $this->users[$from->resourceId]->send(json_encode(array("status" => 200, "msg" => MSG_200)));

                            // send one signal notif
                            $invitedPushID = $this->DBA->Shell("SELECT `PushID` FROM `users` WHERE `Username`='" . $invitedOrg . "')");
                            if ($this->DBA->Size($invitedPushID)) {

                                print_r("push\n");
                                // get one signal id
                                $invitedPushID1 = $this->DBA->Load($invitedPushID)->PushID;

                                $push_content=$username."\n";
                                $push_content.="شما را به مبارزه دعوت کرده است";
                                $notif=new notification();
                                $notif->sendMessage($invitedPushID1,$push_content,$RoomID);

                            }


                            // send data for invited user in home
                            $socketUser = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserID`='$invitedOrg'");
                            echo "SELECT * FROM `socket_user` WHERE `UserID`='$invitedOrg'"."\n";
                            if($this->DBA->Size($socketUser)){
                                $socketUser1 = $this->DBA->Load($socketUser);
                                $ResourceID = $socketUser1->ResourceID;
                                $UserStatus = $socketUser1->UserStatus;

                                $roomOrg = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$RoomID'");
                                if($this->DBA->Size($roomOrg)){
                                    $roomOrg11=$this->DBA->Load($roomOrg);
                                    $EntryPrice=$roomOrg11->EntryPrice;
                                    $PrizePrice=$roomOrg11->PrizePrice;
                                    $MaxMembers=$roomOrg11->MaxMembers;
                                    $Members=$roomOrg11->Members;

                                    $roomDetail = new stdClass();
                                    $roomDetail->roomid = $RoomID;
                                    $roomDetail->message = "دعوت از اتاق " . $RoomID . "\n";
                                    $roomDetail->message .= " ورودی " . $EntryPrice . " - " . " جایزه " . $PrizePrice . "\n";
                                    $roomDetail->message .= "تعداد اعضا " . $MaxMembers . "/" . $Members;

                                    $messages[] = $roomDetail;

                                    $this->users[$ResourceID]->send(json_encode(array("command"=>"messages","messages"=>$messages,"status" => 200, "msg" => MSG_200)));

                                }else{
                                    echo "there is no room ";
                                }
                            }else{
                                echo "user's socket is closed\n\n";
                            }
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("status" => 217, "msg" => MSG_217)));
                        }
                    } else {

                        $invited = $this->DBA->Shell("INSERT INTO  `invite` (`UserName`,`Invited`,`RoomID`)VALUES('" . $username . "','" . $invitedOrg . "','" . $RoomID . "')");
                        $this->users[$from->resourceId]->send(json_encode(array("status" => 200, "msg" => MSG_200)));

                        // send one signal notif
                        $invitedPushID = $this->DBA->Shell("SELECT `PushID` FROM `users` WHERE `Username`='" . $invitedOrg . "'");
                        if ($this->DBA->Size($invitedPushID)) {

                            print_r("push\n");
                            // get one signal id
                            $invitedPushID1 = $this->DBA->Load($invitedPushID)->PushID;

                            $push_content=$username."\n";
                            $push_content.="شما را به مبارزه دعوت کرده است";
                            $notif=new notification();
                            $notif->sendMessage($invitedPushID1,$push_content,$RoomID);

                        }

                        // send data for invited user in home
                        $socketUser = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserID`='$invitedOrg'");
                        if($this->DBA->Size($socketUser)){
                            $socketUser1 = $this->DBA->Load($socketUser);
                            $ResourceID = $socketUser1->ResourceID;
                            $UserStatus = $socketUser1->UserStatus;

                            $roomOrg = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$RoomID'");
                            if($this->DBA->Size($roomOrg)){
                                $roomOrg11=$this->DBA->Load($roomOrg);
                                $EntryPrice=$roomOrg11->EntryPrice;
                                $PrizePrice=$roomOrg11->PrizePrice;
                                $MaxMembers=$roomOrg11->MaxMembers;
                                $Members=$roomOrg11->Members;

                                $roomDetail = new stdClass();
                                $roomDetail->roomid = $RoomID;
                                $roomDetail->message = "دعوت از اتاق " . $RoomID . "\n";
                                $roomDetail->message .= " ورودی " . $EntryPrice . " - " . " جایزه " . $PrizePrice . "\n";
                                $roomDetail->message .= "تعداد اعضا " . $MaxMembers . "/" . $Members;

                                $messages[] = $roomDetail;

                                $this->users[$ResourceID]->send(json_encode(array("command"=>"messages","messages"=>$messages,"status" => 200, "msg" => MSG_200)));

                            }else{
                                echo "there is no room ";
                            }
                        }else{
                            echo "user's socket is closed\n\n";
                        }
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 237, "msg" => MSG_237)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
