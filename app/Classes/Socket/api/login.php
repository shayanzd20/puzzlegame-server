<?php
echo "\n--------------------------------START login API-----------------------------------\n";



if (isset($decode->number) AND
    $decode->number != "" AND
    strlen($decode->number) == 11 AND
    substr($decode->number, 0, 2) == '09'
    ) {

    $number = $decode->number;
    $verification = $this->DBA->Shell("SELECT `ID` FROM `verification` WHERE `Number`='" . $number . "'");
    $code = rand(10000, 99999);
    if ($verification AND $this->DBA->Size($verification)) {
        $this->DBA->Shell("DELETE FROM `verification` WHERE `Number`='" . $number . "')");
    }
        if(
            // MTN
            substr($decode->number, 0, 4) == '0901' OR
            substr($decode->number, 0, 4) == '0902' OR
            substr($decode->number, 0, 4) == '0903' OR
            substr($decode->number, 0, 4) == '0930' OR
            substr($decode->number, 0, 4) == '0933' OR
            substr($decode->number, 0, 4) == '0935' OR
            substr($decode->number, 0, 4) == '0936' OR
            substr($decode->number, 0, 4) == '0937' OR
            substr($decode->number, 0, 4) == '0938' OR

            // RighTell
            substr($decode->number, 0, 4) == '0920' OR
            substr($decode->number, 0, 4) == '0921' OR

            // Talia
            substr($decode->number, 0, 4) == '0932'
        ){
//            include __DIR__ . "/api-sample-php/sms.class.php";
            $sms = new sendSMS();

            $sendType = 1;
            $yourMessageIds = rand(100000, 999999);
            $mobiles = $number;   //ba camma (,) joda shavad
            $message = 'کد فعالسازی اپلیکیشن اسموتی:' . "\n";   //ba camma (,) joda shavad
            $message .= $code;   //ba camma (,) joda shavad
            $send_number = '30006179'; //Your SenderNumber
            $sendOn = null;
            $send = $sms->PtpSms($message, $mobiles, $send_number, $sendOn, $sendType, $yourMessageIds);
            $sendStatus='2';
            $obj = json_decode($send);
            if ($obj->Status == 1) //successfull
            {
        //            print_r($obj->NikIds);
            } else {
                echo 'مراجعه شود به http://niksms.com/fa/Main/Api/HttpApiStatusCode#/PtpSms';
                /*
                Successful = 1
                UnknownError = 2
                InsufficientCredit = 3
                ForbiddenHours = 4
                Filtered = 5
                NoFilters = 6
                PrivateNumberIsDisable = 7
                ArgumentIsNullOrIncorrect = 8
                MessageBodyIsNullOrEmpty = 9
                PrivateNumberIsIncorrect = 10
                ReceptionNumberIsIncorrect = 11
                SentTypeIsIncorrect = 12
                Warning = 13
                PanelIsBlocked = 14
                SiteUpdating = 15
                */
            }

        }else{
            $sendStatus='2';

            // send MCI Validation code
            // Get cURL resource
            $curl = curl_init();
            // Set some options - we are passing in a useragent too here
            curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'http://188.253.3.219/vCore/API/Payment/validationCode.php',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                'shortcode' => '98307817',
                'number' => "98".substr($number,1,10),
                'code' => $code
            )
            ));
            // Send the request & save response to $resp
            $resp = curl_exec($curl);

            if (curl_errno($curl)) {
                print "Error: " . curl_error($curl);
            } else {
                // Show me the result
                var_dump($resp);
                // Close request to clear up some resources
                curl_close($curl);
            }

        }

        // generate randome code
        $this->DBA->Run("INSERT INTO `verification` (`Number`,`Code`,`Status`) VALUES ('" . $number . "','$code','$sendStatus')
            ON DUPLICATE KEY
            UPDATE `Code`='$code',`Status`='$sendStatus',`Date`= NOW();");

        // send SMS
        $this->users[$from->resourceId]->send(json_encode(array("command" => "loginResp","status" => 200,"msg"=>MSG_200)));

        // telegram report
        $userStatus = $this->DBA->Shell("SELECT `Username` FROM `users` WHERE `Number`='" . $number . "'");
        if($this->DBA->Size($userStatus)){
            $userStatus1=$this->DBA->Load($userStatus)->Username;
            $msg=$code." - generated for (".$number.") as ".$userStatus1;
        }else{
            $msg=$code." - generated for (".$number.")";
        }
        $this->teleBot->sendMsgToAdmins($msg);


} else {
    $this->users[$from->resourceId]->send(json_encode(array("command" => "loginResp","status" => 102, "msg" => MSG_102)));
}
echo "\n--------------------------------END login API-----------------------------------\n";
