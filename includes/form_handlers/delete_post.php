<?php
require '../../config/config.php';

    if(isset($_GET['post_id']))
    $post_id = $_GET['post_id'];

    if(isset($_POST['result'])) {
        if($_POST['result'] == 'true')
            $query = $con->query("UPDATE posts SET deleted='yes' WHERE id='$post_id'");
    }
?>