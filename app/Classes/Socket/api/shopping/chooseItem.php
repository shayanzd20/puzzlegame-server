<?php

echo "\n--------------------------------START choose Item API:---------------------------------\n\n";

//api to just click on each item
// input :
// output :

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
            $userPrize = $Valid->Prize;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                // add item to cart
                if(isset($decode->id)){
                    $itemID = $decode->id;

                    // check item is exist or not
                    $item = $this->DBA->Shell("SELECT * FROM `shopping` WHERE `ID`='$itemID'");

                    // item existed
                    if($this->DBA->Size($item)){
                        $item1 = $this->DBA->Load($item);
                        $itemTitle = $item1->Title;
                        $itemPrice = str_replace(",","",$item1->Price);

                        // check if user has enough prize coin or not
                        $sumTmp1=0;
                        $sumTmp = $this->DBA->Shell("SELECT SUM(`Credit`) FROM `shopping_tmp` WHERE `UserID`='$username'");
                        if($this->DBA->Size($sumTmp)){

                            // total of user card item
                            $sumTmp1=$this->DBA->Load($sumTmp);
                        }

                        if($userPrize>=$itemPrice+$sumTmp1){
                            $this->DBA->Run("INSERT INTO `shopping_tmp` (`UserID`,`ItemID`,`ItemTitle`,`ItemPrice`) VALUES
                                                                ('".$username."','".$itemID."','".$itemTitle."','".$itemPrice."')");
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp", "status" => 200, "msg" => MSG_200)));
                        }else{
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 151, "msg" => MSG_151)));
                        }

                    }else{

                        // item not existed
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 150, "msg" => MSG_150)));
                    }
                }else{

                    // show cart
                    $Items=array();
                    $sumTmp = $this->DBA->Shell("SELECT * FROM `shopping_tmp` WHERE `UserID`='$username'");
                    $ShoppingItems1 = $this->DBA->Buffer($sumTmp);
                    if(COUNT($ShoppingItems1)>0){
                        foreach($ShoppingItems1 as $ShoppingItems2){
                            $item=new stdClass();
                            $item->id=$ShoppingItems2->ItemID;
                            $item->title=$ShoppingItems2->ItemTitle;
                            $item->price=$ShoppingItems2->ItemPrice;
                            $Items[]=$item;
                        }
                    }

                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","cart" => $Items, "status" => 200, "msg" => MSG_200)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"chooseItemResp","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------END choose Item API--------------------------------\n";



