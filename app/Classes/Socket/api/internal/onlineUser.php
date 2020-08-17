<?php
//this api is about to get online users

echo "get online users:\n\n";
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
            $userCredit = $Valid->Credit;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                // check item is exist or not
                $charRoom = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserID`!='".$username."'
                `UserID` NOT IN (SELECT `Username` FROM `users_fake`) ORDER BY RAND()");
                $charRoom1 = $this->DBA->Buffer($charRoom);
                if (count($charRoom1) > 0) {

                    $arrayOnlineSocket=array();
                    foreach($charRoom1 as $charRoom2){
                        $arrayOnlineSocket[]=$charRoom2->UserID;
                    }
                    $this->users[$from->resourceId]->send(json_encode(array("command" => "onlineUserResp", "status" => 200, "msg" => MSG_200, "online" => $arrayOnlineSocket ,"id" => $decode->id)));
                }


            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "charRoomResp", "status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "charRoomResp", "status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "charRoomResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "charRoomResp", "status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
