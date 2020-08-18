<html>
<head>
    <title></title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

    <style type="text/css">
    * {
        font-family: Arial, Helvetica, Sans-serif;
    }
    body {
        background-color:#fff;
    }
    form {
    top: -1;
    position: absolute;
}
    </style>


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

            //Get id of post
        if(isset($_GET['post_id'])) {
            $post_id = $_GET['post_id'];
        }

        $get_likes = $con->query("SELECT likes, added_by FROM posts WHERE id='$post_id'");
        $row = $get_likes->fetch(PDO::FETCH_BOTH);
        $total_likes = $row['likes']; //number of likes
        $user_liked = $row['added_by']; //who posted this post

        $user_details_query = $con->query("SELECT * FROM users WHERE username='$user_liked'"); //get all infor about who posted it
        $row = $user_details_query->fetch(PDO::FETCH_BOTH);
        $total_user_likes = $row['num_likes'];

        //Like button
        if(isset($_POST['like_button'])) {
            $total_likes++;
            $query = $con->query("UPDATE posts SET likes='$total_likes' WHERE id='$post_id'");
            $total_user_likes++;
            $user_likes = $con->query("UPDATE users SET num_likes='$total_user_likes' WHERE username='$user_liked'");
            $insert_user = $con->query("INSERT INTO likes VALUES(NULL, '$userLoggedIn', '$post_id')");

            //Insert Notification
        }
        //Unlike button
        if(isset($_POST['unlike_button'])) {
            $total_likes--;
            $query = $con->query("UPDATE posts SET likes='$total_likes' WHERE id='$post_id'");
            $total_user_likes--;
            $user_likes = $con->query("UPDATE users SET num_likes='$total_user_likes' WHERE username='$user_liked'");
            $insert_user = $con->query("DELETE FROM likes WHERE username='$userLoggedIn'AND post_id='$post_id'");

        }

        //Check for previous likes
        $check_query = $con->query("SELECT * FROM likes WHERE username='$userLoggedIn' AND post_id='$post_id'");
        $num_row = $check_query->rowCount();

        if($num_row > 0) { //more than 0 cos been liked before
            echo '<form action="like.php?post_id=' . $post_id . '" method="POST">
                    <input type="submit" class="comment_like" name="unlike_button" value="Unlike">
                    <div class="like_value">
                    '. $total_likes . ' Likes
                    </div>
                </form>
            ';
        }
        else {
            echo '<form action="like.php?post_id=' . $post_id . '" method="POST">
                    <input type="submit" class="comment_like" name="like_button" value="Like">
                    <div class="like_value">
                    '. $total_likes . ' Likes
                    </div>
                </form>
            ';
        }

    ?>

    
</body>
</html>