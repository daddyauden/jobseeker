$(document).ready(function() {
    $("#form_cancel").click(function(){
        window.location.href = $("#form_redirect_uri").val();
    });
});