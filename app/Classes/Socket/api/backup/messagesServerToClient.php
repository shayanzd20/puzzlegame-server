<?php

$RoomIDServerSocket = $this->DBA->Shell("SELECT `RoomID` FROM `invite` WHERE `Invited`='$invitedOrg'");
if ($this->DBA->Size($RoomIDServerSocket) > 0) {

    $RoomIDServerSocket1 = $this->DBA->Buffer($RoomIDServerSocket);
    foreach ($RoomIDServerSocket1 as $RoomIDServerSocket2) {
        $roomSocket = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$RoomIDServerSocket2->RoomID'");
        if ($this->DBA->Size($roomSocket)) {
            $roomOriginal = $this->DBA->Load($roomSocket);
            $roomDetail = new stdClass();
            $roomDetail->roomid = $roomOriginal->ID;
            $roomDetail->message = "دعوت از اتاق " . $roomOriginal->ID . "\n";
            $roomDetail->message .= " ورودی " . $roomOriginal->EntryPrice . " - " . " جایزه " . $roomOriginal->PrizePrice . "\n";
            $roomDetail->message .= "تعداد اعضا " . $roomOriginal->MaxMembers . "/" . $roomOriginal->Members;

//                                $roomDetail->members=$roomOriginal->Members;
//                                $roomDetail->maxmembers=$roomOriginal->MaxMembers;
//                                $roomDetail->entry=$roomOriginal->EntryPrice;
//                                $roomDetail->prize=$roomOriginal->PrizePrice;
            $messages[] = $roomDetail;
        }

    }

    $this->users[$ResourceID]->send(json_encode(array("command"=>"messages","messages" => $messages, "status" => 200, "msg" => MSG_200)));

}