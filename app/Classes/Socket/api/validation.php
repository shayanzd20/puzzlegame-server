<?php
echo "\n------------------------------Start validation API-------------------------------\n";

use \Firebase\JWT\JWT;

$key = DECODE_KEY;
$userPicture = array("fish1.jpg", "fish2.jpg", "fish3.jpg", "fish4.jpg", "fish5.jpg", "fish6.jpg", "fish7.jpg", "fish8.jpg");

require_once __DIR__ . "/../push_notification/class.push.php";

// check if params exists
if (isset($decode->number) AND
    $decode->number != "" AND
    strlen($decode->number) == 11 AND
    substr($decode->number, 0, 2) == '09' AND
    $decode->verification AND
    $decode->verification != "" AND
    strlen($decode->verification) == 5
) {
    $verification = $decode->verification;
    $number = $decode->number;
//        $createToken=$decode->number;
//        $createToken=array("number"=>$decode->number);
    $createToken = array("number" => $decode->number, "date" => date("Y-m-d H:i:s"));

    // then validate with verification code
    $ValidCode = $this->DBA->Shell("SELECT `Code` FROM `verification` WHERE `Number`='$number'");
    if ($ValidCode AND $this->DBA->Size($ValidCode)) {
        $ValidCode = $this->DBA->Load($ValidCode)->Code;
        if ($ValidCode == $verification) {
            $jwt = JWT::encode($createToken, $key);
            $decoded = JWT::decode($jwt, $key, array('HS256'));
//                    print_r($decoded);

            $user = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$number'");
            if ($this->DBA->Size($user)) {
                $user1 = $this->DBA->Load($user);
                $username = $user1->Username;
                $credit = $user1->Credit;

                $userSocket1 = NULL;
                $DeviceUID = NULL;
                $DeviceM = NULL;
                $DeviceModel = NULL;
                $DeviceOS = NULL;
                $DeviceVersion = NULL;
                $AppVersion = NULL;
                $Table = NULL;
                $userSocket = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `ResourceID`='$from->resourceId'");
                if ($this->DBA->Size($userSocket)) {
                    $userSocket1 = $this->DBA->Load($userSocket);
                    $DeviceUID = $userSocket1->DeviceUID;
                    $DeviceM = $userSocket1->DeviceM;
                    $DeviceModel = $userSocket1->DeviceModel;
                    $DeviceOS = $userSocket1->DeviceOS;
                    $DeviceVersion = $userSocket1->DeviceVersion;
                    $AppVersion = $userSocket1->AppVersion;
                    $Table = $userSocket1->Tablet;
                }


//                print_r(json_decode($id));

                $this->DBA->Run("INSERT INTO `users` (`Number`,`Token`,`Avatar`,`RegisterDate`,`DeviceUID`,`DeviceM`,`DeviceModel`,`DeviceOS`,`DeviceVersion`,`AppVersion`,`Tablet`)
                                              VALUES ('$number','$jwt',`Avatar`,'" . $decoded->date . "','" . $DeviceUID . "','" . $DeviceM . "','" . $DeviceModel . "','" . $DeviceOS . "','" . $DeviceVersion . "','" . $AppVersion . "','" . $Table . "')
                                   ON DUPLICATE KEY UPDATE
                                   `Token`='$jwt',
                                   `RegisterDate`='" . $decoded->date . "',
                                   `DeviceUID`='" . $DeviceUID . "',
                                   `DeviceM`='" . $DeviceM . "',
                                   `DeviceModel`='" . $DeviceModel . "',
                                   `DeviceOS`='" . $DeviceOS . "',
                                   `DeviceVersion`='" . $DeviceVersion . "',
                                   `AppVersion`='" . $AppVersion . "',
                                   `Tablet`='" . $Table . "' ");


                $this->DBA->Run("UPDATE `socket_user` SET `Token`='$jwt',`UserID`='$username',`Number`='$number' WHERE `ResourceID`='" . $from->resourceId . "' ");

                $message1 = null;
                $message = $this->DBA->Shell("SELECT COUNT(ID) as count FROM `invite` WHERE `Invited`='$username' AND `Status`=0");
                if ($this->DBA->Size($message)) {
                    $message1 = $this->DBA->Load($message)->count;
                }
                $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "token" => $jwt, "username" => $username, "status" => 200, "msg" => MSG_200)));
            } else {

                $userSocket1 = NULL;
                $DeviceUID = NULL;
                $DeviceM = NULL;
                $DeviceModel = NULL;
                $DeviceOS = NULL;
                $DeviceVersion = NULL;
                $AppVersion = NULL;
                $Table = NULL;
                $userSocket = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `ResourceID`='$from->resourceId'");
                if ($this->DBA->Size($userSocket)) {
                    $userSocket1 = $this->DBA->Load($userSocket);
                    $DeviceUID = $userSocket1->DeviceUID;
                    $DeviceM = $userSocket1->DeviceM;
                    $DeviceModel = $userSocket1->DeviceModel;
                    $DeviceOS = $userSocket1->DeviceOS;
                    $DeviceVersion = $userSocket1->DeviceVersion;
                    $AppVersion = $userSocket1->AppVersion;
                    $Table = $userSocket1->Tablet;
                }

                $randID = rand(0, 7);
                $this->DBA->Run("ALTER TABLE `users` AUTO_INCREMENT=1");



                $this->DBA->Run("INSERT INTO `users` (`Number`,`Token`,`Avatar`,`RegisterDate`,`DeviceUID`,`DeviceM`,`DeviceModel`,`DeviceOS`,`DeviceVersion`,`AppVersion`,`Tablet`)
                                   VALUES ('$number','$jwt','" . $userPicture[$randID] . "','" . $decoded->date . "','" . $DeviceUID . "','" . $DeviceM . "','" . $DeviceModel . "','" . $DeviceOS . "','" . $DeviceVersion . "','" . $AppVersion . "','" . $Table . "')
                                   ON DUPLICATE KEY UPDATE
                                   `Token`='$jwt',
                                   `RegisterDate`='" . $decoded->date . "',
                                   `DeviceUID`='" . $DeviceUID . "',
                                   `DeviceM`='" . $DeviceM . "',
                                   `DeviceModel`='" . $DeviceModel . "',
                                   `DeviceOS`='" . $DeviceOS . "',
                                   `DeviceVersion`='" . $DeviceVersion . "',
                                   `AppVersion`='" . $AppVersion . "',
                                   `Tablet`='" . $Table . "'");


                $this->DBA->Run("INSERT INTO `user_picture` (`Number`,`Username`,`Name`) VALUES ('$number','$jwt','" . $userPicture[$randID] . "')");
                $this->DBA->Run("UPDATE `socket_user` SET `Token`='$jwt',`Number`='$number' WHERE `ResourceID`='" . $from->resourceId . "' ");

                $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "token" => $jwt, "username" => false, "status" => 200, "msg" => MSG_200)));
            }
            $this->DBA->Run("UPDATE `verification` SET `Status`='1' WHERE `Number`='$number'");
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "status" => 201, "msg" => MSG_201)));
        }
    } else {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "status" => 204, "msg" => MSG_204)));
    }
} elseif (!$decode->verification OR $decode->verification == "") {

    $this->DBA->Run("DELETE FROM `verification` WHERE `Number`='$number'");
    $code = rand(10000, 99999);

    // send code
    $this->DBA->Run("INSERT INTO `verification` (`Number`,`Code`) VALUES ('$token','$code')");
    $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "status" => 205, "msg" => MSG_205)));
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "validationResp", "status" => 102, "msg" => MSG_102)));
}

echo "\n------------------------------Start validation API-------------------------------\n";
