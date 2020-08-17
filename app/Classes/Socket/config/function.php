<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// class for functions

class GameSmoothy
{


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