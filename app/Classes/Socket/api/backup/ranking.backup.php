<?php

// get ranking of user
// then after 5 sec delete room

echo "ranking API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id)
) {
    $token = $decode->token;
    $id = $decode->id;
    try {
        $decoded = JWT::decode($token, $key, array('HS256'));
        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            // then we need to validate with token user
            if ($token == $ValidToken) {
                sleep(2);
                $Rank = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id' AND `Status`!='STOPPED'");
//                    echo "SELECT * FROM `rooms` WHERE `ID`='$id' AND `Status`!='STOPPED'";
                if ($this->DBA->Size($Rank)) {
                    $this->users[$from->resourceId]->send(json_encode(array("status" => 233, "msg" => MSG_233)));
                } else {
                    $Rank11 = $this->DBA->Shell("UPDATE `socket_user` SET `UserStatus`='ranking' WHERE `UserID`='$username'");

                    $Rank11 = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                    if ($this->DBA->Size($Rank11)) {

                        $roomInfo = $this->DBA->Load($Rank11);
//                        var_dump($roomInfo);
                        $StopDate = $roomInfo->StopDate;

                        // for user that disconnect from net
                        $ranking = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username' AND `Rank`=0 AND TIMEDIFF(NOW(),`LastActivity`)>'00:00:10' AND `RoomID`='$id'");
                        if ($this->DBA->Size($ranking)) {

                            $to_time = strtotime(date("Y-m-d H:i:s"));
                            $from_time = strtotime($StopDate);
                            $lastActive = round(abs($to_time - $from_time) / 60);

                            if ($lastActive > 10) {
                                $userNoRank = $this->DBA->Load($ranking);
                                $usernameNoRank = $userNoRank->UserID;

                                $ranking = $this->DBA->Shell("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
								('" . $usernameNoRank . "','$id',NULL,0,'اسم'),
								('" . $usernameNoRank . "','$id',NULL,0,'فامیل'),
								('" . $usernameNoRank . "','$id',NULL,0,'ماشین'),
								('" . $usernameNoRank . "','$id',NULL,0,'حیوان'),
								('" . $usernameNoRank . "','$id',NULL,0,'گل'),
								('" . $usernameNoRank . "','$id',NULL,0,'رنگ'),
								('" . $usernameNoRank . "','$id',NULL,0,'کشور')");
                                $ranking = $this->DBA->Shell("UPDATE `user_room` SET `Rank`=1 WHERE `RoomID`='$id'");
                            } else {
                                $this->users[$from->resourceId]->send(json_encode(array("status" => 233, "msg" => MSG_233)));
                            }
                        } else {

                            // check if user ready for rank
                            $readyForRank = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`!=2 AND `RoomID`='$id'");
                            if ($this->DBA->Size($readyForRank)) {
                                $this->users[$from->resourceId]->send(json_encode(array("status" => 242, "msg" => MSG_242)));

                            } else {


                                // ranking users
//                                $Rank = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id' ORDER BY `Score` DESC ");
                                $Rank = $this->DBA->Shell("SELECT s1.`UserID`,SUM(s1.`Score`) AS Score,s2.`Avatar` FROM `user_word` s1
                                                     JOIN `user_room` s2
                                                     ON s1.UserID=s2.UserID
                                                     WHERE s1.RoomID='$id'
                                                     GROUP BY s1.`UserID` ORDER BY SUM(s1.`Score`) DESC");
                                $Rank1 = $this->DBA->Buffer($Rank);
                                $PrizePriceOrg = NULL;
                                $PrizePrice = $this->DBA->Shell("SELECT `PrizePrice` FROM `rooms` WHERE ID='$id'");
                                if ($this->DBA->Size($PrizePrice)) {
                                    $PrizePrice1 = $this->DBA->Load($PrizePrice);
                                    $PrizePriceOrg = $PrizePrice1->PrizePrice;
                                }

                                // check for equal score
                                $equal = 0;
                                $maxScore = $DBA->Shell("SELECT MAX(Score),COUNT(ID) AS MAX FROM `user_room` WHERE `RoomID`='$id' GROUP BY Score ORDER BY MAX ASC LIMIT 1 ");
                                $maxScore1 = $this->DBA->Load($maxScore);
                                if ($maxScore1->MAX > 1) {
                                    $equal = 1;
                                }

                                $userRank = 0;
                                $arrayOfRank = array();
                                $j = 1;
                                for ($i = 1; $i <= count($Rank1); $i++) {

                                    $word = $this->DBA->Shell("SELECT * FROM `user_word` WHERE `UserID`='" . $Rank1[$i - 1]->UserID . "' AND `RoomID`='$id' ORDER BY `ID` ASC");
                                    $word1 = $this->DBA->Buffer($word);
                                    $wordArray = "";
                                    foreach ($word1 as $word2) {
                                        if ($word2->Word == "") {
                                            $word2->Word = "خالی";
                                        }
                                        $wordArray .= $word2->Cat . ":" . $word2->WordOrg . " (" . $word2->Score . ") - ";
                                    }

                                    $wordArray .= "امتیاز کل (" . $Rank1[$i - 1]->Score . ")";
//                                    $wordArray = substr($wordArray, 0, -2);
                                    $arrayRank = new stdClass();
                                    $arrayRank->rank = $i;
                                    $arrayRank->username = $Rank1[$i - 1]->UserID;
                                    if ($wordArray === false) {
                                        $wordArray = "کاربر بعلت قطع ارتباط از بازی خارج شده است";
                                        $this->DBA->Run("UPDATE `user_room` SET `Rank`=1 , `Status`=3 WHERE `RoomID`='$id' AND `UserID`='" . $arrayRank->username . "'");

                                    }

                                    $arrayRank->word = $wordArray;
                                    $arrayRank->prize = 0;
                                    $arrayRank->avatar = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $Rank1[$i - 1]->UserID . "&userPic=" . $Rank1[$i - 1]->Avatar;
                                    array_push($arrayOfRank, $arrayRank);

                                    if ($equal != 1) {
                                        if ($i == 1 AND $Rank1[$i - 1]->Score != 0) {
                                            $arrayRank->prize = $PrizePriceOrg;
                                        }

                                        if ($Rank1[$i - 1]->UserID == $username) {
                                            $userRank = $i;
                                        }
                                    }

                                    // update score of user in user_room
                                    $this->DBA->Run("UPDATE `user_room` SET `Score`='" . $Rank1[$i - 1]->Score . "' WHERE `RoomID`='$id' AND `UserID`='" . $arrayRank->username . "'");
                                    // update user credit

                                    if ($userRank == 1) {
                                        if (is_null($Rank1[$i - 1]->Score)) {
                                            $this->DBA->Shell("UPDATE `users` SET `Credit`=`Credit`+'$PrizePriceOrg' WHERE `Username`='$username'");
                                        }
                                    }
                                }


                                // send foreach user their rank
                                foreach($arrayOfRank as $arrayOfRank1){
                                    $otherUserRank=$this->DBA->Shell("SELECT ResourceID FROM `socket_user` WHERE UserID='$arrayOfRank1->username'");
                                    if($this->DBA->Size($otherUserRank)){
                                        $userSource=$this->DBA->Load($otherUserRank);
                                        $ResourceID=$userSource->ResourceID;
                                        $this->users[$ResourceID]->send(json_encode(array("command" => "ranking" ,"rank" => $arrayOfRank, "userRank" => $arrayOfRank1->rank, "status" => 200, "msg" => MSG_200)));
                                    }
                                }

                                // rank => array of object (rank,score,word)
                                // userRank => rank user k token dade
//                            print_r(array("rank" => $arrayOfRank, "userRank" => $userRank, "status" => 200, "msg" => MSG_200));
                                $this->users[$from->resourceId]->send(json_encode(array("rank" => $arrayOfRank, "userRank" => $userRank, "status" => 200, "msg" => MSG_200)));
                                sleep(1);
                                $this->DBA->Run("UPDATE `user_room` SET `Rank`=1 WHERE `UserID`='$username' AND `RoomID`='$id'");

                                $rank = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id'");
                                if (!$this->DBA->Size($rank)) {
//                            $this->DBA->Run("DELETE FROM `user_room` WHERE  `UserID`='$username'");
//                            $this->DBA->Run("DELETE FROM `user_word` WHERE  `UserID`='$username'");
//                            $this->DBA->Run("DELETE FROM `rooms` WHERE  `ID`='$id'");
                                }


                            }


                        }


                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("status" => 239, "msg" => MSG_239)));
                    }
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("status" => 207, "msg" => MSG_207)));
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
