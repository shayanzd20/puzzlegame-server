<?php
echo "refreshUser API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if (isset($decode->deviceUniqueID) AND
    isset($decode->deviceManufacturer) AND
    isset($decode->deviceModel) AND
    isset($decode->deviceOS) AND
    isset($decode->deviceVersion) AND
    isset($decode->appVersion) AND
    isset($decode->table)
) {

    $deviceUniqueID = $decode->deviceUniqueID;
    $deviceManufacturer = $decode->deviceManufacturer;
    $deviceModel = $decode->deviceModel;
    $deviceOS = $decode->deviceOS;
    $deviceVersion = $decode->deviceVersion;
    $appVersion = $decode->appVersion;
    $table = $decode->table;


    if ($appVersion >= APP_VERSION) {


        // cache status of user
        $UserCache = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `DeviceUID`='" . $deviceUniqueID . "'");
        if ($this->DBA->Size($UserCache)) {
            $UserCache = $this->DBA->Load($UserCache);
            $userStatus = $UserCache->UserStatus;
            echo "\n this is user status:\n";
            var_dump($userStatus);
        } else {
            $userStatus = NULL;
        }

        // remove rooms where there is no user
        $emptyRoom = $this->DBA->Shell("SELECT `ID` FROM `rooms` WHERE `ID` NOT IN (SELECT DISTINCT `RoomID` FROM `user_room`)");
        $emptyRoom1=$this->DBA->Buffer($emptyRoom);
        if(COUNT($emptyRoom1)>0){
            foreach($emptyRoom1 as $emptyRoom2){
                $this->DBA->Run("DELETE FROM `rooms` WHERE `ID`='".$emptyRoom2->ID."'");
            }
        }


        // remove invalid socket id
        $UserOrg = $this->DBA->Shell("SELECT * FROM `socket_user`");
        $UserOrg1 = $this->DBA->Buffer($UserOrg);
        if (count($UserOrg1) > 0) {
            foreach ($UserOrg1 as $UserOrg2) {
                if (!isset($this->users[$UserOrg2->ResourceID])) {
                    echo $UserOrg2->ResourceID . " is not set\n";
                    $UserOrg = $this->DBA->Shell("DELETE FROM `socket_user` WHERE `ResourceID`='" . $UserOrg2->ResourceID . "'");
                }
            }
        }

        $socketUser = $this->DBA->Shell("SELECT * FROM `socket_user` WHERE `DeviceUID`='" . $deviceUniqueID . "'");
        if ($this->DBA->Size($socketUser)) {
            $socketUserOrg = $this->DBA->Load($socketUser);
            $socketUserResourceID = $socketUserOrg->ResourceID;
//        $this->clients->detach($conn);
            $this->users[$socketUserResourceID]->close();
            $this->DBA->Run("DELETE FROM `socket_user` WHERE `ResourceID`='" . $socketUserResourceID . "'");

        } else {
            $this->DBA->Run("INSERT INTO `socket_user` (`ResourceID`,`DeviceUID`,`DeviceM`,`DeviceModel`,`DeviceOS`,`DeviceVersion`,`AppVersion`,`Tablet`,`UserStatus`)
                                        VALUES ('" . $from->resourceId . "','$deviceUniqueID','$deviceManufacturer','$deviceModel','$deviceOS','$deviceVersion','$appVersion','$table','$userStatus')
                    ON DUPLICATE KEY UPDATE `ResourceID`='$from->resourceId',`DeviceUID`='$deviceUniqueID',`DeviceM`='$deviceManufacturer',`DeviceModel`='$deviceModel',`DeviceOS`='$deviceOS',`DeviceVersion`='$deviceVersion',`AppVersion`='$appVersion',`UserStatus`='$userStatus',`Tablet`='$table'");

        }

        $username = NULL;
        $usernumber = NULL;
        $userDevice = $this->DBA->Shell("SELECT * FROM `users` WHERE `DeviceUID`='" . $deviceUniqueID . "'");
        if ($this->DBA->Size($userDevice)) {
            $userDevice1 = $this->DBA->Load($userDevice);
            $username = $userDevice1->Username;
            $usernumber = $userDevice1->Number;
            $userToken = $userDevice1->Token;
            $UserStatus = $userDevice1->UserStatus;

            // update user last activity
            $this->DBA->Run("INSERT INTO `socket_user` (`Number`,`UserID`,`ResourceID`,`DeviceUID`,`DeviceM`,`DeviceModel`,`DeviceOS`,`DeviceVersion`,`AppVersion`,`Tablet`,`Token`,`UserStatus`)
                                        VALUES ('" . $usernumber . "','" . $username . "','" . $from->resourceId . "','$deviceUniqueID','$deviceManufacturer','$deviceModel','$deviceOS','$deviceVersion','$appVersion','$table','$userToken','$UserStatus')
                    ON DUPLICATE KEY UPDATE `Number`='$usernumber',`UserID`='$username',`ResourceID`='$from->resourceId',`DeviceUID`='$deviceUniqueID',`DeviceM`='$deviceManufacturer',`DeviceModel`='$deviceModel',`DeviceOS`='$deviceOS',`DeviceVersion`='$deviceVersion',`AppVersion`='$appVersion',`Tablet`='$table',`Token`='$userToken',`UserStatus`='$UserStatus'");
            $this->teleBot->sendNewUserNotif($username);


            $this->users[$from->resourceId]->send(json_encode(array("command" => "refreshUserResp", "status" => 200, "msg" => MSG_200)));
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "refreshUserResp", "status" => 109, "msg" => MSG_209)));
        }

    } else {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "refreshUserResp", "link" => APP_LINK, "status" => 200, "msg" => MSG_200)));

    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "refreshUserResp", "status" => 102, "msg" => MSG_102)));
}
echo "\n---------------------------------------------------------------------------------\n";
