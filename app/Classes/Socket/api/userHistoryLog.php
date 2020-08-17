<?php
echo "\n\n-------------------------------START User History Log API: ------------------------------\n\n";
//api to get user payment history
//// input :
// token
// "command" => "history"

//// output :
// send back user payment history
// "command" => "historyInfo"

use \Firebase\JWT\JWT;
$key = DECODE_KEY;

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

                $UserHistory = $this->DBA->Shell("SELECT * FROM `user_credit_log` WHERE `Number`='$UserNumber' AND `Type`='MCI'");
                $UserHistory1 = $this->DBA->Buffer($UserHistory);

                $arrayHistory=array();
                if(count($UserHistory1)>0){

                    foreach($UserHistory1 as $UserHistory2){
                        $objHistory=new stdClass();
                        $objHistory->credit=$UserHistory2->Credit;
                        $objHistory->date=$UserHistory2->Date;
                        $arrayHistory[]=$objHistory;
                    }
                }
                $this->users[$from->resourceId]->send(json_encode(array(
                    "command" => "historyInfo",
                    "history" => $arrayHistory,
                    "status" => 200,
                    "msg" => MSG_200)));

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "historyInfo", "status" => 103, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "historyInfo", "status" => 509, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "historyInfo", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "historyInfo", "status" => 502, "msg" => MSG_502)));
}
echo "\n--------------------------------END User History Log API:-------------------------------\n";
