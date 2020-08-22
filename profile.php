<?php
include("includes/header.php");


if(isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = $con->query("SELECT * FROM users WHERE username='$username'");
    $user_array = $user_details_query->fetch(PDO::FETCH_BOTH);

    $num_friends = (substr_count($user_array['friend_array'],",")) - 1;
    //-1 cos we don't count the first comma ex ,pim_walee,momo_lemur,appa_yipyip, = 3
}

if(isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
}
if(isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}
if(isset($_POST['respond_request'])) {
   header("Location: request.php");
}

?>

    <style type="text/css">
    .wrapper {
        margin-left:0px;
        padding-left:0px;
    }
    </style>
    
    <div class="profile_left">
        <img src="<?php echo $user_array['profile_pic'];?>">

        <div class="profile_info">
            <p> <?php echo "Posts : " . $user_array['num_posts'];?> </p>
            <p> <?php echo "Likes : " . $user_array['num_likes'];?> </p>
            <p> <?php echo "Friends : " . $num_friends?> </p>
        </div>

        <form action="<?php echo $username; ?>" method="POST">
            <?php 
            $profile_user_obj = new User($con ,$username);
            if($profile_user_obj->isClosed()) {
                header("Location: user_closed.php");
            }

            $logged_in_user_obj = new User($con ,$userLoggedIn);

            if($userLoggedIn != $username) { //means loggedin person is not on his/her profile so we will show Addfriend button
                if($logged_in_user_obj->isFriend($username)) {
                    echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
                }
                else if ($logged_in_user_obj->didReceiveRequest($username)) {
                    echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
                }
                else if ($logged_in_user_obj->didSendRequest($username)) {
                    echo '<input type="submit" name="" class="default" value="Respond Sent"><br>';
                }
                else
                    echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
            }

            ?>
        </form>

        <input type="submit" class="deep_gray" data-toggle="modal" data-target="#post_form" value="Post Something">

        <?php
        if($userLoggedIn != $username) { //if we are not on our profile echo this
            echo '<div class="profile_info_bottom">';
                echo $logged_in_user_obj->getMutualFriend($username) . " Mutual friends";
                echo '</div>';
        }

        ?>

    </div>

    <div class="profile_main_column column">
        <!-- <?php echo "This is " . $username . " profile!";?> -->
        <div class="posts_area"></div>
        <img id="loading" src="assets/images/icons/Loading_icon.gif">
    </div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Post Something</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
            <p>This will appear on the user's profile page and also their newsfeed for your friends to see!</p>
            <form class="profile_post" action="" method="POST" >
                <div class="form-group">
                    <textarea class="form-control" name="post_body"></textarea>
                    <input type="hidden" name="user_from" value="<?php echo $userLoggedIn;?>">
                    <input type="hidden" name="user_to" value="<?php echo $username;?>">
                </div>
            </form>
      </div>


      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
      </div>
    </div>
  </div>
</div>


<script>
    var userLoggedIn = '<?php echo $userLoggedIn?>';
    var profileUsername = '<?php echo $username; ?>';

    $(document).ready(function() {

        $('#loading').show();

        //Original ajax request for loading first posts call 
        $.ajax({
            url:"includes/handlers/ajax_load_profile_posts.php", //the file to send it to
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,//page=1 cos it's the first call //what should send to the page
            cache:false,

            success: function(data) { //do this function after returns from the call function
                $('#loading').hide(); //return with post but hide the sign
                $('.posts_area').html(data);
            }
        });

        $(window).scroll(function() { //to find out if it is at the bottom of the page or not
            var height = $('.posts_area').height(); //Div containing posts
            var scroll_top = $(this).scrollTop(); //Contain the top of the page where you are loaded
            var page = $('.posts_area').find('.nextPage').val();
            var noMorePosts = $('.posts_area').find('.noMorePosts').val();

            if((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
                $('#loading').show();

                var ajaxReq = $.ajax({
                    url:"includes/handlers/ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                    cache:false,

                    success: function(response) {
                        $('.posts_area').find('.nextPage').remove();//Remove current .nextpage
                        $('.posts_area').find('.noMorePosts').remove();

                        $('#loading').hide();
                        $('.posts_area').append(response); // add the new post to the existing post
                    }
                 });

            }//End if
            return false;
        }); //End $(window).scroll(function()

    });

    </script>


    </div> 
</body>
</html>