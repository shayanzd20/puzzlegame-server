<?php
// accept or ignore invitation
echo "random word API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != ""
    AND isset($decode->accept)
) {
    $token = $decode->token;
    $accept = $decode->accept;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $ValidToken = $this->DBA->Shell("SELECT * FROM `users` WHERE  `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        $Valid = $this->DBA->Load($ValidToken);
        $ValidToken = $Valid->Token;
        $username = $Valid->Username;
        // then we need to validate with token user
        if ($token == $ValidToken) {
            if (isset($decode->roomId) AND $decode->roomId != "") {
                $roomID = $decode->roomId;
                if ($accept == '1') {
                    $this->DBA->Run("DELETE FROM `invite` WHERE `RoomID`='$roomID' AND `Invited`='$username'");
                    $this->users[$from->resourceId]->send(json_encode(array("accept" => true, "status" => 200, "msg" => MSG_200)));
                } else {
                    $this->DBA->Run("DELETE FROM `invite` WHERE `RoomID`='$roomID' AND `Invited`='$username'");
                    $this->users[$from->resourceId]->send(json_encode(array("accept" => false, "status" => 200, "msg" => MSG_200)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 202, "msg" => MSG_202)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 203, "msg" => MSG_203)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 202, "msg" => MSG_202)));
}

echo "\n---------------------------------------------------------------------------------\n";
