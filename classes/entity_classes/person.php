<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class Person {
		
	private $id;
	private $idNum;
	private $surname;
	private $name;
	private $gender;
	private $alias;
	private $address;
	private $fathername;
	private $mothername;
	private $ssn;
	private $birthdate = null;
	private $country;

	private $validity;
    public function getValidity() { return $this->validity; }
	
	//getters
	public function getId() { return $this->id; }
	public function getIdNum() { return $this->idNum; }
	public function getSurname() { return $this->surname; }
	public function getName() { return $this->name; }
	public function getGender() { return $this->gender==""?"Unknown":$this->gender; }
	public function getAlias() { return $this->alias==""?"NULL":$this->alias; }
	public function getAddress() { return $this->address==""?"NULL":$this->address; }
	public function getFathername() { return $this->fathername==""?"NULL":$this->fathername; }
	public function getMothername() { return $this->mothername==""?"NULL":$this->mothername; }
	public function getSSN() { return $this->ssn==""?"NULL":$this->ssn; }
	public function getBirthdate() { return $this->birthdate; }
	public function getCountry() { return $this->country==""?"NULL":$this->country; }
	
	//setters
	public function setId($val) { $this->id  = $val; }
	public function setIdNum($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->idNum  = $val; 
	}
	public function setSurname($val) { 
		if(strlen($val) > 100) {
			//length error
			return;
		}
		$this->surname  = $val; 
	}
	public function setName($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->name  = $val; 		
	}
	public function setGender($val) { 
		if($val != Gender::MALE && $val != Gender::FEMALE && $val != Gender::UNKNOWN) {
			//error here
			return;
		}
		$this->gender  = $val; 
	}
	public function setAlias($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->alias  = $val; 
	}
	public function setAddress($val) { 
		if(strlen($val) > 250) {
			//length error
			return;
		}
		$this->address  = $val; 
	}
	public function setFathername($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->fathername  = $val; 
	}
	public function setMothername($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->mothername  = $val; 
	}
	public function setSSN($val) { 
		if(strlen($val) > 100) {
			//length error
			return;
		}
		$this->ssn  = $val; 
	}
	public function setBirthdate($val) {
        if(!$val || $val == "" || $val == null) {
            return;
        }
		$stamp = strtotime($val);
		if(!$stamp) {
			//date error
			$this->birthdate = null;
		}
		$this->birthdate = $val;
	}
	public function setCountry($val) { 
		if(strlen($val) > 50) {
			//length error
			return;
		}
		$this->country  = $val; 
	}
	
	//constructor
	function __construct() {
		//initialization
		$this->setGender(Gender::UNKNOWN);
	}

	public function isValid() {

	    if(!$this->idNum) {
            $this->validity = "Null person id number";
	        return false;
        }

        if(!$this->surname) {
            $this->validity = "Null person surname";
            return false;
        }

        if(!$this->name) {
            $this->validity = "Null person name";
            return false;
        }

        if(strlen($this->idNum) > 50) {
            $this->validity = "Too long person id number - Length: " . strlen($this->idNum);
            return false;
        }

        if(strlen($this->name) > 50) {
            $this->validity = "Too long person name - Length: " . strlen($this->name);
            return false;
        }

        if(strlen($this->surname) > 100) {
            $this->validity = "Too long person surname - Length: " . strlen($this->surname);
            return false;
        }

        return true;

    }

}


?>