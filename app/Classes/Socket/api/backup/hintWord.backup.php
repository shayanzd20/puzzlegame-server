<?php
echo "hint word API:\n\n";
use \Firebase\JWT\JWT;

//require __DIR__."/../config/gameFunctions.php";
$key = DECODE_KEY;
//$word = new Game();



// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    $decode->id AND $decode->id != ""
) {
    $token = $decode->token;
    $roomId = $decode->id;

    try {
        $key = DECODE_KEY;
        $decoded = JWT::decode($token, $key, array('HS256'));
        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $userCredit = $Valid->Credit;
            $userNumber = $Valid->Number;
            $userID = $Valid->Username;

            // then we need to validate with token user
            if ($token == $ValidToken) {


                // check if room exit
                $Char = $this->DBA->Shell("SELECT s1.`Char`,s2.`EntryPrice`,s2.`Type`,s1.`Hint` FROM `user_room` s1
                                        JOIN `rooms` s2
                                        ON s1.RoomID=s2.ID
                                        WHERE s1.`RoomID`='$roomId' AND s1.`UserID`='$userID' LIMIT 1");

                if ($this->DBA->Size($Char)) {
                    $CharOrg = $this->DBA->Load($Char);

//                    print_r($CharOrg);

                    $Char1 = $CharOrg->Char;
                    $EntryPrice = $CharOrg->EntryPrice;
                    $Type = $CharOrg->Type;
                    $Hint = $CharOrg->Hint;

                    if ($EntryPrice < $userCredit) {

                        if ($Hint < 3) {

                            if (isset($decode->cat) AND $decode->cat != "") {

                                ////////////////////// calculate hint cost /////////
                                if ($EntryPrice > 0 AND $EntryPrice <= 1000) {
                                    if ($Hint == 0) {
                                        $hintCost = 100;
                                    } elseif ($Hint == 1) {
                                        $hintCost = 200;
                                    } else {
                                        $hintCost = 300;
                                    }
                                } elseif ($EntryPrice > 1000 AND $EntryPrice <= 2000) {
                                    if ($Hint == 0) {
                                        $hintCost = 200;
                                    } elseif ($Hint == 1) {
                                        $hintCost = 400;
                                    } else {
                                        $hintCost = 600;
                                    }
                                } else {
                                    if ($Hint == 0) {
                                        $hintCost = 300;
                                    } elseif ($Hint == 1) {
                                        $hintCost = 600;
                                    } else {
                                        $hintCost = 900;
                                    }
                                }
                                $messageCost = "تعداد " . $hintCost . " سکه از حساب شما برداشت شد";
                                ///////////////////////////////////////////

                                if ($Type == 'HARD') {
                                    $CharHint = $this->DBA->Shell("SELECT `WordOrg` as `Word`,`Cat` FROM `words` WHERE SUBSTR(`WordOrg`,-1,1)='$Char1'GROUP BY `Cat`");

                                } else {
                                    $CharHint = $this->DBA->Shell("SELECT `WordOrg` as `Word`,`Cat` FROM `words` WHERE `Char`='$Char1'GROUP BY `Cat`");
//                                    echo "SELECT `WordOrg` as `Word`,`Cat` FROM `words` WHERE `Char`='$Char1'GROUP BY `Cat`"."\n";

                                }
                                $CharHint1 = $this->DBA->Buffer($CharHint);
//                                print_r($CharHint1);
                                $wordNew = array();
                                foreach ($CharHint1 as $CharHint2) {
                                    $wordNew[] = array("Word" => $CharHint2->Word, "Cat" => $CharHint2->Cat,);
                                }

                                switch ($decode->cat) {
                                    case "name":
                                        $ID = $this->recursive_array_search("Name", $wordNew);
                                        if (is_int($ID)) {
                                            $CharHint1 = $wordNew[$ID]['Word'];
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'name', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");

                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;
                                    case "family":
                                        $ID = $this->recursive_array_search("Family", $wordNew);
                                        if (is_int($ID)) {
                                            $CharHint1 = $wordNew[$ID]['Word'];
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'family', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;
                                    case "car":
                                        $ID = $this->recursive_array_search("Car", $wordNew);
                                        if (is_int($ID)) {
                                            $CharHint1 = $wordNew[$ID]['Word'];
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'car', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;

                                    case "animal":
                                        var_dump($CharHint1);
//                                        $ID = $this->recursive_array_search("Animal", $wordNew);
                                        $IDR = $this->word->find_key_in_obj("Animal", $CharHint1);

                                        var_dump($IDR);
                                        if (is_int($IDR)) {
                                            $CharHint1 = $wordNew[$IDR]['Word'];
                                            var_dump($CharHint1);
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'animal', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;

                                    case "flower":
                                        print_r($wordNew);
                                        $ID = $this->recursive_array_search("Flower", $wordNew);
                                        echo "this is ID:".$ID."\n";
                                        if (is_int($ID)) {

                                            $CharHint1 = $wordNew[$ID]['Word'];
                                                echo "flower:".$CharHint1."\n";
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'flower', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;

                                    case "color":
                                        $ID = $this->recursive_array_search("Color", $wordNew);
                                        if (is_int($ID)) {
                                            $CharHint1 = $wordNew[$ID]['Word'];
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'color', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;

                                    case "city":
                                        $ID = $this->recursive_array_search("City", $wordNew);
                                        if (is_int($ID)) {
                                            $CharHint1 = $wordNew[$ID]['Word'];
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","cat" => 'city', "word" => $CharHint1, "message" => $messageCost, "status" => 200, "msg" => MSG_200)));
                                            $this->DBA->Run("UPDATE `user_room` SET `Hint`=`Hint`+1 WHERE `RoomID`='$roomId' AND `UserID`='$userID'");
                                        } else {
                                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));

                                        }
                                        break;

                                    default:
                                        $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 221, "msg" => MSG_221)));
                                }

                                $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`-" . $hintCost . " WHERE `Number`='$userNumber'");


                            } else {
                                $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 221, "msg" => MSG_221)));
                            }
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 240, "msg" => MSG_240)));
                        }
                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 230, "msg" => MSG_230)));
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 220, "msg" => MSG_220)));
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("commmand"=>"hintWordResp","status" => 202, "msg" => MSG_202)));
}



echo "\n---------------------------------------------------------------------------------\n";
