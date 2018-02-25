var RECORDS_PER_PAGE = 600;

$(document).ready(function() {

    resizeSidebar();

    $(window).on('resize', function(e) {
        resizeSidebar();
    });

    $("#queries-panel").dialog({
        width: 800,
        height: 820,
        autoOpen: true,
        draggable: true,
        resizable: false,
        closeOnEscape: false,
        modal:false,
        title: "Query the Database",
        position: {my: "left top", at: "left top", of: "#main-container"},
        open: function() {
            $("div[aria-describedby=queries-panel]").find(".ui-dialog-titlebar-close").remove();
        },
        close: function() {
            $(this).dialog("destroy");
        }
    });

    $("#query-form-to-date-input").datepicker({dateFormat: 'dd/mm/yy'});
    $("#query-form-from-date-input").datepicker({dateFormat: 'dd/mm/yy'});

    $("#query-form-to-creation-date-input").datepicker({dateFormat: 'dd/mm/yy'});
    $("#query-form-from-creation-date-input").datepicker({dateFormat: 'dd/mm/yy'});

    $("#query-form-from-time-input").timepicker({timeFormat: "HH:mm:ss"});
    $("#query-form-to-time-input").timepicker({timeFormat: "HH:mm:ss"});

    $("#query-form-duration-input").durationPicker({
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

    $(".durationpicker-label").remove();

    prepareAutocompleteSetNames();

    //what happens when you click the query button
    $("#query-button").click(function() {

        var obj = gatherQueryParameters();
        obj.uri = "queryDatabase";

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
            dataType:"json",
            data: obj,
            beforeSend: function() {
                $( "#ajax-spinner-dialog" ).dialog({
                    modal: true,
                    resizable: false,
                    title: "Please Wait...",
                    closeOnEscape: false,
                    open: function(event, ui) {
                        $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
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

                    showQueryResults(json.reply.data, json.reply.total_records, 1);

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call

    });

    //what happens when you click the query 2 button
    $("#query-button2").click(function() {

        var obj = gatherQuery2Parameters();
        obj.uri = "query2Database";

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
            dataType:"json",
            data: obj,
            beforeSend: function() {
                $( "#ajax-spinner-dialog" ).dialog({
                    modal: true,
                    resizable: false,
                    title: "Please Wait...",
                    closeOnEscape: false,
                    open: function(event, ui) {
                        $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
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

                    showQueryResults(json.reply.data, json.reply.total_records, 2)

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call

    });

    //what happens when you click a query-result record
    $(document).on("click", ".query-result-record", function() {
        var curVal = $("span#selected-records").html();

        if($(this).hasClass("selected-record")) {
            $(this).removeClass("selected-record");
            curVal--;
            $("#selected-records").text(curVal);
            return;
        }
        curVal++;
        $("#selected-records").text(curVal);
        $(this).addClass("selected-record");
    });

    $(document).on("click", "#select-all-records", function() {
        var counter = 0;
        $(".query-result-record").each(function() {
            $(this).addClass("selected-record");
            counter++;
        });
        $("#selected-records").text(counter);
    });

    $(document).on("click", "#deselect-all-records", function() {
        $(".query-result-record").removeClass("selected-record");
        $("#selected-records").text("0");
    });

    //what happens when you click the create new set button
    $(document).on("click", "#create-new-set-button", function() {

        if($("#create-new-set-input").val() == "" || $("#create-new-set-input").val() == null) {
            toastr.error("Fill in a set name...");
            return;
        }

        var Obj = {
            uri: "newSet",
            name: $("#create-new-set-input").val(),
            type: $("#new-set-type option:selected").text()
        };

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
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

                    var html = "<div class='set-block selectable-set' rel='"+Obj.name+"'>"+Obj.name + " - " + $("#user-box").attr("rel") + " ("+Obj.type+")" + "</div>";
                    $(html).prependTo($("#sets-container"));
                    $("#create-new-set-input").val("");
                    $("#new-set-type option:first").prop("selected", true);

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call

    });

    //what happens when you click a set
    $(document).on("click", ".selectable-set", function() {

        if($(this).hasClass("selected-set")) {
            $(this).removeClass("selected-set");
            return;
        }

        $(".selectable-set").removeClass("selected-set");

        $(this).addClass("selected-set");

    });

    $("#query-results-pagination-previous-button").click(function() {

        var currentPage = parseInt($("#query-results-pagination-current-page").attr("rel"));
        var form = parseInt($("div[aria-describedby=query-results-container]").attr("form"));

        if (currentPage == 1) {
            return;
        }

        if(form == 1) {
            var obj = gatherQueryParameters();
            obj.uri = "queryDatabase";
        }
        if(form == 2) {
            var obj = gatherQuery2Parameters();
            obj.uri = "query2Database";
        }

        var offset = (currentPage-2)*RECORDS_PER_PAGE;

        obj.offSet = offset;
        obj.total_records = parseInt($("div[aria-describedby=query-results-container]").attr("total_records"));

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
            dataType:"json",
            data: obj,
            beforeSend: function() {
                $( "#ajax-spinner-dialog" ).dialog({
                    modal: true,
                    resizable: false,
                    title: "Please Wait...",
                    closeOnEscape: false,
                    open: function(event, ui) {
                        $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
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

                    var newPage = currentPage-1;
                    var startOffset = (currentPage-2)*RECORDS_PER_PAGE + 1;

                    var endOffset = newPage*RECORDS_PER_PAGE;
                    var tag = "Page " + newPage + " ( " + startOffset + " - " + endOffset + " )";

                    $("#query-results-pagination-current-page").attr("rel", currentPage-1);
                    $("#query-results-pagination-current-page").text(tag);

                    showQueryResults(json.reply.data, json.reply.total_records, form);

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call

    });

    $("#query-results-pagination-next-button").click(function() {

        var currentPage = parseInt($("#query-results-pagination-current-page").attr("rel"));
        var totalRecords = parseInt($("div[aria-describedby=query-results-container]").attr("total_records"));

        var form = parseInt($("div[aria-describedby=query-results-container]").attr("form"));

        var maxPage = Math.ceil(totalRecords/RECORDS_PER_PAGE);

        if (currentPage == maxPage) {
            return;
        }

        //calculate offset
        var offset = currentPage*RECORDS_PER_PAGE;
        if(form == 1) {
            var obj = gatherQueryParameters();
            obj.uri = "queryDatabase";
        }
        if(form == 2) {
            var obj = gatherQuery2Parameters();
            obj.uri = "query2Database";
        }

        obj.offSet = offset;
        obj.total_records = totalRecords;

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
            dataType:"json",
            data: obj,
            beforeSend: function() {
                $( "#ajax-spinner-dialog" ).dialog({
                    modal: true,
                    resizable: false,
                    title: "Please Wait...",
                    closeOnEscape: false,
                    open: function(event, ui) {
                        $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
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

                    var newPage = currentPage+1;
                    var startOffset = (currentPage*RECORDS_PER_PAGE) + 1;
                    var endOffset = newPage*RECORDS_PER_PAGE;

                    if(endOffset > totalRecords) {
                        endOffset = totalRecords;
                    }

                    var tag = "Page " + newPage + " ( " + startOffset + " - " + endOffset + " )";

                    $("#query-results-pagination-current-page").attr("rel", currentPage+1);
                    $("#query-results-pagination-current-page").text(tag);

                    showQueryResults(json.reply.data, json.reply.total_records, form);

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call

    });

    $(document).on("click", "#add-query-results-to-set-button", function() {

        //first get the sets
        var Obj = {
            uri: "getSets"
        };

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.file_oriented_requests.php",
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

                    var html = "";

                    for(var i=0; i < json.reply.message.length; i++) {

                        var record = json.reply.message[i];
                        html += "<div class='set-block selectable-set' rel='"+record.name+"'>"+record.name + " - " + record.creator + " ("+record.visibility+")" + "</div>";

                    }

                    $("#sets-container").html(html);

                    //make the sets dialog
                    $( "#create-set-on-import-dialog" ).dialog({
                        autoOpen: true,
                        height: 600,
                        width: 550,
                        modal: true,
                        buttons: {
                            "Continue": function() {

                                if($(".selected-set").length == 0) {
                                    toastr.error("Select a dataset to associate our records with!");
                                    return;
                                }

                                //associate the records with the selected set
                                var form = parseInt($("div[aria-describedby=query-results-container]").attr("form"));
                                var obj = {}
                                if(form == 1) {
                                    obj = gatherQueryParameters();
                                    obj.uri = "associateQueryResultWithSet";
                                }
                                if(form == 2) {
                                    obj = gatherQuery2Parameters();
                                    obj.uri = "associateQuery2ResultWithSet";
                                }

                                obj.set_to_associate_with = $(".selected-set").attr("rel");

                                //ajax call
                                $.ajax({
                                    type: "POST",
                                    url: "ajax/ajax.queries_requests.php",
                                    dataType:"json",
                                    data: obj,
                                    beforeSend: function() {

                                        $( "#ajax-spinner-dialog" ).dialog({
                                            modal: true,
                                            resizable: false,
                                            title: "Please Wait...",
                                            closeOnEscape: false,
                                            open: function(event, ui) {
                                                $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
                                            }
                                        });
                                    },
                                    error: function(xhr, ajaxOptions, thrownError) {
                                        toastr.error(thrownError);
                                        $( "#ajax-spinner-dialog" ).dialog("close");
                                    },
                                    success : function(json) {

                                        $( "#ajax-spinner-dialog" ).dialog("close");

                                        if (json.reply && json.reply.result == "failure") {
                                            toastr.error(json.reply.message);
                                        }
                                        if (json.reply && json.reply.result == "success") {

                                            $( "#create-set-on-import-dialog" ).dialog( "close" );

                                            toastr.success(json.reply.message);
                                        }
                                        if (json.error != null) {
                                            toastr.error(json.error);
                                        }

                                    }
                                }); //end of ajax call

                            },
                            Cancel: function() {
                                $( "#create-set-on-import-dialog" ).dialog( "close" );
                            }
                        },
                        close: function() {
                            //close the dialog
                            $( "#create-set-on-import-dialog" ).dialog( "close" );
                        }
                    });

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call to get sets

    });

});

function viewRecordsDetails(record) {

    RECORD.init(record)
          .initDialog();

}

function showQueryResults(data, total_records, formNumber) {

    var html = "";

    for(var i = 0; i < data.length; i++) {
        html += commRecordHtml(data[i]);
    }

    $("#query-results-inner-container").html(html);
    $("#query-results-records-number").html(+total_records+" Records");

    //reset the selected records
    $("#selected-records").text("0");
    $(".selected-records").removeClass(".selected-records");

    if(total_records <= RECORDS_PER_PAGE) {
        $("#query-results-pagination-container").hide();
        $("#query-results-pagination-current-page").text("Page 1 ( 1 - "+total_records+" )");
    } else {
        $("#query-results-pagination-container").show();
    }

    $("div[aria-describedby=query-results-container]").attr("total_records", total_records);

    $("#query-results-container").dialog({
        width: 640,
        height: (total_records <= RECORDS_PER_PAGE) ? 635 : 695,
        autoOpen: true,
        resizable: false,
        draggable: true,
        modal:true,
        title: "Query Results",
        close: function() {
            $(this).dialog("destroy");
            $("#selected-records").text("0");
            //reset the page number
            $("#query-results-pagination-current-page").attr("rel", 1);
            $("#query-results-pagination-current-page").text("Page 1 ( 1 - "+RECORDS_PER_PAGE+" )");
            $("div[aria-describedby=query-results-container]").removeAttr("form");
        },
        open: function() {
            $("div[aria-describedby=query-results-container]").attr("total_records", total_records);
            $("div[aria-describedby=query-results-container]").attr("form", formNumber);
            setupRightClickTrigger();
        }
    });

}

function setupRightClickTrigger() {

    $.contextMenu({
        selector: '.query-result-record',
        events: {
            show: function(options) {
                if(!$(this).hasClass("selected-record")) {
                    $(this).addClass("selected-record");
                    var curVal = $("span#selected-records").html();
                    curVal++;
                    $("#selected-records").text(curVal);
                }
            }
        },
        items: {
            "view_details": {
                name: "View Details",
                icon: "edit",
                callback: function(key, opt){

                    var record = {
                        number: $(this).attr("number"),
                        ccode: $(this).attr("country_code"),
                        type: $(this).attr("type"),
                        caller: $(this).attr("caller"),
                        called: $(this).attr("called"),
                        timestamp: $(this).attr("intTimestamp"),
                        timestamp_str: $(this).attr("timestamp"),
                        duration: $(this).attr("duration"),
                        weight: $(this).attr("weight"),
                        record_type: $(this).attr("record_type"),
                        creation: $(this).attr("creation"),
                        num_weight: $(this).attr("num_weight")
                    }

                    viewRecordsDetails(record);

                }
            },
            "add_to_set": {
                name: "Add to Dataset",
                icon: "add",
                callback: function(key, opt) {

                    var records = [];

                    $(".selected-record").each(function() {

                        var obj;

                        obj = {
                            number: $(this).attr("number"),
                            ccode: $(this).attr("country_code"),
                            type: $(this).attr("type"),
                            caller: $(this).attr("caller"),
                            called: $(this).attr("called"),
                            timestamp: $(this).attr("intTimestamp"),
                            timestamp_str: $(this).attr("timestamp"),
                            duration: $(this).attr("duration"),
                            weight: $(this).attr("weight"),
                            record_type: $(this).attr("record_type"),
                            creation: $(this).attr("creation"),
                            num_weight: $(this).attr("num_weight")
                        }

                        records.push(obj);

                    }); // end of each function loop

                    addRecordsToSet(records);

                }
            },
            "delete": {
                name: "Delete",
                icon: "delete",
                callback: function(key, opt){

                    if(!confirm("Are you sure you want to delete these records and all their associations from the database? You can not recover the records if deleted!")) {
                        return;
                    }

                    var records = [];

                    $(".selected-record").each(function() {

                        var obj;

                        obj = {
                            number: $(this).attr("number"),
                            ccode: $(this).attr("country_code"),
                            type: $(this).attr("type"),
                            caller: $(this).attr("caller"),
                            called: $(this).attr("called"),
                            timestamp: $(this).attr("intTimestamp"),
                            timestamp_str: $(this).attr("timestamp"),
                            duration: $(this).attr("duration"),
                            weight: $(this).attr("weight"),
                            record_type: $(this).attr("record_type"),
                            creation: $(this).attr("creation"),
                            num_weight: $(this).attr("num_weight")
                        }

                        records.push(obj);

                    }); // end of each function loop

                    deleteRecords(records);

                }
            }
        }
    });

}

function deleteRecords(records) {

    var obj = {
        records: records,
        uri: "deleteRecords"
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.queries_requests.php",
        dataType:"json",
        data: obj,
        beforeSend: function() {

            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
                resizable: false,
                title: "Please Wait...",
                closeOnEscape: false,
                open: function(event, ui) {
                    $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
                }
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            $( "#ajax-spinner-dialog" ).dialog("close");
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
            if (json.reply && json.reply.result == "success") {

                toastr.success(json.reply.message);

                var form = parseInt($("div[aria-describedby=query-results-container]").attr("form"));

                if(form == 1) {
                    $("#query-button").click();
                }
                if(form == 2) {
                    $("#query-button2").click();
                }
                $("#selected-records").text("0");

            }
            if (json.error != null) {
                toastr.error(json.error);
            }

        }
    }); //end of ajax call

}

function addRecordsToSet(records) {

    //first get the sets
    var Obj = {
        uri: "getSets"
    };

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.file_oriented_requests.php",
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

                var html = "";

                for(var i=0; i < json.reply.message.length; i++) {

                    var record = json.reply.message[i];
                    html += "<div class='set-block selectable-set' rel='"+record.name+"'>"+record.name + " - " + record.creator + " ("+record.visibility+")" + "</div>";

                }

                $("#sets-container").html(html);

                //make the sets dialog
                $( "#create-set-on-import-dialog" ).dialog({
                    autoOpen: true,
                    height: 600,
                    width: 550,
                    modal: true,
                    buttons: {
                        "Continue": function() {

                            if($(".selected-set").length == 0) {
                                toastr.error("Select a dataset to associate our records with!");
                                return;
                            }

                            //associate the records with the selected set
                            var obj = {
                                records: records,
                                set: $(".selected-set").attr("rel"),
                                uri: "associateRecordsWithSet"
                            }
console.log(obj);
                            //ajax call
                            $.ajax({
                                type: "POST",
                                url: "ajax/ajax.queries_requests.php",
                                dataType:"json",
                                data: obj,
                                beforeSend: function() {

                                    $( "#ajax-spinner-dialog" ).dialog({
                                        modal: true,
                                        resizable: false,
                                        title: "Please Wait...",
                                        closeOnEscape: false,
                                        open: function(event, ui) {
                                            $("div[aria-describedby=ajax-spinner-dialog]").find(".ui-dialog-titlebar-close").hide();
                                        }
                                    });
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
                                    toastr.error(thrownError);
                                    $( "#ajax-spinner-dialog" ).dialog("close");
                                },
                                success : function(json) {

                                    $( "#ajax-spinner-dialog" ).dialog("close");

                                    if (json.reply && json.reply.result == "failure") {
                                        toastr.error(json.reply.message);
                                    }
                                    if (json.reply && json.reply.result == "success") {

                                        $( "#create-set-on-import-dialog" ).dialog( "close" );

                                        toastr.success(json.reply.message);
                                    }
                                    if (json.error != null) {
                                        toastr.error(json.error);
                                    }

                                }
                            }); //end of ajax call

                        },
                        Cancel: function() {
                            $( "#create-set-on-import-dialog" ).dialog( "close" );
                        }
                    },
                    close: function() {
                        //close the dialog
                        $( "#create-set-on-import-dialog" ).dialog( "close" );
                    }
                });

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call to get sets

}

function commRecordHtml(rec) {

    if(rec.record_type == "telecommunication") {
        var icon = "telecommunication.png"

        if(rec.type == "SMS") {
            icon = "sms.png";
        }

        var html = "<div class='query-result-record no-select' creation='"+rec.creation_date+"' record_type='"+rec.record_type+"' type='"+rec.type+"' intTimestamp='"+rec.stamp+"' caller='"+rec.caller+"' called='"+rec.called+"' timestamp='"+rec.timestamp+"' duration='"+rec.duration+"' weight='"+rec.weight+"'>";
            html+= "<img class='query-result-record-icon no-select' src='templates/images/"+icon+"'>";
            html+= '<div class="query-result-record-caller no-select">'+rec.caller+'</div>';
            html += '<img src="templates/images/arrow-pointing-to-right.png" class="telecommunication-arrow no-select">';
            html += '<div class="query-result-record-called no-select">'+rec.called+'</div>';
            html += " | ";
            html += '<div class="query-result-record-date-and-time no-select">'+rec.timestamp+'</div>';
            html += " | ";
            html += '<div class="query-result-record-duration no-select">'+rec.duration+' seconds</div>';
        html += "</div>";

        return html;
    }

    if(rec.record_type == "telephone") {

        var icon = "mobile.png";

        if(rec.type == "landline") {
            icon = "telephone.png";
        }

        var html = '<div class="query-result-record no-select" num_weight="'+rec.num_weight+'" creation="'+rec.creation+'" type="'+rec.type+'" record_type="'+rec.record_type+'" number="'+rec.number+'" country_code="'+rec.country_code+'">';

                html += '<img class="query-result-record-icon no-select" src="templates/images/'+icon+'">';
                html += '<div class="query-result-telephone-number no-select">'+rec.number+'</div>';

         html += '</div>';

         return html;

    }


}

function prepareAutocompleteSetNames() {

    var obj = {
        uri: 'getSetNames'
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.queries_requests.php",
        dataType:"json",
        data: obj,
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

                $("#query-form-in-set-input").autocomplete({source: json.reply.data});
                $("#query-form2-in-set-input").autocomplete({source: json.reply.data});

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function gatherQueryParameters() {

    var durationParams = $("#query-form-duration-input").val().split(",");

    var duration = (durationParams[0].trim()<10?("0"+durationParams[0].trim()):durationParams[0].trim()) +
                        ":" +
                        (durationParams[1].trim()<10?("0"+durationParams[1].trim()):durationParams[1].trim())
                        + ":"
                        + (durationParams[2].trim()<10?("0"+durationParams[2].trim()):durationParams[2].trim())

    var params = {
        caller: $("#query-form-caller-input").val(),
        called: $("#query-form-called-input").val(),
        andOr: $("#query-form-and-or-select option:selected").attr("rel"),
        dateFrom: $("#query-form-from-date-input").val(),
        dateTo: $("#query-form-to-date-input").val(),
        timeFrom: $("#query-form-from-time-input").val(),
        timeTo: $("#query-form-to-time-input").val(),
        type: $("#query-form-comm-type-select option:selected").attr("rel"),
        duration: duration,
        weightFrom: $("#query-form-weight-from-input").val(),
        weightTo: $("#query-form-weight-to-input").val(),
        insertFrom: $("#query-form-from-creation-date-input").val(),
        insertTo: $("#query-form-to-creation-date-input").val(),
        inSet: $("#query-form-in-set-input").val(),
        offSet: 0
    }

    return params;

}

function gatherQuery2Parameters() {

    var params = {
        number: $("#query-form2-telephone-input").val(),
        id_number: $("#query-form2-id-number-input").val(),
        surname: $("#query-form2-surname-input").val(),
        name: $("#query-form2-name-input").val(),
        fathersname: $("#query-form2-fathersname-input").val(),
        mothersname: $("#query-form2-mothersname-input").val(),
        birthdate: $("#query-form2-birthdate-input").val(),
        country: $("#query-form2-country-input").val(),
        ssn: $("#query-form2-ssn-input").val(),
        alias: $("#query-form2-alias-input").val(),
        address: $("#query-form2-address-input").val(),
        gender: $("#query-form2-gender-select option:selected").attr("rel"),
        inSet: $("#query-form2-in-set-input").val()
    }

    return params;

}

function resizeSidebar() {
    var body = document.body,
        html = document.documentElement;
    var height = Math.max( body.scrollHeight, body.offsetHeight,
        html.clientHeight, html.scrollHeight, html.offsetHeight );
    $("#left-side-bar").css({'height': height + "px"});
}