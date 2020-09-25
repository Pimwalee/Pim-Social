<?php
if(isset($_POST['update_details'])){ //input button name from settings.php
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $email_check = $con->query("SELECT * FROM users WHERE email='$email'");
    $row = $email_check->fetch(PDO::FETCH_BOTH);
    $matched_user = $row['username'];

    if($matched_user == "" || $matched_user == $userLoggedIn) {
        $message = "Details updated!<br><br>";
        //email has to be new, not been used or the same.

        $query = $con->query("UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email' WHERE username='$userLoggedIn'");
    }
    else
        $message = "That email is already in use!<br><br>";
}
else
    $message = "";


//***********************************************************

if(isset($_POST['update_password'])) {

    $old_password = strip_tags($_POST['old_password']);
    $new_password_1 = strip_tags($_POST['new_password_1']);
    $new_password_2 = strip_tags($_POST['new_password_2']);

    $password_query = $con->query("SELECT password FROM users WHERE username='$userLoggedIn'");
    $row = $password_query->fetch(PDO::FETCH_BOTH);
    $db_password = $row['password'];

    if(md5($old_password) == $db_password) {

        if($new_password_1 == $new_password_2) {


            if(strlen($new_password_1) <= 4) {
                $password_message = "Sorry, your password must be greater than 4 characters<br><br>";
            }
            else {
                $new_password_md5 = md5($new_password_1);
                $password_query = $con->query("UPDATE users SET password='$new_password_md5' WHERE username='$userLoggedIn'");
                $password_message = "Password has been changed!<br><br>";
            }

        }
        else {
            $password_message = "Your two new passwords need to match!<br><br>";
        }

    }
    else {
            $password_message = "The old password is incorrect!<br><br>";
    }

}
else {
    $password_message = "";
}


if(isset($_POST['close_account'])) {
    header("Location: close_account.php");
}

?>
