$(document).ready(function(){ //Ajax call, it's going to submit the form for us
//Button for profile post
    $('#submit_profile_post').click(function(){

        $.ajax({
            type: "POST",
            url: "includes/handlers/ajax_submit_profile_post.php",
            data: $('form.profile_post').serialize(),
            success: function(msg) {
                $("#post_form").modal('hide');
                location.reload();
            },
            error: function() {
                alert('Failure');
            }
         });
    });

});

function getUser(value, user) {
    $.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
        $(".results").html(data);
    });
}
//It is going to send the request to includes/handlers/ajax_friend_search.php with these {query:value, userLoggedIn:user} value
//and it will set the value in $(".results") div (div class from messages.php) with .html(data); content of what data was returned
