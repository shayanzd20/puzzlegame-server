<?php
echo "app version API:\n\n";

$this->users[$from->resourceId]->send(json_encode(array("command"=>"appVersionResp","version"=>"1.1","link"=>"http://balootmobile.org/mci/smoothy/","status" => 200, "msg" => MSG_200)));




echo "\n---------------------------------------------------------------------------------\n";
