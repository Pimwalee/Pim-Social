<?php
// require 'config/config.php';


//Declaring variables to prevent errors
$fname = "";//First name
$lname = "";//Last name
$em = "";//email
$em = "";//email2
$password = ""; //password
$password2 = ""; //password2
$date = ""; //Sign up date
$error_array = array();//Holds error messages

if(isset($_POST['register_button'])){ // if the register button been pressed, start the form

//Registeratiom form values

//First name
$fname = strip_tags($_POST['reg_fname']);//strip_tags is removing html tags from string
$fname = str_replace(' ','', $fname); //remove space "Pim  " to "Pim"
$fname = ucfirst(strtolower($fname));//Make $fname's value to lowercase and only make the first character uppercase
$_SESSION['reg_fname'] = $fname; //store first name into session variable

//Last name
$lname = strip_tags($_POST['reg_lname']);
$lname = str_replace(' ','', $lname); 
$lname = ucfirst(strtolower($lname));
$_SESSION['reg_lname'] = $lname;

//Email
$em = strip_tags($_POST['reg_email']);
$em = str_replace(' ','', $em); 
$em = ucfirst(strtolower($em));
$_SESSION['reg_email'] = $em;

//Email 2
$em2 = strip_tags($_POST['reg_email2']);
$em2 = str_replace(' ','', $em2); 
$em2 = ucfirst(strtolower($em2));
$_SESSION['reg_email2'] = $em2;

//Password
$password = strip_tags($_POST['reg_password']);
$password2 = strip_tags($_POST['reg_password2']);

$date = date("Y-m-d"); //Current Date

if($em == $em2){
     // Check if email is in valid format
    if(filter_var($em, FILTER_VALIDATE_EMAIL)){

        $em = filter_var($em, FILTER_VALIDATE_EMAIL);

        // Check if email is already exists
        $e_check = $con->query("SELECT email FROM users WHERE email='$em'");
        //if this return something means it's already been used, so you have to use different one

        /*
         - $e_check already stores the $con
         - and the comparison of the  register page email to the email that is already in the users table in the db. 
         - We only need to make a new variable ($num_row) to store the count ( using rowCount) so we can check if it is greater than 0, we know it already exists, and cannot be used for another user
        */
        $num_row = $e_check->rowCount();
    
        if($num_row > 0) {
            array_push($error_array, "Email already in use<br>");
        }
    }
    else{
        array_push($error_array, "Invalid email format<br>");
    }

    }
    else{
        array_push($error_array, "Emails do not match<br>");
}


if(strlen($fname) > 25 || strlen($fname) < 2) {
    array_push($error_array, "Your first name must be between 2 and 25 charecters<br>");
}
if(strlen($lname) > 25 || strlen($lname) < 2) {
    array_push($error_array, "Your last name must be between 2 and 25 charecters<br>");
}

if($password != $password2) {
    array_push($error_array, "Passwords do not match<br>");
}
else{
    if(preg_match('/[^A-Za-z0-9]/', $password)) {
        array_push($error_array, "Password can only contain English characters or numbers<br>");
    }
}
if(strlen($password) > 30 || strlen($password) < 5) {
    array_push($error_array, "Password must be between 5 and 30 characters<br>");
}

if(empty($error_array)) {
    $password = md5($password);// if there is no error in $error_array, Encrypt(md5) password before sending to the database

    //create username by concatenate first name and last name
    $username = strtolower($fname . "_" . $lname);
    $check_username_query = $con->query("SELECT username FROM users WHERE username = '$username'");

        $i = 0;
        //if username exists, add number to username
        while($check_username_query->rowCount() != 0){
            $i++;
            $username = $username . "_" . $i;
            $check_username_query = $con->query("SELECT username FROM users WHERE username = '$username'");
        }

        //Profile picture assignment to give user ramdom picture
        $rand = rand(1, 2); // Random number between 1 and 2

        if($rand == 1)
            $profile_pic = "assets/images/profile_pics/defaults/defaultpic1.png";
        else if($rand == 2)
            $profile_pic = "assets/images/profile_pics/defaults/defaultpic2.png";

     $query = $con->query("INSERT INTO users VALUES(NULL, '$fname', '$lname', '$username', '$em', '$password', '$date', '$profile_pic', '0', '0', 'no', ',') ");

    array_push($error_array, "<span style='color: #1F621D;'> You are all set! Go ahead and login! </span><br>");

    //Clear session variables that stays in place holders
    $_SESSION['reg_fname'] = "";
    $_SESSION['reg_lname'] = "";
    $_SESSION['reg_email'] = "";
    $_SESSION['reg_email2'] = "";
        
    }

}

?>