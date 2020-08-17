<?php
// check if room was stopped by a user

echo "check stop game API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id)
) {

    $token = $decode->token;
    $id = $decode->id;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            // then we need to validate with token user
            if ($token == $ValidToken) {
                $Stop = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                if ($this->DBA->Size($Stop)) {
                    $Stop1 = $this->DBA->Load($Stop);
                    if ($Stop1->Status == 'STOPPED') {
                        $this->users[$from->resourceId]->send(json_encode(array("status" => 200, "msg" => MSG_200)));
                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("status" => 401, "msg" => MSG_401)));
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 402, "msg" => MSG_402)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 207, "msg" => MSG_207)));
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
