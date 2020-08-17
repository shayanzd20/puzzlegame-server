<?php
echo "enter profile API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if ($decode->token AND $decode->token != "") {
    $token = $decode->token;

    try {
        $key = DECODE_KEY;
        $decoded = JWT::decode($token, $key, array('HS256'));

        $User = $DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($DBA->Size($User)) {
            $UserOrg = $DBA->Load($User);
            $ValidToken = $UserOrg->Token;
            $username = $UserOrg->Username;
            $number = $UserOrg->Number;
            $credit = $UserOrg->Credit;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                $this->users[$from->resourceId]->send(json_encode(array("picture" => "null",
                    "username" => $username,
                    "number" => $number,
                    "credit" => $credit,
                    "status" => 200,
                    "msg" => MSG_200)));
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
    $this->users[$from->resourceId]->send(json_encode(array("status" => 202, "msg" => MSG_202)));
}


echo "\n---------------------------------------------------------------------------------\n";
