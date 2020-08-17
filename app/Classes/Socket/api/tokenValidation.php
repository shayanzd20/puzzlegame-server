<?php
echo "\n---------------------------------------------------------------------------------\n";
echo "token validate API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != "") {
    $token = $decode->token;
    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $ValidToken = $this->DBA->Shell("SELECT `Token` FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($ValidToken)) {
            $ValidToken = $this->DBA->Load($ValidToken)->Token;
            // then we need to validate with token user
            if ($token == $ValidToken) {
                $user = $this->DBA->Shell("SELECT `Username` FROM `users` WHERE `Number`='$decoded->number'");
                if ($this->DBA->Size($user)) {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 206, "msg" => MSG_206, "username" => true)));
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 206, "msg" => MSG_206, "username" => false)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 207, "msg" => MSG_207)));

        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 201, "msg" => MSG_208)));
}
