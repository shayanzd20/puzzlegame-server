<?php
date_default_timezone_set("Asia/Tehran");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../config/systemConfig.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/SignatureInvalidException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/ExpiredException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/BeforeValidException.php";
require_once __DIR__ . "/../vendor/firebase/php-jwt/src/JWT.php";
require_once __DIR__ . "/../config/Terminal.x";
use \Firebase\JWT\JWT;

$key=DECODE_KEY;
$userPicture=array("fish1.jpg","fish2.jpg","fish3.jpg","fish4.jpg","fish5.jpg","fish6.jpg","fish7.jpg","fish8.jpg");
$DBA = new Terminal("balootmo_smooti");
$get = file_get_contents("php://input");
$decode = json_decode($get);


// check if params exists
if (is_object($decode) && (count(get_object_vars($decode)) > 0)) {

    if ($decode->number AND
        $decode->number!="" AND
        strlen($decode->number)==11 AND
        substr($decode->number,0,2)=='09'AND
        $decode->verification AND
        $decode->verification!="" AND
        strlen($decode->verification)==5
    ) {
        $verification = $decode->verification;
        $number=$decode->number;

        // then validate with verification code
        $ValidCode = $DBA->Shell("SELECT `Code` FROM `verification` WHERE `Number`='$number'");
        if ($ValidCode AND $DBA->Size($ValidCode)) {
            $ValidCode=$DBA->Load($ValidCode)->Code;
            if ($ValidCode == $verification) {
                $jwt = JWT::encode($decode->number, $key);
                $decoded = JWT::decode($jwt, $key, array('HS256'));

                $user=$DBA->Shell("SELECT * FROM `users` WHERE `Number`='$number'");
                if($DBA->Size($user)){
                    $user1=$DBA->Load($user);
                    $username=$user1->Username;
                    $credit=$user1->Credit;
                    $message1=null;
                    $message=$DBA->Shell("SELECT COUNT(ID) as count FROM `invite` WHERE `Invited`='$username' AND `Status`=0");
                    if($DBA->Size($message)){
                        $message1=$DBA->Load($message)->count;
                    }
                    echo json_encode(array("token" => $jwt ,"username"=>$username ,"credit"=>$credit ,"message"=>$message1 ,"status" => 200 , "msg" => MSG_200));
                }else{
                    $randID=rand(0,7);
                    $DBA->Run("ALTER TABLE `users` AUTO_INCREMENT=1");
                    $DBA->Run("INSERT INTO `users` (`Number`,`Token`,`Avatar`) VALUES ('$number','$jwt','".$userPicture[$randID]."')");
                    $DBA->Run("INSERT INTO `user_picture` (`Number`,`Username`,`Name`) VALUES ('$number','$jwt','".$userPicture[$randID]."')");

                    echo json_encode(array("token" => $jwt ,"username"=>false ,"credit"=>0 ,"message"=>0 ,"status" => 200 , "msg" => MSG_200));
                }
                $DBA->Run("UPDATE `verification` SET `Status`='1' WHERE `Number`='$number'");
            } else {
                echo json_encode(array("status" => 201, "msg" => MSG_201));
            }
        } else {
            echo json_encode(array("status" => 204, "msg" => MSG_204));
        }
    } elseif (!$decode->verification OR $decode->verification=="") {

        $DBA->Run("DELETE FROM `verification` WHERE `Number`='$number'");
        $code = rand(10000, 99999);

        // send code
        $DBA->Run("INSERT INTO `verification` (`Number`,`Code`) VALUES ('$token','12345')");
        echo json_encode(array("status" => 205, "msg" => MSG_205));
    } else {
        echo json_encode(array("status" => 202, "msg" => MSG_202));
    }
} else {
    echo json_encode(array("status" => 202, "msg" => MSG_202));
}
