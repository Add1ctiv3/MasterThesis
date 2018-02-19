$(document).ready(function() {

    resizeSidebar();

    $(window).on('resize', function(e) {
        resizeSidebar();
    });

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


    $("#import-button").click(function() {
        importCall();
    });

    $("#import-button2").click(function() {
        importTelephonePerson();
    });


});

function importCall() {

    var duration = $("#import-form-duration-input").val();
    var durationParts = duration.split(",");

    var durationStr = "";

    if(durationParts != null && durationParts.length > 0) {

        if(durationParts[0] < 10) {
            durationStr += "0";
        }

        durationStr += durationParts[0].trim() + ":";

        if(durationParts[1] < 10) {
            durationStr += "0";
        }

        durationStr += durationParts[1].trim() + ":";

        if(durationParts[2] < 10) {
            durationStr += "0";
        }

        durationStr += durationParts[2].trim();

    }

    var call = {
        caller: $("#import-form-caller-input").val(),
        called: $("#import-form-called-input").val(),
        timestamp: $("#import-form-timestamp-input").val(),
        duration: durationStr,
        weight: $("#import-form-weight-input").val(),
        type: $("#import-form-type-select option:selected").attr("rel"),
        uri: "importCall"
    }

    if(!call.caller || !call.called || !call.timestamp || !call.duration) {
        toastr.error("You need to at least specify a caller and called number, the date and time of the telecommunication and the duration!");
        return;
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.importing_requests.php",
        dataType:"json",
        data: call,
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
            $("#ajax-spinner-dialog").dialog("close");
            toastr.error(thrownError);
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                clearTelecommunicationForm();
                toastr.success(json.reply.message);

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function importTelephonePerson() {

    var n = $("#import-form2-number-input").val();
    var p = gatherPersonData();

    if(!n) {
        toastr.error("You need to provide a telephone number!");
        return;
    }

    if(p.isNotNull()) {

        if(!p.isValid()) {
            toastr.error("You need to provide at least an id number, a surname and a name for this person!");
            return;
        }

    }


    var obj = {
        number: n,
        id: p.id,
        surname: p.surname,
        name: p.name,
        fathersname: p.fathersname,
        mothersname: p.mothersname,
        birthdate: p.birthdate,
        country: p.country,
        alias: p.alias,
        address: p.address,
        ssn: p.ssn,
        gender: p.gender,
        uri: "importTelephonePerson"
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.importing_requests.php",
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
            $("#ajax-spinner-dialog").dialog("close");
            toastr.error(thrownError);
        },
        success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");

            if (json.error != null) {
                toastr.error(json.error);
                return;
            }

            if (json.reply && json.reply.result == "success") {

                clearTelephonePersonForm();
                toastr.success(json.reply.message);

            } //end of success if block

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function clearTelecommunicationForm() {

    $("#import-form-caller-input").val("");
    $("#import-form-called-input").val("");
    $("#import-form-timestamp-input").val("");
    $("#duration-hours").val("0");
    $("#duration-minutes").val("0");
    $("#duration-seconds").val("0");
    $("#import-form-weight-input").val("");
    $("#import-form-type-select option[rel=other]").prop("selected", true);

}

function clearTelephonePersonForm() {

    $("#import-form2-id-number-input").val(""),
    $("#import-form2-surname-input").val(""),
    $("#import-form2-name-input").val(""),
    $("#import-form2-fathersname-input").val(""),
    $("#import-form2-mothersname-input").val(""),
    $("#import-form2-birthdate-input").val(""),
    $("#import-form2-country-input").val(""),
    $("#import-form2-address-input").val(""),
    $("#import-form2-ssn-input").val(""),
    $("#import-form2-alias-input").val(""),
    $("#import-form2-gender-select option[rel=not_selected]").prop("selected", true);

}

function gatherPersonData() {
    return person = {
        id: $("#import-form2-id-number-input").val(),
        surname: $("#import-form2-surname-input").val(),
        name: $("#import-form2-name-input").val(),
        fathersname: $("#import-form2-fathersname-input").val(),
        mothersname: $("#import-form2-mothersname-input").val(),
        birthdate: $("#import-form2-birthdate-input").val(),
        country: $("#import-form2-country-input").val(),
        address: $("#import-form2-address-input").val(),
        ssn: $("#import-form2-ssn-input").val(),
        alias: $("#import-form2-alias-input").val(),
        gender: $("#import-form2-gender-select option:selected").attr("rel"),
        isValid: function() {
            if(this.id && this.surname && this.name) { return true; }
            return false;
        },
        isNotNull: function() {
            if(this.id || this.surname || this.name || this.birthdate || this.fathersname || this.mothersname || this.country || this.ssn || this.alias || this.address || this.gender != "not_selected") {
                return true;
            }
            return false;
        }
    }
}
