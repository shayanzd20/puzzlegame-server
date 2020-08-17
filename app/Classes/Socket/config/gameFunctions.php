<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// class for functions

class Game
{

    public $DBA;

    function __construct()
    {
        $this->DBA = new Terminal("balootmo_smoothy");
    }

    function checkWord($char, $cat, $type=NULL)
    {
        // to validate cat with char
//        if ($type == 'Hard') {
//            $anyWord = $this->DBA->Shell("SELECT * FROM `words` WHERE SUBSTR(`Word`,-1,1)='$char' AND `Cat`='$cat' LIMIT 1 ");
//
//        } else {
            $anyWord = $this->DBA->Shell("SELECT * FROM `words` WHERE `Char`='$char' AND `Cat`='$cat' LIMIT 1 ");
//            echo "SELECT * FROM `words` WHERE `Char`='$char' AND `Cat`='$cat' LIMIT 1 "."\n";

//        }
        if ($this->DBA->Size($anyWord)) {
            return true;
        }
        return false;
    }

    function checkWordWithCat($word, $char, $cat, $type=NULL)
    {
        // to validate cat with char
//        if ($type == 'Hard') {
//            $anyWord = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$word' AND SUBSTR(`Word`,-1,1)='$char' AND `Cat`='$cat' LIMIT 1 ");
//        } else {
            $anyWord = $this->DBA->Shell("SELECT * FROM `words` WHERE `Word`='$word' AND`Char`='$char' AND `Cat`='$cat' LIMIT 1 ");
//        }
        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`Word`,`Cat`) VALUES ('$word','$cat')");

//        echo "SELECT * FROM `words` WHERE `Word`='$word' AND`Char`='$char' AND `Cat`='$cat' LIMIT 1 \n";
        if ($this->DBA->Size($anyWord)) {
            return true;
        }
        return false;
    }

    function logUserWord($user,$word, $cat,$score)
    {
        // to validate cat with char
        $this->DBA->Run("INSERT IGNORE INTO `user_word_log` (`UserID`,`Word`,`Cat`,`Score`) VALUES ('$user','$word','$cat','$score')");
        return true;

    }

    function countChar($char)
    {
        // to validate cat with char
        $anyWord = $this->DBA->Shell("SELECT * FROM `words` WHERE `Char`='$char'GROUP BY Cat");
        return $this->DBA->Size($anyWord);

    }

    // generating message
    // input (object)
    function messageGen($obj)
    {
//        var_dump($obj);

        $message="کلمات ";
        if($obj->name==1){
            $message.="اسم و";
        }
        if($obj->family==1){
            $message.=" فامیل و";
        }
        if($obj->car==1){
            $message.=" ماشین و";
        }
        if($obj->animal==1){
            $message.=" حیوان و";
        }
        if($obj->flower==1){
            $message.=" گل و";
        }
        if($obj->color==1){
            $message.=" رنگ و";
        }
        if($obj->city==1){
            $message.=" کشور و";
        }
        echo "this is before message:".$message."\n";

        $message = substr($message, 0, -2);
        $message.=" مناسب نمی باشد";
        echo "this is after message:".$message."\n";
        return $message;

    }

    function filter($string)
    {
//        var_dump($string);
        $persianNumber = array('۰', '١', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $num = range(0, 9);
        $string = str_replace($persianNumber, $num, $string);

        $arabicNumber = array('۰', '۱', '۲', '۳', '٤', '٥', '٦', '۷', '۸', '۹');
        $numEng = range(0, 9);
        $string = str_replace($arabicNumber, $numEng, $string);

        $persian = array('ه', 'ا', 'ا', 'ه', 'ی', 'و', 'ی', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ک');
        $arabic = array('ۀ', 'أ', 'إ', 'ة', 'ي', 'ؤ', 'ئ', 'ً', 'ٌ', 'ٍ', 'َ', 'ُ', 'ِ', 'ّ', '', '|', 'ء', '<', '>', '؟', '«', '»', ':', '"', '|', '{', '}', '\"', '،', ')', '(', 'ك');
        $string = str_replace($arabic, $persian, $string);
        $string = preg_replace("/[^آ ا ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع  غ ف ق ک گ ل م ن و ه ی]/", '', $string);
        $string = str_replace(" ", "", $string);
        $string = trim($string);

        return $string;

    }

    function filter_with_space($string)
    {
        $persianNumber = array('۰', '١', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $num = range(0, 9);
        $string = str_replace($persianNumber, $num, $string);

        $arabicNumber = array('۰', '۱', '۲', '۳', '٤', '٥', '٦', '۷', '۸', '۹');
        $numEng = range(0, 9);
        $string = str_replace($arabicNumber, $numEng, $string);

        $persian = array('ه', 'ا', 'ا', 'ه', 'ی', 'و', 'ی', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ک');
        $arabic = array('ۀ', 'أ', 'إ', 'ة', 'ي', 'ؤ', 'ئ', 'ً', 'ٌ', 'ٍ', 'َ', 'ُ', 'ِ', 'ّ', '', '|', 'ء', '<', '>', '؟', '«', '»', ':', '"', '|', '{', '}', '\"', '،', ')', '(', 'ك');
        $string = str_replace($arabic, $persian, $string);
        $string = preg_replace("/[^آ ا ب پ ت ث ج چ ح خ د ذ ر ز ژ س ش ص ض ط ظ ع  غ ف ق ک گ ل م ن و ه ی]/", '', $string);
//        $string= str_replace(" ","", $string);
        $string = trim($string);

        return $string;

    }

    function find_key_in_obj($value,$arrayOfObject){
        $item = null;
        $i=0;
//        var_dump($arrayOfObject);
        foreach($arrayOfObject as $struct) {
//            print_r($struct->Cat);
            if ($value == $struct->Cat) {
                $item = $i;
            }
            $i++;
        }
        return $item;

    }
}