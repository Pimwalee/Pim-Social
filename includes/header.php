<?php 
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Notification.php");


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
            <!-- <a href="index.php">
            <img src="assets/images/logo/pimcial_logo2.png">
            </a> -->
             <!-- if we want to put img logo -->
        </div>

        <div class="search">
            <form action="search.php" method="GET" name="search_form">
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search..." autocomplete="off" id="search_text_input">

                <div class="button_holder">
                    <img src="assets/images/icons/search_icon.png">
                </div>

            </form>

            <div class="search_results">
            </div>

            <div class="search_results_footer_empty">
            </div>


        </div>


            <nav>
                <?php 
                    //Unread messages
                    $messages = new Message($con, $userLoggedIn);
                    $num_messages = $messages->getUnreadNumber();

                    //Unread notifications
                    $notifications = new Notification($con, $userLoggedIn);
                    $num_notifications = $notifications->getUnreadNumber();

                    //Unread friend requests
                    $user_obj = new User($con, $userLoggedIn);
                    $num_requests = $user_obj->getNumberOfFriendRequests();
                ?>

                <a href="<?php echo $userLoggedIn; ?>">
                <?php echo $user['first_name']?></i>
                </a>
                <a href="index.php">
                    <i class="fa fa-home"></i>
                </a>
                <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn;?>', 'message')">
                <i class="fa fa-envelope"></i>
                <?php
                if($num_messages > 0)
                    echo '<span class="notification_badge" id="unread_message">' . $num_messages .'</span>';
                ?>
                </a>
                <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn;?>', 'notification')">
                    <i class="fa fa-bell"></i>
                <?php
                if($num_notifications > 0)
                    echo '<span class="notification_badge" id="unread_notification">' . $num_notifications . '</span>';
                ?>
                </a>
                <a href="requests.php"><i class="fa fa-users"></i>
                <?php
                if($num_requests > 0)
                    echo '<span class="notification_badge" id="unread_requests">' . $num_requests .'</span>';
                ?>
                </a>
                <a href="settings.php"><i class="fa fa-cog"></i></a>
                <a href="includes/handlers/logout.php"><i class="fa fa-sign-out"></i></a>
            </nav>

            <div class="dropdown_data_window" style="height:0px; border:none;"></div>
            <input type="hidden" id="dropdown_data_type" value="">

    </div>


    <script>
    var userLoggedIn = '<?php echo $userLoggedIn?>';

    $(document).ready(function() {


        $('.dropdown_data_window').scroll(function() { //to find out if it is at the bottom of the page or not
            var inner_height = $('.dropdown_data_window').innerHeight(); //Div containing posts
            var scroll_top = $('.dropdown_data_window').scrollTop(); //Contain the top of the page where you are loaded
            var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
            var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

            if((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                var pageName; //Holds name of page to send to ajax request to
                var type = $('#dropdown_data_type').val();

                if(type == 'notification')
                    pageName = "ajax_load_notifications.php"
                    else if(type == 'message')
                    pageName = "ajax_load_messages.php"

                var ajaxReq = $.ajax({
                    url:"includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                    cache:false,

                    success: function(response) {
                        $('.dropdown_data_window').find('.nextPageDropdownData').remove();//Remove current .nextpage
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove();

                        $('.dropdown_data_window').append(response); // add the new post to the existing post
                    }
                 });

            }//End if
            return false;
        }); //End $(window).scroll(function()

    });

    </script>


    <div class="wrapper">
    