<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['u']))
    $user_to = $_GET['u'];
else {
    $user_to = $message_obj->getMostRecentUser();
    if($user_to == false) // Did not start a conversation with anyone yet
        $user_to = 'new'; // Sending a new message
}

if($user_to != "new")// user is not trying to send a new message
    $user_to_obj = new User($con, $user_to);

if(isset($_POST['post_message'])) {

    if(isset($_POST['message_body'])) {
        $body = $_POST['message_body'];
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($user_to, $body, $date);

    }

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

    <div class="main_column column" id="main_column">
        <?php
        if($user_to != "new"){
            echo "<h4> You and <a href='$user_to'>" . $user_to_obj->getFirstAndLastName() . "</a></h4><hr><br>";
            //if user_to is != new and they are trying to send a message to a user we will show them appropriate heading
            echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($user_to);
            echo "</div>";
        }
        else {
            echo "<h4>New Message</h4>";
            //if it is a new message it will show New Message heading if it is not new it will show the message
        }
        ?>
        
        <div class ="message_post">
            <form action="" method="POST">
                <?php
                if($user_to == "new") {
                    echo "Select the friend you would like to message <br><br>";
                    ?>
                     To: <input type='text' onkeyup='getUser(this.value, "<?php echo $userLoggedIn; ?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>
                    <?php
                     //whatever in the input field will be the value

                    echo "<div class='results'></div>";
                }
                else {
                    echo "<textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>";
                    echo "<input type='submit' name='post_message' class='info' id='message_submit' value='Send'>";
                }
                
                ?>
            </form>

        </div>

    <script>
        var div = document.getElementById("scroll_messages");
        if(div != null)
        div.scrollTop = div.scrollHeight;
    </script>

    </div>

    <div class="user_details column" id="conversaitons">
        <h4>Conversations</h4>
            <div class="loaded_conversations">
                <?php echo $message_obj->getConvos();?>
            </div>
        <br>
        <a href="messages.php?u=new">New Message</a>
    </div>