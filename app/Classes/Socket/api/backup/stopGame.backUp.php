<?php
// back up for 08/12/2016

include __DIR__ . "/../config/gameFunctions.php";

echo "stop game API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

$Check = new Game();
// we have to get http header token
if ($decode->token AND $decode->token != "" AND
    isset($decode->id) AND $decode->id != ""
) {
    $token = $decode->token;
    $id = $decode->id;


    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $UserOrg = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($UserOrg)) {
            $User = $this->DBA->Load($UserOrg);
            $ValidToken = $User->Token;
            $username = $User->Username;
            // then we need to validate with token user
            if ($token == $ValidToken) {

                $hard = 0;
                // check if room is stopped or not
                $room = $this->DBA->Shell("SELECT * FROM `rooms` WHERE `ID`='$id'");
                if ($this->DBA->Size($room)) {
                    $roomORG = $this->DBA->Load($room);
                    $Char = $roomORG->Char;
                    $Type = $roomORG->Type;

                    // if room stopped
                    // must not validate other parameters from empty
                    if ($roomORG->Status == 'STOPPED') {
                        sleep(1);

                        if ($roomORG->Type == "HARD") {
                            $hard = 1;
                        }
                        // update status of room for user to 3
                        $this->DBA->Shell("UPDATE `user_room` SET `Status`='3' WHERE `RoomID`='$id' AND `UserID`='$username'");

                        $name = $Check->filter($decode->name);
                        $family = $Check->filter($decode->family);
                        $car = $Check->filter($decode->car);
                        $animal = $Check->filter($decode->animal);
                        $flower = $Check->filter($decode->flower);
                        $color = $Check->filter($decode->color);
                        $city = $Check->filter($decode->city);

//                            $name = trim($decode->name);
//                            $family = trim($decode->family);
//                            $car = trim($decode->car);
//                            $animal = trim($decode->animal);
//                            $flower = trim($decode->flower);
//                            $color = trim($decode->color);
//                            $city = trim($decode->city);

                        $score = 0;
                        //Name
                        $scoreName = 0;
                        if ($Check->checkWord($Char, $Cat = 'Name', $Type)) {
                            if ($Type == 'HARD') {

                                $Name = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Name'");
                            } else {
                                $Name = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Name'");
                            }
                            if ($this->DBA->Size($Name)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Name)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreName = $scoreName + 10;
                                    }
                                } else {
                                    $scoreName = $scoreName + 10;
                                }

                                $NameSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$name'AND `Cat`='اسم'");
                                if ($this->DBA->Size($NameSame)) {
                                    $scoreName = $scoreName - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$name'  AND `Cat`='اسم'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$name','" . trim($decode->name) . "','اسم','$scoreName')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->name','Name','$scoreName')");

                        //Family
                        $scoreFamily = 0;
                        if ($Check->checkWord($Char, $Cat = 'Family', $Type)) {
                            if ($Type == 'HARD') {
                                $Family = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$family' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Family'");
                            } else {
                                $Family = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$family' AND `Cat`='Family'");
                            }
                            if ($this->DBA->Size($Family)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Family)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreFamily = $scoreFamily + 10;
                                    }
                                } else {
                                    $scoreFamily = $scoreFamily + 10;
                                }
                                $FamilySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$family'  AND `Cat`='فامیل'");
                                if ($this->DBA->Size($FamilySame)) {
                                    $scoreFamily = $scoreFamily - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$family'  AND `Cat`='فامیل'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$family','" . trim($decode->family) . "','فامیل','$scoreFamily')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$family','Family','$scoreFamily')");

                        //Car
                        $scoreCar = 0;
                        if ($Check->checkWord($Char, $Cat = 'Car', $Type)) {
                            if ($Type == 'HARD') {
                                $Car = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$car' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Car'");
                            } else {
                                $Car = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$car' AND `Cat`='Car'");

                            }
                            if ($this->DBA->Size($Car)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Car)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreCar = $scoreCar + 10;
                                    }
                                } else {
                                    $scoreCar = $scoreCar + 10;
                                }
                                $CarSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$car'  AND `Cat`='ماشین'");
                                if ($this->DBA->Size($CarSame)) {
                                    $scoreCar = $scoreCar - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$car'  AND `Cat`='ماشین'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$car','" . trim($decode->car) . "','ماشین','$scoreCar')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$car','Car','$scoreCar')");

                        //Animal
                        $scoreAnimal = 0;
                        if ($Check->checkWord($Char, $Cat = 'Animal', $Type)) {

                            if ($Type == 'HARD') {
                                $Animal = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$animal' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Animal'");
                            } else {
                                $Animal = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$animal' AND `Cat`='Animal'");

                            }
                            if ($this->DBA->Size($Animal)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Animal)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreAnimal = $scoreAnimal + 10;
                                    }
                                } else {
                                    $scoreAnimal = $scoreAnimal + 10;
                                }
                                $AnimalSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$animal'  AND `Cat`='حیوان'");
                                if ($this->DBA->Size($AnimalSame)) {
                                    $scoreAnimal = $scoreAnimal - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$animal'  AND `Cat`='حیوان'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$animal','" . trim($decode->animal) . "','حیوان','$scoreAnimal')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$animal','Animal','$scoreAnimal')");

                        //Flower
                        $scoreFlower = 0;

                        if ($Check->checkWord($Char, $Cat = 'Flower', $Type)) {

                            if ($Type == 'HARD') {
                                $Flower = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$flower' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Flower'");
                            } else {
                                $Flower = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$flower' AND `Cat`='Flower'");
                            }
                            if ($this->DBA->Size($Flower)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Flower)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreFlower = $scoreFlower + 10;
                                    }
                                } else {
                                    $scoreFlower = $scoreFlower + 10;
//                                        echo "score of flower: ".$scoreFlower."\n";
                                }
                                $FlowerSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$flower'  AND `Cat`='گل'");
//                                    echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$flower'  AND `Cat`='گل'"."\n";
//                                    echo "count:"."\n";
//                                    var_dump($this->DBA->Size($FlowerSame));
                                if ($this->DBA->Size($FlowerSame)) {
                                    $scoreFlower = $scoreFlower - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$flower'  AND `Cat`='گل'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$flower','" . trim($decode->flower) . "','گل','$scoreFlower')");
//                            echo "INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$decode->flower','گل','$scoreFlower')"."\n";
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->flower','Flower','$scoreFlower')");

                        //Color
                        $scoreColor = 0;
                        if ($Check->checkWord($Char, $Cat = 'Color', $Type)) {

                            if ($Type == 'HARD') {
                                $Color = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$color' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Color'");
                            } else {
                                $Color = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$color' AND `Cat`='Color'");
                            }
                            if ($this->DBA->Size($Color)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($Color)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreColor = $scoreColor + 10;
                                    }
                                } else {
                                    $scoreColor = $scoreColor + 10;
                                }
                                $ColorSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$color' AND `Cat`='رنگ'");
                                if ($this->DBA->Size($ColorSame)) {
                                    $scoreColor = $scoreColor - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$color' AND `Cat`='رنگ'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$color','" . trim($decode->color) . "','رنگ','$scoreColor')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->color','Color','$scoreColor')");

                        //City
                        $scoreCity = 0;
                        if ($Check->checkWord($Char, $Cat = 'City', $Type)) {

                            if ($Type == 'HARD') {
                                $City = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$city' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='City'");
                            } else {
                                $City = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$city' AND `Cat`='City'");
                            }
                            if ($this->DBA->Size($City)) {
                                if ($hard == 1) {
                                    $word = $this->DBA->Load($City)->Word;
                                    if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                        $scoreCity = $scoreCity + 10;
                                    }
                                } else {
                                    $scoreCity = $scoreCity + 10;
                                }
                                $CitySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$city' AND `Cat`='کشور'");
                                if ($this->DBA->Size($CitySame)) {
                                    $scoreCity = $scoreCity - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$city' AND `Cat`='کشور'");

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$city','" . trim($decode->city) . "','کشور','$scoreCity')");
                        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->city','City','$scoreCity')");

                        $score = $scoreName + $scoreFamily + $scoreCar + $scoreFlower + $scoreColor + $scoreCity;
                        $this->DBA->Run("UPDATE `user_room` SET `Score`='$score' WHERE `RoomID`='$id' AND `UserID`='$username'");


                        $this->DBA->Shell("UPDATE `user_room` SET `Rank`=2 WHERE `UserID`='$username' AND `RoomID`='$id'");

//                            $statusRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id' AND `Status`=0");
                        $statusRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id' AND `UserID` IN (SELECT `UserID` FROM `user_word` WHERE `RoomID`='$id')");
                        if ($this->DBA->Size($statusRoom)) {
                            $this->DBA->Run("UPDATE `user_room` SET `Rank`=2 WHERE `Rank`=0 AND `RoomID`='$id' AND `UserID` IN (SELECT `UserID` FROM `user_word` WHERE `RoomID`='$id')");
                        }


                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 200, "msg" => MSG_200)));

                        // send ranking
                        $rankingStatus = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id'");

                        echo "SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id'"."\n";
                        echo "count:\n";
                        var_dump($this->DBA->Size($rankingStatus));
                        if(!$this->DBA->Size($rankingStatus)){
                            include __DIR__."/ranking.php";
                        }

                    } else {

                        // must validate other parameters

                        if (
                            isset($decode->name) AND
                            isset($decode->family) AND
                            isset($decode->car) AND
                            isset($decode->animal) AND
                            isset($decode->flower) AND
                            isset($decode->color) AND
                            isset($decode->city)
                        ) {

                            if ($roomORG->Type == "HARD") {
                                $hard = 1;
                            }

                            $Check->logUserWord($username, $decode->name, 'Name', 'NULL');
                            $Check->logUserWord($username, $decode->family, 'Family', 'NULL');
                            $Check->logUserWord($username, $decode->car, 'Car', 'NULL');
                            $Check->logUserWord($username, $decode->animal, 'Animal', 'NULL');
                            $Check->logUserWord($username, $decode->flower, 'Flower', 'NULL');
                            $Check->logUserWord($username, $decode->color, 'Color', 'NULL');
                            $Check->logUserWord($username, $decode->city, 'City', 'NULL');

                            $name = $Check->filter($decode->name);
                            $family = $Check->filter($decode->family);
                            $car = $Check->filter($decode->car);
                            $animal = $Check->filter($decode->animal);
                            $flower = $Check->filter($decode->flower);
                            $color = $Check->filter($decode->color);
                            $city = $Check->filter($decode->city);

//                                echo "user name: ".$decode->name."\n";
//                                echo "user family: ".$decode->family."\n";
//                                echo "user car: ".$decode->car."\n";
//                                echo "user animal: ".$decode->animal."\n";
//                                echo "user flower: ".$decode->flower."\n";
//                                echo "user color: ".$decode->color."\n";
//                                echo "user city: ".$decode->city."\n";

                            $validName = 0;
                            $validFamily = 0;
                            $validCar = 0;
                            $validAnimal = 0;
                            $validFlower = 0;
                            $validColor = 0;
                            $validCity = 0;

                            $validNameMes = 0;
                            $validFamilyMes = 0;
                            $validCarMes = 0;
                            $validAnimalMes = 0;
                            $validFlowerMes = 0;
                            $validColorMes = 0;
                            $validCityMes = 0;

                            //first check if words existed
                            if ($Check->checkWord($Char, $Cat = 'Name', $Type)) {
                                $validNameMes = 1;
                                if ($Check->checkWordWithCat($name, $Char, $Cat = 'Name', $Type)) {
                                    $validName = 1;
                                    $validNameMes = 2;

                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'Family', $Type)) {
                                $validFamilyMes = 1;
                                if ($Check->checkWordWithCat($family, $Char, $Cat = 'Family', $Type)) {
                                    $validFamilyMes = 2;
                                    $validFamily = 1;
                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'Car', $Type)) {
                                $validCarMes = 1;
                                if ($Check->checkWordWithCat($car, $Char, $Cat = 'Car', $Type)) {
                                    $validCarMes = 2;
                                    $validCar = 1;
                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'Animal', $Type)) {
                                $validAnimalMes = 1;
                                if ($Check->checkWordWithCat($animal, $Char, $Cat = 'Animal', $Type)) {
                                    $validAnimalMes = 2;
                                    $validAnimal = 1;
                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'Flower', $Type)) {
                                $validFlowerMes = 1;
                                if ($Check->checkWordWithCat($flower, $Char, $Cat = 'Flower', $Type)) {
                                    $validFlowerMes = 2;
                                    $validFlower = 1;
                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'Color', $Type)) {
                                $validColorMes = 1;
                                if ($Check->checkWordWithCat($color, $Char, $Cat = 'Color', $Type)) {
                                    $validColorMes = 2;
                                    $validColor = 1;
                                }
                            }
                            if ($Check->checkWord($Char, $Cat = 'City', $Type)) {
                                $validCityMes = 1;
                                if ($Check->checkWordWithCat($city, $Char, $Cat = 'City', $Type)) {
                                    $validCityMes = 2;
                                    $validCity = 1;
                                }
                            }
                            // object of messages
                            $messObj = new stdClass();
                            $messObj->name = $validNameMes;
                            $messObj->family = $validFamilyMes;
                            $messObj->car = $validCarMes;
                            $messObj->animal = $validAnimalMes;
                            $messObj->flower = $validFlowerMes;
                            $messObj->color = $validColorMes;
                            $messObj->city = $validCityMes;

                            // calculating unclear word
                            $unclear = 0;
                            if (($Check->countChar($Char) - 2) > 3) {
                                $unclear = 3;
                            }
//                                echo "check name: ".$validName."\n";
//                                echo "check family: ".$validFamily."\n";
//                                echo "check car: ".$validCar."\n";
//                                echo "check animal: ".$validAnimal."\n";
//                                echo "check flower: ".$validFlower."\n";
//                                echo "check color: ".$validColor."\n";
//                                echo "check city: ".$validCity."\n";

//                                echo "count:\n";
//                                      var_dump($Check->countChar($Char));
//                                      var_dump($unclear);

//                                echo "check count: "."\n";
//                                      print_r($Check->countChar($Char)-$unclear);

//                                echo "\ncheck count user: ".($validCity + $validColor + $validFlower + $validAnimal + $validCar + $validFamily + $validName)."\n";

                            if (($validCity + $validColor + $validFlower + $validAnimal + $validCar + $validFamily + $validName) >= ($Check->countChar($Char)) - ($unclear)) {


//                                    $name = $decode->name;
//                                    $family = $decode->family;
//                                    $car = $decode->car;
//                                    $animal = $decode->animal;
//                                    $flower = $decode->flower;
//                                    $color = $decode->color;
//                                    $city = $decode->city;
//                                    echo "test1"."\n";
                                $this->DBA->Shell("UPDATE `user_room` SET `Status`='3',`Stop`='1' WHERE `RoomID`='$id' AND `UserID`='$username'");
                                $this->DBA->Shell("UPDATE `rooms` SET `Status`='STOPPED',`StopDate`=NOW() WHERE `ID`='$id'");

                                $score = 0;
                                //Name
                                $scoreName = 0;
                                if ($Check->checkWord($Char, $Cat = 'Name', $Type)) {

                                    if ($Type == 'HARD') {

                                        $Name = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Name'");
                                    } else {
                                        $Name = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$name' AND `Cat`='Name'");
                                    }
                                    if ($this->DBA->Size($Name)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Name)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreName = $scoreName + 10;
                                            }
                                        } else {
                                            $scoreName = $scoreName + 10;
                                        }
//                                            $NameSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$name'");
//                                            if ($this->DBA->Size($NameSame)) {
//                                                $scoreName = $scoreName - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$name','" . trim($decode->name) . "','اسم','$scoreName')");
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->name)."','Name','$scoreName')");
                                $Check->logUserWord($username, trim($decode->name), 'Name', $scoreName);


                                //Family
                                $scoreFamily = 0;
                                if ($Check->checkWord($Char, $Cat = 'Family', $Type)) {

                                    if ($Type == 'HARD') {
                                        $Family = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$family' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Family'");
                                    } else {
                                        $Family = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$family' AND `Cat`='Family'");
                                    }
                                    if ($this->DBA->Size($Family)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Family)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreFamily = $scoreFamily + 10;
                                            }
                                        } else {
                                            $scoreFamily = $scoreFamily + 10;
                                        }
//                                            $FamilySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$family'");
//                                            if ($this->DBA->Size($FamilySame)) {
//                                                $scoreFamily = $scoreFamily - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$family','" . trim($decode->family) . "','فامیل','$scoreFamily')");
//                                    echo "INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$family','".trim($decode->family)."','فامیل','$scoreFamily')"."\n";
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->family)."','Family','$scoreFamily')");
                                $Check->logUserWord($username, trim($decode->family), 'Family', $scoreFamily);


                                //Car
                                $scoreCar = 0;
                                if ($Check->checkWord($Char, $Cat = 'Car', $Type)) {

                                    if ($Type == 'HARD') {
                                        $Car = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$car' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Car'");
                                    } else {
                                        $Car = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$car' AND `Cat`='Car'");

                                    }
                                    if ($this->DBA->Size($Car)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Car)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreCar = $scoreCar + 10;
                                            }
                                        } else {
                                            $scoreCar = $scoreCar + 10;
                                        }
//                                            $CarSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$car'");
//                                            if ($this->DBA->Size($CarSame)) {
//                                                $scoreCar = $scoreCar - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$car','" . trim($decode->car) . "','ماشین','$scoreCar')");
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->car)."','Car','$scoreCar')");
                                $Check->logUserWord($username, trim($decode->car), 'Car', $scoreCar);


                                //Animal
                                $scoreAnimal = 0;
                                if ($Check->checkWord($Char, $Cat = 'Animal', $Type)) {

                                    if ($Type == 'HARD') {
                                        $Animal = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$animal' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Animal'");
                                    } else {
                                        $Animal = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$animal' AND `Cat`='Animal'");

                                    }
                                    if ($this->DBA->Size($Animal)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Animal)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreAnimal = $scoreAnimal + 10;
                                            }
                                        } else {
                                            $scoreAnimal = $scoreAnimal + 10;
                                        }
//                                            $AnimalSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$animal'");
//                                            if ($this->DBA->Size($AnimalSame)) {
//                                                $scoreAnimal = $scoreAnimal - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$animal','" . trim($decode->animal) . "','حیوان','$scoreAnimal')");
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->animal)."','Animal','$scoreAnimal')");
                                $Check->logUserWord($username, trim($decode->animal), 'Animal', $scoreAnimal);


                                //Flower
                                $scoreFlower = 0;
                                if ($Check->checkWord($Char, $Cat = 'Flower', $Type)) {

                                    if ($Type == 'HARD') {
                                        $Flower = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$flower' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Flower'");
                                    } else {
                                        $Flower = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$flower' AND `Cat`='Flower'");
//                                            echo "SELECT * FROM `words` WHERE `Word`='$flower' AND `Cat`='Flower'"."\n";
                                    }
//                                        echo "count:"."\n";
//                                        var_dump($this->DBA->Size($Flower));
                                    if ($this->DBA->Size($Flower)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Flower)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreFlower = $scoreFlower + 10;
                                            }
                                        } else {
                                            $scoreFlower = $scoreFlower + 10;
                                        }
//                                            $FlowerSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$flower'");
//                                            if ($this->DBA->Size($FlowerSame)) {
//                                                $scoreFlower = $scoreFlower - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$flower','" . trim($decode->flower) . "','گل','$scoreFlower')");
//                                    echo "INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->flower)."','گل','$scoreFlower')"."\n";
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->flower)."','Flower','$scoreFlower')");
                                $Check->logUserWord($username, trim($decode->flower), 'Flower', $scoreFlower);


                                //Color
                                $scoreColor = 0;
                                if ($Check->checkWord($Char, $Cat = 'Color', $Type)) {

                                    if ($Type == 'HARD') {
                                        $Color = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$color' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='Color'");
                                    } else {
                                        $Color = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$color' AND `Cat`='Color'");
                                    }
                                    if ($this->DBA->Size($Color)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($Color)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreColor = $scoreColor + 10;
                                            }
                                        } else {
                                            $scoreColor = $scoreColor + 10;
                                        }
//                                            $ColorSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$color'");
//                                            if ($this->DBA->Size($ColorSame)) {
//                                                $scoreColor = $scoreColor - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$color','" . trim($decode->color) . "','رنگ','$scoreColor')");
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->color)."','Color','$scoreColor')");
                                $Check->logUserWord($username, trim($decode->color), 'Color', $scoreColor);


                                //City
                                $scoreCity = 0;
                                if ($Check->checkWord($Char, $Cat = 'City', $Type)) {

                                    if ($Type == 'HARD') {
                                        $City = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$city' AND SUBSTR(`Word`,-1,1)='$Char'AND `Cat`='City'");
                                    } else {
                                        $City = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$city' AND `Cat`='City'");
                                    }
                                    if ($this->DBA->Size($City)) {
                                        if ($hard == 1) {
                                            $word = $this->DBA->Load($City)->Word;
                                            if (substr(strrev($word), 0 * 2, 1 * 2) == $Char) {
                                                $scoreCity = $scoreCity + 10;
                                            }
                                        } else {
                                            $scoreCity = $scoreCity + 10;
                                        }
//                                            $CitySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `Word`='$city'");
//                                            if ($this->DBA->Size($CitySame)) {
//                                                $scoreCity = $scoreCity - 5;
//                                            }
                                    }
                                }
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$city','" . trim($decode->city) . "','کشور','$scoreCity')");
//                                    $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','".trim($decode->city)."','City','$scoreCity')");
                                $Check->logUserWord($username, trim($decode->city), 'City', $scoreCity);


                                $score = $scoreName + $scoreFamily + $scoreCar + $scoreFlower + $scoreColor + $scoreCity;
                                $this->DBA->Shell("UPDATE `user_room` SET `Score`='$score',`Rank`=2 WHERE `RoomID`='$id' AND `UserID`='$username'");
//                                    echo "UPDATE `user_room` SET `Score`='$score' WHERE `RoomID`='$id' AND `UserID`='$username'";
//                                    $this->DBA->Shell("UPDATE `user_room` SET `Rank`=2 WHERE `UserID`='$username' AND `RoomID`='$id'");



                                // for each user you must send massage
                                $roomUser=$this->DBA->Shell("SELECT s1.ResourceID,s2.UserID FROM `socket_user` s1
                                                             JOIN `user_room` s2
                                                             ON s1.UserID=s2.UserID
                                                             WHERE s2.RoomID='$id' AND s2.UserID!='$username'");

                                $roomUser1=$this->DBA->Buffer($roomUser);
                                if(count($roomUser1)>0){
                                    foreach($roomUser1 as $roomUser2){
                                        $this->users[$roomUser2->ResourceID]->send(json_encode(array("command" => "stopGameResp","status" => 200, "msg" => MSG_200)));
                                    }
                                }


                                // response for token user
                                $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 200, "msg" => MSG_200)));
                            } else {
                                // function to create message
                                $messageNotValid = $Check->messageGen($messObj);
                                $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 235, "msg" => $messageNotValid)));

                            }
                        } else {
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 232, "msg" => MSG_232)));
                        }
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 210, "msg" => MSG_210)));
                }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 207, "msg" => MSG_207)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 500, "msg" => $e->getMessage())));
    }

} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 202, "msg" => MSG_202)));
}

/*
function familyValidate($input)
{
    $newInput = explode($input, " ");

}*/
echo "\n---------------------------------------------------------------------------------\n";
