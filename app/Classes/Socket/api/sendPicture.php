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


$DBA = new Terminal("balootmo_smooti");


//    $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
//    $size = $_FILES['image']['size'];
//    $image_name = addslashes($_FILES['image']['name']);
//    $info = pathinfo($_FILES['image']['name']);


    // we have to get http header token
    if (isset($_POST['token']) AND $_POST['token'] != "") {
        $token = $_POST['token'];

        try {
            $key=DECODE_KEY;
            $decoded = JWT::decode($token, $key, array('HS256'));
            $User = $DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
            if ($DBA->Size($User)) {
                $Valid = $DBA->Load($User);
                $ValidToken = $Valid->Token;
                $username = $Valid->Username;
                $number = $Valid->Number;

                // then we need to validate with token user
                if ($token == $ValidToken) {

                    //Сheck that we have a file
                    if((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {
                        //Check if the file is JPEG image and it's size is less than 350Kb
                        $image_name = addslashes(basename($_FILES['image']['name']));               ///// if error delete
                        $ext = substr($image_name, strrpos($image_name, '.') + 1);
                        if (($ext == "jpg") && ($_FILES["image"]["type"] == "image/jpeg") &&
                            ($_FILES["image"]["size"] < 350000)) {

                            //Determine the path to which we want to save this file
                            $newname = __DIR__."/../picture/".$image_name;
                            //Check if the file with the same name is already exists on the server
                            if (!file_exists($newname)) {
                                //Attempt to move the uploaded file to it's new place
                                if ((move_uploaded_file($_FILES['image']['tmp_name'],$newname))) {

                                    $DBA->Run("INSERT INTO `user_picture` (`Number`,`Username`,`Name`)
                                               VALUES('{$decoded->number}','{$username}', '{$image_name}')
                                               ON DUPLICATE KEY UPDATE `Number`='{$decoded->number}',`Username`='{$username}',`Name`= '{$image_name}';");
                                    echo json_encode(array("status" => 200, "msg" => MSG_200));

                                } else {
                                    echo json_encode(array("status" => 603, "msg" => MSG_603));
                                }
                            } else {
                                echo json_encode(array("status" => 602, "msg" => MSG_602));
                            }
                        }else{
                            echo json_encode(array("status" => 601, "msg" => MSG_601));
                        }
                    }else{
                        echo json_encode(array("status" => 600, "msg" => MSG_600));
                    }

                } else {
                    echo json_encode(array("status" => 203, "msg" => MSG_203));
                }
            } else {
                echo json_encode(array("status" => 209, "msg" => MSG_209));
            }
        } catch (Exception $e) {
            echo json_encode(array("status" => 500, "msg" => $e->getMessage()));
        }
    } else {
        echo json_encode(array("status" => 202, "msg" => MSG_202));
    }
//} else {
//    echo json_encode(array("status" => 202, "msg" => MSG_202));
//}

