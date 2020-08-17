<?php
echo "\n--------------------------------START address form API:---------------------------------\n\n";

//api to input information of user
//// input :
// token
// name
// family
// postalcode
// address
//// output :



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
            $userNumber = $Valid->Number;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                if (
                    isset($decode->name) AND $decode->name != "" AND
                    isset($decode->family) AND $decode->family != "" AND
                    isset($decode->postalcode) AND $decode->postalcode != "" AND
                    isset($decode->address) AND $decode->address != "" )
                {
                    $name = $decode->name;
                    $family = $decode->family;
                    $postalCode = $decode->postalcode;
                    $address = $decode->address;

                    $this->DBA->Run("INSERT INTO `users_detail` (`Number`,`UserID`,`Name`,`Family`,`Address`,`PostalCode`) VALUES
                    ('" . $userNumber . "','" . $username . "','" . $name . "','" . $family . "','" . $address . "','" . $postalCode . "')");

                    echo "INSERT INTO `users_detail` (`Number`,`UserID`,`Name`,`Family`,`Address`,`PostalCode`) VALUES
                    ('" . $userNumber . "','" . $username . "','" . $name . "','" . $family . "','" . $address . "','" . $postalCode . "')"."\n";

                    $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 200, "msg" => MSG_200)));

                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 102, "msg" => MSG_102)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "addressFormResp", "status" => 102, "msg" => MSG_102)));
}

echo "\n--------------------------------END address form API-------------------------------\n";



