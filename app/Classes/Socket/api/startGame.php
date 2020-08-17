<?php
echo "\n--------------------------------START start game API -----------------------------------\n";
// after 10 second will go to game room and call this api


use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id)

) {

    $token = $decode->token;
    $id = $decode->id;

    try {
        $key = DECODE_KEY;
        $decoded = JWT::decode($token, $key, array('HS256'));

        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            // then we need to validate with token user
            if ($token == $ValidToken) {

                // check if room is already started
                $room = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id' AND `Status`='RUNNING'");
                if ($this->DBA->Size($room)) {
                    $roomDetail=$this->DBA->Load($room);
                    $roomChar=$roomDetail->Char;
                    $EntryPrice=$roomDetail->EntryPrice;
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","char"=>$roomChar,"status" => 200, "msg" => MSG_200)));
                    $this->DBA->Shell("UPDATE `socket_user` SET `UserStatus`='start' WHERE `UserID`='$username'");
                    $this->DBA->Shell("UPDATE `users` SET `UserStatus`='start' WHERE `Username`='$username'");
                    $this->DBA->Shell("UPDATE `user_room` SET `LastActivity`=NOW() WHERE `Username`='$username'");


                } else {
                    $this->DBA->Shell("UPDATE `rooms` SET `Status`='RUNNING' WHERE `ID`='$id'");
                    $this->DBA->Shell("UPDATE `user_room` SET `Status`='2',`LastActivity`=NOW() WHERE `RoomID`='$id'");
                    $this->DBA->Shell("UPDATE `socket_user` SET `UserStatus`='start' WHERE `UserID`='$username'");
                    $this->DBA->Shell("UPDATE `users` SET `UserStatus`='start' WHERE `Username`='$username'");
                    $room1 = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                    $roomDetail1=$this->DBA->Load($room1);
                    $roomChar=$roomDetail1->Char;
                    $EntryPrice=$roomDetail1->EntryPrice;
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","char"=>$roomChar,"status" => 200, "msg" => MSG_200)));

                }

                if ($EntryPrice > 0 AND $EntryPrice <= 1000) {
                        $hintCost1 = 100;
                        $hintCost2 = 200;
                        $hintCost3 = 300;
                } elseif ($EntryPrice > 1000 AND $EntryPrice <= 2000) {
                        $hintCost1 = 200;
                        $hintCost2 = 400;
                        $hintCost3 = 600;
                } else {
                        $hintCost1 = 300;
                        $hintCost2 = 600;
                        $hintCost3 = 900;
                }

                // send notifications to user every 3 seconds
                    $msg1="استفاده اول از کلاه جادویی ".$hintCost1."سکه دومین کلاه جادویی ".$hintCost2." سکه و سومین کلاه جادویی ".$hintCost3." سکه از شما کسر می شود";
//                    $msg2="بازی با حرف ".$roomChar;
//                for($i=0;$i<3;$i++){
//
//                }
                sleep(0);
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"hintWordResp","status"=>243, "msg" => $msg1)));
                sleep(1);

//                $this->users[$from->resourceId]->send(json_encode(array("command"=>"hintWordResp","status"=>243, "msg" => $msg2)));






            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","status" => 107, "msg" => MSG_107)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","status" => 109, "msg" => MSG_109)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","status" => 100, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"startGameResp","status" => 102, "msg" => MSG_102)));
}

echo "\n--------------------------------END start game API -----------------------------------\n";
