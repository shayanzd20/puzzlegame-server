<?php
echo "\n\n-------------------------------START home API: ------------------------------\n\n";
//api to get user information (credit,user picture)
//// input :
// token
// "command" => "home"

//// output :
// send user information
// "command" => "userInfo"

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

            // then we need to validate with token user
            if ($token == $ValidToken) {

                // default code
                $userPic = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;
                $this->users[$from->resourceId]->send(json_encode(array(
                    "command" => "userInfo",
                    "username" => $userID,
                    "avatar" => $userPic,
                    "number" => $UserNumber,
                    "status" => 200,
                    "msg" => MSG_200)));

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "userInfo", "status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "userInfo", "status" => 509, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "userInfo", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "userInfo", "status" => 502, "msg" => MSG_502)));
}
echo "\n--------------------------------END home API:-------------------------------\n";
