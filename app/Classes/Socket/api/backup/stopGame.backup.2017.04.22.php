<?php
// stop game backup with Hard or Simple type
//include __DIR__ . "/../config/gameFunctions.php";

echo "stop game API:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;

//$Check = new Game();
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
//                        sleep(1);

                        if ($roomORG->Type == "HARD") {
                            $hard = 1;
                        }
                        // update status of room for user to 3 as stopped
                        $this->DBA->Shell("UPDATE `user_room` SET `Status`='3' WHERE `RoomID`='$id' AND `UserID`='$username'");

                        $name = $this->Check->filter($decode->name);
                        $family = $this->Check->filter($decode->family);
                        $car = $this->Check->filter($decode->car);
                        $animal = $this->Check->filter($decode->animal);
                        $flower = $this->Check->filter($decode->flower);
                        $color = $this->Check->filter($decode->color);
                        $city = $this->Check->filter($decode->city);

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
                        if ($this->Check->checkWord($Char, $Cat = 'Name', $Type)) {
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

                                $NameSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$name'AND `Cat`='اسم'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$name'AND `Cat`='اسم'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($NameSame));
                                if ($this->DBA->Size($NameSame)) {
                                    $scoreName = $scoreName - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$name'  AND `Cat`='اسم'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$name'  AND `Cat`='اسم'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$name','" . trim($decode->name) . "','اسم','$scoreName')");
                        if($scoreName==0 OR is_null($scoreName)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->name','Name','$scoreName')");
                        }
                        //Family
                        $scoreFamily = 0;
                        if ($this->Check->checkWord($Char, $Cat = 'Family', $Type)) {
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
                                $FamilySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$family'  AND `Cat`='فامیل'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$family'  AND `Cat`='فامیل'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($FamilySame));
                                if ($this->DBA->Size($FamilySame)) {
                                    $scoreFamily = $scoreFamily - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$family'  AND `Cat`='فامیل'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$family'  AND `Cat`='فامیل'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$family','" . trim($decode->family) . "','فامیل','$scoreFamily')");
                        if($scoreFamily==0 OR is_null($scoreFamily)){
                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$family','Family','$scoreFamily')");
                        }

                        //Car
                        $scoreCar = 0;
                        if ($this->Check->checkWord($Char, $Cat = 'Car', $Type)) {
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
                                $CarSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$car'  AND `Cat`='ماشین'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$car'  AND `Cat`='ماشین'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($CarSame));
                                if ($this->DBA->Size($CarSame)) {
                                    $scoreCar = $scoreCar - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$car'  AND `Cat`='ماشین'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$car'  AND `Cat`='ماشین'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$car','" . trim($decode->car) . "','ماشین','$scoreCar')");
                        if($scoreCar==0 OR is_null($scoreCar)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$car','Car','$scoreCar')");
                        }
                        //Animal
                        $scoreAnimal = 0;
                        if ($this->Check->checkWord($Char, $Cat = 'Animal', $Type)) {

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
                                $AnimalSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$animal'  AND `Cat`='حیوان'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$animal'  AND `Cat`='حیوان'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($AnimalSame));
                                if ($this->DBA->Size($AnimalSame)) {
                                    $scoreAnimal = $scoreAnimal - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id'  AND `Word`='$animal'  AND `Cat`='حیوان'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$animal'  AND `Cat`='حیوان'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$animal','" . trim($decode->animal) . "','حیوان','$scoreAnimal')");
                        if($scoreAnimal==0 OR is_null($scoreAnimal)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$animal','Animal','$scoreAnimal')");
                        }
                        //Flower
                        $scoreFlower = 0;

                        if ($this->Check->checkWord($Char, $Cat = 'Flower', $Type)) {

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
                                $FlowerSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$flower'  AND `Cat`='گل'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$flower'  AND `Cat`='گل'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($FlowerSame));
                                if ($this->DBA->Size($FlowerSame)) {
                                    $scoreFlower = $scoreFlower - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$flower'  AND `Cat`='گل'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$flower'  AND `Cat`='گل'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$flower','" . trim($decode->flower) . "','گل','$scoreFlower')");
//                            echo "INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$decode->flower','گل','$scoreFlower')"."\n";
                        if($scoreFlower==0 OR is_null($scoreFlower)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->flower','Flower','$scoreFlower')");
                        }
                        //Color
                        $scoreColor = 0;
                        if ($this->Check->checkWord($Char, $Cat = 'Color', $Type)) {

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
                                $ColorSame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `UserID`!='$username' AND `Word`='$color' AND `Cat`='رنگ'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id' AND `UserID`!='$username' AND `Word`='$color' AND `Cat`='رنگ'"."\n";
                                echo "count:"."\n";
                                var_dump($this->DBA->Size($ColorSame));
                                if ($this->DBA->Size($ColorSame)) {
                                    $scoreColor = $scoreColor - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$color' AND `Cat`='رنگ'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$color' AND `Cat`='رنگ'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$color','" . trim($decode->color) . "','رنگ','$scoreColor')");
                        if($scoreColor==0 OR is_null($scoreColor)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->color','Color','$scoreColor')");
                        }
                        //City
                        $scoreCity = 0;
                        if ($this->Check->checkWord($Char, $Cat = 'City', $Type)) {

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
                                $CitySame = $this->DBA->Shell("SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$city' AND `Cat`='کشور'");
                                echo "SELECT `ID` FROM `user_word` WHERE `RoomID`='$id'  AND `UserID`!='$username' AND `Word`='$city' AND `Cat`='کشور'"."\n";
                                var_dump($this->DBA->Size($CitySame));
                                if ($this->DBA->Size($CitySame)) {
                                    $scoreCity = $scoreCity - 5;
                                    $this->DBA->Run("UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$city' AND `Cat`='کشور'");
                                    echo "UPDATE `user_word` SET `Score`= 5 WHERE `RoomID`='$id' AND `Word`='$city' AND `Cat`='کشور'"."\n";

                                }
                            }
                        }
                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`WordOrg`,`Cat`,`Score`) VALUES ('$username','$id','$city','" . trim($decode->city) . "','کشور','$scoreCity')");
                        if($scoreCity==0 OR is_null($scoreCity)) {

                            $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`RoomID`,`Word`,`Cat`,`Score`) VALUES ('$username','$id','$decode->city','City','$scoreCity')");
                        }

                        $score = $scoreName + $scoreFamily + $scoreCar + $scoreFlower + $scoreColor + $scoreCity;
                        $this->DBA->Run("UPDATE `user_room` SET `Score`='$score' WHERE `RoomID`='$id' AND `UserID`='$username'");


                        $this->DBA->Shell("UPDATE `user_room` SET `Rank`=2 WHERE `UserID`='$username' AND `RoomID`='$id'");

//                            $statusRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `RoomID`='$id' AND `Status`=0");
                        $statusRoom = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id' AND `UserID` IN (SELECT `UserID` FROM `user_word` WHERE `RoomID`='$id')");
                        if ($this->DBA->Size($statusRoom)) {
                            $this->DBA->Run("UPDATE `user_room` SET `Rank`=2 WHERE `Rank`=0 AND `RoomID`='$id' AND `UserID` IN (SELECT `UserID` FROM `user_word` WHERE `RoomID`='$id')");
                        }


                        $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 200, "msg" => MSG_200)));

                        // send words of hanged user
                        $hangedUser=$this->DBA->Shell("SELECT `UserID`,TIMEDIFF(NOW(),`LastActivity`) FROM `user_room`
                                         WHERE TIMEDIFF(NOW(),`LastActivity`)>'00:02:00'
                                         AND  `RoomID`='$id'");
                        $hangedUser1=$this->DBA->Buffer($hangedUser);
                        echo "hanged user:\n";
                        var_dump($hangedUser1);
                        echo "\ncount:".count($hangedUser1);
                        if(COUNT($hangedUser1)>0){
                            foreach($hangedUser1 as $hangedUser2){
                                $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'اسم'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'گل'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $hangedUser2->UserID . "','$id',NULL,0,'کشور')");
                                $this->DBA->Run("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$hangedUser2->UserID' AND `RoomID`='$id'");
                                $this->DBA->Run("UPDATE `users` SET `UserStatus`='home' WHERE `Username`='$hangedUser2->UserID'");

                            }
                        }




                        // send ranking
                        $rankingStatus = $this->DBA->Shell("SELECT * FROM `user_room` WHERE `Rank`=0 AND `RoomID`='$id'");

                        echo "count rank:\n";
                        var_dump($this->DBA->Size($rankingStatus));
                        if(!$this->DBA->Size($rankingStatus)){
                            include __DIR__."/ranking.php";
                        }

                    } else {

                        // must validate other parameters


//                        if (
//                            isset($decode->name) AND
//                            isset($decode->family) AND
//                            isset($decode->car) AND
//                            isset($decode->animal) AND
//                            isset($decode->flower) AND
//                            isset($decode->color) AND
//                            isset($decode->city)
//                        ) {


                        if ($roomORG->Type == "HARD") {
                            $hard = 1;
                        }

                        $this->Check->logUserWord($username, $decode->name, 'Name', 'NULL');
                        $this->Check->logUserWord($username, $decode->family, 'Family', 'NULL');
                        $this->Check->logUserWord($username, $decode->car, 'Car', 'NULL');
                        $this->Check->logUserWord($username, $decode->animal, 'Animal', 'NULL');
                        $this->Check->logUserWord($username, $decode->flower, 'Flower', 'NULL');
                        $this->Check->logUserWord($username, $decode->color, 'Color', 'NULL');
                        $this->Check->logUserWord($username, $decode->city, 'City', 'NULL');

                        $name = $this->Check->filter($decode->name);
                        $family = $this->Check->filter($decode->family);
                        $car = $this->Check->filter($decode->car);
                        $animal = $this->Check->filter($decode->animal);
                        $flower = $this->Check->filter($decode->flower);
                        $color = $this->Check->filter($decode->color);
                        $city = $this->Check->filter($decode->city);

                        echo "user name: ".$decode->name."\n";
                        echo "user family: ".$decode->family."\n";
                        echo "user car: ".$decode->car."\n";
                        echo "user animal: ".$decode->animal."\n";
                        echo "user flower: ".$decode->flower."\n";
                        echo "user color: ".$decode->color."\n";
                        echo "user city: ".$decode->city."\n";

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
                        if ($this->Check->checkWord($Char, $Cat = 'Name', $Type)) {
                            $validNameMes = 1;
                            if ($this->Check->checkWordWithCat($name, $Char, $Cat = 'Name', $Type)) {
                                $validName = 1;
                                $validNameMes = 2;

                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'Family', $Type)) {
                            $validFamilyMes = 1;
                            if ($this->Check->checkWordWithCat($family, $Char, $Cat = 'Family', $Type)) {
                                $validFamilyMes = 2;
                                $validFamily = 1;
                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'Car', $Type)) {
                            $validCarMes = 1;
                            if ($this->Check->checkWordWithCat($car, $Char, $Cat = 'Car', $Type)) {
                                $validCarMes = 2;
                                $validCar = 1;
                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'Animal', $Type)) {
                            $validAnimalMes = 1;
                            if ($this->Check->checkWordWithCat($animal, $Char, $Cat = 'Animal', $Type)) {
                                $validAnimalMes = 2;
                                $validAnimal = 1;
                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'Flower', $Type)) {
                            $validFlowerMes = 1;
                            if ($this->Check->checkWordWithCat($flower, $Char, $Cat = 'Flower', $Type)) {
                                $validFlowerMes = 2;
                                $validFlower = 1;
                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'Color', $Type)) {
                            $validColorMes = 1;
                            if ($this->Check->checkWordWithCat($color, $Char, $Cat = 'Color', $Type)) {
                                $validColorMes = 2;
                                $validColor = 1;
                            }
                        }
                        if ($this->Check->checkWord($Char, $Cat = 'City', $Type)) {
                            $validCityMes = 1;
                            if ($this->Check->checkWordWithCat($city, $Char, $Cat = 'City', $Type)) {
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
                        if (($this->Check->countChar($Char) - 2) > 3) {
                            $unclear = 3;
                        }
                        echo "check name: ".$validName."\n";
                        echo "check family: ".$validFamily."\n";
                        echo "check car: ".$validCar."\n";
                        echo "check animal: ".$validAnimal."\n";
                        echo "check flower: ".$validFlower."\n";
                        echo "check color: ".$validColor."\n";
                        echo "check city: ".$validCity."\n";

//                                echo "count:\n";
//                                      var_dump($this->Check->countChar($Char));
//                                      var_dump($unclear);

//                                echo "check count: "."\n";
//                                      print_r($this->Check->countChar($Char)-$unclear);

//                                echo "\ncheck count user: ".($validCity + $validColor + $validFlower + $validAnimal + $validCar + $validFamily + $validName)."\n";

                        if (($validCity + $validColor + $validFlower + $validAnimal + $validCar + $validFamily + $validName) >= ($this->Check->countChar($Char)) - ($unclear)) {


//                                    $name = $decode->name;
//                                    $family = $decode->family;
//                                    $car = $decode->car;
//                                    $animal = $decode->animal;
//                                    $flower = $decode->flower;
//                                    $color = $decode->color;
//                                    $city = $decode->city;
//                                    echo "test1"."\n";
                            $this->DBA->Shell("UPDATE `user_room` SET `Status`='3',`Stop`='1' WHERE `RoomID`='$id' AND `UserID`='$username'");
                            echo "UPDATE `user_room` SET `Status`='3',`Stop`='1' WHERE `RoomID`='$id' AND `UserID`='$username'"."\n";
                            $this->DBA->Shell("UPDATE `rooms` SET `Status`='STOPPED',`StopDate`=NOW() WHERE `ID`='$id'");

                            $score = 0;
                            //Name
                            $scoreName = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Name', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->name), 'Name', $scoreName);


                            //Family
                            $scoreFamily = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Family', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->family), 'Family', $scoreFamily);


                            //Car
                            $scoreCar = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Car', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->car), 'Car', $scoreCar);


                            //Animal
                            $scoreAnimal = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Animal', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->animal), 'Animal', $scoreAnimal);


                            //Flower
                            $scoreFlower = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Flower', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->flower), 'Flower', $scoreFlower);


                            //Color
                            $scoreColor = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'Color', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->color), 'Color', $scoreColor);


                            //City
                            $scoreCity = 0;
                            if ($this->Check->checkWord($Char, $Cat = 'City', $Type)) {

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
                            $this->Check->logUserWord($username, trim($decode->city), 'City', $scoreCity);


                            $score = $scoreName + $scoreFamily + $scoreCar + $scoreFlower + $scoreColor + $scoreCity;
                            $this->DBA->Shell("UPDATE `user_room` SET `Score`='$score',`Rank`=2 WHERE `RoomID`='$id' AND `UserID`='$username'");

//                                    $this->DBA->Shell("UPDATE `user_room` SET `Rank`=2 WHERE `UserID`='$username' AND `RoomID`='$id'");




                            // response for token user
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 200, "msg" => MSG_200)));

                            // for each user you must send massage
                            $roomUser=$this->DBA->Shell("SELECT s1.ResourceID,s2.UserID FROM `socket_user` s1
                                                             JOIN `user_room` s2
                                                             ON s1.UserID=s2.UserID
                                                             WHERE s2.RoomID='$id' AND s2.UserID!='$username'");

                            $roomUser1=$this->DBA->Buffer($roomUser);
                            if(count($roomUser1)>0){
                                foreach($roomUser1 as $roomUser2){

                                    echo "\nuser online or not\n";
                                    var_dump(isset($roomUser2->ResourceID));
                                    if(isset($roomUser2->ResourceID)){
                                        $this->users[$roomUser2->ResourceID]->send(json_encode(array("command" => "stopGameResp","status" => 200, "msg" => MSG_200)));
                                    }else{
                                        $ranking = $this->DBA->Shell("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'اسم'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'گل'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $usernameNoRank . "','$id',NULL,0,'کشور')");
                                        $ranking = $this->DBA->Shell("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `RoomID`='$id'");
                                    }
                                }
                            }else{

                                // there is no one to send stop game
                                $offlineUser=$this->DBA->Shell("SELECT `UserID` FROM `user_room` WHERE `RoomID`='$id' AND `UserID` NOT IN (SELECT `UserID` FROM `socket_user`)");
                                $offlineUser1=$this->DBA->Buffer($offlineUser);
                                if(count($offlineUser1)>0){
                                    echo "\nstop offline users\n";
                                    foreach($offlineUser1 as $offlineUser2){
                                        $this->DBA->Run("UPDATE `user_room` SET `Status`=3,`Rank`=1 WHERE `UserID`='$offlineUser2->UserID' AND `RoomID`='$id'");
                                        $this->DBA->Run("INSERT INTO `user_word` (`UserID`,`RoomID`,`Word`,`Score`,`Cat`) VALUES
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'اسم'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'فامیل'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'ماشین'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'حیوان'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'گل'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'رنگ'),
                                                                    ('" . $offlineUser2->UserID . "','$id',NULL,0,'کشور')");
                                    }
                                }
                            }


                        } else {
                            // function to create message
                            $messageNotValid = $this->Check->messageGen($messObj);
                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 235, "msg" => $messageNotValid)));

                        }
//                        } else {
//                            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 232, "msg" => MSG_232)));
//                        }
                    }
                } else {
                    $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 110, "msg" => MSG_110)));
                }

            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 107, "msg" => MSG_107)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 109, "msg" => MSG_109)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 100, "msg" => $e->getMessage())));
    }

} else {
    $this->users[$from->resourceId]->send(json_encode(array("command"=>"stopGameResp","status" => 102, "msg" => MSG_102)));
}

/*
function familyValidate($input)
{
    $newInput = explode($input, " ");

}*/
echo "\n---------------------------------------------------------------------------------\n";
