<?php
//this api is about to get random char of a room

echo "get random char of a room:\n\n";
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

                if(isset($decode->id)){
                    // add item to cart
                    $roomID = $decode->id;

                    // check item is exist or not
                    $charRoom = $this->DBA->Shell("SELECT `Char` FROM `rooms` WHERE `ID`='$roomID'");
                    if($this->DBA->Size($charRoom)){
                        $charRoom1 = $this->DBA->Load($charRoom);
                        $char = $charRoom1->Char;
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 200, "msg" => MSG_200, "char" => $char,"id"=>$roomID)));


                    }else{
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 150, "msg" => MSG_150)));
                    }
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"charRoomResp","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
