<?php
echo "\n--------------------------------START confirm items API:---------------------------------\n\n";

// api to confirm card and consume prize coin

///// input :
// token = token of user
// id = id of each item in the card

///// output :

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
            $userPrizeCoin = $Valid->Prize;


            // then we need to validate with token user
            if ($token == $ValidToken) {

                // if id is set delete item if not confirm and decrease credit
                if(isset($decode->id)){
                    $itemID=$decode->id;
                    $this->DBA->Run("DELETE FROM `shopping_tmp` WHERE `ItemID`='$itemID'");
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 200, "msg" => MSG_200)));

                }else{

                    // check if user has enough credit or not
                    $sumTmp1=0;
                    $sumTmp = $this->DBA->Shell("SELECT SUM(`ItemPrice`) as SUM FROM `shopping_tmp` WHERE `UserID`='$username'");
                    if($this->DBA->Size($sumTmp)){

                        // total of user tmp item
                        $sumTmp1=$this->DBA->Load($sumTmp)->SUM;
                    }
                    if($userPrizeCoin>=$sumTmp1){
                        // insert into shopping log
                        $sumTmp11 = $this->DBA->Shell("SELECT * FROM `shopping_tmp` WHERE `UserID`='$username'");
                        $sumTmpBuffer=$this->DBA->Buffer($sumTmp11);
                        foreach($sumTmpBuffer as $sumTmpBuffer1){
                            $this->DBA->Run("INSERT INTO `shopping_log` (`UserID`,`ItemID`,`ItemTitle`,`ItemPrice`) VALUES
                                                                        ('".$username."','".$sumTmpBuffer1->ItemID."','".$sumTmpBuffer1->ItemTitle."','".$sumTmpBuffer1->ItemPrice."')");

                        }

                        // consume user prize coin
                        $this->DBA->Run("UPDATE `users` SET `Prize`=`Prize`- ".$sumTmp1." WHERE `Username`='".$username."'");
                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 200, "msg" => MSG_200)));
                        $this->DBA->Run("DELETE FROM `shopping_tmp` WHERE `UserID`='$username'");



                    }
                }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"confirmItemsResp","status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";

