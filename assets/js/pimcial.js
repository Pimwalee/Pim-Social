$(document).ready(function(){ //Ajax call, it's going to submit the form for us

    $('#search_text_input').focus(function() {
        if(window.matchMedia( "(min-width:800px)" ).matches) {
            $(this).animate({width: '250px'}, 500);
        }
    });

    $('.button_holder').on('click', function() {
        document.search_form.submit();

    });

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

$(document).click(function(e){
    if(e.target.class != "search_results" && e.target.id != "search_text_input") {
        $(".search_results").html("");
        $('.search_results_footer').html("");
        $('.search_results_footer').toggleClass("search_results_footer_empty");
        $('.search_results_footer').toggleClass("search_results_footer");
    }
    if(e.target.class != "dropdown_data_window") {
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({"padding" : "0px", "height" : "0px"});

    }
});


function getUser(value, user) {
    $.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
        $(".results").html(data);
    });
}
//It is going to send the request to includes/handlers/ajax_friend_search.php with these {query:value, userLoggedIn:user} value
//and it will set the value in $(".results") div (results is a class name from messages.php) with .html(data); contains of what data was returned

function getDropdownData(user, type) {
    if($(".dropdown_data_window").css("height") == "0px" ) {
        var pageName;

        if(type == 'notification') {
            pageName = "ajax_load_notifications.php";
            $("span").remove("#unread_notification");
        }
        else if(type == 'message') {
            pageName = "ajax_load_messages.php";
            $("span").remove("#unread_mesage");
        }
        var ajaxReq = $.ajax({
            url: "includes/handlers/" + pageName,
            type: "POST",
            data: "page=1&userLoggedIn=" + user,
            cache: false,

            success: function(response) {
                $(".dropdown_data_window").html(response);
                $(".dropdown_data_window").css({"padding": "0px", "height":"280px", "border":"0px solid #D3D3D3"});
                $("#dropdown_data_type").val(type);
            }
        });
    }
    else{
        $(".dropdown_data_window").html("");
        $(".dropdown_data_window").css({"padding": "0px", "height":"0px","border":"none"});
    }
}
/* we call function  passed user and type'notification' or 'message'
if height == 0px(no message), execute
*/



function getLiveSearchUsers(value, user) {
    $.post("includes/handlers/ajax_search.php", {query:value, userLoggedIn:user}, function(data){

        if($(".search_results_footer_empty")[0]) {
            $(".search_results_footer_empty").toggleClass("search_results_footer");
            $(".search_results_footer_empty").toggleClass("search_results_footer_empty");
        }
            $('.search_results').html(data);
            $('.search_results_footer').html("<a href='search.php?q=" + value + "'>See All Results</a>");
        
            if(value.length == 0) { //type in something but didn't find any results, remove it all
            $('.search_results_footer').html("");
            $('.search_results_footer').toggleClass("search_results_footer_empty");
            $('.search_results_footer').toggleClass("search_results_footer");
        }

    });
//toggleClass: if it is on the page, remove it. If it is showing hide it.
//data: whatever returned from this ($.post("includes/handlers/ajax_search.php") ajax called. and return to this $('.search_results').html(data); dropdown menu. In this case will be the string of bunch of users.
}
