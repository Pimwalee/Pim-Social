<?php 
    require 'config/config.php';
    include("includes/classes/User.php");
    include("includes/classes/Post.php");
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
    <title></title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

    <style type="text/css">
    * {
        font-size:13px;
        font-family:Helvetica,Sans-serif,Arial;
    }
    </style>

    

    <script> 
        function toggle() {
            var element = document.getElementById("comment_section");

            if(element.style.display == "block"){ //if comment is showing
                element.style.display = "none";//hide it
            } else {
                element.style.display = "block";//if comment is hidden show it
        }
    }
    </script>

    <?php
    //Get id of post
    if(isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];
    }

    $user_query = $con->query("SELECT added_by, user_to FROM posts WHERE id='$post_id'");
    $row = $user_query->fetch(PDO::FETCH_BOTH);
    $posted_to = $row['added_by'];
    $user_to = $row['user_to'];

    if(isset($_POST['postComment' . $post_id])) {
        $post_body = $_POST['post_body'];
        $date_time_now = date("Y-m-d H:i:s");
        $query = $con->prepare("INSERT INTO comments (id, post_body, posted_by, posted_to, date_added, removed,post_id) VALUES (NULL, :post_body, :posted_by, :posted_to, :date_added, :removed, :post_id)");
        $query->bindValue(':post_body', $post_body);
        $query->bindValue(':posted_by', $userLoggedIn);
        $query->bindValue(':posted_to', $posted_to);
        $query->bindValue(':date_added', $date_time_now);
        $query->bindValue(':removed', 'no');
        $query->bindValue(':post_id', $post_id);
        $query->execute();

        if($posted_to != $userLoggedIn) {
            $notification = new Notification($con, $userLoggedIn);
            $notification->insertNotification($post_id, $posted_to, "comment");
        }
        if($user_to != 'none' && $user_to != $userLoggedIn) {
            $notification = new Notification($con, $userLoggedIn);
            $notification->insertNotification($post_id, $user_to, "profile_comment");
        }

        $get_commenters = $con->query("SELECT * FROM comments WHERE post_id='$post_id'");
        $notified_users = array();
        while($row = $get_commenters->fetch(PDO::FETCH_BOTH)) {

            if($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to
            && $row['posted_by'] != $userLoggedIn && !in_array($row['posted_by'], $notified_users)){
                
                $notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

                array_push($notified_users,$row['posted_by']);
            }
        }
        /*Don't notify:
        - the person who posted this comment is not the one who posted the original
        - && when we comment on something(we don't notify ourself)
        - && check if the username that comment is not already in $notified_users,$row['posted_by'] so we won't get many notificatons
        */

        echo "<p>Comment Posted!</p>";
    
    }

    ?>
    <form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id;?>" method="POST">
    <textarea name="post_body"></textarea>
    <input type="submit" name="postComment<?php echo $post_id;?>" value ="Post">
    </form>

    <!--Load comments-->
    <?php
    $get_comments = $con->query("SELECT * FROM comments WHERE post_id='$post_id' ORDER BY id ASC");
    $count = $get_comments->rowCount();

    if($count != 0) {

        while($comment = $get_comments->fetch(PDO::FETCH_BOTH)) {

            $comment_body = $comment['post_body'];
            $posted_to = $comment['posted_to'];
            $posted_by = $comment['posted_by'];
            $date_added = $comment['date_added'];
            $removed = $comment['removed'];


            //Timeframe
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($date_added); //Time of post
            $end_date = new DateTime($date_time_now); //Current time
            $interval = $start_date->diff($end_date); //Different between dates
            if($interval->y >=1) { //one year or over
                if($interval == 1)
                    $time_message = $interval->y . " year ago"; // one year ago
                else
                    $time_message = $interval->y . " years ago"; // one+ year ago
            }
            else if ($interval->m >= 1){// one month or more
                if($interval->d == 0) {
                    $days = " ago";
                }
                else if($interval->d == 1) {
                    $days = $interval->d . " day ago";
                }
                else {
                    $days = $interval->d . " days ago";
                }

                if($interval->m == 1) {
                    $time_message = $interval->m . " month" . $days;
                }
                else {
                    $time_message = $interval->m . " months" . $days;
                }

            }
            else if($interval->d >= 1) { // more than one day
                if($interval->d == 1) {
                    $time_message = "Yesterday";
                }
                else{
                    $time_message = $interval->d . " days ago";
                }
            }
            else if($interval->h >=1) {
                if($interval->h == 1) {
                    $time_message = $interval->h . " hour ago";
                }
                else {
                    $time_message = $interval->h . " hours ago";
                }
            }
            else if($interval->i >=1) {
                if($interval->i == 1) {
                    $time_message = $interval->i . " minute ago";
                }
                else {
                    $time_message = $interval->i . " minutes ago";
                }
            }
            else {
                if($interval->s < 30) {
                    $time_message ="Just now";
                }
                else {
                    $time_message = $interval->s . " seconds ago";
                }
            }

            $user_obj = new User($con, $posted_by);

            ?>
            <div class="comment_session">
                <a href="<?php echo $posted_by?>" target="_parent"><img src="<?php echo $user_obj->getProfilePic();?>" title="<?php echo $posted_by; ?>" style="float:left;" height="30"></a>
                <a href="<?php echo $posted_by?>" target="_parent"> <b> <?php echo $user_obj->getFirstAndLastName();?> </b></a>
                &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $time_message . "<br>" . $comment_body; ?>
                <hr>
            </div>
            <?php

        }
    }
    else {
        echo "<center><br><br>No comments to show!</center>";
    }


            ?>


</body>
</html>