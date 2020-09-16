<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Message.php");

$limit = 7; //Number of message to load

//$_REQUEST comes from ajax data: call on pimcial.js
$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);
?>

