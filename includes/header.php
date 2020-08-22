<?php 
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");

if (isset($_SESSION['username'])) {
    $userLoggedIn = $_SESSION['username'];
    $user_details_query = $con->query("SELECT * FROM users WHERE username='$userLoggedIn'");
    $user = $user_details_query->fetch(PDO::FETCH_BOTH);
}
else {
    header("Location: register.php");
}

?>

<html>
<head>
    <title>PimCial</title>

    <!-- JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="assets/js/bootstrap.js"> </script>
    <script src="assets/js/bootbox/bootbox.min.js"></script>
    <script src="assets/js/pimcial.js"> </script>
	<script src="assets/js/jquery.Jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>
    
    
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />


</head>
<body>

    <div class="top_bar">
        <div class ="logo">
            <a href="index.php">PimCial!</a>
            <!-- <img src="assset/images/00000">  if we want to put img logo-->
        </div>

            <nav>
                <a href="<?php echo $userLoggedIn; ?>"><?php echo $user['first_name']?></i></a>
                <a href="index.php"><i class="fa fa-home"></i></a>
                <a href="#"><i class="fa fa-envelope"></i></a>
                <a href="#"><i class="fa fa-bell"></i></a>
                <a href="requests.php"><i class="fa fa-users"></i></a>
                <a href="upload.php"><i class="fa fa-cog"></i></a>
                <a href="includes/handlers/logout.php"><i class="fa fa-sign-out"></i></a>
            </nav>
    </div>

    <div class="wrapper">
    