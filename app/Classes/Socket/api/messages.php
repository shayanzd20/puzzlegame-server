<?php
echo "messages API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if (isset($decode->token)
) {
    $token = $decode->token;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                    $messages = array();
                    $RoomID = $this->DBA->Shell("UPDATE `socket_user` SET `UserStatus`='messages' WHERE `UserID`='$username'");
                    $RoomID = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$username'");
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

//                                $roomDetail->members=$roomOriginal->Members;
//                                $roomDetail->maxmembers=$roomOriginal->MaxMembers;
//                                $roomDetail->entry=$roomOriginal->EntryPrice;
//                                $roomDetail->prize=$roomOriginal->PrizePrice;
                                $messages[] = $roomDetail;
                            }

                        }

                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","messages" => $messages, "status" => 200, "msg" => MSG_200)));

                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","messages" => array(), "status" => 200, "msg" => MSG_200)));
                    }


            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"messages","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";

