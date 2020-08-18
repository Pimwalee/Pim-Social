$(document).ready(function() {

    //on click signup, hide login and show registeration form
    $("#signup").click(function() {
        $("#first").slideUp("slow", function(){
            $("#second").slideDown("slow");
        });
    });

    //on click signup, hide registeration and show login form
    $("#signin").click(function() {
        $("#second").slideUp("slow", function(){
            $("#first").slideDown("slow");
        });
    });

});