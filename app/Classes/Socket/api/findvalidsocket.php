<?php
echo "\n---------------------------------------------------------------------------------\n";
echo "invalid socket:\n\n";


$UserOrg = $this->DBA->Shell("SELECT * FROM `socket_user`");
$UserOrg1=$this->DBA->Buffer($UserOrg);
foreach($UserOrg1 as $UserOrg2){
    if(!isset($this->users[$UserOrg2->ResourceID])){
        echo $UserOrg2->ResourceID." is not set\n";
        $UserOrg = $this->DBA->Shell("DELETE FROM `socket_user` WHERE `ResourceID`='".$UserOrg2->ResourceID."'");

    }else{
        $this->users[$UserOrg2->ResourceID]->send(json_encode(array("status" => 223, "msg" => "test")));
    }
}