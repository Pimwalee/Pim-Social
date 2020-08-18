<?php 
require 'config/config.php';
require 'includes/form_handlers/register_handlers.php';
require 'includes/form_handlers/login_handlers.php';

?>

<html>
<head>
    <title>Register to PimCial</title>
    <link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="assets/js/register.js"></script>
</head>
<body>

<?php
if(isset($_POST['register_button'])) { // if the button is pressed
    echo '
    <script>
    
    $(document).ready(function() {
        $("#first").hide();
        $("#second").show();
    });

    </script>
    ';
}

?>

    <div class="wrapper"> 
       
        <div class="login_box">  

            <div class="login_header">
                <h1>PimCial!</h1>
                Login or sign up below!
            </div>

            <div id="first">

                <form action="register.php" method="POST">
                    <input type="email" name="log_email" placeholder="Email Address" value="<?php
                    if(isset($_SESSION['log_email'])) {
                        echo $_SESSION['log_email'];
                    }
                    ?>" required>
                    <br>
                    <input type="password" name="log_password" placeholder="Password">
                    <br>
                    <?php if(in_array("Email or password was incorrect<br>",$error_array)) echo "Email or password was incorrect<br>" ?>
                    <input type="submit" name="log_button" value="Login">
                    <br>
                    <a href="#" id="signup" class="signup"> Need an account? Register here!</a>

                </form>
            </div>
            <div id="second">            
                <form action="register.php" method="POST"> 

                    <input type = "text" name="reg_fname" placeholder="First Name" value="<?php
                    if(isset($_SESSION['reg_fname'])) {
                        echo $_SESSION['reg_fname'];
                    }
                    ?>" required>
                    <br>
                    <?php if(in_array("Your first name must be between 2 and 25 charecters<br>", $error_array)) echo "Your first name must be between 2 and 25 charecters<br>"; ?> <!--display an error message to users-->


                    <input type = "text" name="reg_lname" placeholder="Last Name" value="<?php
                    if(isset($_SESSION['reg_lname'])) {
                        echo $_SESSION['reg_lname'];
                    }
                    ?>" required>
                    <br>
                    <?php if(in_array("Your last name must be between 2 and 25 charecters<br>", $error_array)) echo "Your last name must be between 2 and 25 charecters<br>"; ?>

                    <input type = "email" name="reg_email" placeholder="Email" value="<?php
                    if(isset($_SESSION['reg_email'])) {
                        echo $_SESSION['reg_email'];
                    }
                    ?>" required>
                    <br>
                    <input type = "email" name="reg_email2" placeholder="Confirm Email" value="<?php
                    if(isset($_SESSION['reg_email2'])) {
                        echo $_SESSION['reg_email2'];
                    }
                    ?>" required>
                    <br>
                    <?php if(in_array("Email already in use<br>", $error_array)) echo "Email already in use<br>";
                    else if(in_array("Invalid email format<br>", $error_array)) echo "Invalid email format<br>"; 
                    else if(in_array("Emails do not match<br>", $error_array)) echo "Emails do not match<br>"; ?>


                    <input type = "password" name="reg_password" placeholder="Password" required>
                    <br>
                    <input type = "password" name="reg_password2" placeholder="Confirm Password" required>
                    <br>
                    <?php if(in_array("Passwords do not match<br>", $error_array)) echo "Passwords do not match<br>";
                    else if(in_array("Password can only contain English characters or numbers<br>", $error_array)) echo "Password can only contain English characters or numbers<br>"; 
                    else if(in_array("Password must be between 5 and 30 characters<br>", $error_array)) echo "Password must be between 5 and 30 characters<br>"; ?>

                    <input type = "submit" name="register_button" value="Register">
                    <br>
                    
                    <?php if(in_array("<span style='color: #1F621D;'> You are all set! Go ahead and login! </span><br>", $error_array)) echo "<span style='color: #1F621D;'> You are all set! Go ahead and login! </span><br>"; ?>
                    <a href="#" id="signin" class="signin"> Already have an account? Sign in here!</a>
                
                </form>
            </div>
        </div>
    </div> 

</body>
</html>
