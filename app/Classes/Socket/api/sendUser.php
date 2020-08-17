<?php

echo "send User API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    $decode->username AND $decode->username != ""
) {

    $token = $decode->token;
    $username = $decode->username;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $ValidToken = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($ValidToken)) {
            $User = $this->DBA->Load($ValidToken);
            $ValidToken = $User->Token;
            $username = $User->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                // check if username is valid or not
                $Validuser = $this->DBA->Shell("SELECT `ID` FROM `users` WHERE `Username`='$decode->username'");
                if ($this->DBA->Size($Validuser)) {
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 234, "msg" => MSG_234)));
                } else {

                    // then validate with verification code
                    $this->DBA->Run("UPDATE `users` SET `Username`='$decode->username' WHERE `Number`='$decoded->number'");
                    $this->DBA->Run("UPDATE `socket_user` SET `UserID`='$decode->username' WHERE `ResourceID`='" . $from->resourceId . "' ");
                    echo "UPDATE `socket_user` SET `UserID`='$decode->username' WHERE `ResourceID`='" . $from->resourceId . "' "."\n";

                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 200, "msg" => MSG_200)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 103, "msg" => MSG_103)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 103, "msg" => MSG_103)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 100, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"sendUserResp","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
