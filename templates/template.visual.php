<?php
    if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
    $menu = require_once("templates/side_menu.php");
    $connectionLevelDialog = require_once("templates/ask_for_level_of_connection_dialog.php");
    $searchForDialog = require_once("templates/search_for_dialog.php");
    $record_details_dialog = require_once("templates/view_record_dialog.php");
    $nc_analysis_results_dialog = require_once("templates/nc_analysis_results_dialog.php");
    $nf_analysis_results_dialog = require_once("templates/nf_analysis_results_dialog.php");
    $reach_analysis_results_dialog = require_once("templates/reach_analysis_results_dialog.php");
    $require_rsl_dialog = require_once("templates/require_RSL_dialog.php");
    $nodes_reach_analysis_results_dialog = require_once("templates/nodes_reach_analysis_results_dialog.php");
?>

<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>Analyze Data</title>

<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

<link rel="stylesheet" type="text/css" href="templates/javascript/jquery_ui/jquery-ui.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/toastr/toastr.css" />

<link rel="stylesheet" type="text/css" href="templates/libs/contextMenu/dist/jquery.contextMenu.css" />

<link href="templates/libs/vizjs/vis.css" rel="stylesheet" type="text/css" />

<link rel="stylesheet" type="text/css" href="templates/css/main.css" />

<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

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
        <div id="main-container" viewmode="free">



        </div> <!-- End of Main Container -->

        <!-- Right sidebar -->
        <div id="right-sidebar">
            <div class="right-sidebar-button" id="visualize-dataset-button" title="Import From Dataset"><img src="templates/images/dataset.png" width="30" height="30" /></div>
            <div class="horizontal-separator-invisible">&nbsp;</div>
            <div class="right-sidebar-button" id="search-node-button" title="Search for a telephone number."><img src="templates/images/search_icon.png" width="30" height="30" /></div>
            <div class="horizontal-separator-invisible">&nbsp;</div>
            <div class="right-sidebar-button" id="multiselect-button" active="false" title="Multiselect by holding and dragging your right mouse button."><img src="templates/images/select.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="select-next-level-button" title="Select Connected Nodes and Edges"><img src="templates/images/select_next_level.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="trace-path-button" title="Trace connection path"><img src="templates/images/path.png" width="30" height="30" /></div>
            <div class="horizontal-separator-invisible">&nbsp;</div>
            <div class="right-sidebar-button" id="network-capital-analysis-button" title="Enforcement analysis based on network capital criteria."><img src="templates/images/tactical_icon.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="fragmentation-analysis-button" title="Enforcement analysis based on network fragmentation."><img src="templates/images/fragmentation_icon.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="reach-analysis-button" title="Intelligence analysis based on network reach."><img src="templates/images/reach.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="degree-button" title="Find each nodes Degree Centrality."><img src="templates/images/reach2.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="closeness-button" title="Find each nodes closeness centrality."><img src="templates/images/closeness_centrality_icon.png" width="30" height="30" /></div>
            <div class="right-sidebar-button" id="betweenness-button" title="Find each nodes betweenness centrality."><img src="templates/images/betweenness_centrality_icon.png" width="30" height="30" /></div>
        </div>

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

        <!-- sets dialog -->
        <div id="sets-panel" class="hidden">

            <span class="sets-label"><b>Select one of the existing datasets to import!</b></span>
            <div class="sets-block" id="sets-container"></div>

        </div> <!-- End of sets dialog -->

        <div id="too-many-records-dialog" class="hidden" title="Too large dataset!">
            <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
            Your dataset has more than 3.000 telecommunications (<span id="exact-nodes-number"></span> records). To avoid losing responsiveness a limit of 3000 telecommunications has been issued!
        </div>

        <!--the connection level dialog-->
        <?php echo $connectionLevelDialog; ?>
        <?php echo $searchForDialog; ?>
        <?php echo $record_details_dialog; ?>
        <?php echo $nc_analysis_results_dialog; ?>
        <?php echo $nf_analysis_results_dialog; ?>
        <?php echo $reach_analysis_results_dialog; ?>
        <?php echo $require_rsl_dialog; ?>
        <?php echo $nodes_reach_analysis_results_dialog; ?>

        <!-- View query results panel -->
        <div id="query-results-container" class="hidden">

            <b><div id="query-results-records-number"></div></b>

            <div id="query-results-inner-container">

            </div>

        </div> <!-- End of query results panel -->

        <script type="text/javascript" src="templates/javascript/jquery-3.2.0.min.js"></script>

        <script src="templates/javascript/jquery_ui/jquery-ui.js"></script>

        <script src="templates/libs/toastr/toastr.min.js"></script>

        <script type="text/javascript" src="templates/libs/slim/slim.jquery.min.js"></script>

        <script type="text/javascript" src="templates/libs/slimScroll/jquery.slimscroll.min.js"></script>

        <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.contextMenu.js"></script>

        <script type="text/javascript" src="templates/libs/contextMenu/dist/jquery.ui.position.js"></script>

        <script type="text/javascript" src="templates/libs/vizjs/vis.js"></script>

        <script type="text/javascript" src="templates/javascript/panel_mechanism.js"></script>

        <script type="text/javascript" src="templates/javascript/user_input_mechanism.js"></script>

        <script type="text/javascript" src="templates/javascript/user_manager.js"></script>

        <script type="text/javascript" src="templates/javascript/menu_mechanism.js"></script>

        <script type="text/javascript" src="templates/javascript/users_functions.js"></script>

        <script type="text/javascript" src="templates/libs/timepicker/jquery-ui-timepicker-addon.js"></script>

        <script type="text/javascript" src="templates/libs/durationpicker/duration-picker.min.js"></script>

        <script type="text/javascript" src="templates/javascript/record_viewing.js"></script>

        <script type="text/javascript" src="templates/javascript/visual.js"></script>

        <script type="text/javascript">

            <?php if($nodes && $edges) { ?>
                importNewNetwork(<?php echo $nodes; ?>, <?php echo $edges; ?>)
            <?php } ?>


        </script>

    </body>

</html>

