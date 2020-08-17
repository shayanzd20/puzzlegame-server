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
$get = file_get_contents("php://input");
$decode = json_decode($get);

if (is_object($decode) && (count(get_object_vars($decode)) > 0)) {

// we have to get http header token
    if ($decode->token AND $decode->token != "" AND
        isset($decode->name) AND
        isset($decode->family) AND
        isset($decode->car) AND
        isset($decode->animal) AND
        isset($decode->flower) AND
        isset($decode->color) AND
        isset($decode->city) AND
        isset($decode->id) AND $decode->id !=""

    ) {

        $token = $decode->token;
        $name = $decode->name;
        $family = $decode->family;
        $car = $decode->car;
        $animal = $decode->animal;
        $flower = $decode->flower;
        $color = $decode->color;
        $city = $decode->city;
        $id = $decode->id;

        try {
            $key = DECODE_KEY;
            $decoded = JWT::decode($token, $key, array('HS256'));
            $UserOrg = $DBA->Shell("SELECT * FROM `users` WHERE  `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
            if ($DBA->Size($UserOrg)) {
                $User = $DBA->Load($UserOrg);
                $ValidToken = $User->Token;
                $username = $User->Username;
                // then we need to validate with token user
                if ($token == $ValidToken) {
                    $DBA->Shell("UPDATE `user_room` SET `Status`='1' WHERE `RoomID`='$id'");

                    $score = 0;
                    //Name
                    $Name = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Name'");
                    if ($DBA->Size($Name)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$name')");
                        $NameSame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$name'");
                        if ($DBA->Size($NameSame)) {
                            $score = $score - 5;
                        }
                    }

                    //Family
                    $Family = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Family'");
                    if ($DBA->Size($Family)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$family')");
                        $FamilySame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$family'");
                        if ($DBA->Size($FamilySame)) {
                            $score = $score - 5;
                        }
                    }
                    //Car
                    $Car = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Car'");
                    if ($DBA->Size($Car)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$car')");
                        $CarSame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$car'");
                        if ($DBA->Size($CarSame)) {
                            $score = $score - 5;
                        }
                    }

                    //Animal
                    $Animal = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Animal'");
                    if ($DBA->Size($Animal)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$animal')");
                        $AnimalSame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$animal'");
                        if ($DBA->Size($AnimalSame)) {
                            $score = $score - 5;
                        }
                    }

                    //Flower
                    $Flower = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Flower'");
                    if ($DBA->Size($Flower)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$flower')");
                        $FlowerSame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$flower'");
                        if ($DBA->Size($FlowerSame)) {
                            $score = $score - 5;
                        }
                    }

                    //Color
                    $Color = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Color'");
                    if ($DBA->Size($Color)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$color')");
                        $ColorSame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$color'");
                        if ($DBA->Size($ColorSame)) {
                            $score = $score - 5;
                        }
                    }

                    //City
                    $City = $DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='City'");
                    if ($DBA->Size($City)) {
                        $score = $score + 10;
                        $DBA->Run("INSERT INTO `user_word` (`User`,`RoomID`,`Word`) VALUES ('$username','$id','$city')");
                        $CitySame = $DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$city'");
                        if ($DBA->Size($CitySame)) {
                            $score = $score - 5;
                        }
                    }

                    $DBA->Shell("UPDATE `user_room` SET `Score`='$score' WHERE `RoomID`='$id' AND `Number`='$decoded->number'");

                    // ranking users
                    $Rank = $DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id' ORDER BY `Score` DESC ");
                    $Rank1 = $DBA->Buffer($Rank);
                    $arrayRank = array();
                    $i = 1;
                    foreach ($Rank1 as $Rank1) {
                        $arrayRank[] = $i . " - " . $Rank1->UserID;
                        $i++;
                        if ($Rank1->UserID == $username) {
                            $userRank = $i;
                        }
                    }

                    $PrizePrice = $DBA->Shell("SELECT `PrizePrice` FROM `rooms` WHERE ID='$id'");
                    if ($DBA->Size($PrizePrice)) {
                        $PrizePrice1 = $DBA->Load($PrizePrice);
                        $PrizePriceOrg = $PrizePrice1->PrizePrice;
                    }
                    // update user credit
                    if ($userRank == 1) {
                        $DBA->Shell("UPDATE `users` SET `Credit`='$PrizePriceOrg' WHERE `Username`='$username'");

                    }
                    echo json_encode(array("rank" => $arrayRank, "userRank" => $userRank, "prize" => $PrizePriceOrg,"status" => 200, "msg" => MSG_200));
                } else {
                    echo json_encode(array("status" => 207, "msg" => MSG_207));
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


} else {
    echo json_encode(array("status" => 202, "msg" => MSG_202));
}