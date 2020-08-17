<?php
echo "\n--------------------------------START ranking API -----------------------------------\n";

// get ranking of user
// then after 5 sec delete room

$Rank11 = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
if ($this->DBA->Size($Rank11)) {
    $roomInfo = $this->DBA->Load($Rank11);
    $StopDate = $roomInfo->StopDate;
    $EntryPrice = $roomInfo->EntryPrice;

    // for user that disconnect from net
    $ranking = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `UserID`='$username' AND `Rank`=0 AND TIMEDIFF(NOW(),`LastActivity`)>'00:01:10' AND `RoomID`='$id'");
    if ($this->DBA->Size($ranking)) {
        echo "exist"."\n";

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
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"rankingResp","status" => 233, "msg" => MSG_233)));
        }
    } else {

        echo "if user not exist"."\n";
            // ranking users

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

            // update user score in user_room
            foreach($Rank1 as $Rank2){
                $this->DBA->Run("UPDATE `user_room` SET `Score`='".$Rank2->Score."' WHERE `UserID`='".$Rank2->UserID."' AND `RoomID`='".$id."'");
            }

            // check for equal score
            $equal = 0;
            $maxScore = $this->DBA->Shell("SELECT MAX(Score) AS MAXSCORE ,COUNT(ID) AS MAX FROM `user_room` WHERE `RoomID`='$id' GROUP BY Score ORDER BY MAX(Score) DESC LIMIT 1 ");
            echo "\n"."SELECT MAX(Score),COUNT(ID) AS MAX FROM `user_room` WHERE `RoomID`='$id' GROUP BY Score ORDER BY MAX ASC LIMIT 1 "."\n";
            $maxScore1 = $this->DBA->Load($maxScore);
            echo "max score:\n";
            var_dump($maxScore1);
            if ($maxScore1->MAX > 1) {
                $equal = 1;
            }

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
                $arrayRank->avatar = "https://balootmobile.org/vendor/cboden/ratchet/src/api/userPicture/userAvatar.php?username=" . $Rank1[$i - 1]->UserID . "&userPic=" . $Rank1[$i - 1]->Avatar;
                array_push($arrayOfRank, $arrayRank);

                $userRank = 0;

                // users score not equal
                echo "\nstatus of equal:\n";
                var_dump($equal);
                $winnerUser=NULL;
                if ($equal == 0) {

                    // it means not equal
                    if ($i == 1 AND $Rank1[$i - 1]->Score != 0) {
                        $arrayRank->prize = $PrizePriceOrg;
                        $winnerUser=$Rank1[$i - 1]->UserID;
                    }

                    echo "the i is :".$i."\n";
                    echo "if Rank1[i - 1]->UserID == username"."\n";
                    var_dump($Rank1[$i - 1]->UserID == $username);
                    if ($Rank1[$i - 1]->UserID == $username) {
                        $userRank = $i;
                    }
                    if ($i==1) {
                        $userRank = 1;
                    }

                }else{
                    echo "\nequal:\n";
                    $equalScore = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id' AND `Score`='".$maxScore1->MAXSCORE."' AND `UserID`='".$Rank1[$i - 1]->UserID."' ");
                    echo "\n"."SELECT * FROM `user_room` WHERE `RoomID`='$id' AND `Score`='".$maxScore1->MAXSCORE."' AND `UserID`='".$Rank1[$i - 1]->UserID."' "."\n";

                    echo "count: ".$this->DBA->Size($equalScore)."\n";
                    if($this->DBA->Size($equalScore)){

                        // update prize coin of user in
                        $NewOrgPrize=$PrizePriceOrg/$maxScore1->MAX;
                        $arrayRank->prize = $NewOrgPrize;

                        $prize=0;
                        if($NewOrgPrize>$EntryPrice){
                            $prize=$NewOrgPrize-$EntryPrice;
                            $Credit=$EntryPrice;
                        }else{
                            $Credit=$NewOrgPrize;
                            $prize=0;
                        }

                        $this->DBA->Run("UPDATE `users` SET `Prize`=`Prize`+'$prize',`Credit`=`Credit`+'$Credit' WHERE `Username`='".$Rank1[$i - 1]->UserID."' AND `Username` IN (SELECT `UserID` FROM `user_room`)");
                       echo "\n"."UPDATE `users` SET `Prize`=`Prize`+'$prize' WHERE `Username`='".$Rank1[$i - 1]->UserID."' AND `Username` IN (SELECT `UserID` FROM `user_room`)"."\n";

                        // update game log
                        $this->DBA->Run("UPDATE `users_game_log` SET `Winner`=CONCAT(`Winner` ,' AND ' '".$Rank1[$i - 1]->UserID."')  WHERE `RoomID`='".$id."'");


                        // insert prize coin log
                        $this->DBA->Run("INSERT INTO `users_prize_coin_log` (`UserID`,`Prize`,`Status`) VALUES ('".$Rank1[$i - 1]->UserID."','".$prize."','ACHIVE')");
                        $this->DBA->Run("INSERT INTO `users_prize_coin_log` (`UserID`,`Prize`,`Status`) VALUES ('".$Rank1[$i - 1]->UserID."','".$Credit."','GAME')");

                    }

                }
                echo "\nstatus of user rank per user:\n".$arrayRank->username."\n";
                var_dump($userRank);


                // update score of user in user_room
                $this->DBA->Run("UPDATE `user_room` SET `Score`='" . $Rank1[$i - 1]->Score . "' WHERE `RoomID`='$id' AND `UserID`='" . $arrayRank->username . "'");

                // update user credit
                if ($userRank == 1 AND $equal == 0) {

                        $prize=$PrizePriceOrg-$EntryPrice;
                        $Credit=$EntryPrice;


                    // update user prize coin
                        $this->DBA->Run("UPDATE `users` SET `Prize`=`Prize`+'$prize',`Credit`=`Credit`+'$Credit' WHERE `Username`='$winnerUser' AND `Username` IN (SELECT `UserID` FROM `user_room`)");
                        echo "UPDATE `users` SET `Prize`=`Prize`+'$prize',`Credit`=`Credit`+'$Credit' WHERE `Username`='$winnerUser' AND `Username` IN (SELECT `UserID` FROM `user_room`)"."\n";

                    // update game log
                    $this->DBA->Run("UPDATE `users_game_log` SET `Winner`='".$winnerUser."' WHERE `RoomID`='".$id."'");


                    // insert prize coin log
                    $this->DBA->Run("INSERT INTO `users_prize_coin_log` (`UserID`,`Prize`,`Status`) VALUES ('".$winnerUser."','".$prize."','ACHIVE')");
                    $this->DBA->Run("INSERT INTO `users_prize_coin_log` (`UserID`,`Prize`,`Status`) VALUES ('".$winnerUser."','".$Credit."','GAME')");

                }
            }


            // send foreach user their rank
            foreach ($arrayOfRank as $arrayOfRank1) {
                $otherUserRank = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE UserID='$arrayOfRank1->username'");
                if ($this->DBA->Size($otherUserRank)) {
                    $userSource = $this->DBA->Load($otherUserRank);
                    $ResourceID = $userSource->ResourceID;
                    $UserID = $userSource->UserID;
                    echo "\n\nuser rank:$UserID\n";
                    print_r($arrayOfRank1->rank);
                    echo "\n\n";
                    echo "other's rank";
                    print_r($arrayOfRank);

                    // send user credit info
                    $userCredit = $this->DBA->Shell("SELECT * FROM `users` WHERE Username='$arrayOfRank1->username'");
                    $userCredit1 = $this->DBA->Load($userCredit);
                    // call user credit
                    $this->users[$ResourceID]->send(json_encode(array(
                        "command" => "userCredit",
                        "credit_game" => intval($userCredit1->Credit)+intval($userCredit1->Prize),
                        "credit_shopping" => intval($userCredit1->Prize))));

                    // send user rank
                    $this->users[$ResourceID]->send(json_encode(array("command" => "rankingResp", "rank" => $arrayOfRank, "userRank" => $arrayOfRank1->rank, "status" => 200, "msg" => MSG_200)));
                    $this->DBA->Run("UPDATE `user_room` SET `Rank`=1 WHERE `UserID`='$UserID' AND `RoomID`='$id'");
                    $this->DBA->Run("UPDATE `socket_user` SET `UserStatus`='home' WHERE `UserID`='$UserID'");
                    $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$UserID'");
                }
            }

            $this->DBA->Run("UPDATE `user_room` SET `Rank`=1 WHERE `UserID`='$username' AND `RoomID`='$id'");


        sleep(1);
            // delete room after ranking
            $rank = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`!=1 AND `RoomID`='$id'");
            if (!$this->DBA->Size($rank)) {
                            $this->DBA->Run("DELETE FROM `user_room` WHERE  `RoomID`='$id'");
                            $this->DBA->Run("DELETE FROM `user_word` WHERE  `RoomID`='$id'");
                            $this->DBA->Run("DELETE FROM `rooms` WHERE  `ID`='$id'");
            }
    }


} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"rankingResp","status" => 110, "msg" => MSG_110)));
}

echo "\n--------------------------------END ranking API -----------------------------------\n";
