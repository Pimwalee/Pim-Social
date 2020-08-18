<?php

ob_start();//it saves php data when it loaded, pass all php code to browser all at once
//session_start();
if(!isset($_SESSION)) 
    { 
        session_start(); 
    }

$timezone = date_default_timezone_set("America/Los_Angeles");

try {
    $con = new PDO("mysql:dbname=pimsocial;host=localhost", "root", ""); 
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);//show err and stop executing
}
catch(PDOExeption $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>