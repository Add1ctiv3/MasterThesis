<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }
if (!defined('DONT_CHECK_CLOSED')) { define('DONT_CHECK_CLOSED', true); }

class PUser
{
	
	public static function actionHome() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }
		
		$user = User::getLoggedUser();
        
        Template::load('home', array("user" => $user));
    }

    public static function actionUploads() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }

        $user = User::getLoggedUser();

        Template::load('uploads', array("user" => $user));
    }

    public static function actionDatasets() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }

        $user = User::getLoggedUser();
        $datasets = Dataset::getAllDatasets();

        Template::load('datasets', array("user" => $user, "datasets" => $datasets));
    }

    public static function actionQueries() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }

        $user = User::getLoggedUser();

        Template::load('queries', array("user" => $user));
    }

    public static function actionImport() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }

        $user = User::getLoggedUser();

        Template::load('import', array("user" => $user));
    }

    public static function actionExport() {
        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }
        $user = User::getLoggedUser();
        Template::load('export', array("user" => $user));
    }

    public static function actionAnalyzer() {

        if (!User::isLoggedIn()) { Url::redirect('index.php?p=user/login'); }

        $user = User::getLoggedUser();

        if(isset($_GET['set']) && !empty($_GET['set'])) {

            $set_to_visualize = $_GET['set'];

            $q = new Visualizer($set_to_visualize);

            $data = $q->visualizeDataset("KEY_PLAYER");

        }

        Template::load('visual', array("user" => $user, "nodes" => $data['data']['nodes'], "edges" => $data['data']['edges']));

    }

    public static function actionLogin() {
        if (User::isLoggedIn()) { Url::redirect('index.php'); }
        
        Template::load('login');
    }
    
    public static function actionLogout() {
        if (!User::isLoggedIn()) { Url::redirect('index.php'); }
        
        User::logout();
        
        Url::redirect("index.php?p=admin/login");
    }

}
?>