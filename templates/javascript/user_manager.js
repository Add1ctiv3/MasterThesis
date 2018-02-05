
function userManager(containerDiv, data) {
	
	var manager = {
		users: data,
		container: containerDiv,
		html: function() {
			
				var html = '';
				
				for(var i = 0; i < this.users.length; i++) {
					
					html += '<div class="user-container" id="'+this.users[i].username+'">';		
					
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<img class="user-administration-user-icon" src="templates/images/user_images/'+this.users[i].icon+'"  />';
						html += "</div>";
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Username:</span>';
							html += '<div rel="'+this.users[i].username+'" class="user-username-container searchable">'+this.users[i].username+'</div>';							
						html += "</div>";
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Last Name:</span>';
							html += '<div rel="'+this.users[i].lastname+'" class="user-lastname-container searchable editable-text">'+this.users[i].lastname+'</div>';							
						html += '</div>';
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">First Name:</span>';
							html += '<div rel="'+this.users[i].firstname+'" class="user-firstname-container searchable editable-text">'+this.users[i].firstname+'</div>';							
						html += '</div>';
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Email:</span>';
							html += '<div rel="'+this.users[i].email+'" class="user-email-container searchable editable-text">'+this.users[i].email+'</div>';							
						html += "</div>";
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">User Type:</span>';
							html += '<div class="user-type-container searchable editable-select">'+this.users[i].type+'</div>';							
						html += "</div>";
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Access Level:</span>';
							html += '<div class="user-access-level-container searchable editable-select">'+this.users[i].access_level+'</div>';							
						html += '</div>';
							
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Last Login:</span>';
							html += '<div class="user-last-login-container searchable">'+this.users[i].last_login+'</div>';													
						html += '</div>';
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<span class="panel-input-label">Blocked Y/N - Reason:</span>';
							html += '<input type="checkbox" class="user-blocked-checkbox editable-checkbox" '+(this.users[i].blocked===1?"checked":"")+' />';
							html += '<input class="users-block-message" type="text" '+(this.users[i].blocked===1?"":"disabled")+' value="'+this.users[i].blocked_message+'" />';							
						html += '</div>';
						
						html += "<div class='panel-input-container onethird-container-width'>";
							html += '<input type="button" class="button delete-user-button" value="Delete"  />';
							html += '<img src="templates/images/ajax-loader.gif" class="ajax_spinner user-ajax-spinner" />';
						html += '</div>';
											
					html += '</div>';
					
				}
												
				this.container.html(html);			
				
				return this;
				
			}
		
	}
	
	return manager;
	
}