<?php
echo "home page API:\n\n";
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

//                // check if has push notification id or not
//                if(!$UserPushID AND is_null($UserPushID)){
//
//                    // get one signal id
//                    $notif=new notification();
//                    $id=$notif->addADevice($UserDeviceVersion,$UserDeviceOS,$UserDeviceModel);
//                    $this->DBA->Run("UPDATE `users` SET `PushID`='".$id."' WHERE `Username`='$userID'");
//
//                    print_r($id);
//                }

                switch ($UserStatus) {
                    case "room":
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
                                $userOriginal->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $usersRoom2->UserID . "&userPic=" . $usersRoom2->Avatar;
                                if ($usersRoom2->UserID) {
                                    $userOriginal->username = $usersRoom2->UserID;
                                } else {
                                    $userOriginal->username = NULL;
                                }
                                $users[] = $userOriginal;
                            }


                            $this->users[$from->resourceId]->send(json_encode(array("command" => "enterroomResp", "id" => $id, "mem" => $users, "roomstatus" => $status, "status" => 200, "msg" => MSG_200)));
                        } else {
                            echo "refer to home";
                            $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$userID'");

                            $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                            $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                            $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");
                            $this->users[$from->resourceId]->send(json_encode(array(
                                "command" => "homeResp",
                                "username" => $userID,
                                "credit" => $UserCredit,
                                "avatar" => $userPic,
                                "number" => $UserNumber,
                                "notifications" => $this->DBA->Size($UserOrgInvite),
                                "status" => 200,
                                "msg" => MSG_200)));
                        }

                        break;

                    case "start":
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
                            $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                            $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");
                            $this->users[$from->resourceId]->send(json_encode(array(
                                "command" => "homeResp",
                                "username" => $userID,
                                "credit" => $UserCredit,
                                "avatar" => $userPic,
                                "number" => $UserNumber,
                                "notifications" => $this->DBA->Size($UserOrgInvite),
                                "status" => 200,
                                "msg" => MSG_200)));
                        }

                        break;


                    default:
                        // default code
                        $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                        $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;

                        $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$userID'");
                        $this->users[$from->resourceId]->send(json_encode(array(
                            "command" => "homeResp",
                            "username" => $userID,
                            "credit" => $UserCredit,
                            "avatar" => $userPic,
                            "number" => $UserNumber,
                            "notifications" => $this->DBA->Size($UserOrgInvite),
                            "status" => 200,
                            "msg" => MSG_200)));

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
echo "\n---------------------------------------------------------------------------------\n";
