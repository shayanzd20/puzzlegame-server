<?php
// after 10 second will go to game room and call this api
echo "call home API:\n\n";

        $UserOrg = $this->DBA->Shell("SELECT `users`.`Username` as UserID,`users`.`UserStatus` as UserStatus,`user_credit_log`.`Date`,TIMEDIFF(NOW(),`Date`) FROM `user_credit_log`,`users`
                                      WHERE TIMEDIFF(NOW(),`Date`)<'00:10:00'
                                      AND `users`.`Number`=`user_credit_log`.`Number`  GROUP BY `users`.`Username`");
        if (COUNT($UserOrg)>0) {
            $User = $this->DBA->Buffer($UserOrg);

            foreach($User as $User1){
                $UserID = $User1->UserID;
                $UserStatus = $User1->UserStatus;
                if($UserStatus=='home'){
                    $UserOrg1 = $this->DBA->Shell("SELECT `socket_user`.`ResourceID` AS ResourceID,
                                                               `socket_user`.`UserID` AS UserID,
                                                               `users`.`Credit` AS Credit,
                                                               `users`.`Avatar` AS Avatar,
                                                               `users`.`Number` AS Number
                                                               FROM `socket_user`,`users`
                                                               WHERE `socket_user`.`UserID`=`users`.`Username` AND `users`.`Username`='$UserID'");
                    if($this->DBA->Size($UserOrg1)){
                        $UserOrg2=$this->DBA->Load($UserOrg1);
                        $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$UserOrg2->UserID'");
                        $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $UserOrg2->UserID . "&userPic=" . $UserOrg2->Avatar;
                        echo "resource:\n";
                        var_dump($UserOrg2->ResourceID);

                        $this->users[$UserOrg2->ResourceID]->send(json_encode(array(
                            "command" => "homeResp",
                            "username" => $UserOrg2->UserID,
                            "credit" => $UserOrg2->Credit,
                            "avatar" => $userPic,
                            "number" => $UserOrg2->Number,
                            "notifications" => $this->DBA->Size($UserOrgInvite),
                            "status" => 200,
                            "msg" => MSG_200)));
                    }
                }
            }
        }



echo "\n---------------------------------------------------------------------------------\n";
