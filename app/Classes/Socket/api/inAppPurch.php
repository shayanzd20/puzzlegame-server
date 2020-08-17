<?php
echo "in app purchase API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

if (
    isset($decode->token) AND $decode->token != "" AND
    isset($decode->mDeveloperPayload) AND
    isset($decode->mSignature) AND $decode->mSignature != "" AND
    isset($decode->mSku) AND $decode->mSku != "" AND
    isset($decode->mItemType) AND $decode->mItemType != "" AND
    isset($decode->mToken) AND $decode->mToken != "" AND
    isset($decode->mPackageName) AND $decode->mPackageName != "" AND
    isset($decode->mPurchaseState)  AND
    isset($decode->mPurchaseTime)
) {
    $token = $decode->token;
    $mDeveloperPayload = $decode->mDeveloperPayload;
    $mSignature = $decode->mSignature;
    $mSku = $decode->mSku;
    $mItemType = $decode->mItemType;
    $mToken = $decode->mToken;
    $mPackageName = $decode->mPackageName;
    $mPurchaseState = $decode->mPurchaseState;
    $mPurchaseTime = $decode->mPurchaseTime;

    // constants
    define("coinspack1", "500");
    define("coinspack2", "2000");
    define("coinspack3", "3500");
    define("coinspack4", "5000");
    define("coinspack5", "10000");
    define("devtest1", "100");
    define("devtest2","10000");
    $coin=constant($mSku);
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


                $PayloadExist = $this->DBA->Shell("SELECT * FROM `inapp_purch_user_log` WHERE `UserID`='$userID' AND `MToken`='$mToken'");
                if ($this->DBA->Size($PayloadExist)) {

                    $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 511, "msg" => MSG_511)));



                } else {
                    $this->DBA->Run("INSERT INTO `inapp_purch_user_log` (`UserID`,`PayLoad`,`MSignature`,`MToken`,`Credit`) VALUES
                                                                            ('" . $userID . "',
                                                                            '" . $mDeveloperPayload . "',
                                                                            '" . $mSignature . "',
                                                                            '" . $mToken . "',
                                                                            '" . $coin . "')");
                    // insert cafe bazzar credit log
                    $this->DBA->Run("INSERT INTO `user_credit_log` (`Number`,`UserID`,`Credit`,`Type`) VALUES ('" . $userNumber . "','" . $userID . "','" . $coin . "','CB')");

                    // update user credit
                    $this->DBA->Run("UPDATE `users` SET `Credit`=`Credit`+ '" . $coin . "' WHERE `Username`='$userID'");
                    $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 200, "msg" => MSG_200)));

                    // return home
                    $User1 = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
                    $Valid = $this->DBA->Load($User1);
                    $userCredit = $Valid->Credit;
                    $userNumber = $Valid->Number;
                    $userID = $Valid->Username;
                    $UserAvatar = $Valid->Avatar;

                    $UserOrgInvite = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userID'");
                    $userPic = "http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userID . "&userPic=" . $UserAvatar;
                    $this->users[$from->resourceId]->send(json_encode(array(
                        "command" => "homeResp",
                        "username" => $userID,
                        "credit" => $userCredit,
                        "avatar" => $userPic,
                        "number" => $userNumber,
                        "notifications" => $this->DBA->Size($UserOrgInvite),
                        "status" => 200,
                        "msg" => MSG_200)));


                }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "inAppPurchResp", "status" => 102, "msg" => MSG_102)));
}


echo "\n---------------------------------------------------------------------------------\n";
