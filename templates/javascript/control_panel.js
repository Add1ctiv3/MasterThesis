var GLOBAL = {
		TEMP_DATA: null
	};

var UPLOADED_FILES_PANEL = null;

var IMPORT_DATA_PANEL = null;
	
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
	
	var addEvent = function(object, type, callback) {
		if (object == null || typeof(object) == 'undefined') return;
		if (object.addEventListener) {
			object.addEventListener(type, callback, false);
		} else if (object.attachEvent) {
			object.attachEvent("on" + type, callback);
		} else {
			object["on"+type] = callback;
		}
	};
	
	addEvent(window, "resize", function() {
		
		var h = $(".panel:visible").height();
		var w = $(".panel:visible").width();
		$(".panel:visible").css({'top': 200, 'left': (window.innerWidth/2 - w/2)});
		$("#dumpener").css({'width': window.innerWidth, 'height': window.innerHeight});
				
		var windowWidth = window.innerWidth;
		var windowHeight = window.innerHeight;
		
		if($("#filtered-data-table").length) {
		
			$("#import-data-panel").animate({
				width: (windowWidth-50) + "px",
				height: (windowHeight-50) + "px",
				top: '25px',
				left: '25px'
			},
			700);
			
			$("#import-data-panel-container")			
				.animate({
					width: (windowWidth-100) + 'px',
					height: (windowHeight-300)+'px'
				});
				
		}
		
	});
		
	$(".panel-input-label").click(function() {
		
		var target = "#" + $(this).attr("for") + "-input";
		$(target).focus();
		
	});
			
	//menu mechanism
	$("#menu-button").click(function() {
		
		if($("#left-side-bar").hasClass("hidden")) {
			//show the bar
			$("#left-side-bar").removeClass("hidden").addClass("visible");
			$( "#left-side-bar" ).animate({
				opacity: 1,
				left: "+=250"
			  }, 500);
			$( "#main-container" ).animate({				
				marginLeft: "+=250"
			  }, 500);
			return;
		} 
		
		if($("#left-side-bar").hasClass("visible")) {
			//hide the bar
			$("#left-side-bar").removeClass("visible").addClass("hidden");
			$( "#left-side-bar" ).animate({
				opacity: 0.25,
				left: "-=250"
			  }, 500);
			  $( "#main-container" ).animate({				
				marginLeft: "-=250"
			  }, 500);
			return;
		}
		
	}); //end of menu mechanism
		
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
						blackScreen: true,
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
	
	
	/******************************************************************* UPLOADED FILES ******************************************************************/
	
	
	$("#uploaded-files-toggle").click(function() {
						
		$("#uploaded-files-tabs").tabs();
						
		//make the panel appear
		var Options = {
			blackScreen: false,
			draggable: true,
			onCloseCallback: function() {
					
				},
			onBeforeShow: function() {					
					initializeFileUploads();
					loadUploadedFiles($("#uploaded-files-tab-1 div.container"));
					$("#uploaded-files-tab-1 div.container").slimScroll({destroy: true});
					$("#uploaded-files-tab-1 div.container").slimScroll({
							//width: '300px',
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
							allowPageScroll: false,
							disableFadeOut: false
						});
				}
		};
		
		UPLOADED_FILES_PANEL = createJPanel($("#uploaded-files-panel"), Options);
        UPLOADED_FILES_PANEL.toggle();
				
	});
	
	$(document).on("click", "#uploaded-files-tabs ul li:first-child > a", function() {
		loadUploadedFiles($("#uploaded-files-tab-1 .container"));
	});
	
	$(document).on("click", ".file-control", function() {
		
		var id = $(this).parent().parent().attr("rel");
				
		//rename
		if($(this).hasClass("rename-file")) {				
			
			var initialName = ($("#uploaded-file-"+id).find("div.uploaded-file-name").text() == null || $("#uploaded-file-"+id).find("div.uploaded-file-name").text() == "") ? "" : $("#uploaded-file-"+id).find("div.uploaded-file-name").text();

			$("#uploaded-file-"+id).find("div.uploaded-file-name").attr("initialName", initialName);
			
			var newHtml = "<input type='text' value='"+initialName+"' class='uploaded-file-rename-input' />";
			
			newHtml += "<div class='button uploaded-file-button rename-uploaded-file-button'>Rename</div>";
			
			newHtml += "<div class='button uploaded-file-button cancel-rename-uploaded-file-button'>Cancel</div>";
			
			$("#uploaded-file-"+id).find("div.uploaded-file-name").html(newHtml);
							
		}
		
		//filter
		if($(this).hasClass("import-data")) {
			$("#import-data-panel").attr("rel", id)
			importDataStart();
		}
					
		//delete
		if($(this).hasClass("delete-file")) {				
			if(confirm("Are you sure you want to delete this file?")) {
				deleteUploadedFile(id, $(this));
			}			
		}
		
	});
	
	//cancel file rename
	$(document).on("click", ".uploaded-file .uploaded-file-name .cancel-rename-uploaded-file-button", function() {
		
		$(this).parent().html($(this).parent().attr("initialName"));
		$(this).parent().removeAttr("initialName")
		
	});
	
	//file rename
	$(document).on("click", ".uploaded-file .uploaded-file-name .rename-uploaded-file-button", function() {
		
		var id = $(this).parent().parent().attr("rel");
		var name = $(this).prev("input[type=text]").val();
		
		renameUploadedFile(id, name, $(this));
		
	});
	
	//on available import template click
	$(document).on("click", ".available-import-template", function() {
		if($(this).hasClass("selected-template")) {
			$(this).removeClass("selected-template");
			return;
		}
		$(".available-import-template").removeClass("selected-template");
		$(this).addClass("selected-template");
	});
	
	//on data import next button click
	$(document).on("click", "#import-data-next-button", function() {		
		importDataSecond();		
	});
	
	//on click add filter button
	$(document).on("click", ".add-filter", function() {				
		$(this).next(".filter-items").append(GLOBAL.TEMP_DATA.addFilterMechanism());
	});
	
	//what happens when the user changes the filter type selected option
	$(document).on("change", ".available-filters", function() {		
		
		var columnTH = $(this).parent().parent().parent();
		var columnIndex = columnTH.index() + 1;
		
		var valueInput = $(this).next("input.filter-mechanism-value-input");
		
		if(valueInput.val() == "" || valueInput.val() == null) {
			toastr.warning("Fill in the filter value!");
			return;
		}
		
		var liItemIndex = $(this).parent().index() + 1;
				
		//first notify the global object and see if this filter already existed so it can be changed
		var filter = {
			column: columnIndex,
			order: liItemIndex,
			type: $(this).find("option:selected").attr("rel"),
			value: valueInput.val()
		}		
		
		if(filter.type == "choose") {
			toastr.warning("Choose a filter type!");
			return;
		}
		
		GLOBAL.TEMP_DATA
			.restoreValues()
			.addOrModifyFilter(filter, 'type')
			.applyFilters();
		
	});
	
	//what happens when the user changes the filter input value
	$(document).on("input", ".filter-mechanism-value-input", function() {		
		
		var columnTH = $(this).parent().parent().parent();
		var columnIndex = columnTH.index() + 1;
		
		var typeValue = $(this).parent().find(".available-filters option:selected").attr("rel");
		
		if(typeValue == "choose") {
			toastr.warning("Choose a filter type!");
			return;
		}
		
		var liItemIndex = $(this).parent().index() + 1;
		
		//first notify the global object and see if this filter already existed so it can be changed
		var filter = {
			column: columnIndex,
			order: liItemIndex,
			type: typeValue,
			value: $(this).val()
		}
		
		if(filter.value == "" || filter.value == null) {
			toastr.warning("Fill in the filter value!");
			return;
		}
		
		GLOBAL.TEMP_DATA
			.restoreValues()
			.addOrModifyFilter(filter, 'value')
			.applyFilters();
		
	});
		
	//what happens when you click the remove filter button
	$(document).on("click", ".remove-filter", function() {
		
		var liItem = $(this).parent();
		
		//what happens when the user sorts a filter
		var columnTH = liItem.parent().parent();
		var columnIndex = columnTH.index() + 1;
		
		var typeValue = liItem.find(".available-filters option:selected").attr("rel");
		var val = liItem.find(".filter-mechanism-value-input").val();
		
		var liItemIndex = liItem.index() + 1;
		
		if(typeValue == 'choose' || val == "" || val == null) {
			liItem.fadeOut(200).remove();
		}
		
		//first notify the global object and see if this filter already existed so it can be changed
		var filter = {
			column: columnIndex,
			order: liItemIndex,
			type: typeValue,
			value: val
		}
				
		GLOBAL.TEMP_DATA
			.restoreValues()
			.removeFilter(filter, liItem)
			.rearrangeFiltersOrder(columnIndex)
			.applyFilters(columnIndex);
					
	});
	
	//what happens when you change the number of lines input
	$(document).on("input", "#import-data-ignore-lines-filter", function() {
		
		GLOBAL.TEMP_DATA.changeIgnoredLines($(this).val());
		
	});
	
	//what happens when you click the save as a template button
	$(document).on("click", "#pick-template-name-button", function() {
		
		if(!$("#impot-data-template-name-container").is(":visible")) {
				
			$("#impot-data-template-name-container")
				.css({'width': '0px'})
				.show()
				.animate({					
						width: '350px'
					}, 
					300);
			
		}			
	
	});
	
	//what happens when you click the ok button to save the template
	$(document).on("click", "#import-data-save-template-button", function() {
				
		var obj = {
			'uri': 'save_as_template',
			'name': $("#save-template-input").val(),
			'filters': GLOBAL.TEMP_DATA.filters,
			'fields': GLOBAL.TEMP_DATA.assigned_fields,
			'ignore': GLOBAL.TEMP_DATA.ignored_lines
		}
		
		if(obj.name == null || obj.name == "") {
			toastr.error("Please provide a name for your import template!");
			return;
		}
				
		//ajax call
		$.ajax({
			type: "POST",
			url: "ajax/ajax.file_oriented_requests.php",
			dataType:"json",
			data: obj,		
			beforeSend: function() {
					$("#import-data-spinner").fadeIn(200);
				},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError);
				$("#import-data-spinner").fadeOut(200);			
			},
			success : function(json) {
				
				$("#import-data-spinner").fadeOut(200);
				
				if (json.error != null) {
					toastr.error(json.error);
					return;	
				}
				
				if (json.reply && json.reply.result == "success") {
					toastr.success("Your import data template has been saved!");
					$("#impot-data-template-name-container")
						.animate({
								width: '0px'
							}, 
							300, function() {
								$(this).hide();
							});
				}
															
			} 
		}); //end of ajax call
		
	});
	
	//what happens when the user assigns a field to a column
	$(document).on("change", ".available-fields", function() {
		
		var columnTH = $(this).parent();
		var columnIndex = columnTH.index() + 1;
		
		var selectIndex = $(this).find("option:selected").index();
		var optgroupIndex = $(this).find("option:selected").parent().index();
		
		//first notify the global object and see if this filter already existed so it can be changed
		var field = {
			column: columnIndex,
			select_index: selectIndex,
			optgroup: optgroupIndex
		}
				
		GLOBAL.TEMP_DATA				
			.addOrModifyField(field);
			
		
	});
	
	//what happens when the user clicks on the delete template button
	$(document).on("click", "#delete-template-button", function() {
		
		var obj = { 
			template: $(".selected-template").text(),
			'uri': 'delete_template'
		 };
		
		if(!obj.template) {return;}
		
		if(!confirm("Are you sure you wantto delete this import template?")) { return; }
		
		//ajax call
		$.ajax({
			type: "POST",
			url: "ajax/ajax.file_oriented_requests.php",
			dataType:"json",
			data: obj,		
			beforeSend: function() {
					$("#import-data-spinner").fadeIn(200);
				},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError);
				$("#import-data-spinner").fadeOut(200);			
			},
			success : function(json) {
				
				$("#import-data-spinner").fadeOut(200);
				
				if (json.error != null) {
					toastr.error(json.error);
					return;	
				}
				
				if (json.reply && json.reply.result == "success") {
					toastr.success("Your import data template has been saved!");
					$(".selected-template").fadeOut(200, function() {
						
						$(this).remove();
						
						if($(".selected-template").length == 0) {
							var html = "There are no available import templates";
							$(this).parent().html(html);
						}
						
					});
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
	
	//what happens when the user presses the import button
	$(document).on("click", "#import-data-button", function() {
		
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
											
					dialog = $( "#create-set-on-import-dialog" ).dialog({
					  autoOpen: false,
					  height: 600,
					  width: 550,
					  modal: true,
					  buttons: {
						"Continue": function() {
								//continue with the import
								dialog.dialog( "close" );
                                GLOBAL.TEMP_DATA.comsType == $("#communications-type-select option:selected").attr("id");
								importDataFinal($(".selected-set").attr("rel"));
							},
						Cancel: function() {
						  dialog.dialog( "close" );
						}
					  },
					  close: function() {
						//close the dialog
                          dialog.dialog( "close" );
					  }
					});			
					
					dialog.dialog("open");
								
					
				} //end of success if block
				
				if (json.reply && json.reply.result == "failure") {
					toastr.error(json.reply.message);
				}											
			} 
		}); //end of ajax call	
		
	});

});

function verifyTelecommunicationType() {

	var caller = false, called= false, stamp = false, duration = false;
	var date = false; var time = false;

	for(var i = 0; i < GLOBAL.TEMP_DATA.assigned_fields.length; i++) {

		var field = GLOBAL.TEMP_DATA.assigned_fields[i];

		//caller
		if(field.optgroup == 1 && field.select_index == 0) { caller = true; }

		//called
        if(field.optgroup == 2 && field.select_index == 0) { called = true; }

        //duration
        if(field.optgroup == 3 && (field.select_index == 3 || field.select_index == 4)) { duration = true; }

        //timestamp
        if(field.optgroup == 3 && field.select_index == 0) { stamp = true; }

        //date and time
        if(field.optgroup == 3 && field.select_index == 1) { date = true; }
        if(field.optgroup == 3 && field.select_index == 2) { time = true; }

	}

	if(caller && called && duration && (stamp || (date && time))) {
		return true;
	}

	return false;

}

function importDataFinal(setName) {

	if(GLOBAL.TEMP_DATA.comsType == "telecommunication" && !verifyTelecommunicationType()) {
		toastr.error("You need to assign fields for at least the mandatory fields caller-number, called-number, date and time and duration.");
		return;
	}

	var credentialsObj = {
		uri: "ImportData",
		delimiter: GLOBAL.TEMP_DATA.delimiter,
		com_type: GLOBAL.TEMP_DATA.comsType,
		filters: GLOBAL.TEMP_DATA.filters,
		ignored_lines: GLOBAL.TEMP_DATA.ignored_lines,
		fields: GLOBAL.TEMP_DATA.assigned_fields,
		file_id: GLOBAL.TEMP_DATA.file_id,
		set: setName
	};

	if(!credentialsObj.set) { credentialsObj.set = "no_set"; }
	console.log(credentialsObj);
	//ajax call
	$.ajax({
		type: "POST",
		url: "ajax/ajax.file_oriented_requests.php",
		dataType:"json",
		data: credentialsObj,		
		beforeSend: function() {

            $( "#ajax-spinner-dialog" ).dialog({
                modal: true,
				resizable: false,
				title: "Please Wait..."
            });

			},
		error: function(xhr, ajaxOptions, thrownError) {
			toastr.error(thrownError);		
		},
		success : function(json) {

            $( "#ajax-spinner-dialog" ).dialog("close");
			
			
			if (json.error != null) {
				toastr.error(json.error);
				return;	
			}
			
			if (json.reply && json.reply.result == "success") {

                UPLOADED_FILES_PANEL.toggle();
                IMPORT_DATA_PANEL.toggle();

                $("#import-message").text(json.reply.message);
                $("#import-lines-cell").text(json.reply.lines);
                $("#import-filtered-cell").text(json.reply.filtered)
				$("#import-errors-cell").text(json.reply.errors);
                $("#import-log-link").attr("href", json.reply.log_path);

                $("#import-proc-tel-numbers-cell").text(json.reply.PROCESSED_NUMBERS);
                $("#import-ins-tel-numbers-cell").text(json.reply.INSERTED_NUMBERS);
                $("#import-dub-tel-numbers-cell").text(json.reply.DUBLICATE_NUMBERS);

                $("#import-proc-telecom-cell").text(json.reply.PROCESSED_COMS);
                $("#import-ins-telecom-cell").text(json.reply.INSERTED_COMS);
                $("#import-dub-telecom-cell").text(json.reply.DUBLICATE_COMS);

                $("#import-statistics-panel").dialog({
					modal: true,
					resizable: false,
					title: "Import Successful",
					buttons: [
                        {
                            text: "Ok",
                            click: function() {
                                $( this ).dialog( "close" );
                            }
                        }
                    ],
					width: 500
				})
													
			}
			
			if (json.reply && json.reply.result == "failure") {
				toastr.error(json.reply.message);
			}											
		} 
	}); //end of ajax call
	
}

//create the preview data window
function importDataSecond() {
	
	var id = $("#import-data-panel").attr("rel");
	var templ = $(".selected-template").html() || null;
		
	var credentialsObj = {
			uri: "getFirstRows",
			delimiter: $("#data-import-csv-delimiter-input").val(),
			type: $("#communications-type-select option:selected").attr("id"),
			template: templ,
			id: id //file generated id
		};

	//ajax call
	$.ajax({
		type: "POST",
		url: "ajax/ajax.file_oriented_requests.php",
		dataType:"json",
		data: credentialsObj,		
		beforeSend: function() {
				$("#import-data-spinner").fadeIn(200);
			},
		error: function(xhr, ajaxOptions, thrownError) {
			toastr.error(thrownError);
			$("#import-data-spinner").fadeOut(200);			
		},
		success : function(json) {
			
			$("#import-data-spinner").fadeOut(200);
			
			if (json.error != null) {
				toastr.error(json.error);
				return;	
			}
			
			if (json.reply && json.reply.result == "success") {
				
				var width = $(document).width();
				var height = $(document).height();
				
				$("#import-data-panel").css({'minHeight': '600px', 'minWidth': '900px'});
				$("#import-data-panel").animate({
						top: '25px',
						left: '25px',
						width: (width-50) + 'px',
						height: (height-50) + 'px'
					}, 
					700)
					.attr("stage", "2");
								
				GLOBAL.TEMP_DATA = getTempDataHtml(json.reply.data, 
								json.reply.max_columns, 
								json.reply.available_filters, 
								json.reply.available_fields, 
								json.reply.filters, 
								json.reply.fields,
								json.reply.ignore_lines,
								credentialsObj.type,
								credentialsObj.delimiter,
								credentialsObj.id);
								
				var html = GLOBAL.TEMP_DATA
								.html();
												
				$("#import-data-panel-container")
					.html(html)
					.css({'overflow-x': 'scroll', 'overflow-y': 'scroll'})
					.animate({
						width: (width-100) + 'px',
						height: (height-300)+'px'
					});
					
				var controlsHtml = "<div id='data-import-controls'>";
				
					controlsHtml += "<div class='button import-control' id='pick-template-name-button'>Save Import Template</div>";
					controlsHtml += "<img class='ajax_spinner' id='import-data-spinner' src='templates/images/ajax-loader.gif' />";										
					controlsHtml += "<div class='button import-control' id='import-data-button'>Import Data</div>";
				
				controlsHtml += "</div>";
				
				controlsHtml += "<div id='impot-data-template-name-container' class='import-control'>";
						
					controlsHtml += "<input type='text' id='save-template-input' value='"+json.reply.saved_template+"' placeholder='Template Name' />";
					controlsHtml += "<div class='button' id='import-data-save-template-button'>Ok</div>";
					controlsHtml += "<div style='position:relative; float:left;'>&nbsp;</div>";
											
				controlsHtml += "</div>"
					
				$(controlsHtml).insertAfter("#import-data-panel-container");
								
				GLOBAL.TEMP_DATA
					.selectSavedFiltersAndFields()
					.applyFilters();
					
				if(json.reply.ignore_lines) {
					GLOBAL.TEMP_DATA.changeIgnoredLines(json.reply.ignore_lines);
				}
													
			}
			
			if (json.reply && json.reply.result == "failure") {
				toastr.error(json.reply.message);
			}											
		} 
	}); //end of ajax call	
	
 }

function importDataStart() {
		
	var credentialsObj = {
			uri: "importDataStart"
		}
		
	var id = $("#import-data-panel").attr("rel");
		
	//ajax call
	$.ajax({
		type: "POST",
		url: "ajax/ajax.file_oriented_requests.php",
		dataType:"json",
		data: credentialsObj,		
		beforeSend: function() {
				$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeIn(200);
			},
		error: function(xhr, ajaxOptions, thrownError) {
			toastr.error(thrownError);
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);			
		},
		success : function(json) {
			
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);
			
			if (json.error != null) {
				toastr.error(json.error);
				return;	
			}
			
			if (json.reply && json.reply.result == "success") {
							
				var panelOptions = {					
					blackScreen: false,
					draggable: true,
					onBeforeShow: function() {
							
						if(json.reply.delimiter != null) {
							$("#data-import-csv-delimiter-input").val(json.reply.delimiter);
						}

						var html = "";

						if(json.reply.available_templates != null && json.reply.available_templates.length == 0) {
							html = "There are no available import templates";
						}

						for(var i = 0; i < json.reply.available_templates.length; i++) {
							html += "<div rel='"+json.reply.available_templates[i]+"' class='available-import-template'>"+json.reply.available_templates[i]+"</div>";
						}

						$("#available-import-templates").html(html);

						//set the right click context menu here
						onTemplateRightClickListenerSettings();

					}
				};
				IMPORT_DATA_PANEL = createJPanel($("#import-data-panel"), panelOptions);
                IMPORT_DATA_PANEL.toggle();

			}
			
			if (json.reply && json.reply.result == "failure") {
				toastr.error(json.reply.message);
			}
		} 
	}); //end of ajax call	
	
}

//function that is setting up the right click context menu on the template items
function onTemplateRightClickListenerSettings() {

    $.contextMenu({
        selector: '.available-import-template',
        callback: function(key, options) {

        	var templateId = $(this).attr("rel");

            if(confirm("Are you sure you wish to delete this template?")) {
            	deleteImportTemplate(templateId);
			}

        },
        events: {
        	show: function(options) {
                $(".available-import-template").removeClass("selected-template");
                $(this).addClass("selected-template");
			}
		},
        items: {
            "delete": {name: "Delete", icon: "delete"}
        }
    });

}

function deleteImportTemplate(id) {

    var credentialsObj = {
        id: id,
        uri: "deleteImportTemplate"
    }

    //ajax call
    $.ajax({
        type: "POST",
        url: "ajax/ajax.file_oriented_requests.php",
        dataType:"json",
        data: credentialsObj,
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

                var noTemplatesMessage = "";
                if($(".available-import-template").length == 1) {
                    noTemplatesMessage = "<div>There are no import Templates</div>";
                }
                $(".selected-template").fadeOut(200, function() {
                    $(this).remove();
                    $("#available-import-templates").append(noTemplatesMessage);
                });
            }

            if (json.reply && json.reply.result == "failure") {
                toastr.error(json.reply.message);
            }
        }
    }); //end of ajax call

}

function deleteUploadedFile(id, button) {
	
	if(button.hasClass("working")) { return; }
	
	button.addClass("working");
	
	var credentialsObj = {
			id: id,
			uri: "deleteFile"
		}
		
	//ajax call
	$.ajax({
		type: "POST",
		url: "ajax/ajax.file_oriented_requests.php",
		dataType:"json",
		data: credentialsObj,		
		beforeSend: function() {
				$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeIn(200);
			},
		error: function(xhr, ajaxOptions, thrownError) {
			toastr.error(thrownError);
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);
			button.removeClass("working");
		},
		success : function(json) {
			
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);
			button.removeClass("working");
			
			if (json.error != null) {
				toastr.error(json.error);
				return;	
			}
			
			if (json.reply && json.reply.result == "success") {
				toastr.success(json.reply.message);
				var noFilesMessage = "";
				if($(".uploaded-file").length == 1) {
					noFilesMessage = "<div>There are no uploaded files</div>";
				}
				$("#uploaded-file-"+id).fadeOut(200, function() {
					$(this).remove();
					$("#uploaded-files-tab-1 div.container").append(noFilesMessage);
				});
			}
			
			if (json.reply && json.reply.result == "failure") {
				toastr.error(json.reply.message);
			}											
		} 
	}); //end of ajax call	
	
}

function renameUploadedFile(id, name, button) {
	
	if(button.hasClass("working")) { return; }
	
	button.addClass("working");
	
	var credentialsObj = {
			name: name,
			id: id,
			uri: "renameFile"
		}
		
	//ajax call
	$.ajax({
		type: "POST",
		url: "ajax/ajax.file_oriented_requests.php",
		dataType:"json",
		data: credentialsObj,		
		beforeSend: function() {
				$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeIn(200);
			},
		error: function(xhr, ajaxOptions, thrownError) {
			toastr.error(thrownError);
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);
			button.removeClass("working");
		},
		success : function(json) {
			
			$("#uploaded-file-"+id+" .uploaded-file-controls .uploaded-file-spinner").fadeOut(200);
			button.removeClass("working");
			
			if (json.error != null) {
				toastr.error(json.error);
				return;	
			}
			
			if (json.reply && json.reply.result == "success") {
				toastr.success(json.reply.message);
				$("#uploaded-file-"+id).find("div.uploaded-file-name").html(json.reply.name).removeAttr("initialName");						
			}
			
			if (json.reply && json.reply.result == "failure") {
				toastr.error(json.reply.message);
			}											
		} 
	}); //end of ajax call	
	
}

function loadUploadedFiles(container) {
	
	var credentialsObj = {				
				uri: "getUploadedFiles"
			}
			
		//ajax call
		$.ajax({
			type: "POST",
			url: "ajax/ajax.file_oriented_requests.php",
			dataType:"json",
			data: credentialsObj,		
			beforeSend: function() {
						//userContainer.find(".user-ajax-spinner").fadeIn(200);
				},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError);
				//userContainer.find(".user-ajax-spinner").fadeOut(200);
			},
			success : function(json) {
				
				if (json.reply && json.reply.result == "success") {
					
					var html = "";
					
					for(var i = 0 ; i < json.reply.data.length; i++) {
						
						var file = getFile(json.reply.data[i]);
						
						html += file.html();
											
					}
					
					if(json.reply.data.length == 0) {
						html += "<div>There are no uploaded files</div>";
					}
					
					$(container).html(html);
					$(container).slimScroll({destroy: true});
					$(container).slimScroll({
							//width: '300px',
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
							allowPageScroll: false,
							disableFadeOut: false
						});
					
				}
				
				if (json.error != null) {
					toastr.error(json.error);
					return;	
				}
												
			} 
		}); //end of ajax call	
}

function initializeFileUploads() {
	return $('#upload-data-file').fileuploader({        
		limit: 1, // limit of the files {Number}
		maxSize: 7, // files maximal size in Mb {Number}
		fileMaxSize: 7, // file maximal size in Mb {Number}
		extensions: ['csv'], // allowed extensions or types {Array}
		changeInput: '<div class="fileuploader-input">' +
						  '<div class="fileuploader-input-caption">' +
							  '<span>${captions.feedback}</span>' +
						  '</div>' +
						  '<div class="fileuploader-input-button">' +
							  '<span>${captions.button}</span>' +
						  '</div>' +
					  '</div>',
		inputNameBrackets: true,
		theme: 'default',
		thumbnails: {
			box: '<div class="fileuploader-items">' +
					  '<ul class="fileuploader-items-list"></ul>' +
				  '</div>',
			boxAppendTo: null,
			item: '<li class="fileuploader-item">' +
					   '<div class="columns">' +
						   '<div class="column-thumbnail">${image}</div>' +
						   '<div class="column-title">' +
							   '<div title="${name}">${name}</div>' +
							   '<span>${size2}</span>' +
						   '</div>' +
						   '<div class="column-actions">' +
							   '<a class="fileuploader-action fileuploader-action-remove" title="Remove"><i></i></a>' +
						   '</div>' +
					   '</div>' +
					   '<div class="progress-bar2">${progressBar}<span></span></div>' +
				   '</li>',
			item2: '<li class="fileuploader-item">' +
						'<div class="columns">' +
							'<a href="${data.url}" target="_blank">' +
								'<div class="column-thumbnail">${image}</div>' +
								'<div class="column-title">' +
									'<div title="${name}">${name}</div>' +
									'<span>${size2}</span>' +
								'</div>' +
							'</a>' +
							'<div class="column-actions">' +
								'<a href="${file}" class="fileuploader-action fileuploader-action-download" title="Download" download><i></i></a>' +
								'<a class="fileuploader-action fileuploader-action-remove" title="Remove"><i></i></a>' +
							'</div>' +
						'</div>' +
					'</li>',
			itemPrepend: false,
			removeConfirmation: true,
			startImageRenderer: true,
			synchronImages: true,
			canvasImage: {
				width: null,
				height: null
			},
			_selectors: {
				list: '.fileuploader-items-list',
				item: '.fileuploader-item',
				start: '.fileuploader-action-start',
				retry: '.fileuploader-action-retry',
				remove: '.fileuploader-action-remove'
			},
			beforeShow: function(parentEl, newInputEl, inputEl) {
				// your callback here
			},
			onItemShow: function(item, listEl, parentEl, newInputEl, inputEl) {
				if(item.choosed)
					item.html.find('.column-actions').prepend(
						'<a class="fileuploader-action fileuploader-action-start" title="Start upload"><i></i></a>'
					);
			},
			onItemRemove: function(itemEl, listEl, parentEl, newInputEl, inputEl) {
				itemEl.children().animate({'opacity': 0}, 200, function() {
					setTimeout(function() {
						itemEl.slideUp(200, function() {
							itemEl.remove(); 
						});
					}, 100);
				});
			},
			onImageLoaded: function(itemEl, listEl, parentEl, newInputEl, inputEl) {
				// your callback here
			}
		},
		upload: {
			url: 'ajax/ajax.file_upload.php',
			data: null,
			type: 'POST',
			enctype: 'multipart/form-data',
			start: true,
			synchron: true,
			beforeSend: function(item, listEl, parentEl, newInputEl, inputEl) {
				item.upload.data.username = $("#user-box").attr("rel");				
				return true;
			},
			onSuccess: function(data, item, listEl, parentEl, newInputEl, inputEl, textStatus, jqXHR) {
				
				var data = JSON.parse(data);
				
				item.html.find('.column-actions').append('<a class="fileuploader-action fileuploader-action-remove fileuploader-action-success" title="Remove"><i></i></a>');
				
				setTimeout(function() {
					item.html.find('.progress-bar2').fadeOut(400);
				}, 400);
				
				//success message here
				if(data.result == "error" || data.result == "warning") {
					toastr.error(data.message);
				}
				
				if(data.result == "success") {
					toastr.success("Your file has been uploaded");
					$("#uploaded-files-tabs ul li:first-child > a").click();

				}
				
			},
			onError: function(item, listEl, parentEl, newInputEl, inputEl, jqXHR, textStatus, errorThrown) {
				var progressBar = item.html.find('.progress-bar2');
				
				if(progressBar.length > 0) {
					progressBar.find('span').html(0 + "%");
					progressBar.find('.fileuploader-progressbar .bar').width(0 + "%");
					item.html.find('.progress-bar2').fadeOut(400);
				}
				
				item.upload.status != 'cancelled' && item.html.find('.fileuploader-action-retry').length == 0 ? item.html.find('.column-actions').prepend(
					'<a class="fileuploader-action fileuploader-action-retry" title="Retry"><i></i></a>'
				) : null;
				
				toastr.error(errorThrown);
				
			},
			onProgress: function(data, item, listEl, parentEl, newInputEl, inputEl) {
				var progressBar = item.html.find('.progress-bar2');
				
				if(progressBar.length > 0) {
					progressBar.show();
					progressBar.find('span').html(data.percentage + "%");
					progressBar.find('.fileuploader-progressbar .bar').width(data.percentage + "%");
				}
			},
			onComplete: function(listEl, parentEl, newInputEl, inputEl, jqXHR, textStatus) {
				// your callback here
			}
		},
		dragDrop: {
			container: null,
			onDragEnter: function(event, listEl, parentEl, newInputEl, inputEl) {
				// your callback here
			},
			onDragLeave: function(event, listEl, parentEl, newInputEl, inputEl) {
				// your callback here
			},
			onDrop: function(event, listEl, parentEl, newInputEl, inputEl) {
				// your callback here
			}
			
		},
		addMore: false,        
		clipboardPaste: 2000,
		listInput: true,
		enableApi: true,
		listeners: {
			click: function(event) {
				// input was clicked
			}	
		},
		onSupportError: function(parentEl, inputEl) {
			// your callback here
			alert(inputEl);
		},
		beforeRender: function(parentEl, inputEl) {
			// your callback here
			
			return true;
		},
		afterRender: function(listEl, parentEl, newInputEl, inputEl) {
			// your callback here
		},
		beforeSelect: function(files, listEl, parentEl, newInputEl, inputEl) {
			// your callback here
			return true;
		},
		onFilesCheck: function(files, options, listEl, parentEl, newInputEl, inputEl) {
			// your callback here
			return true;
		},
		onSelect: function(item, listEl, parentEl, newInputEl, inputEl) {
			// your callback here
		},
		afterSelect: function(listEl, parentEl, newInputEl, inputEl) {
			// your callback here
		},
		onListInput: function(fileList, listInputEl, listEl, parentEl, newInputEl, inputEl) {
			var list = [];
			
			$.each(fileList, function(index, value) {
				list.push(value.name);
			});
			
			return list;
		},
		onRemove: function(item, listEl, parentEl, newInputEl, inputEl) {
			// your callback
			return true;
		},
		onEmpty: function(listEl, parentEl, newInputEl, inputEl) {
			// your callback
		},
		dialogs: {
			alert: function(text) {
				return alert(text);
			},
			confirm: function(text, callback) {
				confirm(text) ? callback() : null;
			}
		},
		captions: {
			button: function(options) { return 'Choose ' + (options.limit == 1 ? 'File' : 'Files'); },
			feedback: function(options) { return 'Choose ' + (options.limit == 1 ? 'file' : 'files') + ' to upload'; },
			feedback2: function(options) { return options.length + ' ' + (options.length > 1 ? ' files were' : ' file was') + ' chosen'; },
			drop: 'Drop the files here to Upload',
			paste: '<div class="fileuploader-pending-loader"><div class="left-half" style="animation-duration: ${ms}s"></div><div class="spinner" style="animation-duration: ${ms}s"></div><div class="right-half" style="animation-duration: ${ms}s"></div></div> Pasting a file, click here to cancel.',
			removeConfirmation: 'Are you sure you want to remove this file?',
			errors: {
				filesLimit: 'Only ${limit} files are allowed to be uploaded.',
				filesType: 'Only ${extensions} files are allowed to be uploaded.',
				fileSize: '${name} is too large! Please choose a file up to ${fileMaxSize}MB.',
				filesSizeAll: 'Files that you choosed are too large! Please upload files up to ${maxSize} MB.',
				fileName: 'File with the name ${name} is already selected.',
				folderUpload: 'You are not allowed to upload folders.'
			}
		}		
	});
}

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
				height: 100
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

function getFile(f) {
	
	var file = {
		
		id:   f.file_id,
		name: f.file_name,
		filters: f.file_filters,		
		html: function() {
				
				var html = "<div class='uploaded-file' id='uploaded-file-"+this.id+"' rel='"+this.id+"'>";
				
					html += "<div class='uploaded-file-name'>"+this.name+"</div>";
					html += "<div class='uploaded-file-controls'>"
						
						html += "<img class='uploaded-file-spinner' src='templates/images/ajax-loader.gif' />";
						html += "<img class='file-control rename-file' title='Rename File' src='templates/images/edit_file_icon.png' />";
						html += "<img class='file-control import-data' title='Import Data' src=templates/images/filter_file_icon.png />";
						html += "<img class='file-control delete-file' title='Delete File' src='templates/images/delete_file_icon.png' />";
						
					html += "</div>";
					
				html += "</div>";
				
				return html;
								
			} //end of html method
			
		
	}
	
	return file;
	
}

function getTempDataHtml(data, column_number, filter_types, field_types, filters, assigned_fields, ignore_lines, communicationsType, delimiter, file_id) {
	
	var del = delimiter;
	if(!delimiter) {
		del = ";";
	}

	var htmlConstructor = {
		file_id: file_id,
		data: data,
		delimiter: del,
		filters: filters,
		assigned_fields: assigned_fields,
		filter_types: filter_types,
		fields: field_types,
		columns: column_number,	
		comsType: communicationsType,
		ignored_lines: ignore_lines|0,
		
		selectSavedFiltersAndFields: function() {
			
				var tableSelector = $("#filtered-data-table");
			
				var headerRowsSelector = tableSelector.find("tr th");
				
				//every column itterator
				for(var j = 1; j <= headerRowsSelector.length; j++) {	
				
					var filterHTML = "<ul class='filter-items'>";
												
					for(var i = 0; i < this.filters.length; i++) {
						if(this.filters[i].column == j) {
							
							filterHTML += this.addFilterMechanism(this.filters[i].value,this.filters[i].type);
														
							tableSelector.find("tr td:nth-child("+j+")").addClass('filtered');
														
						} 
					}//end of filters itterator
					
					filterHTML += "</ul>";
						
					$(filterHTML).appendTo(tableSelector.find("tr th:nth-child("+j+")"));
					
					for(var k = 0; k < this.assigned_fields.length; k++) {						
						if(this.assigned_fields[k].column == j) {							
							//select the right assigned field
							var selectIndex = parseInt(this.assigned_fields[k].select_index)+1;
							var optgroupIndex = parseInt(this.assigned_fields[k].optgroup)-1;
							var column = parseInt(j)-1;
														
							$("#filtered-data-table tr th:eq("+column+") select.available-fields optgroup:eq("+optgroupIndex+") option:nth-child("+selectIndex+")").attr("selected", "selected");	
						}						
					}
					
					//select the right filters
					$("#filtered-data-table tr th:nth-child("+j+") ul.filter-items li").each(function() {
						
						var filterTYPE = $(this).attr("rel");
						
						$(this).find(".available-filters option").each(function() {
														
							if($(this).attr("rel") == filterTYPE) {
								$(this).prop("selected", true);
							}
							
						});						
						
					});
						
				}//end of columns itterator
				
				//making the filters sortable
				$( ".filter-items" ).sortable({
				  placeholder: "ui-state-highlight",
				  update: function( event, ui ) {
					  		
							var listItem = ui.item;
							
							//what happens when the user sorts a filter
							var columnTH = $(this).parent();
							var columnIndex = columnTH.index() + 1;
							
							var typeValue = listItem.find(".available-filters option:selected").attr("rel");
							
							if(typeValue == "choose") {
								toastr.warning("Choose a filter type!");
								return;
							}
							
							var liItemIndex = listItem.index() + 1;
							
							//first notify the global object and see if this filter already existed so it can be changed
							var filter = {
								column: columnIndex,
								order: liItemIndex,
								type: typeValue,
								value: listItem.find(".filter-mechanism-value-input").val()
							}
							
							if(filter.value == "" || filter.value == null) {
								toastr.warning("Fill in the filter value!");
								return;
							}
							
							GLOBAL.TEMP_DATA
								.addOrModifyFilter(filter, 'order')
								.rearrangeFiltersOrder(columnIndex)
								.applyFilter(columnIndex);
								
							
					  }
				});
				$( ".filter-items" ).disableSelection();
				
				return this;
				
			}, //end of selectSavedFiltersAndFields
		
		html: function() {
					
					var html = "<div id='import-data-ignore-lines-container'>";
					
						html += "<span class='directions'>You can choose to ignore 0 or more lines from the import procedure.</span>";
					
						html += "<label title='Ignore this number of lines and exclude them from the import procedure.'>Ignore Lines: </label>";
						
						var ign = (this.ignored_lines?this.ignored_lines:0);
												
						html += "<input id='import-data-ignore-lines-filter' min='0' type='number' value='"+ign+"' />";
						
					html += "</div>";
					
					html += "<span class='directions'>You can assign the files columns to a specific database field and add filters to manipulate each column of data before importing it to the database. You can view the results of your filters on the sample file lines. The filters you add can be sorted and applied by order of preference by dragging and dropping them in the desired order.</span>";
					
					html += "<div id='table-container'>";
								
							html += "<table id='filtered-data-table'>";
							
								html += "<thead>";
									
									html += "<tr>";
									
									var addFilterButton = "<div class='add-filter button'>Add Filter</div>";
									
									for(var i = 0; i < this.columns ; i++) {									
										html += "<th>"+this.fields+addFilterButton+"</th>";										
									}
									
									html += "</tr>";
									
								html += "</thead>";
								
								html += "<tbody>";
								
								for(var i = 0; i < this.data.length; i++) {
									html += "<tr>";
									
									for(var j = 0 ; j < this.data[i].length; j++) {
										html += "<td initialValue='"+this.data[i][j]+"'>"+this.data[i][j]+"</td>";
									}
									
									html += "</tr>";
								}
								
								html += "</tbody>";
							
							html += "</table>";						
								
					return html; 
					
				}, //end of html method
		
		restoreValues: function() {
								
				$("#filtered-data-table tr td").each(function() {
					$(this).text($(this).attr("initialValue"));
					$(this).removeClass("excluded");
				});
				
				return this;
					
			}, //end of restore values method
		
		applyFilters: function(column) {		
					
				if(this.filters.length == 0) {
					this.restoreValues();
					return;
				}
											
				if(column != null) {											
					this.applyFilter(column);					
					return this;					
				}
								
				//itterate over every column
				for(var j = 1; j <= this.columns; j++) {
					this.applyFilter(j);										
				}
				
				return this;
				
			}, //end of apply filters method
			
		addFilterMechanism: function(Value, Rel) {
				
				var filterHTML = "<li class='filter-mechanism-item ui-state-default' rel='"+(Rel==null?"":Rel)+"'>";
														
					filterHTML += this.filter_types;
					
					filterHTML += "<input type='text' class='filter-mechanism-value-input' value='"+(Value==null?"":Value)+"' />";
					
					filterHTML += "<div class='remove-filter'>X</div>";
													
				filterHTML += "</li>";
				
				return filterHTML;
			}, //end of addFilterMechanism
		
		addOrModifyFilter: function(f, fieldToBeModified) {
				
				var found = false;
				
				for(var i = 0 ; i < this.filters.length; i++) {
					
					if(fieldToBeModified == 'type') {
						if(this.filters[i].column == f.column && this.filters[i].order == f.order && this.filters[i].value == f.value) {
							found = true;
							this.filters[i].type = f.type;
						}
					}
					
					if(fieldToBeModified == 'value') {
						if(this.filters[i].column == f.column && this.filters[i].order == f.order && this.filters[i].type == f.type) {
							found = true;
							this.filters[i].value = f.value;
						}
					}
					
					if(fieldToBeModified == 'order') {
						if(this.filters[i].column == f.column && this.filters[i].value == f.value && this.filters[i].type == f.type) {							
							found = true;														
							this.filters[i].order = f.order;							
						}
					}
					
				} //end of filters array itterator
				
				if(!found) {
					$("#filtered-data-table tr td:nth-child("+f.column+")").addClass("filtered");
					this.filters.push(f);
				}
				
				return this;
								
			},//end of addOrModifyMethod
		
		removeFilter: function(f, selector) {
				
				for(var i = 0 ; i < this.filters.length; i++) {					
					if(this.filters[i].order == f.order && this.filters[i].type == f.type && this.filters[i].value == f.value && this.filters[i].column == f.column) {						
						
						var thisColsFilters = getFiltersByColumn(this.filters[i].column, this.filters);
						if(thisColsFilters.length == 1) { 
							$("#filtered-data-table tr td:nth-child("+this.filters[i].column+")").removeClass("filtered"); 
						}
						this.filters.splice(i, 1);						
						selector.fadeOut(200, function() {
							$(this).remove();
						});						
					}					
				}
				
				return this;
								
			}, // end of removeFilter method
		
		changeIgnoredLines: function(lines) {
								
				if(!lines) {
					this.ignored_lines = 0;
					return;
				}
				
				this.ignored_lines = lines;
				
				$("#filtered-data-table tr td:not(.excluded)").css({ 'textDecoration': 'none', 'color': '#000' });
				
				for(var i = 1; i <= this.ignored_lines; i++) {
					$("#filtered-data-table tr:nth-child("+i+") td").each(function() {
						
						$(this).css({'textDecoration': 'line-through', 'color': '#ccc'});
						
					});
				}				
				
			}, // end of changeIgnoredLines
		
		applyFilter: function(column) {
								
					//get this columns filters sorted by filter order
					var colFilters = getFiltersByColumn(column, this.filters);
					
					$("#filtered-data-table tr").each(function() {
						
						var td = $(this).find("td:nth-child("+column+")");
						var initialValue = td.attr("initialValue");
						var filteredValue = initialValue;
												
						for(var i = 0 ; i < colFilters.length; i++) {
													
							var filter = colFilters[i];
														
							if(filter.type == 'remove_prefix') {
								if(filteredValue == null) {
									continue;
								}
								var regex = new RegExp("^"+filter.value, "g");
								filteredValue = filteredValue.replace(regex, '');								
								td.text(filteredValue);
								//we use continue to stop the flow from checking the ifs
								continue;
							}
							
							if(filter.type == 'remove_suffix') {																
								if(filteredValue == null) {
									return;
								}								
								var regex = new RegExp(filter.value + "$", "g");
								filteredValue = filteredValue.replace(regex, '');								
								td.text(filteredValue);
								continue;
							}
							
							if(filter.type == 'add_prefix') {
								filteredValue = filter.value + filteredValue;								
								td.text(filteredValue);
								continue;
							}
											
							if(filter.type == 'add_suffix') {
								filteredValue = filteredValue + filter.value;								
								td.text(filteredValue);
								continue;
							}
						
							if(filter.type == 'remove_fixed_prefix') {															
								if(filteredValue == null) {
									return;
								}
								filteredValue = filteredValue.substr(filter.value, filteredValue.length);								
								td.text(filteredValue);
								continue;
							}
						
							if(filter.type == 'remove_fixed_suffix') {																					
								if(filteredValue == null) {
									return;
								}								
								filteredValue = filteredValue.substr(0, (filteredValue.length-filter.value));
								td.text(filteredValue);
								continue;				
							}
						
							if(filter.type == 'exclude_if_contains') {																						
								if(filteredValue == null) {
									return;
								}								
								if(filteredValue.indexOf(filter.value) !== -1) {									
									td.parent().children().addClass("excluded");									
								}
							}
						
							if(filter.type == 'replace_with_null') {								
								if(filteredValue == null) {
									return;
								}								
								var regex = new RegExp(filter.value, "g");								
								filteredValue = filteredValue.replace(regex, "");
								td.text(filteredValue);
								continue;
							}
							
						}
						
					});	
				
			}, //end of apply single filter method
		
		rearrangeFiltersOrder: function(column) {
				
				var _this = this.filters;;
								
				$("#filtered-data-table tr th:nth-child("+column+") ul li.filter-mechanism-item").each(function() {
										
					var order = $(this).index() + 1;
					var type = $(this).find("select.available-filters option:selected").attr("rel");
					var value = $(this).find(".filter-mechanism-value-input").val();					
					
					if(!(type == null || type == "choose" || value == "" || value == null)) {  
											
						for(var index = 0 ; index < _this.length; index++) {
							
							if(_this[index].column == column) {
								
								if(_this[index].type == type && _this[index].value == value) {
									_this[index].order = order;
								}
								
							}
							
						}
											
					}
					
				});
				
				return this;
				
			}, //end or rearrangeFiltersOrder method
		
		addOrModifyField: function(field) {
			
				for(var i = 0 ; i < this.assigned_fields.length; i++) {
					if(this.assigned_fields[i].column == field.column) {
						if(field.optgroup == 0 && field.select_index == 0) {
							this.assigned_fields.splice(i,1);							
							return;
						}
						this.assigned_fields[i] = field;						
						return;
					}
				}
				
				this.assigned_fields.push(field);
			} //end of addOrModifyField method
					
	} 
	
	return htmlConstructor;
	
}

//return the given columns filters. Index is not 0 based, aka first index is 1 , 2 , 3 ... etc
function getFiltersByColumn(columnIndex, filtersArray) {	
	if(!filtersArray) { return []; }
	var arrayToRet = [];
	for(var i = 0; i < filtersArray.length; i++) {
		if(columnIndex == filtersArray[i].column) {
			arrayToRet.push(filtersArray[i]);
		}
	}
	return arrayToRet.sort( function(a, b) {return a.order - b.order;} );
}

function resizeSidebar() {
    var body = document.body,
        html = document.documentElement;
    var height = Math.max( body.scrollHeight, body.offsetHeight,
        html.clientHeight, html.scrollHeight, html.offsetHeight );
    $("#left-side-bar").css({'height': height + "px"});
}
