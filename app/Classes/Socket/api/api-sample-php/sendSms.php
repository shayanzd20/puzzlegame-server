<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('sms.class.php');


$sms=new sendSMS;
$mobiles='09366360042';   //ba camma (,) joda shavad
$message='YourMessage';   //ba camma (,) joda shavad
$send_number='30006179'; //Your SenderNumber
$sendOn=null; /* Null For Send Now Else in this format
$datetime = new DateTime('2010-12-30 23:21:46');
$sendOn=$datetime->format('c');*/
$sendType=1;
$yourMessageIds='Your Message Ids';//ba camma (,) joda shavad
$send=$sms->PtpSms($message,$mobiles,$send_number,$sendOn,$sendType,$yourMessageIds);
$obj=json_decode($send);
if($obj->Status==1) //successfull
{
    print_r($obj->NikIds);
}
else{
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
