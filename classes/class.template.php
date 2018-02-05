<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class Template
{
    public static function load($TEMPLATE_NAME, $TEMPLATE_VARIARBLES = array()) {
        foreach ($TEMPLATE_VARIARBLES as $VARIABLE => $VALUE) {
            $$VARIABLE = $VALUE;
        }		
			
		if (!file_exists(TEMPLATES_PATH . "/template." . strtolower($TEMPLATE_NAME) . ".php")) { 
			die("Template <b>template." . strtolower($TEMPLATE_NAME) . "</b> was not found!"); 
		}	
	
		include(TEMPLATES_PATH . "/template." . strtolower($TEMPLATE_NAME) . ".php");        
        
    }
	
}
?>