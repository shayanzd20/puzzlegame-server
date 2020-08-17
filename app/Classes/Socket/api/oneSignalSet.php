<?php
echo "validation API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;
$userPicture = array("fish1.jpg", "fish2.jpg", "fish3.jpg", "fish4.jpg", "fish5.jpg", "fish6.jpg", "fish7.jpg", "fish8.jpg");

// check if params exists
if (isset($decode->token) AND
    isset($decode->oneSignal)
) {
    $token = $decode->token;
    $oneSignal = $decode->oneSignal;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $UserNumber = $User->Number;
            $UserCredit = $User->Credit;
            $UserAvatar = $User->Avatar;
            $UserStatus = $User->UserStatus;
            $UserPushID = $User->PushID;
            $UserDeviceModel = $User->DeviceModel;
            $UserDeviceOS = $User->DeviceOS;
            $UserDeviceVersion = $User->DeviceVersion;
            if ($User->Username) {
                $userID = $User->Username;
            } else {
                $userID = null;
            }

            $this->DBA->Shell("UPDATE `socket_user` SET `Number`='$UserNumber',`UserID`='$userID',`UserStatus`='$UserStatus' WHERE `ResourceID`='" . $from->resourceId . "'");

            // then we need to validate with token user
            if ($token == $ValidToken) {
                echo "valid"."\n";

                // check if has push notification id or not
                var_dump($UserPushID);
                if(!$UserPushID OR is_null($UserPushID) OR $UserPushID !=""){

                    // get one signal id
                    $this->DBA->Run("UPDATE `users` SET `PushID`='".$oneSignal."' WHERE `Username`='$userID'");

                    echo "\n"."UPDATE `users` SET `PushID`='".$oneSignal."' WHERE `Username`='$userID'"."\n";

                }
                $this->users[$from->resourceId]->send(json_encode(array("command" => "oneSignalResp", "status" => 200, "msg" => MSG_200)));

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "oneSignalResp", "status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "oneSignalResp", "status" => 509, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "homeResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "oneSignalResp", "status" => 502, "msg" => MSG_502)));
}

echo "\n---------------------------------------------------------------------------------\n";
