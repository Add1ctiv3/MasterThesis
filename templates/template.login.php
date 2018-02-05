<?php if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }  ?>
<!doctype html>

<html>

<head>

    <meta charset="utf-8">
    
    <title>Welcome</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />
    <link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />
    <link rel="stylesheet" type="text/css" href="templates/libs/tooltiper/css/jquery.powertip.css" />
    <link rel="stylesheet" type="text/css" href="templates/css/login_css.css" />

</head>

<body>

	<div id="login_panel" class="panel">
    	
        <img id="logo" src="templates/images/graphicx_logo.png" width="360" height="173" alt="Logo" />
        
        <form action="#" id="login_form">
        	
            <label for="login_username">Username: </label>
            <input type="text" placeholder="Type your username here..." id="login_username" class="login_page_input" />
            
            <label for="login_password">Password: </label>
            <input type="password" id="login_password" class="login_page_input" placeholder="Type your password here..." />
            
            <a id="create_new_account_link" href="#">Don't have an account?</a>
            
            <input id="login_button" class="button" type="button" value="Log in" />
        
        </form>
		
        <img class="ajax_spinner" src="templates/images/ajax-loader.gif" alt="spinner" />
        
    </div>
    
    <!-- this part is actually hidden till user clicks on the signup link -->
    <div id="dumpener">
    
        <div id="signup_panel" class="panel">
        
        	<img id="signup_x" src='templates/images/close-panel-icon.png' width='16' height='16' />
            
            <form action="#" id="signup_form">
            	
                <img id="signup_logo" src="templates/images/graphicx_logo.png" width="240" height="115" alt="Logo" />
                
                <label for="signup_username">Username: </label>
                <input type="text" placeholder="Type your username here..." id="signup_username" class="login_page_input" />
                
                <label for="signup_password">Password: </label>
                <input type="password" id="signup_password" class="login_page_input" placeholder="Type your password here..." />
                
                <label for="signup_password_repeat">Repeat Password: </label>
                <input type="password" id="signup_password_repeat" class="login_page_input" placeholder="Confirm your password..." />
                
                <div class='separator-hor'></div>
                 
                <label for="signup_lastname">Last Name: </label>
                <input type="text" placeholder="Type your last name here..." id="signup_lastname" class="login_page_input" />
                
                <label for="signup_firstname">First Name: </label>
                <input type="text" placeholder="Type your first name here..." id="signup_firstname" class="login_page_input" />
                
                <label for="signup_email">E-mail: </label>
                <input type="email" placeholder="Type your e-mail here..." id="signup_email" class="login_page_input" />
                
                <input id="signup_button" class="button" type="button" value="Sign Up" />
                       
            </form>
            
            <img class="ajax_spinner" src="templates/images/ajax-loader.gif" alt="spinner" />
            
        </div>
           
    </div>
 
    <script type="text/javascript" src="templates/javascript/jquery-3.2.0.min.js"></script>
    <script type="text/javascript" src="templates/javascript/jquery_ui/jquery-ui.js"></script>
    <script type="text/javascript" src="templates/libs/toastr/toastr.min.js"></script>
    <script type="text/javascript" src="templates/libs/tooltiper/jquery.powertip.js"></script>
    <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>
	<script type="text/javascript" src="templates/javascript/login.js"></script>

</body>
</html>
