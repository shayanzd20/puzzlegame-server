<?php
echo "show active rooms API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

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

                    // user enter the active rooms page
                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='showroom' WHERE `UserID`='$username'");

                    if (intval($decode->offset) >= 0) {
                        $offset = $decode->offset;
                        $offset = $offset * 10;
                        $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY' AND `Type`='PUBLIC' LIMIT " . $offset . ",10");
                        if ($this->DBA->Size($rooms)) {
                            $rooms1 = $this->DBA->Buffer($rooms);
                            $showRooms = array();
                            foreach ($rooms1 as $rooms2) {
                                $showRooms[] = array("id" => $rooms2->ID,
                                    "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                    "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                    "type" => $rooms2->Type);
                            }
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","offset" => $offset, "rooms" => $showRooms, "status" => 200, "msg" => MSG_200)));
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 200, "msg" => MSG_200, "rooms" => array())));
                        }
                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 141, "msg" => MSG_241)));
                    }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 109, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 100, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "showroomsResp","status" => 102, "msg" => MSG_202)));
}
echo "\n---------------------------------------------------------------------------------\n";

