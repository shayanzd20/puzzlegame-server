<?php
echo "update user API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != ""

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
                    $this->DBA->Run("UPDATE `user_room` SET `LastActivity`=NOW() WHERE `UserID`='$username'");

                // remove invalid socket id
                $UserOrg = $this->DBA->Shell("SELECT * FROM `socket_user`");
                $UserOrg1=$this->DBA->Buffer($UserOrg);
                if(count($UserOrg1)>0){
                    foreach($UserOrg1 as $UserOrg2){
                        if(!isset($this->users[$UserOrg2->ResourceID])){
                            echo $UserOrg2->ResourceID." is not set\n";
                            $UserOrg = $this->DBA->Shell("DELETE FROM `socket_user` WHERE `ResourceID`='".$UserOrg2->ResourceID."'");
                        }
                    }
                }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 103, "msg" => MSG_103)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 109, "msg" => MSG_109)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 100, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 102, "msg" => MSG_102)));
}
echo "\n---------------------------------------------------------------------------------\n";

