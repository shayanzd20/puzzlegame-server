<?php
date_default_timezone_set("Asia/Tehran");
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: image/jpg");

if (isset($_GET['title'])) {
    $title = $_GET['title'];
    $image = __DIR__ . "/../shoppingItems/".$title.".jpg";

} else {

        $image = __DIR__ . "/../shoppingItems/default_cart.jpg";
}

readfile($image);

