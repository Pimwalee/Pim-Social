<?php
include("includes/header.php");
// include("includes/classes/User.php");
// include("includes/classes/Post.php");


if(isset($_POST['post'])){//if post have been pressed

    $uploadOk = 1; //hold status if it is okay to load or not
    $imageName = $_FILES['fileToUpload']['name'];
    $errorMessage = "";
    
    if($imageName != ""){
        $targetDir = "assets/images/posts/";
        $imageName = $targetDir . uniqid() . basename($imageName);
        $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);
        //uniqid() create random str or num to make name unique if some file name are the same

        if($_FILES['fileToUpload']['size'] > 10000000) {
            $errorMessage = "Sorry your file is too large";
            $uploadOk = 0;
        }

        if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg" ) {
            $errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
            $uploadOk = 0;
        }

        if($uploadOk) {

            if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
                //image uploaded okay
            }
            else {
                //image did not upload
                $uploadOk = 0;
            }
        }
        //tmp_name is a temporary name that gives the file while it's loading
    }
    if($uploadOk) {
        $post = new Post($con, $userLoggedIn);
        $post->submitPost($_POST['post_text'], 'none', $imageName);
    }
    else {
        echo "<div style='text-align:center;' class='alert alert-danger'>
                $errorMessage
            </div>";
    }
    header("Location: index.php");


    // $post = new Post($con, $userLoggedIn);
    // $post->submitPost($_POST['post_text'], 'none');//none cos it's from index page not to anyone
    // header("Location: index.php");
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
        <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" id="fileToUpload">
            <textarea name="post_text" id="post_text" placeholder="What is on your mind, <?php echo $user['first_name'] ?>?"></textarea>
            <input type="submit" name="post" id="post_button" value="Post"><hr>
            
        </form>

        <div class="posts_area"></div>
        <img id="loading" src="assets/images/icons/Loading_icon.gif">

    </div>

    <div class="user_details column">

        <h4>Popular</h4>

        <div class="trends">
            <?php
            $query = $con->query("SELECT * FROM trends ORDER BY hits DESC LIMIT 9");

            foreach($query as $row) { //while($row = $query->fetch(PDO::FETCHBOTH));
                $word = $row['title'];
                $word_dot = strlen($word) >= 14 ? "..." : "";
                $trimmed_word = str_split($word, 14);
                $trimmed_word = $trimmed_word[0];

                echo "<div style'padding:1px'>";
                echo $trimmed_word . $word_dot;
                echo "<br></div>";

            }
            ?>
        </div>
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