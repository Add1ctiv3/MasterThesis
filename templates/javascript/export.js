$(document).ready(function() {

    $("#import-data-panel").dialog({
        width: 800,
        height: 620,
        autoOpen: true,
        draggable: true,
        resizable: false,
        closeOnEscape: false,
        modal:false,
        title: "Import Data",
        position: {my: "left top", at: "left top", of: "#main-container"},
        open: function() {
            $("div[aria-describedby=import-data-panel]").find(".ui-dialog-titlebar-close").remove();

            $("#import-form-duration-input").durationPicker({
                hours: {
                    label: '',
                    min: 0,
                    max: 24
                },
                minutes: {
                    label: '',
                    min: 0,
                    max: 59
                },
                seconds: {
                    label: '',
                    min: 0,
                    max:59
                },
                classname: "durationpicker",
                type: 'number',
                responsive: false
            });

            $("#import-form-timestamp-input").datetimepicker({
                timeFormat: 'HH:mm:ss',
                dateFormat: 'dd/mm/yy'
            });
        },
        close: function() {
            $(this).dialog("destroy");
        }
    });

    //first get the sets and then on success open the dialog
    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.file_oriented_requests.php",
        dataType:"json",
        data: {uri: "getSets"},
        beforeSend: function() {

        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
        },
        success : function(json) {

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var html = "";

                for(var i=0; i < json.reply.message.length; i++) {

                    var record = json.reply.message[i];
                    html += "<div class='set-block selectable-set' rel='"+record.name+"'>"+record.name + " - " + record.creator + " ("+record.visibility+")" + "</div>";

                }

                $("#sets-container").html(html);

                //make the sets dialog
                $( "#export-data-panel" ).dialog({
                    autoOpen: true,
                    height: 600,
                    width: 550,
                    modal: false,
                    draggable: true,
                    resizable: false,
                    closeOnEscape: false,
                    position: {my: "left top", at: "left top", of: "#main-container"},
                    title: "Export your data",
                    buttons: {
                        "Export": function() {

                            if(!$("#export-dataset-file-name-input").val()) {
                                toastr.error("Type a file name!");
                                return;
                            }

                            if($(".selected-set").length == 0) {
                                toastr.error("Select a dataset to export!");
                                return;
                            }

                            exportFile();

                        }
                    },
                    open: function() {
                        $("div[aria-describedby=export-data-panel]").find(".ui-dialog-titlebar-close").hide();
                    }
                });

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

    //what happens when you click a set
    $(document).on("click", ".selectable-set", function() {

        if($(this).hasClass("selected-set")) {
            $(this).removeClass("selected-set");
            return;
        }

        $(".selectable-set").removeClass("selected-set");

        $(this).addClass("selected-set");

    });

});

function exportFile() {

    $.ajax({
        type: "POST",
        url: "ajax/ajax.exporting_requests.php",
        dataType:"json",
        data: {uri: "exportSet", filename: $("#export-dataset-file-name-input").val(), set: $(".selected-set").attr("rel")},
        beforeSend: function() {

        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
        },
        success : function(json) {

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                $("#export-dataset-file-name-input").val("");
                $(".selected-set").removeClass("selected-set");

                toastr.success(json.reply.message);
                window.location = json.reply.link;

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

