<?php 
class Post {
    private $user; //holds the result all the query
    private $con;

    public function __construct($con, $user){ //using instance from User.php
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to, $imageName) {
        $body = strip_tags($body); //remove html tags
        $body = str_replace('\r\n', '\n', $body);
        $body = nl2br($body);//replace new line\n with line breaks<br>

        $check_empty = preg_replace('/\s+/', '', $body); //delete all spaces

        if($check_empty != "" || $imageName != "") { //if it contains something

            $body_array = preg_split("/\s+/", $body);
 
            foreach($body_array as $key => $value) { //$value is links, $key keeps track number of element in the array
 
                if(strpos($value, "www.youtube.com/watch?v=") !== false) {
 
                /*  From 1 song link looks like
                            https://www.youtube.com/watch?v=SlPhMPnQ58k
                        
                    From playlists link looks like (split to array at &)
                            https://www.youtube.com/watch?v=SlPhMPnQ58k& [0]list=PL4o29bINVT4EG_y-k5jGoOu3-Am8Nvi10& [1]
                            index=1 [2]
                    for playlists links, split to array at & and only use link[0]
                */

                    $link = preg_split("!&!", $value); 
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);

                    // $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value ."\'></iframe><br>";
                    $value = "<br><iframe width='420' height='315' src='" . $value ."'></iframe><br>";
                    $body_array[$key] = $value;

                }
        }
        $body = implode(" ", $body_array);

            //Current date and time
            $date_added = date("Y-m-d H:i:s");
            //Get username
            $added_by = $this->user_obj->getUsername();

            //If user is on own profile,user_to is 'none'   
            if($user_to == $added_by) { 
                $user_to = "none"; //none cos it is from our own page
            }

            //Insert post

            $query = $this->con->prepare('INSERT INTO posts(id, body, added_by, user_to, date_added, user_closed, deleted, likes, image) VALUES (NULL, :body, :added_by, :user_to, :date_added, :user_closed, :deleted, :likes, :image)');
            $query->bindValue(':body', $body);
            $query->bindValue(':added_by', $added_by);
            $query->bindValue(':user_to', $user_to);
            $query->bindValue(':date_added', $date_added);
            $query->bindValue(':user_closed', 'no');
            $query->bindValue(':deleted', 'no');
            $query->bindValue(':likes', '0');
            $query->bindValue(':image', $imageName);
            $query->execute();


            $returned_id = $this->con->lastInsertId(); //return the id of the person that just submitted the post 

            //Insert notification
            if($user_to != 'none') { //we do it on our profile no need to notify
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "profile_post");
            }

            //Update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = $this->con->query("UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");

            $stopWords = "a about above across after again against all almost alone along already
            also although always among am an and another any anybody anyone anything anywhere are 
            area areas around as ask asked asking asks at away b back backed backing backs be became
            because become becomes been before began behind being beings best better between big 
            both but by c came can cannot case cases certain certainly clear clearly come could
            d did differ different differently do does done down down downed downing downs during
            e each early either end ended ending ends enough even evenly ever every everybody
            everyone everything everywhere f face faces fact facts far felt few find finds first
            for four from full fully further furthered furthering furthers g gave general generally
            get gets give given gives go going good goods got great greater greatest group grouped
            grouping groups h had has have having he her here herself high high high higher
            highest him himself his how however i im if important in interest interested interesting
            interests into is it its itself j just k keep keeps kind knew know known knows
            large largely last later latest least less let lets like likely long longer
            longest m made make making man many may me member members men might more most
            mostly mr mrs much must my myself n necessary need needed needing needs never
            new new newer newest next no nobody non noone not nothing now nowhere number
            numbers o of off often old older oldest on once one only open opened opening
            opens or order ordered ordering orders other others our out over p part parted
            parting parts per perhaps place places point pointed pointing points possible
            present presented presenting presents problem problems put puts q quite r
            rather really right right room rooms s said same saw say says second seconds
            see seem seemed seeming seems sees several shall she should show showed
            showing shows side sides since small smaller smallest so some somebody
            someone something somewhere state states still still such sure t take
            taken than that the their them then there therefore these they thing
            things think thinks this those though thought thoughts three through
            thus to today together too took toward turn turned turning turns two
            u under until up upon us use used uses v very w want wanted wanting
            wants was way ways we well wells went were what when where whether
            which while who whole whose why will with within without work
            worked working works would x y year years yet you young younger
            youngest your yours z lol haha omg hey ill iframe wonder else like 
            hate sleepy reason for some little yes bye choose";

            //Convert stop words into array - split at white space
            $stopWords = preg_split("/[\s,]+/", $stopWords);

            //Remove all punctionation
            $no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

            //Predict whether user is posting a url. If so, do not check for trending words
            if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false
            && strpos($no_punctuation, "http") === false && strpos($no_punctuation, "youtube") === false){
            //Convert users post (with punctuation removed) into array - split at white space
            $keywords = preg_split("/[\s,]+/", $no_punctuation);

            foreach($stopWords as $value) { //go each word of stopword
                foreach($keywords as $key => $value2){ //also go each word of post
                    if(strtolower($value) == strtolower($value2))
                        $keywords[$key] = ""; //if $value $value2 matches means it found stopword inside,so remove it.
                }
            }

            foreach ($keywords as $value) {
                $this->calculateTrend(ucfirst($value));
                //each word in the array will go check if it is in trends table already
            }
            
         }

    }
}

public function calculateTrend($term) {

    if($term != '') {
        $query = $this->con->query("SELECT * FROM trends WHERE title='$term'");

        if($query->rowCount() == 0)
            $insert_query = $this->con->query("INSERT INTO trends(title,hits) VALUES('$term','1')");
        else 
            $insert_query = $this->con->query("UPDATE trends SET hits=hits+1 WHERE title='$term'");
    }

}

    public function loadPostsFriends($data, $limit) { 

        $page = $data['page']; // $_REQUEST in ajax_load_posts from ajax data in index.php
        $userLoggedIn = $this->user_obj->getUsername();

        if($page == 1)
            $start = 0;
        else
            $start = ($page -1) * $limit; //limit = 10

        $str = ""; //String to return
        $data_query = $this->con->query("SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if($data_query->rowCount() > 0){
        

                $num_iteration = 0; //count how many times the loop been round// Number of result cheked
                $count = 1;
        
                while($row = $data_query->fetch(PDO::FETCH_BOTH)) {
                    $id = $row['id'];
                    $body = $row['body'];
                    $added_by = $row['added_by'];
                    $date_time = $row['date_added'];
                    $imagePath = $row['image'];

                    //Prepare user_to string so it can be included even if not posted to a user
                    if($row['user_to'] == "none") {
                        $user_to = "";
                    }
                    else {
                        $user_to_obj = new User($this->con, $row['user_to']);
                        $user_to_name = $user_to_obj->getFirstAndLastName();
                        $user_to = "to <a href='". $row['user_to'] . "'>" . $user_to_name . "</a>"; // will say "to..(and the link to the person)"
                    }
                    //Check if user posted has their account closed
                    $added_by_obj = new User($this->con, $added_by);
                    if($added_by_obj->isClosed()) {
                        continue;
                    }
                    //only show posts friends //if true do below if false will go around again
                    $user_logged_obj = new User($this->con, $userLoggedIn);
                    if($user_logged_obj->isFriend($added_by)){


                        if($num_iteration++ < $start) //if less than start(0) go back and do another iteration
                        continue;

                        //Once 10 posts have been loaded, break
                        if($count > $limit){
                        break; // leave the loop
                        }
                        else {
                            $count++;
                        }

                        if($userLoggedIn == $added_by)
                            $delete_button = "<button class='delete_button' id='post$id'>X</button>";
                        else 
                            $delete_button = "";

                        $user_details_query = $this->con->query("SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = $user_details_query->fetch(PDO::FETCH_BOTH);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];


                        ?>
                        <script>
                            function toggle<?php echo $id; ?>() {

                                var target = $(event.target);
                                if(!target.is("a")) { //if click on "a" link don't show the comment frame
                                    var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                    if(element.style.display == "block") {//if comment is showing 
                                        element.style.display = "none";//hide it
                                    } else {
                                    element.style.display = "block";//if comment is hidden show it
                                    }
                                    
                                }
                            }

                        </script>
                        <?php
                        //Count comments
                        $comments_check = $this->con->query("SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = $comments_check->rowCount();

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); //Time of post
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
                        
                        if($imagePath != "") {
                            $imageDiv = "<div class='postedImage'>
                                            <img src='$imagePath'>
                                        </div>";
                        }
                        else {
                            $imageDiv = "";
                        }

                        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='$profile_pic' width='50'>
                                    </div>   
                                    
                                    <div class='posted_by' style='color:#9c9c9c;'>
                                        <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                        $delete_button
                                    </div>
                                    <div id='post_body'>
                                        $body
                                        <br>
                                        $imageDiv
                                        <br>
                                    </div>

                                        <div class='newsfeedPostOptions'>
                                            Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                            <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                        </div>

                                </div>
                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                                </div>
                                <hr>";
                    }
                    ?>
                    <script>

                        $(document).ready(function() {
                            
                            $('#post<?php echo $id; ?>').on('click', function(){
                                bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                    $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});// sending the result to this page

                                    if(result)
                                    location.reload();
                                });
                            });
                        });

                    </script>
                    <?php
                    

                }//the end of while loop

                if($count > $limit) //if there will be only 6 posts left stop!  cos there's no more left
                    $str .= "<input type='hidden' class='nextPage' value='" . ($page +1) . "'>
                            <input type='hidden' class='noMorePosts' value='false'>";
                else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show!</p>"; 
            }
            echo $str;

    }

    public function loadProfilePosts($data, $limit) { 

        $page = $data['page'];
        $profileUsername = $data['profileUsername']; /* $data['profileUsername'] comes from $_REQUEST in ajax_load_profile_posts.php that comes from ajax data: in profile.php and we can access it by using $_REQUEST['whatever word we use in data'] */
        $userLoggedIn = $this->user_obj->getUsername();

        if($page == 1)
            $start = 0;
        else
            $start = ($page -1) * $limit; //limit = 10

        $str = ""; //String to return
        $data_query = $this->con->query("SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUsername' AND user_to='none') OR user_to='$profileUsername') ORDER BY id DESC"); // Pim walee goes to Justin's profile to make a post so user_to need to be Justin anyway, so we don't have to show that Pim walee TO Justin.

        if($data_query->rowCount() > 0){
        

                $num_iteration = 0; //count how many times the loop been round// Number of result cheked
                $count = 1;
        
                while($row = $data_query->fetch(PDO::FETCH_BOTH)) {
                    $id = $row['id'];
                    $body = $row['body'];
                    $added_by = $row['added_by'];
                    $date_time = $row['date_added'];


                        if($num_iteration++ < $start) //if less than start(0) go back and do another iteration
                        continue;

                        //Once 10 posts have been loaded, break
                        if($count > $limit){
                        break; // leave the loop
                        }
                        else {
                            $count++;
                        }

                        if($userLoggedIn == $added_by)
                            $delete_button = "<button class='delete_button' id='post$id'>X</button>";
                        else 
                            $delete_button = "";

                        $user_details_query = $this->con->query("SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = $user_details_query->fetch(PDO::FETCH_BOTH);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];


                        ?>
                        <script>
                            function toggle<?php echo $id; ?>() {

                                var target = $(event.target);
                                if(!target.is("a")) { //if click on "a" link don't show the comment frame
                                    var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                    if(element.style.display == "block") {//if comment is showing 
                                        element.style.display = "none";//hide it
                                    } else {
                                    element.style.display = "block";//if comment is hidden show it
                                    }
                                    
                                }
                            }

                        </script>
                        <?php
                        //Count comments
                        $comments_check = $this->con->query("SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = $comments_check->rowCount();

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); //Time of post
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
                        
                        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='$profile_pic' width='50'>
                                    </div>   
                                    
                                    <div class='posted_by' style='color:#9c9c9c;'>
                                        <a href='$added_by'> $first_name $last_name </a>  &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                        $delete_button
                                    </div>
                                    <div id='post_body'>
                                        $body
                                        <br>
                                        <br>
                                    </div>

                                        <div class='newsfeedPostOptions'>
                                            Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                            <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                        </div>

                                </div>
                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                                </div>
                                <hr>";
                    ?>
                    <script>

                        $(document).ready(function() {
                            
                            $('#post<?php echo $id; ?>').on('click', function(){
                                bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                    $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});// sending the result to this page

                                    if(result)
                                    location.reload();
                                });
                            });
                        });

                    </script>
                    <?php
                    

                }//the end of while loop

                if($count > $limit) //if there will be only 6 posts left stop!  cos there's no more left
                    $str .= "<input type='hidden' class='nextPage' value='" . ($page +1) . "'>
                            <input type='hidden' class='noMorePosts' value='false'>";
                else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;'> No more posts to show!</p>"; 
            }
            echo $str;

    }

    public function getSinglePost($post_id) {

        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = $this->con->query("UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = ""; //String to return
        $data_query = $this->con->query("SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if($data_query->rowCount() > 0){

                    $row = $data_query->fetch(PDO::FETCH_BOTH);
                    $id = $row['id'];
                    $body = $row['body'];
                    $added_by = $row['added_by'];
                    $date_time = $row['date_added'];

                    //Prepare user_to string so it can be included even if not posted to a user
                    if($row['user_to'] == "none") {
                        $user_to = "";
                    }
                    else {
                        $user_to_obj = new User($this->con, $row['user_to']);
                        $user_to_name = $user_to_obj->getFirstAndLastName();
                        $user_to = "to <a href='". $row['user_to'] . "'>" . $user_to_name . "</a>"; // will say "to..(and the link to the person)"
                    }
                    //Check if user posted has their account closed
                    $added_by_obj = new User($this->con, $added_by);
                    if($added_by_obj->isClosed()) {
                        return;
                    }
                    //only show posts friends //if true do below if false will go around again
                    $user_logged_obj = new User($this->con, $userLoggedIn);
                    if($user_logged_obj->isFriend($added_by)){

                        if($userLoggedIn == $added_by)
                            $delete_button = "<button class='delete_button' id='post$id'>X</button>";
                        else 
                            $delete_button = "";

                        $user_details_query = $this->con->query("SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                        $user_row = $user_details_query->fetch(PDO::FETCH_BOTH);
                        $first_name = $user_row['first_name'];
                        $last_name = $user_row['last_name'];
                        $profile_pic = $user_row['profile_pic'];


                        ?>
                        <script>
                            function toggle<?php echo $id; ?>() {

                                var target = $(event.target);
                                if(!target.is("a")) { //if click on "a" link don't show the comment frame
                                    var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                    if(element.style.display == "block") {//if comment is showing 
                                        element.style.display = "none";//hide it
                                    } else {
                                    element.style.display = "block";//if comment is hidden show it
                                    }
                                    
                                }
                            }

                        </script>
                        <?php
                        //Count comments
                        $comments_check = $this->con->query("SELECT * FROM comments WHERE post_id='$id'");
                        $comments_check_num = $comments_check->rowCount();

                        //Timeframe
                        $date_time_now = date("Y-m-d H:i:s");
                        $start_date = new DateTime($date_time); //Time of post
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
                        
                        $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
                                    <div class='post_profile_pic'>
                                        <img src='$profile_pic' width='50'>
                                    </div>   
                                    
                                    <div class='posted_by' style='color:#9c9c9c;'>
                                        <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
                                        $delete_button
                                    </div>
                                    <div id='post_body'>
                                        $body
                                        <br>
                                        <br>
                                    </div>

                                        <div class='newsfeedPostOptions'>
                                            Comments($comments_check_num)&nbsp;&nbsp;&nbsp;
                                            <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                        </div>

                                </div>
                                <div class='post_comment' id='toggleComment$id' style='display:none;'>
                                    <iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
                                </div>
                                <hr>";
                    ?>
                    <script>

                        $(document).ready(function() {
                            
                            $('#post<?php echo $id; ?>').on('click', function(){
                                bootbox.confirm("Are you sure you want to delete this post?", function(result) {

                                    $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});// sending the result to this page

                                    if(result)
                                    location.reload();
                                });
                            });
                        });

                    </script>
                    <?php
                    }
                    else {
                        echo "<p>You cannot see this post because are not friends with this user.</p>";
                        return;
                    }
            }
            else {
                echo "<p>No post found. If you clicked a link, it may be broken.</p>";
                        return;
            }
            echo $str;
    }
}

?>
