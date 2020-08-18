<?php 
class User {
    private $user; //holds the result all the query
    private $con;

    public function __construct($con, $user){
        $this->con = $con;
        $user_details_query = $con->query("SELECT * FROM users WHERE username='$user'");
        $this->user = $user_details_query->fetch(PDO::FETCH_BOTH);
    }

    public function getUsername(){
        return $this->user['username'];
    }

    public function getNumPosts() {
        $username = $this->user['username'];
        $query = $this->con->query("SELECT num_posts FROM users WHERE username='$username'");
        $row = $query->fetch(PDO::FETCH_BOTH);
        return $row['num_posts'];
    }

    public function getFirstAndLastName() {
        $username = $this->user['username'];
        $query = $this->con->query("SELECT first_name,last_name FROM users WHERE username='$username'");
        $row = $query->fetch(PDO::FETCH_BOTH);
        return $row['first_name'] . " " . $row['last_name'];
    }

    public function getProfilePic() {
        $username = $this->user['username'];
        $query = $this->con->query("SELECT profile_pic FROM users WHERE username='$username'");
        $row = $query->fetch(PDO::FETCH_BOTH);
        return $row['profile_pic'];
    }

    public function getFriendArray() {
        $username = $this->user['username'];
        $query = $this->con->query("SELECT friend_array FROM users WHERE username='$username'");
        $row = $query->fetch(PDO::FETCH_BOTH);
        return $row['friend_array'];
    }

    public function isClosed() {
        $username = $this->user['username'];
        $query = $this->con->query("SELECT user_closed FROM users WHERE username='$username'");
        $row = $query->fetch(PDO::FETCH_BOTH);

        if($row['user_closed']== 'yes')
        return true;
        else
        return false;
    }

    public function isFriend($username_to_check) {
        $usernameComma = "," . $username_to_check . ","; //,pim_walee,
        if((strstr($this->user['friend_array'], $usernameComma) || $username_to_check == $this->user['username'])) {
        // strstr is to check if sees srt $usernameComma inside str friend_array
            return true;
        }
        else  {
            return false;
        }
    }

    public function didReceiveRequest($user_from) {
        $user_to = $this->user['username'];
        $check_request_query = $this->con->query("SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'");
        if($check_request_query->rowCount() > 0){
            return true;
        }
        else {
            return false;
        }

    }

    public function didSendRequest($user_to) {
        $user_from = $this->user['username'];
        $check_request_query = $this->con->query("SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from'");
        if($check_request_query->rowCount() > 0){
            return true;
        }
        else {
            return false;
        }
    }

    public function removeFriend($user_to_remove) {
        $logged_in_user = $this->user['username'];

        $query = $this->con->query("SELECT friend_array FROM users WHERE username='$user_to_remove'");
        $row = $query->fetch(PDO::FETCH_BOTH);
        $friend_array_username = $row['friend_array'];

        $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);//remove username that follow by comma replace with blank from ['friend_array']
        $remove_friend = $this->con->query("UPDATE users SET friend_array='$new_friend_array' WHERE username ='$logged_in_user'");

        $new_friend_array = str_replace($this->user['username']. ",", "", $friend_array_username);//removing friend from the other person's friend array as well
        $remove_friend = $this->con->query("UPDATE users SET friend_array='$new_friend_array' WHERE username ='$user_to_remove'");
    }

    public function sendRequest($user_to) {
        $user_from = $this->user['username'];
        $query = $this->con->query("INSERT INTO friend_requests VALUES(NULL,'$user_to','$user_from')");
    }









}

?>

