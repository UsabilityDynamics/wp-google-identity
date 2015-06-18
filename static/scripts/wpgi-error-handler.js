!function($, wpgi) {
    "undefined" != typeof wpgi.message && $(document).ready(function() {
        $("body").append('<div id="wpgi_error"><p><label>' + wpgi.message + '</label> <a class="wpgi-close" href="javascript:;">' + wpgi.close + "</a></p></div>"), 
        $("#wpgi_error").show(), console.log($("#wpgi_error")), $("a.wpgi-close", "#wpgi_error").on("click", function() {
            $("#wpgi_error").remove();
        });
    });
}(jQuery, wpgi_err);