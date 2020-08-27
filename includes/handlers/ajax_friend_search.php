<?php
include("../../config/config.php");
include("../classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];
$names = explode(" ", $query);
//$query is what we/they are searching for
// these were passed inside getUser(); in pimcial.js
// split whatever we/they search for to string when we see space

//Predit what we are searching for
if(strpos($query, "_") !== false) { 
    $usersReturn = $con->query("SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
    //ex, $query% cat% cathy cate catrin
    //look in side $query try to find _ , if it's there it must be usernames so we check all users for it
}
else if(count($names) == 2) {
    $usersReturn = $con->query("SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed='no' LIMIT 8");
    //'%$names[0]%' the first name we are searching for
    // if there is 2 names in the array we will return lastname too
}
else {
    $usersReturn = $con->query("SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND user_closed='no' LIMIT 8");
}
if($query != "") {
    while($row = $usersReturn->fetch(PDO::FETCH_BOTH)) {
        $user = new User($con, $userLoggedIn);

        if($row['username'] != $userLoggedIn) {
            $mutual_friends = $user->getMutualFriend($row['username']) . " friends in common";
        }
        else {
            $mutual_friends = "";
        }
        if($user->isFriend($row['username'])) {
            echo "<div class='resultDisplay'>
  <a href='messages.php?u=" . $row['username'] . "' style='color: #000'>
      <div class='liveSearchProfilePic'>
          <img src='". $row['profile_pic'] . "'>
      </div>
      <div class='liveSearchText'>
        ".$row['first_name'] . " " . $row['last_name']. "
        <p style='margin: 0;'>". $row['username'] . "</p>
        <p id='gray'>".$mutual_friends . "</p>
    </div>
  </a>
</div>";

        }

    }
}


?> 

