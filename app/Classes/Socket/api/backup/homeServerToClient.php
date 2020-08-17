<?php

$UserinvitedSocket = $this->DBA->Shell("SELECT * FROM `users` WHERE `Username`='$invitedOrg'");
if ($this->DBA->Size($UserinvitedSocket)) {
    $UserinvitedSocket1 = $this->DBA->Load($UserinvitedSocket);
    $ValidTokenSocket = $UserinvitedSocket1->Token;
    $UserNumberSocket = $UserinvitedSocket1->Number;
    $UserCreditSocket = $UserinvitedSocket1->Credit;
    $UserAvatarSocket = $UserinvitedSocket1->Avatar;
    if ($UserinvitedSocket1->Username) {
        $userIDSocket = $UserinvitedSocket1->Username;
    } else {
        $userIDSocket = null;
    }
            $UserOrgInviteSocket = $this->DBA->Shell("SELECT * FROM `invite` WHERE `Invited`='$userIDSocket'");
            $userPicSocket="http://smooti.balootmobile.org/api/userAvatar.php?username=" . $userIDSocket . "&userPic=" . $UserAvatarSocket;
            $this->users[$ResourceID]->send(json_encode(array(
                "command"=>"home",
                "username"=>$userIDSocket,
                "credit"=>$UserCreditSocket,
                "avatar"=>$userPicSocket,
                "number"=>$UserNumberSocket,
                "notifications"=>$this->DBA->Size($UserOrgInviteSocket),
                "status" => 200,
                "msg" => MSG_200)));
} else {
    $this->users[$from->resourceId]->send(json_encode(array("status" => 209, "msg" => MSG_209)));
}
