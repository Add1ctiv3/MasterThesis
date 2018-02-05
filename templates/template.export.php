<?php
    if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
    $menu = require_once("templates/side_menu.php");
?>

<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>Export Data</title>

<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />

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

        <div id="export-data-panel" class="hidden">

            <label for="export-dataset-file-name-input">Name your file:</label>
            <input type="text" id="export-dataset-file-name-input" placeholder="export file name (no extension needed)..." />

            <span class="sets-label"><b>Select one of the existing datasets to export in a .csv file!</b></span>
            <div class="sets-block" id="sets-container">

        </div> <!-- End of export data panel -->

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

    <script type="text/javascript" src="templates/javascript/panel_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_manager.js"></script>

    <script type="text/javascript" src="templates/javascript/menu_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/users_functions.js"></script>

    <script type="text/javascript" src="templates/javascript/export.js"></script>


</body>

</html>

