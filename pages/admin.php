<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class PAdmin {
	
	public function actionLogin() {
		//die(__S('user-username'));
		if(User::isLoggedIn() && User::isAdmin(__S('user-username'))) { Url::redirect('index.php?p=admin/videos'); }
		
		Template::load('admin.login');
		
	}
			
	public function actionLogout() {
		
		User::logout();
		Url::redirect("index.php?p=admin/login");
		
	}
			
}
?>