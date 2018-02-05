<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class Telephone {
	
	const LANDLINE = "landline";
	const MOBILE = "mobile";
	const UNKNOWN = "unknown";
		
	private $number;
	private $type;
	private $countryCode;

	private $weight;

	private $validity;
	public function getValidity() { return $this->validity; }
	
	//getters
	public function getNumber() { return $this->number; }
	public function getType() { return $this->type; }
	public function getCountryCode() { return $this->countryCode; }
    public function getWeight() { return $this->weight; }
	
	//setters
	public function setNumber($val) {
		$this->number = $val; 
	}
	public function setType($val) { 
		if($val != self::LANDLINE && $val != self::MOBILE) {
			$this->type  = self::UNKNOWN;
		}		
	}
	public function setCountryCode($val) {
		$this->countryCode  = $val;
	}

    public function setWeight($val) {
        if($val > 0 && $val <= 1) {
            $this->weight = $val;
        }
    }
	
	//constructor
	function __construct($number) {
		
		//initialization
		$this->setNumber($number);
		$this->setType(self::UNKNOWN);

		$this->setWeight(0.5);

		$this->countryCode = "";
		
		$this->clearNumber();
		
		$this->determineType();
		
	}
	
	private function determineType() {
		if(startsWith($this->number, "69") && strlen($this->number) == 10) {
			$this->type = self::MOBILE;
			return;
		}
		if(startsWith($this->number, "2") && strlen($this->number) == 10) {
			$this->type = self::LANDLINE;
			return;
		}
		$this->type = self::UNKNOWN;
	}
	
	public function clearNumber() {
		$this->number = preg_replace('/[^0-9]/', "", $this->number);
	}
	
	public function isValid() {
		
		if(strlen($this->number) > 50 || strlen($this->number) < 3) {
		    $this->validity = "strlen number problem - " . strlen($this->number) . " - " . $this->number;
			return false;
		}

        if($this->countryCode != "" && (strlen($this->countryCode) > 10 || strlen($this->countryCode) < 0 || !is_int($this->countryCode))) {
            $this->validity = "countrycode problem";
            return false;
        }
		
		return true;
		
	}
	
}

?>