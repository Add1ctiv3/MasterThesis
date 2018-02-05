function getInputObject(input, iconNamePng, secondInput) {

	var CheckFactors = { NOT_EMPTY: "1000", LENGTH: "2000", MATCHING_WITH: "3000", STRENGTH: "4000", EMAIL_TYPE: "5000" }

	var inputObj = {
					 jInput: input,
					 mInput: secondInput,
					 icon: iconNamePng,
					 tooltipPosition: 'e',
					 iconize: true,
					 hasError: false,
					 status: "",
					 check: function(checkFactors) {
													
													this.normal();
													
													//error check variable														
													var checkReason = "";
													
													//itterate through the checkFactors
													for(var i = 0; i < arguments.length; i++) {
														//alert("Field: " + this.jInput.attr("id") + " - " + arguments[i]);
														//check the factor type
														
														//blank error
														if(arguments[i] === CheckFactors.NOT_EMPTY) {
															if(this.jInput.val() === "") {
																//error
																this.hasError = true;
																this.status = "This field can not be empty!";
																this.error(false);
																return this;
															}																
														}
														
														//matching error
														if(arguments[i] === CheckFactors.MATCHING_WITH) {
															if(this.mInput !== null) {
																if((this.jInput.val() !== "" && this.mInput.val() !== "") && this.jInput.val() === this.mInput.val()) {
																	this.success();
																}
																if((this.jInput.val() !== "" || this.mInput.val() !== "") && this.jInput.val() !== this.mInput.val()) {
																	//error
																	this.hasError = true;
																	this.status = "Your passwords do not match!";
																	this.error(false);
																	return this;
																}
																
															}
														}
														
														//length error
														if(arguments[i] === CheckFactors.LENGTH) {
															if(this.jInput.val().length <= 2 || this.jInput.val().length > 50) {
																//error
																this.hasError = true;
																this.status = "This field can not have less than 3 or more than 50 characters!";
																this.error(false);
																return this;
															}
														}
														
														//strength error
														if(arguments[i] === CheckFactors.STRENGTH) {															
															if(this.jInput.val() !== "") {
																var patt = new RegExp("^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{6,16}$");
																if(!patt.test(this.jInput.val())) {
																	this.hasError = true;
																	this.status = "Your password must be 6-16 charcters long and contain at least one "
																							+ "letter, number and special character!";
																	this.error(false);
																	return this;
																}
															}
														}
														
														//type error
														if(arguments[i] === CheckFactors.EMAIL_TYPE) {
	var patt = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
															if(!patt.test(this.jInput.val())) {
																this.hasError = true;
																this.status = "Invalid e-mail!";
																this.error(false);
																return this;
															}
														}
														
													}//end of check method
					 							},
						error: function(visible) {
												if(this.hasError && this.status !== "") {
													this.normal();
													this.jInput.data("powertip", this.status);
													this.jInput.powerTip({placement: this.tooltipPosition});
													if(this.iconize) { this.jInput.css({ 'backgroundImage': 'url(templates/images/fail-icon.png)' }); }
													this.jInput.css({ 'borderColor': '#A60000' });
													this.jInput.addClass("input_error");
													if(visible) { this.jInput.powerTip("show"); }
													return this;	
												}												
											}, //end of error method
						success: function() {
											 this.normal();
											 if(this.iconize) { this.jInput.css({ 'backgroundImage': 'url(templates/images/checked-icon.png)' }); }
											 this.jInput.css({ 'borderColor': '#ddd' });
											 return this;
											}, //end of success method
						normal: function() {
											this.jInput.removeClass("input_error");											
											this.jInput.removeData("powertip");
											$.powerTip.destroy(this.jInput);
											if(this.iconize) { this.jInput.css({'backgroundImage': 'url(templates/images/'+this.icon+')'}); }
											this.jInput.css({ 'borderColor': '#ddd' });
											return this;
											} //end of normal method				
										
					}
					
	return inputObj;
		
}