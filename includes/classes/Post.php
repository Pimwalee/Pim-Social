<?php 
class Post {
    private $user; //holds the result all the query
    private $con;

    public function __construct($con, $user){ //using instance from User.php
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to) {
        $body = strip_tags($body); //remove html tags

        $body = str_replace('\r\n', '\n', $body);
        $body = nl2br($body);//replace new line\n with line breaks<br>

        $check_empty = preg_replace('/\s+/', '', $body); //delete all spaces

        if($check_empty != "") { //if it contains something

            //Current date and time
            $date_added = date("Y-m-d H:i:s");
            //Get username
            $added_by = $this->user_obj->getUsername();

            //If user is on own profile,user_to is 'none'   
            if($user_to == $added_by) { 
                $user_to = "none"; //none cos it is from our own page
            }

            //Insert post

            $query = $this->con->prepare('INSERT INTO posts(id, body, added_by, user_to, date_added, user_closed, deleted, likes) VALUES (NULL, :body, :added_by, :user_to, :date_added, :user_closed, :deleted, :likes)');
            $query->bindValue(':body', $body);
            $query->bindValue(':added_by', $added_by);
            $query->bindValue(':user_to', $user_to);
            $query->bindValue(':date_added', $date_added);
            $query->bindValue(':user_closed', 'no');
            $query->bindValue(':deleted', 'no');
            $query->bindValue(':likes', '0');
            $query->execute();


            $return_id = $this->con->lastInsertId(); //return the id of the person that just submitted the post 

            //Insert notification

            //Update post count for user
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = $this->con->query("UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");
        }
    }

public function loadPostsFriends($data, $limit) { 

    $page = $data['page'];
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
            $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: centre;'> No more posts to show!</p>"; 
        }
         echo $str;

}

}

?>
