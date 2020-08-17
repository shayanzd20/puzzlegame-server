<?php
echo "\n\n-------------------------------START user credit API: ------------------------------\n\n";
//api to get user information (playing coin, shopping coin)
//// input :
// token
// "command" => "credit"

//// output :
// send user information
// "command" => "userCredit"

use \Firebase\JWT\JWT;

$key = DECODE_KEY;

// we have to get http header token
if (isset($decode->token)
) {
    $token = $decode->token;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $UserNumber = $User->Number;
            $UserCredit = $User->Credit;
            $UserPrize = $User->Prize;
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

            // sync user with socket id
            $this->DBA->Shell("UPDATE `socket_user` SET `Number`='$UserNumber',`UserID`='$userID',`UserStatus`='$UserStatus' WHERE `ResourceID`='" . $from->resourceId . "'");

            // then we need to validate with token user
            if ($token == $ValidToken) {

                $this->users[$from->resourceId]->send(json_encode(array(
                    "command" => "userCredit",
                    "credit_game" => intval($UserCredit)+intval($UserPrize),
                    "credit_shopping" => intval($UserPrize),
                    "status" => 200,
                    "msg" => MSG_200)));

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "userCredit", "status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "userCredit", "status" => 509, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "userCredit", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "userCredit", "status" => 502, "msg" => MSG_502)));
}
echo "\n--------------------------------END user credit API:-------------------------------\n";
