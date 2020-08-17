<?php

// api to check if real user exist in room or not
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/Terminal.x";
$DBA = new Terminal("balootmo_smoothy");


$id = $_GET['id'];


// check item is exist or not
$charRoom = $DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='".$id."' AND
`UserID` NOT IN (SELECT `Username` FROM `users_fake`)");
if ($DBA->Size($charRoom)) {
    echo 0;
} else {
    echo 1;
}



