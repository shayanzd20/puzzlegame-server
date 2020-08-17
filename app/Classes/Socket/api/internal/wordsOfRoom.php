<?php
//this api is about to get random words of room char

echo "get random words of room char:\n\n";
use \Firebase\JWT\JWT;

$key = DECODE_KEY;


// we have to get http header token
if (isset($decode->token)
) {
    $token = $decode->token;

    try {
        $decoded = JWT::decode($token, $key, array('HS256'));

        $User = $this->DBA->Shell("SELECT * FROM `users` WHERE `Number`='$decoded->number' AND `RegisterDate`='$decoded->date'");
        if ($this->DBA->Size($User)) {
            $Valid = $this->DBA->Load($User);
            $ValidToken = $Valid->Token;
            $username = $Valid->Username;
            $userCredit = $Valid->Credit;

            // then we need to validate with token user
            if ($token == $ValidToken) {

                if (isset($decode->char)) {
                    // add item to cart
                    $char = $decode->char;

                    $limit = rand(5, 7);
                    // check item is exist or not
                    $wordsOfRoom = $this->DBA->Shell("SELECT `WordOrg`,`Word`,`Cat` FROM `words` WHERE `Char`='" . $char . "' GROUP BY `Cat` LIMIT " . $limit);
                    echo "\n" . "SELECT `Word`,`Cat` FROM `words` WHERE `Char`='" . $char . "' GROUP BY `Cat` LIMIT " . $limit . "\n";
                    $wordsOfRoom1 = $this->DBA->Buffer($wordsOfRoom);

                    echo "count: " . COUNT($wordsOfRoom1);
                    if (COUNT($wordsOfRoom1) > 0) {

                        $name=NULL;
                        $family=NULL;
                        $car=NULL;
                        $animal=NULL;
                        $flower=NULL;
                        $color=NULL;
                        $city=NULL;

                        foreach ($wordsOfRoom1 as $wordsOfRoom2) {
                            if ($wordsOfRoom2->Cat == "Name") {
                                $name=$wordsOfRoom2->WordOrg;
                            }
                            if ($wordsOfRoom2->Cat == "Family") {
                                $family=$wordsOfRoom2->WordOrg;

                            }
                            if ($wordsOfRoom2->Cat == "Animal") {
                                $animal=$wordsOfRoom2->WordOrg;

                            }
                            if ($wordsOfRoom2->Cat == "Color") {
                                $color=$wordsOfRoom2->WordOrg;

                            }
                            if ($wordsOfRoom2->Cat == "City") {
                                $city=$wordsOfRoom2->WordOrg;

                            }
                            if ($wordsOfRoom2->Cat == "Car") {
                                $car=$wordsOfRoom2->WordOrg;

                            }
                            if ($wordsOfRoom2->Cat == "Flower") {
                                $flower=$wordsOfRoom2->WordOrg;

                            }
                        }

                        print_r($wordsOfRoom1);

                        $char = $decode->char;
                        $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp",
                            "status" => 200,
                            "msg" => MSG_200,
                            "id" => $decode->id,
                            "name" => $name,
                            "family" => $family,
                            "car" => $car,
                            "animal" => $animal,
                            "flower" => $flower,
                            "color" => $color,
                            "city" => $city
                        )));


                    } else {
                        $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp", "status" => 150, "msg" => MSG_150)));
                    }
                }
            } else {
                $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp", "status" => 203, "msg" => MSG_203)));
            }
        } else {
            $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp", "status" => 209, "msg" => MSG_209)));
        }
    } catch (Exception $e) {
        $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp", "status" => 500, "msg" => $e->getMessage())));
    }
} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "wordsOfRoomResp", "status" => 102, "msg" => MSG_102)));
}

echo "\n---------------------------------------------------------------------------------\n";
