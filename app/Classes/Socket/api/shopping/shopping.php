<?php
echo "\n--------------------------------START shopping API:---------------------------------\n\n";

// api to get all item of shopping
// input : token
// output : items (with picture and prize)

use \Firebase\JWT\JWT;

$key = DECODE_KEY;
if (isset($decode->token))
{
    $token = $decode->token;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                $Items=array();
                $ShoppingItems = $this->DBA->Shell("SELECT * FROM `shopping`");
                $ShoppingItems1 = $this->DBA->Buffer($ShoppingItems);
                foreach($ShoppingItems1 as $ShoppingItems2){
                    $item=new stdClass();
                    $item->id=$ShoppingItems2->ID;
                    $item->title=$ShoppingItems2->Title;
                    $item->price=$ShoppingItems2->Price;
                    $item->picture="https://balootmobile.org/vendor/cboden/ratchet/src/api/shoppingpicture.php?title=".$ShoppingItems2->Picture;
                    $Items[]=$item;
                }
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"shoppingResp","items" => $Items, "status" => 200, "msg" => MSG_200)));
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"shoppingResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"shoppingResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"shoppingResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"shoppingResp","status" => 102, "msg" => MSG_102)));
}

echo "\n------------------------------END shopping API-------------------------------\n";

