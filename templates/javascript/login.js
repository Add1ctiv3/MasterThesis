
$(document).ready(function() {
	
	var CheckFactors = { NOT_EMPTY: "1000", LENGTH: "2000", MATCHING_WITH: "3000", STRENGTH: "4000", EMAIL_TYPE: "5000" }
			
	//toastr settings
	toastr.options = { positionClass: 'toast-bottom-right', }
	toastr.options.closeButton = true;
	toastr.options.newestOnTop = true;
	toastr.options.closeMethod = 'fadeOut';
	toastr.options.closeDuration = 300;
	toastr.options.closeEasing = 'swing';
		
	//signup listeners
	$("#create_new_account_link").click(function() {
		toggleSignupPanel();
	});
	
	$("#signup_x").click(function() {
		toggleSignupPanel();
	});		
	
	//set a timer to count when a user stops typing
	var username_autocheck_reset_timer;	
    $("#signup_username").keyup(function (e){
		
		var signup_username = getInputObject($("#signup_username"), "user-icon.png", null);
		
        clearTimeout(username_autocheck_reset_timer);
		
        var user_name = signup_username.jInput.val();
		
		if(user_name === "") {
			signup_username.normal();
		} else {
			username_autocheck_reset_timer = setTimeout(function(){
				check_username_ajax(user_name);
			}, 700);
		}
    }); 
		
	//passwords check
	var passwords_autocheck_reset_timer;	
	$("#signup_password, #signup_password_repeat").keyup(function(e) {		
		
		var password = getInputObject($("#signup_password"), "password-open-icon.png", $("#signup_password_repeat"));
		var password_repeat = getInputObject($("#signup_password_repeat"), "password-open-icon.png", $("#signup_password"));
			
		clearTimeout(passwords_autocheck_reset_timer);
		
		passwords_autocheck_reset_timer = setTimeout(function(){	
			
			password.check(CheckFactors.STRENGTH, CheckFactors.MATCHING_WITH);
			password_repeat.check(CheckFactors.STRENGTH, CheckFactors.MATCHING_WITH);
			
		}, 700);			
	}); //end of passwords check
		
	//signup button click trigger
	$("#signup_button").click(function() {
				
		if($("#signup_button").hasClass("busy")) { return; }
		
		$("#signup_panel .ajax_spinner").show();
		$("#signup_button").addClass("busy");
				
		var username = getInputObject($("#signup_username"), "user-icon.png", null);
		var password = getInputObject($("#signup_password"), "password-open-icon.png", $("#signup_password_repeat"));
		var password_repeat = getInputObject($("#signup_password_repeat"), "password-icon.png", $("#signup_password"));
		var lastname = getInputObject($("#signup_lastname"), "credentials-icon.png", null);
		var firstname = getInputObject($("#signup_firstname"), "credentials-icon.png", null);
		var email = getInputObject($("#signup_email"), "email-icon.png", null);		
		
		username
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.LENGTH);
		password
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.MATCHING_WITH, CheckFactors.STRENGTH);
		password_repeat
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.MATCHING_WITH, CheckFactors.STRENGTH);
		lastname
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.LENGTH);
		firstname
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.LENGTH);
		email
			.normal()
			.check(CheckFactors.NOT_EMPTY, CheckFactors.EMAIL_TYPE);
		
		//if there is an error return and dont perform the next checks
		if(username.hasError || password.hasError || password_repeat.hasError || lastname.hasError || firstname.hasError || email.hasError) {
			$("#signup_panel").effect("shake");
			$("#signup_panel .ajax_spinner").fadeOut(200);
			$("#signup_button").removeClass("busy");
			return;
		}
				
		//username taken check
		if($("#signup_button").attr("username_taken") === "true") {	
			username.hasError = true;
			username.status = "This username is already in use!";
			username.error(false);
			
			$("#signup_panel").effect("shake");
			$("#signup_panel .ajax_spinner").fadeOut(200);
			$("#signup_button").removeClass("busy");
			return;
		}
						
		var credentialsObj = {
			username: $("#signup_username").val(),
			password: $("#signup_password").val(),
			password_repeat: $("#signup_password_repeat").val(),
			lastname: $("#signup_lastname").val(),
			firstname: $("#signup_firstname").val(),
			email: $("#signup_email").val(),
			uri: "user_signup"
		};
		
		$.ajax({
			type: "POST",
			url: "ajax/login.php",
			dataType:"json",
			data: credentialsObj,
			error: function(xhr, ajaxOptions, thrownError) { 
									toastr.error(xhr.status); 
									toastr.error(thrownError); 
									$("#signup_button").removeClass("busy");
									$("#signup_panel .ajax_spinner").fadeOut(200); 
						},
			success : function(json) {
				if (json.error != null) {
					toastr.error(json.error);
					$("#signup_button").removeClass("busy");
					$("#signup_panel .ajax_spinner").fadeOut(200);
				}
				if(json.reply !== null) {
					
					if(json.reply.result === "error") {	
						for(var i = 0; i < json.reply.data.length; i++) {
							var obj = getInputObject($("#"+json.reply.data[i].field), resolveIcon(json.reply.data[i].field), null);
							obj.hasError = true;
							obj.status = json.reply.data[i].message;
							obj.error(false);
							$("#signup_button").removeClass("busy");
							$("#signup_panel .ajax_spinner").fadeOut(200);
						}
					}
					
					if(json.reply.result === "success") {
						//success
						$("#signup_button").removeClass("busy");
						$("#signup_panel .ajax_spinner").fadeOut(200);
						toggleSignupPanel();
						toastr.success("Your account was successfully created!");
					}
					
				}									
			} 
		});
				
	}); //end of signup button click trigger
			
	//login listeners
	$("#login_button").click(function() {
		
		if($("#login_button").hasClass("busy")) { return; }
		$("#login_button").addClass("busy");
		$("#login_panel .ajax_spinner").show();
		
		//first remove previous error signs
		
		var login_username = getInputObject($("#login_username"), "user-icon.png", null);
		var login_password = getInputObject($("#login_password"), "password-icon.png", null);
		
		//check for errors
		login_username.normal().check(CheckFactors.NOT_EMPTY, CheckFactors.LENGTH);
		login_password.normal().check(CheckFactors.NOT_EMPTY, CheckFactors.LENGTH);
			
		if(login_username.hassError || login_password.hasError) {			
			$("#login_panel .ajax_spinner").fadeOut(200);
			$("#login_button").removeClass("busy");
			$("#login_panel").effect("shake");
			return;
		}
		
		//send the credentials to our backend
		var credentialsObj = {
			username: $("#login_username").val(),
			password: $("#login_password").val(),			
			uri: "login_user"
		};
		
		$.ajax({
			type: "POST",
			url: "ajax/login.php",
			dataType:"json",
			data: credentialsObj,
			beforeSend: function() {
					
				},
			error: function(xhr, ajaxOptions, thrownError) { 
						toastr.error(xhr.status); 
						toastr.error(thrownError);
						$("#login_button").removeClass("busy");
						$("#login_panel .ajax_spinner").fadeOut(200); 
					},
			success : function(json) {
				
				if (json.error != null) {
					toastr.error(json.error);
					$("#login_button").removeClass("busy");
					$("#login_panel .ajax_spinner").fadeOut(200);
				}
				
				if(json.reply != null) {
					
					if(json.reply.result === "error") {
						
						for(var i = 0; i < json.reply.data.length; i++) {							
							var obj = getInputObject($("#"+json.reply.data[i].field), resolveIcon(json.reply.data[i].field), null);
							
							obj.hasError = true;
							obj.status = json.reply.data[i].message;
							var showError = (json.reply.data[i].field === "login");
							obj.error(showError);						
						}						
						
						if(json.reply.data[0].field === "login") {
							var obj = getInputObject($("#login_username"), resolveIcon("login_username"), null);
							var obj2 = getInputObject($("#login_password"), resolveIcon("login_password"), null);
							obj.hasError = true;
							obj2.hasError = true;
							obj.status = json.reply.data[0].message;
							obj2.status = json.reply.data[0].message;
							obj.error(false);
							obj2.error(false);
						}
							
						$("#login_panel").effect("shake");
										
						$("#login_panel .ajax_spinner").fadeOut(200);
						$("#login_button").removeClass("busy");
						
						return;						
					}
					
					if(json.reply.result === "success") {
						//success
						$("#login_button").removeClass("busy");
						$("#login_panel .ajax_spinner").fadeOut(200);
						
						window.location = "index.php?p=user/home";
						
					}
					
				}									
			} 
		});	
						
	}); //end of on login button click section	
		
});

function resolveIcon(field) {
	if(field === "signup_username" || field === "login_username") { return "user-icon.png"; }
	if(field === "signup_password" || field === "login_password") { return "password-open-icon.png"; }
	if(field === "signup_password_repeat") { return "password-icon.png"; }
	if(field === "signup_lastname") { return "credentials-icon.png"; }
	if(field === "signup_firstname") { return "credentials-icon.png"; }
	if(field === "signup_email") { return "email-icon.png"; }
}

function check_username_ajax(username){
    
	//show the checking spinner
	$("#signup_username").css({'backgroundImage': 'url(templates/images/ajax-loader-small.gif)'});
	
	var credentialsObj = {
		username: $("#signup_username").val(),		
		uri: "check_signup_username"
	};
	
	$.ajax({
		type: "POST",
		url: "ajax/login.php",
		dataType:"json",
		data: credentialsObj,
		error: function(xhr, ajaxOptions, thrownError) { 
					toastr.error(xhr.status); 
					toastr.error(thrownError); 
					$("#signup_username").css({'backgroundImage': 'url(templates/images/fail-icon.png)'});
				},
		success : function(json) {
			if (json.error != null) {					
				toastr.error(json.error);
				$("#signup_username").css({'backgroundImage': 'url(templates/images/fail-icon.png)'});
			}
			if(json.reply != null) {					
				
				if(json.reply.result === "error") {					
					
					var obj = getInputObject($("#"+json.reply.data[0].field), "user-icons.png", null);
					obj.hasError = true;
					obj.status = json.reply.data[0].message;
					obj.error(true);
					
					if(json.reply.data[0].message === "This username is already in use!") {
						$("#signup_button").attr("username_taken", "true");
					}						
									
				}
				
				if(json.reply.result === "success") {
					var obj = getInputObject($("#signup_username"), "user-icons.png", null);		
					obj.success();
					$("#signup_button").attr("username_taken", "false");
				}
				
			}									
		} 
	});
			
}

function toggleSignupPanel() {
	
	if(!$("#dumpener").is(":visible")) {
		
		//signup link function
		$("#dumpener").fadeIn(200, 'swing', function() {			
			//callback function
			$(this).css({
						'width': $(document).width(), 
						'height': $(document).height()}
						);
						
			$("#signup_panel").fadeIn(300, 'swing', function() {

				$("#signup_username").focus();

				//listen for esc key pressing
				$(document).keyup(function(e) {
					 if (e.keyCode == 27) { // escape key maps to keycode `27`
						if($("#dumpener").is(":visible")) {
							toggleSignupPanel();
						}					
					}
				});
												
			});
						
		});
		
	} else {
		
		$("#signup_panel").fadeOut(200);
		$("#dumpener").fadeOut(200);
		
		var username = getInputObject($("#signup_username"), "user-icon.png", null);
		var password = getInputObject($("#signup_password"), "password-open-icon.png", $("#signup_password_repeat"));
		var password_repeat = getInputObject($("#signup_password_repeat"), "password-icon.png", $("#signup_password"));
		var lastname = getInputObject($("#signup_lastname"), "credentials-icon.png", null);
		var firstname = getInputObject($("#signup_firstname"), "credentials-icon.png", null);
		var email = getInputObject($("#signup_email"), "email-icon.png", null);		
		
		username
			.normal().jInput.val("");
		password
			.normal().jInput.val("");
		password_repeat
			.normal().jInput.val("");
		lastname
			.normal().jInput.val("");
		firstname
			.normal().jInput.val("");
		email
			.normal().jInput.val("");
	
	}
	
}

