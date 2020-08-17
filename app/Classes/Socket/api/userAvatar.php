<?php
date_default_timezone_set("Asia/Tehran");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../config/systemConfig.php";
require_once __DIR__ . "/../config/Terminal.x";

header("Content-Type: image/jpg");


$DBA = new Terminal("balootmo_smooti");
if (isset($_GET['number'])) {
    $number = $_GET['number'];
    $usernameOrg = $DBA->Shell("SELECT `Username` FROM `users` WHERE `Number`='$number'");
    if ($DBA->Size($usernameOrg)) {

        $usernameOrg1 = $DBA->Load($usernameOrg)->Username;

    }

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $number1 = $DBA->Shell("SELECT `Name` FROM `user_picture` WHERE `Name` NOT IN (SELECT `Avatar` FROM `user_room` WHERE `RoomID`='.$id.')");

        if ($DBA->Size($number1)) {

            $number2 = $DBA->Load($number1)->Name;
            $image = __DIR__ . "/../picture/" . $number2;
            $DBA->Run("UPDATE `user_room` SET `Avatar`='" . $number2 . "' WHERE `UserID`='$usernameOrg1'");

        } else {
            $image = __DIR__ . "/../picture/Victor-Ciobanu.jpg";
        }
    } else {

        if (isset($_GET['default'])) {
            $image = __DIR__ . "/../picture/Victor-Ciobanu.jpg";
        } else {
            if(isset($_GET['userPic'])){

                $userPic=$_GET['userPic'];
                $image = __DIR__ . "/../picture/" . $userPic;
            }else{
                $number1 = $DBA->Shell("SELECT `Name` FROM user_picture ORDER BY RAND() LIMIT 1");
                if ($DBA->Size($number1)) {
                    $number2 = $DBA->Load($number1)->Name;
                    $image = __DIR__ . "/../picture/" . $number2;
//                    readfile($image);
//                $DBA->Run("UPDATE `user_room` SET `Avatar`='" . $number2 . "' WHERE `UserID`='$usernameOrg1'");

                } else {
                    $image = __DIR__ . "/../picture/Victor-Ciobanu.jpg";
//                    readfile($image);
                }
            }

        }


    }

} else {

    if (isset($_GET['username'])) {

        $username = $_GET['username'];

        if(isset($_GET['userPic'])){

            $userPic=$_GET['userPic'];
            $image = __DIR__ . "/../picture/" . $userPic;
        }else{
            $picture = $DBA->Shell("SELECT `Avatar` FROM `user_room` WHERE `UseID`='$username'");
            if ($DBA->Size($picture)) {
                $picture1 = $DBA->Load($picture)->Name;
                $image = __DIR__ . "/../picture/" . $picture1;
            } else {
                $image = __DIR__ . "/../picture/Victor-Ciobanu.jpg";
            }
        }

    } else {
        $image = __DIR__ . "/../picture/Victor-Ciobanu.jpg";
    }
}

readfile($image);


