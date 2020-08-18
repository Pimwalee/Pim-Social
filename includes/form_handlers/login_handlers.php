<?php


if(isset($_POST['log_button'])) {
    $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); //make sure email is in  the correct form

    $_SESSION['log_email'] = $email; //store emailinto session variable
    $password = md5($_POST['log_password']);//get password

    $check_database_query = $con->query("SELECT * FROM users WHERE email='$email' AND password='$password'");//email and password have to match  
    $check_login_query = $check_database_query->rowCount();

    if($check_login_query == 1) {
        $row = $check_database_query->fetch(PDO::FETCH_BOTH);
        $username = $row['username'];

        $user_closed_query = $con->query("SELECT * FROM users WHERE email='$email' AND user_closed='yes'");
        if($user_closed_query->rowCount() == 1) {
            $reopen_account = $con->query("UPDATE users SET user_closed='no' WHERE email='$email'");
        }

        $_SESSION['username'] = $username;
        header("Location:index.php");
        exit();
    }
    else {
        array_push($error_array, "Email or password was incorrect<br>");
    }


}


?>

