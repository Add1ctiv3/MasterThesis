<?php
    if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
    $menu = require_once("templates/side_menu.php");
?>

<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>Import Data</title>

<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/timepicker/jquery-ui-timepicker-addon.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/durationpicker/duration-picker.min.css" />

<link rel="stylesheet" type="text/css" href="templates/css/main.css" />

</head>

<body>

    <header id="top-bar">

    	<div id="top-bar-logo-container">

        	<img id="menu-button" src="templates/images/menu-icon-dark.png" alt="menu button" />

            <img id="top-bar-logo" src="templates/images/intelx_bar_logo.png" />

        </div>

    </header>

    <!-- Left Side Bar -->
	<aside id="left-side-bar" class="visible">    	

        <div id="user-box" rel="<?php echo $user['username']; ?>" icon="<?php echo $user['icon']; ?>" >

        	<div id="user-icon-container">

            	<img id="logged-user-icon" width='50' height='50' src="templates/images/user_images/<?php echo $user['icon'] . "?" . Now(); ?>" />

            </div>

            <div id="user-info">

            	<ul>

                	<li><a href="#" id="logged-user-fullname"><?php echo ($user['lastname'] . " " . $user['firstname']); ?></a></li>

                    <li id="logged-user-email"><?php echo ucfirst($user['email']); ?></li>                    

                </ul>

            </div>

        </div>

        <!-- side menu -->
        <?php echo $menu; ?>


    </aside> <!-- end of left bar -->

    <!-- main container div -->
    <div id="main-container">

        <div id="import-data-panel" class="hidden">

            <div class="import-fields-container full">

                <div class="import-fields-container full panel-label no-select" style="margin:15px 0;">Import Telecommunications</div>

                <div class="import-form-field one-third">
                    <label for="import-form-caller-input">Caller</label>
                    <input id="import-form-caller-input" placeholder="Caller's Number..." />
                </div>

                <div class="import-form-field one-third" style=" padding:30px 0 0 0;">
                    <img src="templates/images/long-arrow-pointing-to-right.png" class="no-select" style=" position:relative; display:block; margin:0 auto;" />
                </div>


                <div class="import-form-field one-third">
                    <label for="import-form-called-input">Called</label>
                    <input id="import-form-called-input" placeholder="Called Number..." />
                </div>

            </div>

            <div class="import-fields-container full">

                <div class="import-form-field one-quarter">
                    <label for="import-form-timestamp-input">Date and Time</label>
                    <input id="import-form-timestamp-input" placeholder="Timestamp..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form-duration-input">Duration hh:mm:ss</label>
                    <input id="import-form-duration-input" placeholder="Duration..." />
                </div>


                <div class="import-form-field one-quarter">
                    <label for="import-form-type-input">Type</label>
                    <select id="import-form-type-select">
                        <option rel="OTHER">Other</option>
                        <option rel="CALL">Telephone Call</option>
                        <option rel="SMS">Sms</option>
                    </select>
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form-weight-input">Weight ( 0.1 - 1 )</label>
                    <input id="import-form-weight-input" type="number" step="0.1" min="0.1" max="1" placeholder="Weight..." />
                </div>

            </div>

            <div class="button" id="import-button">Import</div>


            <div class="import-fields-container full">

                <div class="import-fields-container full panel-label no-select" style="margin:15px 0;">Import Telephone/Person Details</div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-caller-input">Telephone Number</label>
                    <input id="import-form2-number-input" placeholder="Telephone..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-id-number-input">ID Number</label>
                    <input id="import-form2-id-number-input" placeholder="ID Number..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-surname-input">Surname</label>
                    <input id="import-form2-surname-input" placeholder="Surname..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-name-input">Name</label>
                    <input id="import-form2-name-input" placeholder="Name..." />
                </div>

            </div>

            <div class="import-fields-container full">

                <div class="import-form-field one-quarter">
                    <label for="import-form2-fathersname-input">Father's Name</label>
                    <input id="import-form2-fathersname-input" placeholder="Father's Name..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-mothersname-input">Mother's Name</label>
                    <input id="import-form2-mothersname-input" placeholder="Mother's Name..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-birthdate-input">Birthdate</label>
                    <input id="import-form2-birthdate-input" placeholder="Birthdate..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-country-input">Country of Origin</label>
                    <input id="import-form2-country-input" placeholder="Country..." />
                </div>

            </div>

            <div class="import-fields-container full">

                <div class="import-form-field one-quarter">
                    <label for="import-form2-address-input">Address</label>
                    <input id="import-form2-address-input" placeholder="Address..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-ssn-input">Social Security Number</label>
                    <input id="import-form2-ssn-input" placeholder="Ssn..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-alias-input">Alias</label>
                    <input id="import-form2-alias-input" placeholder="Alias..." />
                </div>

                <div class="import-form-field one-quarter">
                    <label for="import-form2-gender-select">Gender</label>
                    <select id="import-form2-gender-select">
                        <option rel="not_selected">Select...</option>
                        <option rel="M">Male</option>
                        <option rel="F">Female</option>
                        <option rel="Unknown">Unknown</option>
                    </select>
                </div>

            </div>

            <div class="button" id="import-button2">Import</div>

        </div><!-- End of import panel -->

    </div> <!-- End of Main Container -->

    <!-- logged user panel -->
    <div id="loggedUsersPanel" class="panel">

        <div class='panel-header'>

            <img src="templates/images/close-panel-icon.png" width='20' height='20' class='close-panel-icon' />

        </div>

        <div class='panel-body'>                            

            <div class='panel-input-container' id="username-input-container">

                <img id="logged-users-panel-icon" width='100' height='100' src="templates/images/user_images/<?php echo $user['icon']; ?>?1" />

                <div id="logged-user-username-container">

                    <span class="panel-input-label" for="username-proxy">Username: </span>

                    <span class="panel-input-proxy" id="username-proxy"><?php echo ucfirst($user['username']); ?></span>

                </div>                	

            </div>            

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="lastname-proxy">Last Name: </span>

                <span class="panel-input-proxy" id="lastname-proxy"><?php echo $user['lastname']; ?></span>

            </div>            

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="firstname-proxy">First Name: </span>

                <span class="panel-input-proxy" id="firstname-proxy"><?php echo $user['firstname']; ?></span>

            </div>           

            <div class='panel-input-container'>

                <span class="panel-input-label" for="email-proxy">E-mail: </span>

                <span class="panel-input-proxy" id="email-proxy"><?php echo $user['email']; ?></span>

            </div>            

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="type-proxy">User Type: </span>

                <select class="panel-select" id="type-proxy-input" disabled >

                    <option id="administrator-option" 		 <?php if($user['type'] == 'administrator') { echo "selected"; } ?> >Administrator</option>

                    <option id="super-user-option" 			 <?php if($user['type'] == 'super-user') { echo "selected"; } ?>    >Super User</option>

                    <option id="user-option" 				 <?php if($user['type'] == 'user') { echo "selected"; } ?>		   >User</option>

                    <option id="new-user-option" 			 <?php if($user['type'] == 'new-user') { echo "selected"; } ?>	   >New User</option>                        

                </select>

            </div>            

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="access-level-proxy">Access Level: </span>

                <select class="panel-select" id="access-level-proxy-input" disabled>

                    <option id="al-1-option" <?php if($user['access-level'] == '1') { echo "selected"; } ?> >1</option>

                    <option id="al-2-option" <?php if($user['access-level'] == '2') { echo "selected"; } ?> >2</option>

                    <option id="al-3-option" <?php if($user['access-level'] == '3') { echo "selected"; } ?> >3</option>

                    <option id="al-4-option" <?php if($user['access-level'] == '4') { echo "selected"; } ?> >4</option>

                    <option id="al-5-option" <?php if($user['access-level'] == '5') { echo "selected"; } ?> >5</option>

                </select>

            </div>            

            <div class='panel-container'>

                <span class="panel-input-label">Last Login: </span>

                <span id="last-login-container"><?php echo $user['last-login']; ?></span>

            </div>            

            <div class='panel-container'>

                <input type='button' class='button' id="edit-logged-user-button" value="Edit" />

                <img class="ajax_spinner" src="templates/images/ajax-loader.gif" id="edit-logged-users-info-spinner" />

            </div>            

            <!-- password reset -->

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="password-reset">Change Password: </span>

                <input type='password' id="password-reset-input" />

            </div>            

            <div class='panel-input-container half-container-width'>

                <span class="panel-input-label" for="password-repeat-reset">Repeat Password: </span>

                <input type='password' id="password-repeat-reset-input" />

            </div>            

            <input type='button' class='button' id="change-user-password-button" value="Change Password" />

        </div>        

    </div> <!--end of logged users panel-->

    <!-- Users control panel -->
    <div id="users-administration-panel" class="panel">
    	
        <div class='panel-header'>

        	<span class="panel-header-title">User Administration</span>

            <img src="templates/images/close-panel-icon.png" width='20' height='20' class='close-panel-icon' />

        </div>

        <!-- User administration panels body -->
        <div class='panel-body'>

            <div class='panel-input-container'>

            	<input id="users-administration-search-input" placeholder="Search username, last/first name, e-mail or type here..." type='text' />

            </div>

            <div id="user-administration-users-container" class='panel-input-container'>

            </div>            

            <div class="clear"></div>

        </div><!-- End of User administration panels body -->

    </div><!-- end of users control panel -->

    <!-- Ajax spinner dialog -->
    <div id="ajax-spinner-dialog">
        <table valign="middle" cellspacing="5">
            <tr>
                <td><img src="templates/images/ajax-spinner-large.gif" /></td>
                <td>Please wait...</td>
            </tr>
        </table>
    </div>

	<script type="text/javascript" src="templates/javascript/jquery-3.2.0.min.js"></script>

    <script src="templates/javascript/jquery_ui/jquery-ui.js"></script>

    <script src="templates/libs/toastr/toastr.min.js"></script>

    <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.ui.position.js"></script>

    <script type="text/javascript" src="templates/libs/timepicker/jquery-ui-timepicker-addon.js"></script>

    <script type="text/javascript" src="templates/libs/durationpicker/duration-picker.min.js"></script>

    <script type="text/javascript" src="templates/javascript/panel_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_manager.js"></script>

    <script type="text/javascript" src="templates/javascript/menu_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/users_functions.js"></script>

    <script type="text/javascript" src="templates/javascript/import.js"></script>


</body>

</html>

