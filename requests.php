<?php
include("includes/header.php");
?>

<div class="main_column column" id="main_column">
    <h4>Friend Requests</h4>

    <?php

    $query = $con->query("SELECT * FROM friend_requests WHERE user_to='$userLoggedIn'");
    if($query->rowCount() == 0)
        echo "You have no friend requests at this time";
    else {

        while($row = $query->fetch(PDO::FETCH_BOTH)) {
            $user_from = $row['user_from'];
            $user_from_obj = new User($con, $user_from);

            echo $user_from_obj->getFirstAndLastName() . " sent you a friend request!";

            $user_from_friend_array = $user_from_obj->getFriendArray();

            if(isset($_POST['accept_request' . $user_from])) {
                $add_friend_query = $con->query("UPDATE users set friend_array=CONCAT(friend_array, '$user_from,')WHERE username='$userLoggedIn'");
                $add_friend_query = $con->query("UPDATE users set friend_array=CONCAT(friend_array, '$userLoggedIn,')WHERE username='$user_from'");

                $delete_query = $con->query("DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "You are now friends!";
                header("Location:requests.php");
            }
            if(isset($_POST['ignore_request' . $user_from])) {
                $delete_query = $con->query("DELETE FROM friend_requests WHERE user_to='$userLoggedIn' AND user_from='$user_from'");
                echo "Request ignore!";
                header("Location:requests.php");
            }
            ?>
            <form action="requests.php" method="POST">
                <input type="submit" name="accept_request<?php echo $user_from;?>" id="accept_button" value="Accept">
                <input type="submit" name="ignore_request<?php echo $user_from;?>" id="ignore_button" value="Ignore">
            </form>
            <?php
        }
    }

    ?>
    
    

</div>