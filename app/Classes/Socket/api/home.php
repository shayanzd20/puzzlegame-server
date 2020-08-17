<?php
echo "\n------------------------------Start home page API-------------------------------\n";
/*****************************************************************************************/
/*input:
token:token



output:
//////////////////////////////////Modified 2017-05-17//////////////////////////////////////

call messages count, user data(avatar,username), active rooms, credits
*/
/*****************************************************************************************/

use \Firebase\JWT\JWT;
$key = DECODE_KEY;
require_once __DIR__."/../push_notification/class.push.php";

//var_dump($decode);
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
            $UserNumber = $User->Number;
            $UserCredit = $User->Credit;
            $UserPrize = $User->Prize;
            $UserAvatar = $User->Avatar;
            $UserStatus = $User->UserStatus;
            $UserPushID = $User->PushID;
            $UserDeviceModel = $User->DeviceModel;
            $UserDeviceOS = $User->DeviceOS;
            $UserDeviceVersion = $User->DeviceVersion;
            if ($User->Username) {
                $userID = $User->Username;
            } else {
                $userID = null;
            }

            $this->DBA->Shell("UPDATE `socket_user` SET `Number`='$UserNumber',`UserID`='$userID',`UserStatus`='$UserStatus' WHERE `ResourceID`='" . $from->resourceId . "'");

            // then we need to validate with token user
            if ($token == $ValidToken) {


                switch ($UserStatus) {

                    case "room":

                        // call messages
                        $messages = array();
                        $RoomID = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$userID'");
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

                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","messages" => $messages)));
                        }

                        // call user info
                        $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userInfo",
                            "username" => $userID,
                            "avatar" => $userPic,
                            "number" => $UserNumber)));

                        // call user credit
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userCredit",
                            "credit_game" => intval($UserCredit)+intval($UserPrize),
                            "credit_shopping" => intval($UserPrize))));

                        // call active rooms
                        $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY' AND `Type`='PUBLIC' LIMIT 10");
                        if ($this->DBA->Size($rooms)) {
                            $rooms1 = $this->DBA->Buffer($rooms);
                            $showRooms = array();
                            foreach ($rooms1 as $rooms2) {
                                $showRooms[] = array("id" => $rooms2->ID,
                                    "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                    "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                    "type" => $rooms2->Type);
                            }
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","offset" => 0, "rooms" => $showRooms)));
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp", "rooms" => array())));
                        }

                        // room info must send to user
                        $RoomDetail = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$userID'");
                        echo "SELECT * FROM `user_room` WHERE `UserID`='$userID'" . "\n";
                        if ($this->DBA->Size($RoomDetail)) {
                            $RoomDetail1 = $this->DBA->Load($RoomDetail);
                            $id = $RoomDetail1->RoomID;
                            $status = $RoomDetail1->Status;
                            $usersRoom = $this->DBA->Shell("SELECT * FROM `user_room`  WHERE `RoomID`='$id'");
                            $usersRoom1 = $this->DBA->Buffer($usersRoom);
                            $users = array();
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


                            $this->users[$from->resourceId]->send(json_encode(array("command" => "enterroomResp", "id" => $id, "mem" => $users, "roomstatus" => $status, "status" => 200, "msg" => MSG_200)));
                        } else {
                            echo "\n"."refer to home"."\n\n";
                            $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$userID'");
                            $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                            $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                            $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");

                            $this->users[$from->resourceId]->send(json_encode(array(
                                "command" => "homeResp",
                                "status" => 200,
                                "msg" => MSG_200
                            )));

                        }

                        break;

                    case "start":

                        // call messages
                        $messages = array();
                        $RoomID = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$userID'");
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

                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","messages" => $messages)));
                        }

                        // call user info
                        $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userInfo",
                            "username" => $userID,
                            "avatar" => $userPic,
                            "number" => $UserNumber)));

                        // call user credit
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userCredit",
                            "credit_game" => intval($UserCredit)+intval($UserPrize),
                            "credit_shopping" => intval($UserPrize))));

                        // call active rooms
                        $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY' AND `Type`='PUBLIC' LIMIT 10");
                        if ($this->DBA->Size($rooms)) {
                            $rooms1 = $this->DBA->Buffer($rooms);
                            $showRooms = array();
                            foreach ($rooms1 as $rooms2) {
                                $showRooms[] = array("id" => $rooms2->ID,
                                    "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                    "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                    "type" => $rooms2->Type);
                            }
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","offset" => 0, "rooms" => $showRooms)));
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp", "rooms" => array())));
                        }

                        $RoomDetail = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$userID'");
                        if ($this->DBA->Size($RoomDetail)) {
                            $RoomDetail1 = $this->DBA->Load($RoomDetail);
                            $id = $RoomDetail1->RoomID;
                            $status = $RoomDetail1->Status;
                            $roomChar = $RoomDetail1->Char;
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "startGameResp", "id" => $id, "char" => $roomChar, "status" => 200, "msg" => MSG_200)));
                        } else {
                            echo "refer to home";
                            $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$userID'");
                            $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                            $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                            $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");
                            $this->users[$from->resourceId]->send(json_encode(array(
                                "command" => "homeResp",
                                "status" => 200,
                                "msg" => MSG_200
                            )));
                        }

                        break;

                    default:
                        // default code
                        $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                        $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                        $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "homeResp",
                            "status" => 200,
                            "msg" => MSG_200)));

                        // call messages
                        $messages = array();
                        $RoomID = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$userID'");
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

                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","messages" => $messages)));
                        }

                        // call user info
                        $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userInfo",
                            "username" => $userID,
                            "avatar" => $userPic,
                            "number" => $UserNumber)));

                        // call user credit
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "userCredit",
                            "credit_game" => intval($UserCredit)+intval($UserPrize),
                            "credit_shopping" => intval($UserPrize))));

                        // call active rooms
                        $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY' AND `Type`='PUBLIC' LIMIT 10");
                        if ($this->DBA->Size($rooms)) {
                            $rooms1 = $this->DBA->Buffer($rooms);
                            $showRooms = array();
                            foreach ($rooms1 as $rooms2) {
                                $showRooms[] = array("id" => $rooms2->ID,
                                    "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                    "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                    "type" => $rooms2->Type);
                            }
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","offset" => 0, "rooms" => $showRooms)));
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp", "rooms" => array())));
                        }

                }
//                }


            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "homeResp", "status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "homeResp", "status" => 509, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "homeResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "homeResp", "status" => 502, "msg" => MSG_502)));
}
echo "\n------------------------------END home page API-------------------------------\n";
