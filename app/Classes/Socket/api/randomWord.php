<?php
echo "random word API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    $decode->id AND $decode->id != ""
) {
    $token = $decode->token;
    $roomId = $decode->id;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $word = array("ا", "ب", "پ", "ت", "ث", "ج", "چ", "ح", "خ", "د", "ذ", "ر", "ز", "ژ", "س", "ش", "ص", "ض", "ط", "ظ", "ع", "غ", "ف", "ق", "ک", "گ", "ل", "م", "ن", "و", "ه", "ی");
        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {
                // check if room exit
                $Roomid = $this->DBA->Shell("SELECT `ID` FROM `rooms` WHERE `ID`='$roomId'");
                if ($this->DBA->Size($Roomid)) {
                    $randID = rand(0, 31);
                    $randomWord = $word[$randID];
                    //update room word in user_room table
                    $this->DBA->Run("UPDATE `user_room` SET `Char`='$randomWord' WHERE `RoomID`='$roomId'");

                    $this->users[$from->resourceId]->send(json_encode(array("char" => $randomWord, "status" => 200, "msg" => MSG_200)));
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 210, "msg" => MSG_210)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
