<?php
echo "\n---------------------------------START create room API:-----------------------------\n";
use \Firebase\JWT\JWT;
$key = DECODE_KEY;

$avatar = array("fish1.jpg", "fish2.jpg", "fish3.jpg", "fish4.jpg", "fish5.jpg", "fish6.jpg", "fish7.jpg", "fish8.jpg");


// we have to get http header token
if (
    isset($decode->token) AND
    isset($decode->members) AND is_int($decode->members) AND
    isset($decode->price) AND is_int($decode->price) AND
    isset($decode->prize) AND is_int($decode->prize) AND
    isset($decode->type) AND !is_int($decode->type) AND
    ($decode->type=='public' OR $decode->type='private')
) {

    $token = $decode->token;
    $members = $decode->members;
    $price = $decode->price;
    $prize = $decode->prize;
    $type = $decode->type;   // public ,private

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $UserOrg = $this->DBA->Shell("SELECT `Number` ,`Username`,`Credit`,`Prize`,`Token`,`Avatar`
                                 FROM `users`
                                 WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            $credit = $User->Credit;
            $userPrize = $User->Prize;
            $picOrig = $User->Avatar;
            $Picture = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?number=" . $decoded->number . "&userPic=" . $picOrig;


            // then we need to validate with token user
            if ($token == $ValidToken) {

                // user Already In AnOther Room
                $test = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username'");
                if ($this->DBA->Size($test)) {
                    $id = $this->DBA->Load($test)->RoomID;
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 223, "msg" => MSG_223, "roomid" => $id)));
                } else {

                    // check if user has sufficient credit
                    if ($credit+$userPrize >= $price) {
                        $word = array("آ یا ا", "ب", "پ", "ت", "ج", "چ", "ح", "خ", "د",  "ر", "ز", "ژ", "س", "ش", "ص", "ط", "ع", "غ", "ف", "ق", "ک", "گ", "ل", "م", "ن", "و", "ه", "ی");
                        $randID = rand(0, 27);
                        $randomWord = $word[$randID];

                        $this->DBA->Run("ALTER TABLE `rooms` AUTO_INCREMENT=1");
                        $this->DBA->Shell("INSERT INTO `rooms` (`MaxMembers`,`EntryPrice`,`PrizePrice`,`Char`,`Type`) VALUES
                                     ('" . $members . "','" . $price . "','" . $prize . "','" . $randomWord . "','" . $type . "')");
                        $ID = $this->DBA->LastID();

                        $userPic = array_rand($avatar, 1);

                        $this->DBA->Run("ALTER TABLE `user_room` AUTO_INCREMENT=1");
                        $this->DBA->Shell("INSERT INTO `user_room` (`UserID`,`RoomID`,`Admin`,`Char`,`EntryPrice`,`PrizePrice`,`Avatar`) VALUES
                                     ('" . $username . "','" . $ID . "','Y','" . $randomWord . "','" . $price . "','" . $prize . "','" . $picOrig . "')");

                        // log the game
                        $this->DBA->Shell("INSERT INTO `users_game_log` (`RoomID`,`Members`,`EntryPrice`,`PrizePrice`,`Char`,`Type`) VALUES
                                     ('".$ID."','" . $members . "','" . $price . "','" . $prize . "','" . $randomWord . "','" . $type . "')");

                        $userOriginal = new stdClass();
                        $userOriginal->avatar = $Picture;
                        $userOriginal->username = $username;

                        // increase credit of user
                        if($credit>=$price){

                            // when credit of user is more than entry
                            $creditDecreased=$price;
                            $prizeDecreased=0;
                        }else{

                            // when credit of user is less than entry
                            $creditDecreased=$credit;
                            $prizeDecreased=$price-$credit;
                        }
                        $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`-" . $creditDecreased . ",`Prize`=`Prize`-".$prizeDecreased." WHERE `Number`='$decoded->number'");
                        echo "\n"."UPDATE `users` SET `Credit`=`Credit`-" . $creditDecreased . " WHERE `Number`='$decoded->number'"."\n";

                        // send rooms detail to users
                        $rooms = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `Status`='EMPTY'");
                        if ($this->DBA->Size($rooms)) {
                            $rooms1 = $this->DBA->Buffer($rooms);
                            $showRooms = array();
                            foreach ($rooms1 as $rooms2) {
                                $showRooms[] = array("id" => $rooms2->ID,
                                    "members" => $rooms2->Members . '-' . $rooms2->MaxMembers,
                                    "price" => $rooms2->EntryPrice . '-' . $rooms2->PrizePrice,
                                    "type" => $rooms2->Type);
                            }
                            $UserShowrooms = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `UserStatus`='showroom'");
                            $UserShowrooms1=$this->DBA->Buffer($UserShowrooms);
                            if(count($UserShowrooms1)){
                                foreach($UserShowrooms1 as $UserShowrooms2){
                                    $this->users[$UserShowrooms2->ResourceID]->send(json_encode(array("command" => "showroomResp", "rooms" => $showRooms)));
                                }
                            }
                        }

                        $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='room' WHERE `UserID`='$username'");
                        $this->DBA->Run("UPDATE `users` SET `UserStatus`='room' WHERE `Username`='$username'");


                        // response
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","id" => $ID, "users" => array($userOriginal), "status" => 200, "msg" => MSG_200)));

                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 227, "msg" => MSG_227)));
                    }
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"createroomResp","status" => 102, "msg" => MSG_102)));
}
echo "\n---------------------------------END create room API:-----------------------------\n";
