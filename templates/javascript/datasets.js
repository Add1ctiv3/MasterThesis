

var DATASETS_COMBINED = {
    alreadySelected: [],
    selectionLimit: 0,
    mode: "",
    initialSelect: "",
    initialSelect: "",
    determineInfo: function() {

        if(this.mode == "union") {
            return "With union you can create a dataset from the combined contents of any number of sets. If there are common records between multiple datasets, then the duplicates are omitted. You add datasets that you wish to combine with the add button below and then provide a dataset name for the dataset that will contain all the combined data.";
        }

        if(this.mode == "intersection") {
            return "With intersection you create a dataset containing the records that commonly exist in the two datasets selected below. A limitation of two datasets have been applied to avoid timeouts due to large datasets. If you wish to intersect more than two datasets, intersect the first two and then intersect the derrived dataset with the next and so on...";
        }

        if(this.mode == "asymmetric_difference") {
            return "With asymmetric difference you create a dataset containing records that exist ONLY in each dataset and are NOT in common.";
        }

        if(this.mode == "subtracting") {
            return "With subtracting you can create a dataset containing records that exist in dataset A and NOT in dataset B. A limitation of two datasets have been applied to avoid timeouts due to large datasets.";
        }

        return "";

    },
    determineImage: function() {
        return "<img src='templates/images/"+this.mode+".jpg' class='full' />";
    },
    determineMechanism: function() {

        if(this.mode == "union") {
            return this.initialSelect + '<img id="add-dataset-for-combining-button" title="Add Dataset" src="templates/images/add-button.png" width="30" height="30">';
        }

        if(this.mode == "subtracting" || this.mode == "intersection" || this.mode == "asymmetric_difference") {
            return "<span class='combining-sets-label'>Set A</span>" + this.initialSelect + "<span class='combining-sets-label'>Set B</span>" + this.initialSelect;
        }

    }
}
	
$(document).ready(function() {

    resizeSidebar();

    $(window).on('resize', function(e) {
        resizeSidebar();
    });

	toastr.options = { positionClass: 'toast-bottom-right' }
	toastr.options.closeButton = true;
	toastr.options.newestOnTop = true;
	toastr.options.closeMethod = 'fadeOut';
	toastr.options.closeDuration = 300;
	toastr.options.closeEasing = 'swing';

	$(".panel-input-label").click(function() {

		var target = "#" + $(this).attr("for") + "-input";
		$(target).focus();

	});

    $("#available-datasets").slimScroll({
        width: '800px',
        height: '500px',
        //size: '10px',
        position: 'right',
        color: '#0073aa',
        alwaysVisible: false,
        distance: '1px',
        start: 'top',
        railVisible: true,
        railColor: '#222',
        railOpacity: 0.3,
        wheelStep: 10,
        allowPageScroll: true,
        disableFadeOut: false
    });

    //what happens when you click on an available set
    $(document).on("click", ".available-dataset", function() {

        if($(this).hasClass("selected-dataset")) {
            $(this).removeClass("selected-dataset")
        } else {
            $(this).addClass("selected-dataset")
        }

        if($(".selected-dataset").length > 0) {
            $(".datasets-control-button").each(function() {
                $(this).removeClass("inactive-button");
            });
        } else {
            $(".datasets-control-button").each(function() {
                $(this).addClass("inactive-button");
            });
        }

        if($(".selected-dataset").length > 1) {
            $("#visualize-dataset-button").addClass("inactive-button");
        }

    });

    //what happens when you click the delete button
    $("#delete-dataset-button").click(function() {

        if($(this).hasClass("inactive-button")) { return; }

        $("#dialog-delete-dataset-confirm").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Delete": function() {

                    var idsArray = [];
                    $(".selected-dataset").each(function() {
                        idsArray.push($(this).attr("rel"));
                    });

                    //delete the dataset function here
                    deleteDatasets(idsArray);

                    $( this ).dialog( "close" );

                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });

    });

	$("#combine-datasets-button").click(function() {

        getAvailableDatasetsForCombining(true);

	});

   $("#create-dataset-button").click(function() {

       $("#create-dataset-dialog").dialog({
           width: 400,
           height: 170,
           modal: true,
           resizable: false,
           title: "Create New Dataset",
           closeOnEscape: true,
           buttons: {
               "Create" : function() {
                   createDataset($("#new-dataset-name-input").val(), $("#new-dataset-type-select option:selected").attr("rel"), $(this));
               } //end of create function
           },
           close: function() {
               $( this ).dialog( "close" );
               $("#new-dataset-name-input").val("");
               $("#new-dataset-type-select option:first-child").prop("selected", true);
           }
       });

   });

   //what happens when you change combine mode
   $(document).on("change", "#combine-datasets-mode-select", function() {

       DATASETS_COMBINED.mode = $("#combine-datasets-mode-select option:selected").attr("rel");
       $("#datasets-combining-info").text(DATASETS_COMBINED.determineInfo());
       $("#datasets-combining-image").html(DATASETS_COMBINED.determineImage())
                                     .find(":only-child")
                                     .hide()
                                     .fadeIn(200);

       if(DATASETS_COMBINED.mode == "union") {
           DATASETS_COMBINED.selectionLimit = 0;
       }

       if(DATASETS_COMBINED.mode == "intersection") {
           DATASETS_COMBINED.selectionLimit = 2;
       }

       if(DATASETS_COMBINED.mode == "asymmetric_difference") {
           DATASETS_COMBINED.selectionLimit = 0;
       }

       if(DATASETS_COMBINED.mode == "subtracting") {
           DATASETS_COMBINED.selectionLimit = 2;
       }

       DATASETS_COMBINED.alreadySelected = [];

        $("#datasets-combining-set-container").html(DATASETS_COMBINED.determineMechanism());

   });

   //what happens when you select a dataset from a temp select
   $(document).on("change", ".datasets-for-combining-select", function() {

       DATASETS_COMBINED.alreadySelected = [];

       $(".datasets-for-combining-select").each(function() {
           if($(this).children(":selected").attr("rel") != "choose-set") {
               DATASETS_COMBINED.alreadySelected.push($(this).children(":selected").attr("rel"));
           }
       });

   });

   //what happens when you click the add set button
   $(document).on("click", "#add-dataset-for-combining-button", function() {

       breakVar = false;

       $(".datasets-for-combining-select").each(function() {
           if($(this).children(":selected").attr("rel") == "choose-set") {
               breakVar = true;
           }
       });

       if(breakVar) {
           toastr.error("First select a dataset in all the available lists!");
           return;
       }

       getAvailableDatasetsForCombining(false);

   });

   //what happens when you combine all selected sets
   $(document).on("click", "#combine-datasets-start-button", function() {

       if(DATASETS_COMBINED.alreadySelected.length < 2) {
           toastr.error("You need to select at least two datasets!");
           return;
       }

       if($("#datasets-combining-derivative-set-name-input").val() == "" || $("#datasets-combining-derivative-set-name-input").val() == null) {
           toastr.error("You need to provide a name for the derivative dataset!");
           return;
       }

       var obj = {
           datasets: DATASETS_COMBINED.alreadySelected,
           mode: DATASETS_COMBINED.mode,
           newName: $("#datasets-combining-derivative-set-name-input").val(),
           uri: "combineDatasets"
       }

       //ajax call
       $.ajax({
           type: "POST",
           url: "ajax/ajax.datasets_requests.php",
           dataType:"json",
           data: obj,
           beforeSend: function() {
               $( "#ajax-spinner-dialog" ).dialog({
                   modal: true,
                   resizable: false,
                   title: "Please Wait...",
                   closeOnEscape: false,
                   open: function(event, ui) {
                       $(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                   }
               });
           },
           error: function(xhr, ajaxOptions, thrownError) {
               $( "#ajax-spinner-dialog" ).dialog("close");
               toastr.error(thrownError);
           },
           success : function(json) {

               $( "#ajax-spinner-dialog" ).dialog("close");

               if (json.error != null) {
                   toastr.error(json.error);
                   return;
               }

               if (json.reply && json.reply.result == "success") {

                   $("#combine-datasets-panel").dialog("destroy");
                   $("#combine-datasets-mode-select option:first-child").prop("selected", true);
                   resetDatasetsCombinedGlobal();
                   $("#datasets-combining-derivative-set-name-input").val("");

                   var html = "<div class='available-dataset' rel='"+json.reply.data.name+"'>"+json.reply.data.name+" <span class='available-dataset-info'>"+json.reply.data.infoLine+"</span></div>";

                   $(html).prependTo($("#available-datasets"));

               } //end of success if block

               if (json.reply && json.reply.result == "failure") {
                   toastr.error(json.reply.message);
               }
           }
       }); //end of ajax call

   });

    $("#visualize-dataset-button").click(function() {

        if($(this).hasClass("inactive-button")) {
            return;
        }

        window.location = "index.php?p=user/analyzer&set=" + $(".selected-dataset").attr("rel");

    });

});

function getAvailableDatasetsForCombining(initialCallOrNot) {

    var Obj = {
        uri: "getAvailableDatasetsForCombining",
        alreadySelected: DATASETS_COMBINED.alreadySelected,
        limit: DATASETS_COMBINED.selectionLimit,
        mode: DATASETS_COMBINED.mode
    };

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.datasets_requests.php",
        dataType:"json",
        data: Obj,
        beforeSend: function() {
            $("#datasets-ajax-spinner").show();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $("#datasets-ajax-spinner").hide();
            toastr.error(thrownError);
        },
        success : function(json) {

            $("#datasets-ajax-spinner").hide();

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                var select = createdAvailableDatasetsForCombiningSelect(json.reply.data);

                if(initialCallOrNot) {

                    DATASETS_COMBINED.initialSelect = select;
                    DATASETS_COMBINED.mode = $("#combine-datasets-mode-select option:selected").attr("rel");
                    $("#datasets-combining-info").text(DATASETS_COMBINED.determineInfo());
                    $("#datasets-combining-image").html(DATASETS_COMBINED.determineImage())
                        .find(":only-child")
                        .hide()
                        .fadeIn(200);
                    DATASETS_COMBINED.selectionLimit = 0;


                    var initHTML = select + '<img id="add-dataset-for-combining-button" title="Add Dataset" src="templates/images/add-button.png" width="30" height="30" />';

                    $("#datasets-combining-set-container").html(initHTML);

                    $("#combine-datasets-panel").dialog({
                        width: 1000,
                        height: 590,
                        title: "Combine Datasets",
                        close: function() {
                            $(this).dialog("destroy");
                            $("#combine-datasets-mode-select option:first-child").prop("selected", true);
                            resetDatasetsCombinedGlobal();
                            $("#datasets-combining-derivative-set-name-input").val("");
                        }
                    });

                } else {
                    $(select).insertBefore($("#add-dataset-for-combining-button"));
                }

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function createdAvailableDatasetsForCombiningSelect(arrayWithSetNames) {

    var html = "<select class='datasets-for-combining-select'>";
            html += "<option rel='choose-set'>Please select a dataset...</option>";

    if(!arrayWithSetNames || arrayWithSetNames.length == 0) {
        html += "</select>";
        return html;
    }

    for(var i = 0; i < arrayWithSetNames.length; i++) {

        html += "<option rel='"+arrayWithSetNames[i]+"'>"+arrayWithSetNames[i]+"</option>";

    }

    html += "</select>";
    return html;

}

function createDataset(setName, setType, dialog) {

    if(setName == "" || setName == null) {
        toastr.error("Fill in a set name...");
        return;
    }

    var Obj = {
        uri: "newSet",
        name: setName,
        type: setType
    };

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.datasets_requests.php",
        dataType:"json",
        data: Obj,
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

                var html = "<div class='available-dataset' rel='"+Obj.name+"'>" + Obj.name + "<span class=\"available-dataset-info\">"+json.reply.info_message+"</span>" + "</div>";

                $(html).prependTo($("#available-datasets"));

                dialog.dialog("close");

                toastr.success(json.reply.message);

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call
}

function deleteDatasets(idsArray) {

    var obj = {
        ids: idsArray,
        uri: "deleteDatasets"
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.datasets_requests.php",
        dataType:"json",
        data: obj,
        beforeSend: function() {
            $("#datasets-ajax-spinner").fadeIn(200);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $("#datasets-ajax-spinner").fadeOut(200);
        },
        success : function(json) {

            $("#datasets-ajax-spinner").fadeOut(200);

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
            if (json.reply && json.reply.result == "success") {
                $(".available-dataset").each(function() {
                    if(idsArray.indexOf($(this).attr("rel")) !== -1) {
                        $(this).remove();
                    }
                });
                $("#delete-dataset-button").addClass("inactive-button");
                $("#view-dataset-content").addClass("inactive-button");
                $("#visualize-dataset-button").addClass("inactive-button");
                toastr.success(json.reply.message);
            }
            if (json.error != null) {
                toastr.error(json.error);
            }

        }
    }); //end of ajax call

}

function resetDatasetsCombinedGlobal() {
    DATASETS_COMBINED.alreadySelected = [];
    DATASETS_COMBINED.selectionLimit = 0;
    DATASETS_COMBINED.mode = "";
}