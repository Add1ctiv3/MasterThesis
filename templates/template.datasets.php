<?php
    if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
    $menu = require_once("templates/side_menu.php");
    $record = require_once("templates/view_record_dialog.php");
?>

<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>Datasets</title>

<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/slim/slim.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/timepicker/jquery-ui-timepicker-addon.css" />

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

        <!-- Available-datasets-panel -->
        <div id="available-datasets-panel">

            <div class="panel-title-bar">Available Datasets</div>

            <!-- Inner div -->
            <div id="datasets-inner-div">
                <div id="available-datasets">

                    <?php

                    foreach($datasets as $set) {
                        echo "<div class='available-dataset' rel='".$set->getName()."'>".$set->getName()."  <span class='available-dataset-info'>".$set->getCreatedOn()." created by ".$set->getCreator()."</span></div>";
                    }

                    ?>

                </div> <!-- End of available datasets div -->
            </div> <!-- End of inner div container -->

            <div id="dataset-controls">
                <div class="button datasets-control-button inactive-button" id="delete-dataset-button">Delete</div>
                <div class="button datasets-control-button inactive-button" id="visualize-dataset-button">Visualize</div>
                <div class="button datasets-control-button" id="combine-datasets-button">Combine Sets</div>
                <img src="templates/images/ajax-loader.gif" class="ajax_spinner" id="datasets-ajax-spinner" />
                <img id="create-dataset-button" src="templates/images/add-button.png" width="40" height="40" />
            </div><!-- End of dataset controls -->

        </div> <!-- End of available-datasets-panel -->

        <!-- View dataset content panel -->
        <div id="dataset-view-container">

            <div class="panel-title-bar"></div>

            <img id="close-dataset-button" src="templates/images/close-panel-icon-white.png" />

            <div id="datasets-view-search-container">
                <input type="text" id="dataset-contents-search" placeholder="Search for telephone numbers..." />
                <img src="templates/images/search-white.png" title="Search" id="search-dataset-content-button" width="18" height="18" />
            </div>

            <table id="dataset-statistics">
                <tr>
                    <td><b>Telephone Numbers: </b></td>
                    <td id="dataset-telephones-number"></td>
                    <td><b>Telecommunications: </b></td>
                    <td id="dataset-telecommunications-number"></td>
                </tr>
            </table>

            <!-- Inner div -->
            <div id="dataset-view-inner-container">

                <!-- Dataset content container -->
                <div offset="0" id="dataset-content">



                </div> <!-- End of dataset content container -->

            </div> <!-- End of inner div -->

        </div> <!-- End of view dataset content panel -->

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

    <!-- Confirmation Delete Dialog -->
    <div id="dialog-delete-dataset-confirm" title="Delete selected datasets?">
        <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>This/These dataset/s will be permanently deleted and cannot be recovered. Are you sure?</p>
    </div>

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

    <!-- Record viewing and editing panel -->
    <?php echo $record; ?>

    <!-- Create dataset dialog -->
    <div id="create-dataset-dialog">

        <input type="text" placeholder="Type your desired dataset name..." id="new-dataset-name-input" />
        <select id="new-dataset-type-select">
            <option rel="public">Public</option>
            <option rel="private">Private</option>
        </select>

    </div>

    <!-- Dataset combining panel -->
    <div id="combine-datasets-panel">

        <select id="combine-datasets-mode-select">
            <option rel="union">Union</option>
            <option rel="intersection">Intersection</option>
            <option rel="asymmetric_difference">Asymmetric Difference</option>
            <option rel="subtracting">Subtracting</option>
        </select>

        <div id="datasets-combining-container">

            <div class="half" style="padding:10px;">
                <div id="datasets-combining-image">
                    <img src="templates/images/union.jpg" class="full"/>
                </div>

                <div id="datasets-combining-new-dataset-name-container">
                    <span class="combining-sets-label">Derivative Dataset Name</span>
                    <input type="text" id="datasets-combining-derivative-set-name-input" placeholder="Type your desired dataset name here..." />
                </div>

                <div id="combine-datasets-start-button" class="button">Combine</div>
            </div>

            <div class="half" style="padding:10px;">
                <div id="datasets-combining-info">
                    With union you can create a dataset from the combined contents of any number of sets. If there are common records between multiple datasets, then the duplicates are omitted. You add datasets that you wish to combine with the add button below and then provide a dataset name for the dataset that will contain all the combined data.
                </div>

                <div id="datasets-combining-set-container">
                    <img id="add-dataset-for-combining-button" title="Add Dataset" src="templates/images/add-button.png" width="30" height="30" />
                </div>
            </div>

        </div>

    </div>


	<script type="text/javascript" src="templates/javascript/jquery-3.2.0.min.js"></script>

    <script src="templates/javascript/jquery_ui/jquery-ui.js"></script>

    <script type="text/javascript" src="templates/libs/slim/slim.jquery.min.js"></script>

    <script type="text/javascript" src="templates/libs/slimScroll/jquery.slimscroll.min.js"></script>

    <script src="templates/libs/toastr/toastr.min.js"></script>

    <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.contextMenu.js"></script>

    <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.ui.position.js"></script>

    <script type="text/javascript" src="templates/libs/timepicker/jquery-ui-timepicker-addon.js"></script>

    <script type="text/javascript" src="templates/javascript/record_viewing.js"></script>

    <script type="text/javascript" src="templates/javascript/panel_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/user_manager.js"></script>

    <script type="text/javascript" src="templates/javascript/menu_mechanism.js"></script>

    <script type="text/javascript" src="templates/javascript/users_functions.js"></script>

    <script type="text/javascript" src="templates/javascript/datasets.js"></script>


</body>

</html>

