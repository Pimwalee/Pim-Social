<?php 
class Message {
    private $user_obj; //holds the result all the query
    private $con;

    public function __construct($con, $user){ //using instance from User.php
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }
    
    public function getMostRecentUser() {
            $userLoggedIn = $this->user_obj->getUsername();

            $query = $this->con->query("SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='userLoggedIn' ORDER BY id DESC LIMIT 1");// Limit 1 is the most recent one

            if($query->rowCount() == 0)
            return false;

            $row = $query->fetch(PDO::FETCH_BOTH);
            $user_to = $row['user_to'];
            $user_from = $row['user_from'];

            if($user_to != $userLoggedIn)
                return $user_to;
            else
                return $user_from;
    }

    public function sendMessage($user_to, $body, $date) {

        if($body != "") {
            $userLoggedIn = $this->user_obj->getUserName();
            $query = $this->con->prepare("INSERT INTO messages (id, user_to,user_from, body, date, opened, viewed, deleted) VALUES (NULL, :user_to,:user_from, :body, :date, :opened, :viewed, :deleted)");
            $query->bindValue(':user_to', $user_to);
            $query->bindValue(':user_from', $userLoggedIn);
            $query->bindValue(':body', $body);
            $query->bindValue(':date', $date);
            $query->bindValue(':opened', 'no');
            $query->bindValue(':viewed', 'no');
            $query->bindValue(':deleted', 'no');
            $query->execute();

        }
    }

    public function getMessages($otherUser) {
        $userLoggedIn = $this->user_obj->getUserName();
        $data = "";

        $query = $this->con->query("UPDATE messages SET opened='yes' WHERE user_to='$userLoggedIn' AND user_from='$otherUser'");

        $get_messages_query =$this->con->query("SELECT * FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$otherUser') OR (user_from='$userLoggedIn' AND user_to='$otherUser')");

        while($row = $get_messages_query->fetch(PDO::FETCH_BOTH)) {
            $user_to = $row['user_to'];
            $user_from = $row['user_from'];
            $body = $row['body'];

            $div_top = ($user_to == $userLoggedIn) ? "<div class='message' id='lightgray'>" : "<div class='message' id='darkgray'>";
            $data = $data . $div_top . $body . "</div><br><br>";
        }
        return $data;
    }

    public function getLastestMessage($userLoggedIn, $user2) {
        $details_array = array();

        $query = $this->con->query("SELECT body, user_to, date FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$user2') OR (user_to='$user2' AND user_from='$userLoggedIn') ORDER BY id DESC LIMIT 1");

        $row = $query->fetch(PDO::FETCH_BOTH);
        $sent_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

      //Timeframe
      $date_time_now = date("Y-m-d H:i:s");
      $start_date = new DateTime($row['date']); //Time of post
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
        array_push($details_array, $sent_by);
        array_push($details_array, $row['body']);
        array_push($details_array, $time_message);

        return $details_array;
    }

    public function getConvos() {
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";
        $convos = array();

        $query = $this->con->query("SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY id DESC");

        while($row = $query->fetch(PDO::FETCH_BOTH)) {
            $user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from']; // whichever one userLoggedIn is, do the other one
            if(!in_array($user_to_push, $convos)) { //if $user_to_push, is not in $convos array
                array_push($convos, $user_to_push); //if not in there, push $user_to_push in $convos
            }
        } 
        foreach($convos as $username) {
            $user_found_obj = new User($this->con, $username);
            $lastest_message_detail = $this->getLastestMessage($userLoggedIn, $username);

            $dots = (strlen($lastest_message_detail[1]) >= 12) ? "..." : "";
            $split = str_split($lastest_message_detail[1], 12);
            $split = $split[0] . $dots; 
                // $split[0] is $split on line 160
                //[0]= $sent_by 
                //[1]= $row['body']
                //[2]= $time_message
                //are in getLastestMessage();
                // .= is to add to its existing value

                $return_string .= "<a href='messages.php?u=$username'>
                 <div class='user_found_messages'>
                <img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right:15px;'>
                " . $user_found_obj->getFirstAndLastName() . " <br>
                <span class='timestamp_smaller' id='gray'> " . $lastest_message_detail[2] . "</span>
                <p id='gray' style='margin: 0;'>" . $lastest_message_detail[0] . $split . " </p>
                </div>
                </a>";

        }
        return $return_string;
    }
}

?>