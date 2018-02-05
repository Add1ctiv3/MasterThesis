<?php 

class Scanner {

	private $mFile;
	
	function __construct($file) {
		$this->mFile = $file;
	}	
	
	function scan() {		
		
		if(is_file($mFile)) {
			$result = $this->check(file_get_contents($mFile));
			if($result) {
				return array("result" => "infected");
			}
			return array("result" => "clean");
		}
		else {
			return array("result" => "error", "message" => "This is not a file!");
		}
		
				
	}
	
	private function check($contents) {		
		if(preg_match('/(?<![a-z0-9_])eval\((base64|eval|\$_|\$\$|\$[A-Za-z_0-9\{]*(\(|\{|\[))/i',$contents)) {
			return true;
		}
		return false;
	}
		
}

?>