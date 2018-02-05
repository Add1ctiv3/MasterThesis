/**
 * Created by Addictive on 11/09/2017.
 */
var RECORD = {

    type: null,
    record_type: null,
    caller: null,
    called: null,
    number: null,
    timestamp: null,
    timestamp_str: null,
    duration: null,
    weight: null,
    num_weight: null,
    creation: null,
    country_code: null,
    associations: null,
    editCallbackFunction: null,

    init: function(record, cb) {

        this.type = record.type;
        this.record_type = record.record_type;
        this.creation = record.creation;

        if(this.record_type == "telecommunication") {
            this.caller = record.caller;
            this.called = record.called;
            this.timestamp = record.timestamp;
            this.duration = record.duration;
            this.weight = record.weight;
            this.timestamp_str = record.timestamp_str;
        }

        if(this.record_type == "telephone") {
            this.number = record.number;
            this.country_code = record.ccode;
            this.num_weight = record.num_weight;
        }

        this.editCallbackFunction = cb;

        return this;

    },

    initDialog: function() {

        if(this.record_type == "telecommunication") {

            $("td.record-field[field=caller]").html(this.caller);
            $("td.record-field[field=called]").html(this.called);
            $("td.record-field[field=timestamp]").html(this.timestamp_str);
            $("td.record-field[field=duration]").html(this.duration);
            $("td.record-field[field=weight]").html(this.weight);
            $("td.record-field[field=type]").html(this.type);
            $("td.record-field[field=insert_timestamp]").html(this.creation);

            $("#telecommunication-icon").remove();

            var icon = "telecommunication.png";
            if(this.type == "SMS") {
                icon = "sms.png";
            }

            $('<img src="templates/images/'+icon+'" width="32" height="32" id="telecommunication-icon" />').insertBefore($("#telecommunication-record-table"));

            $("#view-telecommunication-panel").dialog({
                title: "Telecommunication Record Details",
                width: 480,
                resizable:true,
                autoOpen: true,
                draggable: true,
                closeOnEscape: true,
                modal:true,
                close: function() {
                    $(this).dialog("destroy");
                },
                buttons: {
                    "Edit" : function() {

                        //get the dialogs buttons
                        var buttons = $("#view-telecommunication-panel").dialog("option", "buttons"); // getter
                        //add the cancel button and the callback function
                        $.extend(buttons, {
                            Cancel: function () {
                                //what happens when you click the cancel button
                                $("#telecommunication-record-table tr td.record-field:not([field=type])").each(function() {
                                    $(this).html($(this).attr("temp"));
                                });

                                $("#telecommunication-record-table tr td.record-field[field=type]").html($("#telecommunication-record-table tr td.record-field[field=type]").attr("temp"));

                                $("div[aria-describedby=view-telecommunication-panel]").attr("editing", "false");

                                var buttons2 = $("#view-telecommunication-panel").dialog("option", "buttons"); // getter

                                buttons3 = {Edit: buttons2.Edit};

                                $("#view-telecommunication-panel").dialog("option", "buttons", buttons3); // setter
                            } //end of the cancel button
                        });

                        //set the new button set
                        $("#view-telecommunication-panel").dialog("option", "buttons", buttons); // setter

                        //see if we are editing already
                        var editing = $("div[aria-describedby=view-telecommunication-panel]").attr("editing");

                        //if we are we need to commit the changes
                        if(editing == "true") {

                            var record = {
                                type: RECORD.record_type,
                                caller: $("#telecommunication-record-table tr td.record-field[field=caller] input").val(),
                                initialCaller: $("#telecommunication-record-table tr td.record-field[field=caller]").attr("temp"),
                                called: $("#telecommunication-record-table tr td.record-field[field=called] input").val(),
                                initialCalled: $("#telecommunication-record-table tr td.record-field[field=called]").attr("temp"),
                                timestamp: $("#telecommunication-record-table tr td.record-field[field=timestamp] input").val(),
                                initialTimestamp: $("#telecommunication-record-table tr td.record-field[field=timestamp]").attr("temp"),
                                duration: $("#telecommunication-record-table tr td.record-field[field=duration] input").val(),
                                initialDuration: $("#telecommunication-record-table tr td.record-field[field=duration]").attr("temp"),
                                weight: $("#telecommunication-record-table tr td.record-field[field=weight] input").val(),
                                comType: $("#telecommunication-record-table tr td.record-field[field=type] select option:selected").attr("rel").toUpperCase()
                            };

                            //edit the record
                            RECORD.editRecord(record);

                        } else { //else show the inputs

                            $("div[aria-describedby=view-telecommunication-panel]").attr("editing", "true");

                            $("#telecommunication-record-table tr td.record-field:not([field=type]):not([field=insert_timestamp])").each(function() {
                                var tmp = $(this).text();
                                $(this).attr("temp", tmp);
                                $(this).html("<input type='text' class='record-temp-input' value='"+tmp+"' />");
                            });

                            var tmpVal = $("#telecommunication-record-table tr td.record-field[field=type]").text();
                            $("#telecommunication-record-table tr td.record-field[field=type]").attr("temp", tmpVal);

                            var select = "<select class='record-temp-select'>";
                            select += "<option rel='OTHER' "+(tmpVal=="OTHER"?"selected":"")+">OTHER</option>";
                            select += "<option rel='CALL' "+(tmpVal=="CALL"?"selected":"")+">CALL</option>";
                            select += "<option rel='SMS' "+(tmpVal=="SMS"?"selected":"")+">SMS</option>";
                            select += "</select>";

                            $("#telecommunication-record-table tr td.record-field[field=type]").html(select);

                            var time = $("#telecommunication-record-table tr td.record-field[field=timestamp] input").val();
                            var parts = time.split(" ");
                            var tparts = parts[1].split(":");

                            $("#telecommunication-record-table tr td.record-field[field=timestamp] input").datetimepicker({
                                timeFormat: 'HH:mm:ss',
                                hour: tparts[0],
                                minute: tparts[1],
                                second: tparts[2],
                                dateFormat: 'dd/mm/yy'
                            });

                            $("#telecommunication-record-table tr td.record-field[field=weight] input").attr("type", "number").attr("min", "0.1").attr("max", 1).attr("step", "0.1");

                        } //end of if we are editing block

                    }
                }
            });
        }

        if(this.record_type == "telephone") {

            $("td.record-field[field=number]").html(this.number);
            $("td.record-field[field=country_code]").html(this.country_code);
            $("td.record-field[field=num_weight]").html(this.num_weight);
            $("td.record-field[field=type]").html(this.type);
            $("td.record-field[field=insert_timestamp]").html(this.creation);

            $("#telephone-icon").remove();

            var icon = "mobile.png";
            if(this.type == "landline") {
                icon = "telephone.png";
            }

            $('<img src="templates/images/'+icon+'" width="32" height="32" id="telephone-icon" />').insertBefore($("#telephone-record-table"));

            this.getTelephonesAssociations();

            $("#view-telephone-panel").dialog({
                title: "Telephone Record Details",
                width: 700,
                resizable:true,
                autoOpen: true,
                draggable: true,
                closeOnEscape: true,
                modal:true,
                close: function() {
                    $(this).dialog("destroy");
                },
                buttons: {
                    "Edit" : function() {

                        //get the dialogs buttons
                        var buttons = $("#view-telephone-panel").dialog("option", "buttons"); // getter
                        //add the cancel button and the callback function
                        $.extend(buttons, {

                            Cancel: function () {
                                //what happens when you click the cancel button
                                $("#telephone-record-table tr td.record-field:not([field=type])").each(function() {
                                    $(this).html($(this).attr("temp"));
                                });

                                $("#telephone-record-table tr td.record-field[field=type]").html($("#telephone-record-table tr td.record-field[field=type]").attr("temp"));

                                $("div[aria-describedby=view-telephone-panel]").attr("editing", "false");

                                var buttons2 = $("#view-telephone-panel").dialog("option", "buttons"); // getter

                                var buttons3 = {Edit: buttons2.Edit};

                                $("#view-telephone-panel").dialog("option", "buttons", buttons3); // setter
                            }
                        });

                        //set the new button set
                        $("#view-telephone-panel").dialog("option", "buttons", buttons); // setter

                        //see if we are editing already
                        var editing = $("div[aria-describedby=view-telephone-panel]").attr("editing");

                        //if we are, we need to commit the changes
                        if(editing == "true") {

                            var record = {
                                type: RECORD.record_type,
                                number: $("#telephone-record-table tr td.record-field[field=number] input").val(),
                                initialNumber: $("#telephone-record-table tr td.record-field[field=number]").attr("temp"),
                                country_code: $("#telephone-record-table tr td.record-field[field=country_code] input").val(),
                                num_weight: $("#telephone-record-table tr td.record-field[field=num_weight] input").val(),
                                telType: $("#telephone-record-table tr td.record-field[field=type] select option:selected").attr("rel").toUpperCase(),
                            };

                            //edit the record
                            RECORD.editRecord(record);

                        } else { //else show the inputs

                            $("div[aria-describedby=view-telephone-panel]").attr("editing", "true");

                            $("#telephone-record-table tr td.record-field:not([field=type]):not([field=insert_timestamp])").each(function() {
                                var tmp = $(this).text();
                                $(this).attr("temp", tmp);
                                $(this).html("<input type='text' class='record-temp-input' value='"+tmp+"' />");
                            });

                            var tmpVal = $("#telephone-record-table tr td.record-field[field=type]").text();
                            $("#telephone-record-table tr td.record-field[field=type]").attr("temp", tmpVal);

                            var select = "<select class='record-temp-select'>";
                            select += "<option rel='MOBILE' "+((tmpVal=="mobile" || tmpVal=="MOBILE")?"selected":"")+">MOBILE</option>";
                            select += "<option rel='LANDLINE' "+((tmpVal=="landline" || tmpVal=="LANDLINE")?"selected":"")+">LANDLINE</option>";
                            select += "<option rel='UNKNOWN' "+(tmpVal=="unknown"?"selected":"")+">UNKNOWN</option>";
                            select += "</select>";

                            $("#telephone-record-table tr td.record-field[field=type]").html(select);

                        } //end of if we are editing block
                    }
                }
            });
        }

        return this;

    },

    editRecord: function(record) {

        var __this = this;

        var Obj = {
            uri: "editRecord",
            record: record
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

                    toastr.success(json.reply.message);

                    var initialNumber = $("#telephone-record-table tr td.record-field[field=number]").attr("temp");

                    var number = $("#telephone-record-table tr td.record-field[field=number] input").val();
                    var country_code = $("#telephone-record-table tr td.record-field[field=country_code] input").val();
                    var type = $("#telephone-record-table tbody tr td[field=type] select option:selected").attr("rel");

                    var rec = $("div[number="+initialNumber+"]");

                    $("div[caller="+initialNumber+"]").each(function() {
                        $(this).attr("caller", number);
                        $(this).find(".query-result-record-caller").text(number);
                    });

                    $("div[called="+initialNumber+"]").each(function() {
                        $(this).attr("called", number);
                        $(this).find(".query-result-record-called").text(number);
                    });

                    rec.attr("number", number)
                        .attr("country_code", country_code)
                        .attr("type", type);

                    rec.find(".query-result-telephone-number").text(number);

                    var icon = rec.find(".query-result-record-icon");
                    var htmlIcon;
                    var path;

                    if(type!="LANDLINE") {
                        htmlIcon = $("<img src='templates/images/mobile.png' class='query-result-record-icon no-select' />");
                        path = "templates/images/mobile.png";
                    } else {
                        htmlIcon = $("<img src='templates/images/telephone.png' class='query-result-record-icon no-select' />");
                        path = "templates/images/telephone.png";
                    }

                    icon.hide();
                    htmlIcon.insertBefore(icon).hide();
                    icon.remove();
                    htmlIcon.fadeIn(200);

                    $("#telephone-icon").remove();
                    $('<img src="'+path+'" width="32" height="32" id="telephone-icon" />').insertBefore($("#telephone-record-table"));

                    $("#telephone-record-table tr td.record-field:not([field=type])").each(function() {
                        $(this).html($(this).find("input").val());
                    });

                    $("#telephone-record-table tr td.record-field[field=type]").html($("#telephone-record-table tr td.record-field[field=type] select option:selected").attr("rel"));

                    $("#telecommunication-record-table tr td.record-field[field=caller]").html(record.caller);
                    $("#telecommunication-record-table tr td.record-field[field=called]").html(record.called);
                    $("#telecommunication-record-table tr td.record-field[field=timestamp]").html(record.timestamp);
                    $("#telecommunication-record-table tr td.record-field[field=duration]").html(record.duration);
                    $("#telecommunication-record-table tr td.record-field[field=weight]").html(record.weight);
                    $("#telecommunication-record-table tr td.record-field[field=type]").html(record.comType);

                    var line = $(".query-result-record[caller="+record.initialCaller+"][called="+record.initialCalled+"][timestamp='"+record.initialTimestamp+"'][duration="+record.initialDuration+"]");
                        line.attr("caller", record.caller)
                            .attr("called", record.called)
                            .attr("timestamp", record.timestamp)
                            .attr("duration", record.duration)
                            .attr("weight", record.weight)
                            .attr("type", record.comType);

                    line.find(".query-result-record-caller").text(record.caller);
                    line.find(".query-result-record-called").text(record.called);
                    line.find(".query-result-record-date-and-time").text(record.timestamp);
                    line.find(".query-result-record-duration").text(record.duration + " seconds");

                    //and remove the cancel button
                    if(record.type == "telephone") {

                        $("div[aria-describedby=view-telephone-panel]").attr("editing", "false");

                        var buttons2 = $("#view-telephone-panel").dialog("option", "buttons"); // getter

                        var buttons3 = {Edit: buttons2.Edit};

                        $("#view-telephone-panel").dialog("option", "buttons", buttons3);

                    }

                    if(record.type == "telecommunication") {

                        $("div[aria-describedby=view-telecommunication-panel]").attr("editing", "false");

                        var buttons2 = $("#view-telecommunication-panel").dialog("option", "buttons"); // getter

                        var buttons3 = {Edit: buttons2.Edit};

                        $("#view-telecommunication-panel").dialog("option", "buttons", buttons3);

                    }

                    if(isFunction(__this.editCallbackFunction)) {

                        var group = "mobiles";
                        if(type === "LANDLINE") {
                            group = "landlines";
                        }

                        var beforeNode = {
                            id: record.initialNumber,
                            label: record.initialNumber
                        };

                        var afterNode = {
                            id: record.number,
                            label: record.number,
                            weight: record.num_weight,
                            group: group
                        };

                        __this.editCallbackFunction(beforeNode, afterNode);
                    }

                } //end of success if block

                if (json.reply && json.reply.result == "failure") {
                    toastr.error(json.reply.message);
                }
            }
        }); //end of ajax call
    },

    getTelephonesAssociations: function() {

        $("#telephone-record-associations").html("");

        var _this = this;

        var obj = {
            number: this.number,
            'uri': "getTelephonesAssociations"
        }

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.queries_requests.php",
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

                if (json.reply && json.reply.result == "success") {

                    //building the html
                    var html = "";

                    if(!json.reply.data) {
                        return "";
                    }

                    for(var i = 0; i < json.reply.data.length; i++) {
                        var record = json.reply.data[i];
                        html += "<table class='telephone-person-association-table' number='"+_this.number+"' surname='"+record.surname+"' name='"+record.name+"' " +
                            "id_number='"+record.id_number+"' birthdate='"+record.birthdate+"' country='"+record.country+"' fathersname='"+record.fathersname+"'" +
                            "mothersname='"+record.mothersname+"' alias='"+record.alias+"' address='"+record.address+"' ssn='"+record.ssn+"' gender='"+record.gender+"'" +
                            "relationship='"+record.relationship+"' validity='"+record.validity+"' >";

                            html += "<tr><td colspan='4' class='person-associations-buttons-container'>" +
                                "<img src='templates/images/fail-icon.png' width='12' height='12' class='remove-person-association-button' title='Remove Association' />" +
                                "<img src='templates/images/edit_file_icon.png' width='17' height='17' class='edit-person-association-button' title='Edit Association' />" +
                                "</td></tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>ID Number:</td>";
                                html += "<td class='association-record-field' initial='"+record.id_number+"' field='id_number'>"+record.id_number+"</td>";
                                html += "<td class='number-association-label'>Birthdate:</td>";
                                html += "<td class='association-record-field' field='birthdate' initial='"+record.birthdate+"'>"+record.birthdate+"</td>";
                            html += "</tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>Surname:</td>";
                                html += "<td class='association-record-field' field='surname' initial='"+record.surname+"'>"+record.surname+"</td>";
                                html += "<td class='number-association-label'>Country:</td>";
                                html += "<td class='association-record-field' field='country' initial='"+record.country+"'>"+record.country+"</td>";
                            html += "</tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>Name:</td>";
                                html += "<td class='association-record-field' field='name' initial='"+record.name+"'>"+record.name+"</td>";
                                html += "<td class='number-association-label'>Alias:</td>";
                                html += "<td class='association-record-field' field='alias' initial='"+record.alias+"'>"+record.alias+"</td>";
                            html += "</tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>Father's Name:</td>";
                                html += "<td class='association-record-field' field='fathersname' initial='"+record.fathersname+"'>"+record.fathersname+"</td>";
                                html += "<td class='number-association-label'>Address:</td>";
                                html += "<td class='association-record-field' field='address' initial='"+record.address+"'>"+record.address+"</td>";
                            html += "</tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>Mother's Name:</td>";
                                html += "<td class='association-record-field' field='mothersname' initial='"+record.mothersname+"'>"+record.mothersname+"</td>";
                                html += "<td class='number-association-label'>SSN:</td>";
                                html += "<td class='association-record-field' field='ssn' initial='"+record.ssn+"'>"+record.ssn+"</td>";
                            html += "</tr>";

                            html += "<tr>";
                                html += "<td class='number-association-label'>Data Validity:</td>";
                                html += "<td class='association-record-field' field='validity' initial='"+record.validity+"'>"+record.validity+"</td>";

                                var gen = "Unknown";
                                if(record.gender == "M") {
                                    gen = "Male";
                                }
                                if(record.gender == "F") {
                                    gen = "Female";
                                }

                                html += "<td class='number-association-label'>Gender:</td>";
                                html += "<td class='association-record-field' field='gender' initial='"+record.gender+"'>"+gen+"</td>";
                            html += "</tr>";

                            html += "<tr class='association-record-edit-buttons-container hidden'><td colspan='4'>" +
                                    "<div class='button association-record-cancel-edit-button'>Cancel</div>" +
                                    "<div class='button association-record-edit-button'>Edit</div>" +
                                "</td></tr>";

                        html += "</table>";
                    }

                    $("#telephone-record-associations").html("<b>Associations</b> <span style=\"font-family:'Lora', serif; front-size:12px;\">(Red: owned by, Orange: used by)</span><br/>" + html);

                    _this.setupAssociationTriggers();

                }

                if (json.error != null) {
                    toastr.error(json.error);
                }

            }
        }); //end of ajax call

    },

    setupAssociationTriggers: function() {

        //edit button click
        $(".edit-person-association-button").click(function() {

            //retrieve the record
            var tableElement = $(this).parent().parent().parent().parent();

            //first check if the record is being edited
            if(tableElement.attr("editing") != "true") {

                tableElement.attr("editing", "true");

                tableElement.find(".association-record-field:not([field=relationship]):not([field=validity]):not([field=gender]):not([field=id_number]):not([field=surname]):not([field=name])").each(function() {
                    var _text = $(this).text();
                    $(this).html("<input class='association-record-temp-input' type='text' value='"+_text+"' />");
                });

                tableElement.find(".association-record-field[field=birthdate] input").datepicker({dateFormat: 'dd/mm/yy'});

                //validity select
                var validitySelect = "<select class='association-record-temp-select'>";
                    validitySelect += "<optgroup label='A'>";
                        validitySelect += "<option rel='A1' "+(tableElement.attr("validity")=="A1"?"selected":"")+" >A1</option>";
                        validitySelect += "<option rel='A2' "+(tableElement.attr("validity")=="A2"?"selected":"")+" >A2</option>";
                        validitySelect += "<option rel='A3' "+(tableElement.attr("validity")=="A3"?"selected":"")+" >A3</option>";
                        validitySelect += "<option rel='A4' "+(tableElement.attr("validity")=="A4"?"selected":"")+" >A4</option>";
                    validitySelect += "</optgroup>";
                    validitySelect += "<optgroup label='B'>";
                        validitySelect += "<option rel='B1' "+(tableElement.attr("validity")=="B1"?"selected":"")+" >B1</option>";
                        validitySelect += "<option rel='B2' "+(tableElement.attr("validity")=="B2"?"selected":"")+" >B2</option>";
                        validitySelect += "<option rel='B3' "+(tableElement.attr("validity")=="B3"?"selected":"")+" >B3</option>";
                        validitySelect += "<option rel='B4' "+(tableElement.attr("validity")=="B4"?"selected":"")+" >B4</option>";
                    validitySelect += "</optgroup>";
                        validitySelect += "<optgroup label='C'>";
                        validitySelect += "<option rel='C1' "+(tableElement.attr("validity")=="C1"?"selected":"")+" >C1</option>";
                        validitySelect += "<option rel='C2' "+(tableElement.attr("validity")=="C2"?"selected":"")+" >C2</option>";
                        validitySelect += "<option rel='C3' "+(tableElement.attr("validity")=="C3"?"selected":"")+" >C3</option>";
                        validitySelect += "<option rel='C4' "+(tableElement.attr("validity")=="C4"?"selected":"")+" >C4</option>";
                    validitySelect += "</optgroup>";
                    validitySelect += "<optgroup label='D'>";
                        validitySelect += "<option rel='D1' "+(tableElement.attr("validity")=="D1"?"selected":"")+" >D1</option>";
                        validitySelect += "<option rel='D2' "+(tableElement.attr("validity")=="D2"?"selected":"")+" >D2</option>";
                        validitySelect += "<option rel='D3' "+(tableElement.attr("validity")=="D3"?"selected":"")+" >D3</option>";
                        validitySelect += "<option rel='D4' "+(tableElement.attr("validity")=="D4"?"selected":"")+" >D4</option>";
                    validitySelect += "</optgroup>";
                validitySelect += "</select>";

                tableElement.find(".association-record-field[field=validity]").html(validitySelect);

                //gender select
                var genderSelect = "<select class='association-record-temp-select'>";
                    genderSelect += "<option rel='M' "+(tableElement.attr("gender")=="M"?"selected":"")+" >Male</option>";
                    genderSelect += "<option rel='F' "+(tableElement.attr("gender")=="F"?"selected":"")+" >Female</option>";
                    genderSelect += "<option rel='Unknown' "+(tableElement.attr("gender")=="Unknown"?"selected":"")+" >Unknown</option>";
                genderSelect += "</select>";

                tableElement.find(".association-record-field[field=gender]").html(genderSelect);

                tableElement.find(".association-record-edit-buttons-container").fadeIn(200);

                //set the button triggers

                //cancel button
                tableElement.find(".association-record-cancel-edit-button").click(function() {

                    tableElement.attr("editing", "false");

                    tableElement.find(".association-record-field").each(function() {
                        var _text = $(this).attr("initial");
                        $(this).html(_text);
                    });

                    //rewrite gender
                    var gender = tableElement.find(".association-record-field[field=gender]").attr("initial");
                    var gend = "Unknown";
                    if(gender == "M") {
                        gend = "Male";
                    }
                    if(gender == "F") {
                        gend = "Female";
                    }
                    tableElement.find(".association-record-field[field=gender]").html(gend);

                    //hide the edit buttons
                    tableElement.find(".association-record-edit-buttons-container").hide();

                }); //end of cancel button

                //edit button
                tableElement.find(".association-record-edit-button").click(function() {

                    var obj = {
                        telephone: tableElement.attr("number"),
                        id_number: tableElement.attr("id_number"),
                        surname: tableElement.attr("surname"),
                        name: tableElement.attr("name"),
                        fathersname: tableElement.find(".association-record-field[field=fathersname] input").val(),
                        mothersname: tableElement.find(".association-record-field[field=mothersname] input").val(),
                        birthdate: tableElement.find(".association-record-field[field=birthdate] input").val(),
                        country: tableElement.find(".association-record-field[field=country] input").val(),
                        ssn: tableElement.find(".association-record-field[field=ssn] input").val(),
                        gender: tableElement.find(".association-record-field[field=gender] select option:selected").attr("rel"),
                        alias: tableElement.find(".association-record-field[field=alias] input").val(),
                        address: tableElement.find(".association-record-field[field=address] input").val(),
                        validity: tableElement.find(".association-record-field[field=validity] select option:selected").attr("rel"),
                        relationship: tableElement.attr("relationship"),
                        uri: "editTelephonePersonAssociation"
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

                                toastr.success(json.reply.message);

                                tableElement.attr("editing", "false");

                                tableElement.find(".association-record-field").each(function() {
                                    var _text = $(this).find("input").val();
                                    $(this).html(_text);
                                });

                                tableElement.find(".association-record-field[field=validity]").html(obj.validity);
                                tableElement.attr("validity", obj.validity);

                                //rewrite gender
                                var gender = tableElement.find(".association-record-field[field=gender] select option:selected").attr("rel");
                                var gend = "Unknown";
                                if(gender == "M") {
                                    gend = "Male";
                                }
                                if(gender == "F") {
                                    gend = "Female";
                                }
                                tableElement.find(".association-record-field[field=gender]").html(gend);

                                //hide the edit buttons
                                tableElement.find(".association-record-edit-buttons-container").hide();

                                tableElement.attr("fathersname", obj.fathersname);
                                tableElement.attr("mothersname", obj.mothersname);
                                tableElement.attr("birthdate", obj.birthdate);
                                tableElement.attr("country", obj.country);
                                tableElement.attr("alias", obj.alias);
                                tableElement.attr("address", obj.address);
                                tableElement.attr("ssn", obj.ssn);
                                tableElement.attr("gender", obj.gender);

                            } //end of success if block

                            if (json.reply && json.reply.result == "failure") {
                                toastr.error(json.reply.message);
                            }
                        }
                    }); //end of ajax call

                }); //end of edit button click trigger

            } //end of if editing IS NOT ALREADY happening block

        });

        //remove association button click
        $(".remove-person-association-button").click(function() {

            //retrieve the record
            var tableElement = $(this).parent().parent().parent().parent();

            $("#dialog-remove-association-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {

                    "Delete": function() {

                        var _this = $(this);

                        var obj = {
                            telephone: tableElement.attr("number"),
                            id_number: tableElement.attr("id_number"),
                            surname: tableElement.attr("surname"),
                            name: tableElement.attr("name"),
                            relationship: tableElement.attr("relationship"),
                            uri: "deleteTelephonePersonAssociation"
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

                                    toastr.success(json.reply.message);

                                    tableElement.fadeOut(200).remove();

                                    _this.dialog( "close" );

                                } //end of success if block

                                if (json.reply && json.reply.result == "failure") {
                                    toastr.error(json.reply.message);
                                }
                            }
                        }); //end of ajax call

                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    }
                }
            });

        });

    }

}

function isFunction(functionToCheck) {
    var getType = {};
    return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
}
