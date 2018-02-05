$(document).ready(function() {

    /******************************************************************* LOGGED USERS FUNCTIONS***********************************************************/

    //logout function
    $("#logout-link").click(function() {

        var obj = {
            uri: "logout"
        }

        $.ajax({
            type: "POST",
            url: "ajax/ajax.user_oriented_requests.php",
            dataType:"json",
            data: obj,
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
            },
            success : function(json) {
                if (json.error != null) {
                    toastr.error(json.error);
                }

                if (json.success != null) {
                    toastr.success(json.success);
                    window.location = "index.php";
                }
            }
        }); //end of ajax call

    });

    //logged users panel
    $(document).on("click", "#logged-user-fullname, #logged-user-icon", function() {

        var CheckFactors = { NOT_EMPTY: "1000", LENGTH: "2000", MATCHING_WITH: "3000", STRENGTH: "4000", EMAIL_TYPE: "5000" }

        if($("#loggedUsersPanel").is(":visible")) { return; }

        loadLoggedUsersData();

        var Options = {
            blackScreen: false,
            draggable: true
        };

        //mechanism in panel_mechanism.js
        var userDetailsPanel = createJPanel($("#loggedUsersPanel"), Options);
        userDetailsPanel.toggle();

        //passwords check
        var passwords_autocheck_reset_timer;
        $("#loggedUsersPanel #password-reset-input, #loggedUsersPanel #password-repeat-reset-input").keyup(function(e) {

            var password = getInputObject($("#loggedUsersPanel #password-reset-input"), null, $("#loggedUsersPanel #password-repeat-reset-input"));
            var password_repeat = getInputObject($("#loggedUsersPanel #password-repeat-reset-input"), null, $("#loggedUsersPanel #password-reset-input"));

            password.tooltipPosition = 'n';
            password_repeat.tooltipPosition = 'n';

            clearTimeout(passwords_autocheck_reset_timer);

            passwords_autocheck_reset_timer = setTimeout(function(){

                password.check(CheckFactors.STRENGTH, CheckFactors.MATCHING_WITH);
                password_repeat.check(CheckFactors.STRENGTH, CheckFactors.MATCHING_WITH);

            }, 700);
        }); //end of passwords check

    }); //end of logged users panel

    //on edit button click
    $(document).on("click", ("#edit-logged-user-button"), function() {

        toggleEditLoggedUserButton();

    }); //end of on edit button click

    //on change password button click
    $("#change-user-password-button").click(function() {

        if($("#password-reset-input").hasClass("input_error")) {
            return;
        }

        if($("#loggedUsersPanel #password-reset-input").val() == "") {
            toastr.error("You must provide a new password!");
            return;
        }

        var credentialsObj = {
            username: $("#user-box").attr("rel"),
            password: $("#loggedUsersPanel #password-reset-input").val(),
            password_repeat: $("#loggedUsersPanel #password-repeat-reset-input").val(),
            uri: "change_logged_users_password"
        }

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.user_oriented_requests.php",
            dataType:"json",
            data: credentialsObj,
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
            },
            success : function(json) {
                if (json.error != null) {
                    toastr.error(json.error);
                }
                if(json.reply.result == "error") {
                    for(var i = 0; i < json.reply.data.length; i++) {
                        var obj = getInputObject($("#"+json.reply.data[i].field), null, null);
                        obj.hasError = true;
                        obj.status = json.reply.data[i].message;
                        obj.error(false);
                    }
                }
                if (json.reply.result == "success") {
                    var obj = getInputObject($("#loggedUsersPanel #password-reset-input"), null, null);
                    var obj2 = getInputObject($("#loggedUsersPanel #password-repeat-reset-input"), null, null);
                    obj.normal().jInput.val("");
                    obj2.normal().jInput.val("");
                    toastr.success("Your password has been successfully changed!");
                }
            }
        }); //end of ajax call

    });//end of on change password button click


    /******************************************************************* USER ADMINISTRATION ******************************************************************/
    $("#users-administration-toggle").click(function() {

        if($("#users-administration-panel").is(":visible")) { return; }

        //first load the user data and append it to the container
        var credentialsObj = {
            uri: "get_all_users"
        }

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.user_oriented_requests.php",
            dataType:"json",
            data: credentialsObj,
            beforeSend: function() {
                $("#menu-users-spinner").fadeIn(200);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
                $("#menu-users-spinner").fadeOut(200);
            },
            success : function(json) {
                $("#menu-users-spinner").fadeOut(200);
                if (json.error != null) {
                    toastr.error(json.error);
                }
                //success reply
                if (json.reply.result == "success") {

                    var users = new Array()

                    for(var i = 0; i < json.reply.data.length; i++) {

                        users.push({
                            username: json.reply.data[i].username,
                            lastname: json.reply.data[i].lastname,
                            firstname: json.reply.data[i].firstname,
                            email: json.reply.data[i].email,
                            type: json.reply.data[i].type,
                            access_level: json.reply.data[i].access_level,
                            last_login: json.reply.data[i].last_login,
                            blocked: json.reply.data[i].blocked,
                            blocked_message: json.reply.data[i].blocked_message,
                            icon: json.reply.data[i].icon
                        });

                    }

                    var manager = userManager($("#user-administration-users-container"), users);
                    manager.html();

                    //make the panel appear
                    var Options = {
                        blackScreen: false,
                        draggable: true,
                        onCloseCallback: function() {
                            $("#user-administration-users-container").slimScroll({destroy: true});
                        },
                        onBeforeShow: function() {
                            $("#user-administration-users-container").slimScroll({destroy: true});
                            $("#user-administration-users-container").slimScroll({
                                //width: '300px',
                                height: '420px',
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
                                allowPageScroll: false,
                                disableFadeOut: false
                            });
                        }
                    };

                    var userDetailsPanel = createJPanel($("#users-administration-panel"), Options);
                    userDetailsPanel.toggle();

                }//end of success reply
            }
        }); //end of ajax call

    });

    $(document).on("click", ".user-container .panel-input-label", function() {
        $(this).next("div").click();
    });

    //what happens when you click on an editable text field
    $(document).on("click", ".user-container .editable-text:not(.being-edited)", function() {

        var userContainer = $(this).parent().parent();

        var fieldContainer = $(this);

        fieldContainer.html("<input type='text' value='"+$(this).attr("rel")+"' />")
            .css({'padding': '0px 20px 0px 0px'})
            .addClass("being-edited")
            .find("input")
            .focus();

        var input = fieldContainer.find("input").attr("id", new Date().getTime());
        var inputId = input.attr("id");
        var tmpStr = input.val();
        input.val('');
        input.val(tmpStr);

        $(document).on("focusout", "#" + inputId, function() {

            fieldContainer
                .attr("rel", input.val())
                .html(input.val())
                .removeClass("being-edited");

            $(document).off("focusout", "#" + inputId, function() {});

            $("#" + inputId).removeAttr("id");

            editChosenUserData(userContainer);

        });

    }); // end of what happens when you click on an editable text field

    //what happens when you click on an editable select field
    $(document).on("click", ".user-container .editable-select:not(.being-edited)", function() {

        var userContainer = $(this).parent().parent();
        var fieldContainer = $(this);
        var initialValue = $(this).text();

        var tempHtml = "<select>";

        if(fieldContainer.hasClass("user-type-container")) {
            tempHtml += '<option rel="administrator" '+(initialValue.toLowerCase()=="administrator"?"selected":"")+'>Administrator</option>';
            tempHtml += '<option rel="super-user" '+(initialValue.toLowerCase()=="super-user"?"selected":"")+'>Super-User</option>';
            tempHtml += '<option rel="user" '+(initialValue.toLowerCase()=="user"?"selected":"")+'>User</option>';
            tempHtml += '<option rel="new-user" '+(initialValue.toLowerCase()=="new-user"?"selected":"")+'>New-User</option>';
        }

        if(fieldContainer.hasClass("user-access-level-container")) {
            tempHtml += '<option rel="1" '+(initialValue=="1"?"selected":"")+'>1</option>';
            tempHtml += '<option rel="2" '+(initialValue=="2"?"selected":"")+'>2</option>';
            tempHtml += '<option rel="3" '+(initialValue=="3"?"selected":"")+'>3</option>';
            tempHtml += '<option rel="4" '+(initialValue=="4"?"selected":"")+'>4</option>';
            tempHtml += '<option rel="5" '+(initialValue=="5"?"selected":"")+'>5</option>';
        }

        tempHtml += "</select>";

        fieldContainer.html(tempHtml)
            .addClass("being-edited")
            .css({'padding': '0px 20px 0px 0px'})
            .find("select")
            .focus();

        var selectt = fieldContainer.find("select");
        selectt.attr("id", new Date().getTime());
        var selectId = selectt.attr("id");

        $(document).on("focusout", "#" + selectId, function() {

            fieldContainer
                .attr("rel", selectt.find("option:selected").val())
                .html(selectt.find("option:selected").text())
                .removeClass("being-edited");

            $(document).off("focusout", "#" + selectId, function() {});

            $("#" + selectId).removeAttr("id");

            editChosenUserData(userContainer);

        });

    }); // end of what happens when you click on an editable select field

    //what hppens when you click the blocked checkbox
    $(document).on("change", ".user-blocked-checkbox", function() {

        var userContainer = $(this).parent().parent();

        var input = $(this).parent().find("input[type=text]");

        if($(this).is(":checked")) {
            input
                .attr("disabled", false)
                .focus();
        } else {
            input.val("").attr("disabled", true);
            editChosenUserData(userContainer);
        }

    });//end of what hppens when you click the blocked checkbox

    $(document).on("focusout", ".users-block-message", function() {

        var userContainer = $(this).parent().parent();

        //gimick i used to let the unchecking of the checkbox happen before the input loses focus and fires the backend communication twice
        setTimeout(function() {
            if(!userContainer.find("input[type=checkbox]").is(":checked")) { return; }
            editChosenUserData(userContainer);
        }, 200);


    });

    $(document).on("click", ".delete-user-button", function() {

        if(!confirm("Are you sure you want to delete this user?")) { return; }

        var userContainer = $(this).parent().parent();

        if($("#user-box").attr("username") == userContainer.attr("id")) {
            toastr.error("You can not delete your own user!");
            return;
        }

        var credentialsObj = {
            username: userContainer.attr("id"),
            uri: "delete_user"
        }

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.user_oriented_requests.php",
            dataType:"json",
            data: credentialsObj,
            beforeSend: function() {
                userContainer.find(".user-ajax-spinner").fadeIn(200);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
                userContainer.find(".user-ajax-spinner").fadeOut(200);
            },
            success : function(json) {

                userContainer.find(".user-ajax-spinner").fadeOut(200);

                if (json.reply && json.reply.result == "error") {
                    toastr.error(json.reply.data[0].message);
                    return;
                }
                if (json.reply && json.reply.result == "success") {
                    toastr.success("Ο χρήστης διαγράφηκε επιτυχώς!");
                    userContainer.fadeOut(200, 'swing', function() {
                        $(this).remove();
                    });
                    return;
                }
                if (json.error !== null) {
                    toastr.error(json.error);
                }

            }
        }); //end of ajax call

    });

    $(document).on("keyup", "#users-administration-search-input", function() {

        //first make all the users invisible
        $(".user-container").hide();

        //save the search value
        var searchFor = $(this).val();

        //remove highlights
        $(".highlight").contents().unwrap();

        //if the given value is longer than one character then search otherwise show all users
        if(searchFor.length > 1) {

            $("div.searchable").each(function() {

                var elementToSearchIn = $(this);

                elementToSearchIn
                    .html(elementToSearchIn.html().replace(
                        new RegExp(searchFor.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), "gi"),
                        "<span class='highlight'>"+searchFor+"</span>")
                    );

                if(elementToSearchIn.text().includes(searchFor)) {
                    $(this).parent().parent().show();
                }

            });

        } // end of if the given value is longer than 1 character
        else {
            $(".user-container").show();
        }

    });

});

function editChosenUserData(userContainer) {

    var credentialsObj = {
        username: userContainer.find(".user-username-container").text(),
        lastname: userContainer.find(".user-lastname-container").text(),
        firstname: userContainer.find(".user-firstname-container").text(),
        email: userContainer.find(".user-email-container").text(),
        access_level: userContainer.find(".user-access-level-container").text(),
        type: userContainer.find(".user-type-container").text(),
        blocked: userContainer.find(".user-blocked-checkbox").is(":checked"),
        blocked_message: userContainer.find(".users-block-message").val(),
        uri: "edit_user"
    };

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.user_oriented_requests.php",
        dataType:"json",
        data: credentialsObj,
        beforeSend: function() {
            userContainer.find(".user-ajax-spinner").fadeIn(200);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
            userContainer.find(".user-ajax-spinner").fadeOut(200);
        },
        success : function(json) {

            userContainer.find(".user-ajax-spinner").fadeOut(200);

            if (json.reply && json.reply.result == "error") {
                //refocus the problematic field
                userContainer.find(".user-"+json.reply.data[0].field+"-container").click();
                toastr.error(json.reply.data[0].message);
                return;
            }
            if (json.reply && json.reply.result == "success") {
                toastr.success("Τα στοιχεία άλλαξαν επιτυχώς!");
                return;
            }
            if (json.error !== null) {
                toastr.error(json.error);
            }

        }
    }); //end of ajax call

}

function loadLoggedUsersData() {

    var credentialsObj = {
        username: $("#user-box").attr("rel"),
        uri: "load_logged_users_data"
    };

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.user_oriented_requests.php",
        dataType:"json",
        data: credentialsObj,
        error: function(xhr, ajaxOptions, thrownError) {
            toastr.error(thrownError);
        },
        success : function(json) {
            if (json.error != null) {
                toastr.error(json.error);
            }
            if (json.success != null) {

                $("#loggedUsersPanel span#username-proxy").html(json.success.username);
                $("#loggedUsersPanel span#lastname-proxy").html(json.success.lastname);
                $("#loggedUsersPanel span#firstname-proxy").html(json.success.firstname);
                $("#loggedUsersPanel span#email-proxy").html(json.success.email);
                $("#loggedUsersPanel span#last-login-container").html(json.success.last_login);
                $("#loggedUsersPanel select#type-proxy-input option#"+json.success.type+"-option").attr("selected", true);
                $("#loggedUsersPanel select#access-level-proxy-input option#al-"+json.success.access_level+"-option").attr("selected", true);

            }
        }
    }); //end of ajax call

}

function toggleEditLoggedUserButton() {

    var button = $("#edit-logged-user-button");

    //if it has the editing class then we need to return every input to span mode and save changes
    if(button.hasClass("editing")) {

        //first check for given errors
        if(!checkSubmittedLoggedUserValues()) { return; }

        var credentialsObj = {
            username: $("#username-proxy").text(),
            lastname: $("#lastname-proxy-input").val(),
            firstname: $("#firstname-proxy-input").val(),
            email: $("#email-proxy-input").val(),
            access_level: $("#access-level-proxy-input option:selected").val(),
            uri: "logged_user_edit"
        };

        //ajax call
        $.ajax({
            type: "POST",
            url: "ajax/ajax.user_oriented_requests.php",
            dataType:"json",
            data: credentialsObj,
            beforeSend: function() {
                $("#edit-logged-users-info-spinner").fadeIn(200);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                toastr.error(thrownError);
                $("#edit-logged-users-info-spinner").fadeOut(200);
            },
            success : function(json) {
                if (json.error != null) {
                    toastr.error(json.error);
                    $("#edit-logged-users-info-spinner").fadeOut(200);
                }
                if(json.reply !== null) {

                    if(json.reply.result == "error") {
                        for(var i = 0; i < json.reply.data.length; i++) {
                            var obj = getInputObject($("#"+json.reply.data[i].field+"-proxy-input"), null, null);
                            obj.hasError = true;
                            obj.status = json.reply.data[i].message;
                            obj.iconize = false;
                            obj.tooltipPosition = 'n';
                            obj.error(false);
                            $("#edit-logged-users-info-spinner").fadeOut(200);
                        }
                    }

                    if(json.reply.result == "success") {

                        //success
                        toastr.success("Your user info have been successfully edited!");

                        button.attr("value", "Edit")
                            .removeClass("save-logged-users-changes-button")
                            .removeClass("editing");

                        $("#loggedUsersPanel .panel-input-proxy").each(function() {
                            var content = $(this).find("input").val();
                            $(this).html(content);
                        });

                        $("#logged-user-fullname").html(credentialsObj.lastname + " " + credentialsObj.firstname);
                        $("#logged-user-email").html(credentialsObj.email);

                        $("#loggedUsersPanel .panel-select").attr("disabled", true);

                        $("#edit-logged-users-info-spinner").fadeOut(200);

                        //remove the image cropper and replace it with a static image
                        var path = $("#user-box").attr("rel");

                        var d = new Date();

                        $("<img id='logged-users-panel-icon' width='100' height='100' src='templates/images/user_images/"+path+".png?"+d.getTime()+"'>")
                            .insertAfter($("#logged-users-panel-cropper")).hide();

                        $("#logged-users-panel-cropper").fadeOut(200, 'swing', function() {

                            $(this).remove();
                            $("#logged-users-panel-icon").fadeIn(200);

                        })

                    }

                }
            }
        }); //end of ajax call


    } else { //else convert spans to inputs

        button.attr("value", "Save")
            .addClass("save-logged-users-changes-button")
            .addClass("editing");

        $("#loggedUsersPanel .panel-input-proxy:not(#username-proxy)").each(function() {
            var content = $(this).html();
            var id = $(this).prev("span").attr("for") + "-input";
            $(this).html("<input id='"+id+"' class='logged-user-temp-input' type='text' value='"+content+"' />");
        });

        if($("#type-proxy-input option:selected").attr("id") == "administrator-option") {
            $("#loggedUsersPanel .panel-select:not(#type-proxy-input)").attr("disabled", false);
        }

        //convert the image to a cropper input
        var options = {
            ratio:'1:1',
            minSize: {
                width: 100,
                height: 100,
            },
            download: false,
            instantEdit:true,
            label: 'Drop your image here',
            buttonConfirmLabel: 'Ok',
            push:true,
            forceType: 'png',
            service: 'ajax/async.php',
            forceSize: {width:100, height:100},
            meta: {
                username:$("#user-box").attr("rel")
            },
            didUpload: function(error, data, response) {
                $("#logged-user-icon").fadeOut(200, 'swing' , function() {
                    $(this).remove();
                    var d = new Date();
                    $("<img id='logged-user-icon' style='border-radius:50%; width:50px; height:50px;' src='templates/images/user_images/"+$("#user-box").attr("rel")+".png?"+d.getTime()+"'>")
                        .appendTo($("#user-icon-container"))
                        .hide()
                        .fadeIn(200);
                });
            }
        }

        var image_url = $("#user-box").attr("icon");

        $("<div id='logged-users-panel-cropper' style='width:100px; height:100px; border-radius:50%;'>"
            + "<img style='border-radius:50%;' src='templates/images/user_images/"+image_url+"?"+new Date().getTime()+"' alt=''>"
            + "<input style='display:none;' type='file'/>"
            + "</div>").insertAfter($("#logged-users-panel-icon"))
            .hide();

        $("#logged-users-panel-icon").fadeOut(200, 'swing', function() {

            $(this).remove();

            $("#logged-users-panel-cropper").fadeIn(200, 'swing', function() {
                $("#logged-users-panel-cropper").slim(options);
            });

        });


    }

}

function checkSubmittedLoggedUserValues() {

    var CheckFactors = { NOT_EMPTY: "1000", LENGTH: "2000", MATCHING_WITH: "3000", STRENGTH: "4000", EMAIL_TYPE: "5000" }

    var last_name = getInputObject($("#lastname-proxy-input"), null, null);
    var first_name = getInputObject($("#firstname-proxy-input"), null, null);
    var email = getInputObject($("#email-proxy-input"), null, null);

    last_name.iconize = false;
    first_name.iconize = false;
    email.iconize = false;

    last_name.tooltipPosition = 'n';
    first_name.tooltipPosition = 'n';
    email.tooltipPosition = 'n';

    last_name.check(CheckFactors.NOT_EMPTY);
    first_name.check(CheckFactors.NOT_EMPTY);
    email.check(CheckFactors.EMAIL_TYPE);

    if(last_name.hasError || first_name.hasError || email.hasError) {
        return false;
    }

    return true;

}