<?php
    if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
    $menu = require_once("templates/side_menu.php");
    $set_creation_dialog = require_once("templates/sets_creation_dialog.php");
    $record_details_dialog = require_once("templates/view_record_dialog.php");
?>

<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>Queries</title>

<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/slim/slim.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/timepicker/jquery-ui-timepicker-addon.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/durationpicker/duration-picker.min.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/contextMenu/dist/jquery.contextMenu.css" />

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

        <!-- Queries-panel -->
        <div id="queries-panel" class="hidden">

            <div id="queries-panel-body">

                <div class="query-fields-container full panel-label">Query for Telecommunications</div>

                <div class="query-fields-container full" style="margin-top:10px;">

                    <div class="query-form-field two-fifths">
                        <label for="query-form-caller-input">Caller Number</label>
                        <input id="query-form-caller-input" type="text" placeholder="Caller number..." />
                    </div>

                    <div class="query-form-field one-fifth center">
                        <label for="query-form-and-or-select">And - OR</label>
                        <select id="query-form-and-or-select">
                            <option rel="OR">OR</option>
                            <option rel="AND">AND</option>
                        </select>
                    </div>

                    <div class="query-form-field two-fifths">
                        <label for="query-form-called-input">Called Number</label>
                        <input id="query-form-called-input" type="text" placeholder="Called number..." />
                    </div>

                </div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form-from-date-input">Date From</label>
                        <input id="query-form-from-date-input" placeholder="Date From..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-to-date-input">Date To</label>
                        <input id="query-form-to-date-input" placeholder="Date To..." />
                    </div>


                    <div class="query-form-field one-quarter">
                        <label for="query-form-from-time-input">Time From</label>
                        <input id="query-form-from-time-input" placeholder="Time From..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-to-time-input">Time To</label>
                        <input id="query-form-to-time-input" placeholder="Time To..." />
                    </div>

                </div>

                <div class="query-fields-container full">
                    <div class="query-form-field one-quarter">
                        <label for="query-form-comm-type-select">Telecommunication Type</label>
                        <select id="query-form-comm-type-select">
                            <option rel="any">Any Type</option>
                            <option rel="CALL">Telephone Call</option>
                            <option rel="SMS">Sms</option>
                        </select>
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-duration-input">Duration (hh:mm:ss)</label>
                        <input id="query-form-duration-input" placeholder="Duration..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-weight-from-input">Weight From</label>
                        <input id="query-form-weight-from-input" placeholder="Weight From..." type="number" min="0.1" max="1" step="0.1" />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-weight-to-input">Weight To</label>
                        <input id="query-form-weight-to-input" placeholder="Weight To..." type="number" min="0.1" max="1" step="0.1" />
                    </div>
                </div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form-from-creation-date-input">Insert Date From</label>
                        <input id="query-form-from-creation-date-input" placeholder="Insert Date From..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-to-creation-date-input">Insert Date To</label>
                        <input id="query-form-to-creation-date-input" placeholder="Insert Date To..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form-in-set-input">In Set</label>
                        <input id="query-form-in-set-input" placeholder="Set Name..." />
                    </div>

                </div>

                <div class="button" id="query-button">Search</div>

                <div class="query-fields-container full panel-label" style="margin:25px 0 15px 0;">Query for Telephones/People</div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-telephone-input">Telephone Number</label>
                        <input id="query-form2-telephone-input" placeholder="Insert Telephone Number..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-id-number-input">ID Number</label>
                        <input id="query-form2-id-number-input" placeholder="Insert ID Number..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-surname-input">Surname</label>
                        <input id="query-form2-surname-input" placeholder="Set Surname..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-name-input">Name</label>
                        <input id="query-form2-name-input" placeholder="Set Name..." />
                    </div>

                </div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-fathersname-input">Father's Name</label>
                        <input id="query-form2-fathersname-input" placeholder="Insert Father's Name..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-mothersname-input">Mother's Name</label>
                        <input id="query-form2-mothersname-input" placeholder="Insert Mother's Name..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-birthdate-input">Birth Date</label>
                        <input id="query-form2-birthdate-input" placeholder="Insert Birth Date..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-country-input">Country Of Origin</label>
                        <input id="query-form2-country-input" placeholder="Insert Country..." />
                    </div>

                </div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-ssn-input">Social Security Number</label>
                        <input id="query-form2-ssn-input" placeholder="Insert Social Security Number..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-alias-input">Alias</label>
                        <input id="query-form2-alias-input" placeholder="Insert Alias..." />
                    </div>

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-address-input">Address</label>
                        <input id="query-form2-address-input" placeholder="Insert Address..." />
                    </div>

                    <div class="query-form-field one-quarter center">
                        <label for="query-form2-gender-select">Gender</label>
                        <select id="query-form2-gender-select">
                            <option rel="not_selected">Select...</option>
                            <option rel="M">Male</option>
                            <option rel="F">Female</option>
                            <option rel="Unknown">Unknown</option>
                        </select>
                    </div>

                </div>

                <div class="query-fields-container full">

                    <div class="query-form-field one-quarter">
                        <label for="query-form2-in-set-input">In Set</label>
                        <input id="query-form2-in-set-input" placeholder="Set Name..." />
                    </div>

                </div>

                <div class="button" id="query-button2">Search</div>

            </div> <!-- end of queries panel body -->

        </div> <!-- End of queries-panel -->

        <!-- View query results panel -->
        <div id="query-results-container" class="hidden">

            <b><div id="query-results-records-number"></div></b>

            <div id="query-results-inner-container">

            </div>

            <div id="query-results-pagination-container">
                <a href="#" id="query-results-pagination-previous-button">Previous</a>
                <div id="query-results-pagination-current-page" rel="1">Page 1  ( 1 - 600 )</div>
                <a href="#" id="query-results-pagination-next-button">Next</a>
            </div>

            <div id="query-results-selected-records">
                Selected Records: <b><span id="selected-records">0</span></b> <a id="select-all-records" href="#">Select All</a> <a id="deselect-all-records" href="#">Deselect All</a>
                <div id="add-query-results-to-set-button" class="button">Add All To Set</div>
            </div>

        </div> <!-- End of query results panel -->

        <!-- Merging panel -->
        <div id="merging-panel" class="hidden">

        </div>

    </div><!-- End of main container div -->

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

    <!-- Confirmation Delete ASSOCIATION Dialog -->
    <div id="dialog-remove-association-confirm" title="Remove/Delete person - telephone association?" class="hidden">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Are you sure you want to remove this person-telephone association?</p>
    </div>

    <!-- Ajax spinner dialog -->
    <div id="ajax-spinner-dialog">
        <table valign="middle" cellspacing="5">
            <tr>
                <td><img src="templates/images/ajax-spinner-large.gif" /></td>
                <td>Please wait...</td>
            </tr>
        </table>
    </div>

    <!-- View Record Details Panel -->
    <?php echo $record_details_dialog; ?>

    <!-- Sets  creation dialog box -->
    <?php echo $set_creation_dialog; ?>


	<script type="text/javascript" src="templates/javascript/jquery-3.2.0.min.js"></script>

    <script src="templates/javascript/jquery_ui/jquery-ui.js"></script>

    <script type="text/javascript" src="templates/libs/slim/slim.jquery.min.js"></script>

    <script type="text/javascript" src="templates/libs/slimScroll/jquery.slimscroll.min.js"></script>

    <script src="templates/libs/toastr/toastr.min.js"></script>

    <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.contextMenu.js"></script>

    <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.ui.position.js"></script>

    <script type="text/javascript" src="templates/libs/timepicker/jquery-ui-timepicker-addon.js"></script>

    <script type="text/javascript" src="templates/libs/durationpicker/duration-picker.min.js"></script>

    <script type="text/javascript" src="templates/javascript/record_viewing.js"></script>

    <script type="text/javascript" src="templates/javascript/panel_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_manager.js"></script>

    <script type="text/javascript" src="templates/javascript/menu_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/users_functions.js"></script>

    <script type="text/javascript" src="templates/javascript/queries.js"></script>


</body>

</html>

