<?php
include("includes/header.php");
// include("includes/classes/User.php");
// include("includes/classes/Post.php");


if(isset($_POST['post'])){//if post have been pressed
    $post = new Post($con, $userLoggedIn);
    $post->submitPost($_POST['post_text'], 'none');//none cos it's from index page not to anyone
    header("Location: index.php");
}

?>
    <div class= "user_details column">
        <a href="<?php echo $userLoggedIn; ?>"> <img src="<?php echo $user['profile_pic'];?>"></a>

        <div class="user_details_left_right">

            <a href="<?php echo $userLoggedIn; ?>">
            <?php echo $user['first_name'] . " " . $user['last_name'];?>
            </a>
            <br>
            <?php echo "Posts : " . $user['num_posts']. "<br>";
            echo "Likes : " . $user['num_likes'];
            ?>
        </div>
    </div>

    <div class="main_column column">
        <form class="post_form" action="index.php" method="POST">
            <textarea name="post_text" id="post_text" placeholder="What is on your mind, <?php echo $user['first_name'] ?>?"></textarea>
            <input type="submit" name="post" id="post_button" value="Post">
            

        </form>


        <div class="posts_area"></div>
        <img id="loading" src="assets/images/icons/Loading_icon.gif">

    </div>

    <script>
    var userLoggedIn = '<?php echo $userLoggedIn?>';

    $(document).ready(function() {

        $('#loading').show();

        //Original ajax request for loading first posts call 
        $.ajax({
            url:"includes/handlers/ajax_load_posts.php", //the file to send it to
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn,//page=1 cos it's the first call //what should send to the page
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
                    url:"includes/handlers/ajax_load_posts.php", 
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn, 
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

    </div> <!--clsing div from div wrapper in header.php cos we include it on the top-->
</body>
</html>