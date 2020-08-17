<?php
include('sms.class.php');


$sms=new sendSMS;
$sms->user='09367826359'; //your UserName
$sms->pass='G0jeS'; //your Password


//GroupSms
$mobiles='09366360042';   //ba camma (,) joda shavad
$message='salam';
$send_number='30006179'; //Your SenderNumber
$sendOn=null; /* Null For Send Now Else in this format 
$datetime = new DateTime('2010-12-30 23:21:46');
$sendOn=$datetime->format('c');*/
$sendType=1;
$yourMessageIds='Your Message Ids';//ba camma (,) joda shavad
$send=$sms->GroupSms($message,$mobiles,$send_number,$sendOn,$sendType,$yourMessageIds);
$obj=json_decode($send); 
if($obj->Status==1) //successfull
{
    print_r($obj->NikIds);    
}
else{
    echo 'مراجعه شود به http://niksms.com/fa/Main/Api/HttpApiStatusCode#/groupSms';  
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


//Ptp
$mobiles='YourMobiles';   //ba camma (,) joda shavad
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

//GetCredit
$obj=$sms->GetCredit();
$credit = json_decode($obj);
echo $credit;

//GetDiscountCredit
$obj=$sms->GetDiscountCredit();
$discountCredit = json_decode($obj);
echo $discountCredit;

//GetPanelExpireDate
$obj=$sms->GetPanelExpireDate();
$panelExpireTime = json_decode($obj);
echo $panelExpireTime;

//GetReceiveSms
$startDate=null; /* Null For All Time Else in this format 
$datetime = new DateTime('2010-12-30 23:21:46');
$startDate=$datetime->format('c');*/
$endDate=null; /* Null For All Time Else in this format 
$datetime = new DateTime('2010-12-30 23:21:46');
$endDate=$datetime->format('c');*/
$obj=$sms->GetReceiveSms($startDate,$endDate);
$messages = json_decode($obj);
foreach($messages as $message)
{
    print_r($message);
}

//ResetReceiveSmsVisitedStatus
$startDate=null; /* Null For All Time Else in this format 
$datetime = new DateTime('2010-12-30 23:21:46');
$startDate=$datetime->format('c');*/
$endDate=null; /* Null For All Time Else in this format 
$datetime = new DateTime('2010-12-30 23:21:46');
$endDate=$datetime->format('c');*/
$obj=$sms->ResetReceiveSmsVisitedStatus($startDate,$endDate);
$result = json_decode($obj);
echo $result;


//GetSmsDelivery
$tctCode='YourNikIds'; //ba camma (,) joda shavad
$obj=$sms->GetSmsDelivery($tctCode);
$results = json_decode($obj);
foreach($results as $result)
{
    if($result==3)
    {
        echo 'Sent';
    }
    else{
        echo 'مراجعه شود به http://niksms.com/fa/Main/Api/HttpApiStatusCode#/getSmsDelivery';
        /*
        NotFound = 0
        DoNotSend = 1
        InQueue = 2
        Sent = 3
        InsufficientCredit = 4
        Block = 6
        NotDeliverdSmsAdvertisingBlock = 9
        NotDeliverdBlackList = 10
        NotDeliverdDelay = 11
        NotDeliverdCanceled = 8
        NotDeliverdNoViber = 13
        NotDeliverdFiltering = 14
        WaitingForRecheckInOprator=15
        OpratorFault=16
        NotDeliveredBlocked = 17
        SendedButStatusNotUpdated = 18
        NotDeliveredDuplicate = 19
        NotDeliveredBlockPanel = 20
        */
    }
}
?>
