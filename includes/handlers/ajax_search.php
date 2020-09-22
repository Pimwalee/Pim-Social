<?php
include("../../config/config.php");
include("../../includes/classes/User.php");

$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];
/*from getLiveSearchUsers(value, user) funciton
$.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn:user}
*/
$names = explode(" ", $query);
//split the value of $query at the space in an array


if(strpos($query,'_') !== false)
    $usersReturnedQuery = $con->query("SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
//If query contains an underscore, assume user is searching for usernames
//if find _ in value of $query, return the str position. If doesn't find it return false.

else if(count($names) == 2)
    $usersReturnedQuery = $con->query("SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed='no' LIMIT 8");
//If there are two words, assume they are first AND last names respectively.

else
    $usersReturnedQuery = $con->query("SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed='no' LIMIT 8");
//If query has one word only, search first names OR last names.

if($query != ""){

    while($row = $usersReturnedQuery->fetch(PDO::FETCH_BOTH)) {
        $user = new User($con, $userLoggedIn);

        if($row['username'] != $userLoggedIn)
        $mutual_friends = $user->getMutualFriend($row['username']) . " friends in common";
        else 
            $mutual_friends = "";

        echo "<div class='resultDisplay'>
                <a href='" . $row['username'] . "' style='color: #000000'>
                    <div class='liveSearchProfilePic'>
                        <img src='" . $row['profile_pic'] ."'>
                    </div>
                    <div class='liveSearchText'>
                        " . $row['first_name'] . " " . $row['last_name'] . "
                        <p>" . $row['username'] ."</p>
                        <p id='gray'>" . $mutual_friends ."</p>
                    </div>
                </a>
                </div>";

    }

}

?>