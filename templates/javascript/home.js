
$(document).ready(function() {

    resizeSidebar();

    $(window).on('resize', function(e) {
        resizeSidebar();
    });

	toastr.options = { positionClass: 'toast-bottom-right', }
	toastr.options.closeButton = true;
	toastr.options.newestOnTop = true;
	toastr.options.closeMethod = 'fadeOut';
	toastr.options.closeDuration = 300;
	toastr.options.closeEasing = 'swing';

    $(".panel-input-label").click(function() {

        var target = "#" + $(this).attr("for") + "-input";
        $(target).focus();

    });

});

function resizeSidebar() {
    var body = document.body,
        html = document.documentElement;
    var height = Math.max( body.scrollHeight, body.offsetHeight,
        html.clientHeight, html.scrollHeight, html.offsetHeight );
    $("#left-side-bar").css({'height': height + "px"});
}