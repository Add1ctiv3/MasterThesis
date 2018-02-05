
$(document).ready(function() {

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

