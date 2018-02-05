<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class Telecommunication {
	
	private $caller;
	private $called;
	private $date;
	private $time;
	private $timestamp;
	private $duration;
	private $weight;
	private $type;

    private $validity;
    public function getValidity() { return $this->validity; }
	
	//getters
	public function getCaller() { return $this->caller; }
	public function getCalled() { return $this->called; }
	public function getDate() { return $this->date; }
	public function getTime() { return $this->time; }
	public function getTimestamp() { return $this->timestamp; }
    public function getTimestampInt() { return dateToInt($this->timestamp); }
	public function getSqlTimestamp() {
		$t = dateAndTimeToInt($this->timestamp);
		return date("Y-m-d H:i:s", $t);
	}
	public function getDuration() { return $this->duration; }
	public function getWeight() { return $this->weight; }
	public function getType() { return $this->type; }
	
	public function setCaller($caller) {
		$this->caller = $caller;
	}
	public function setCalled($called) {
		$this->called = $called;
	}
	public function setDate($date) {
		$this->date = $date;
	}
	public function setTime($time) {
		$this->time = $time;
	}
	public function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}
	public function setDuration($duration) {
		
		if(isTime($duration)) { 
			$this->duration = timeToInt($duration); 
			return; 
		}
		if(is_int($duration)) {
			$this->duration = $duration;
			return;
		}
		
		$this->duration = 0;
		
	}
	public function setWeight($val) {
		if($val > 0 && $val <= 1) {
            $this->weight = $val;
		}
	}
	public function setType($type) {
		$this->determineType($type);
	}
	
	
	//constructor
	function __construct(Telephone $caller, Telephone $called, $date, $time, $duration) {

		$this->setWeight(0.5);
		$this->caller = $caller;
		$this->called = $called;
		$this->date = $date;
		$this->time = $time;
		$this->setDuration($duration);
		$this->determineType("");
		
		$this->timestamp = $this->date . " " . $this->time;
		
	}
	
	private function determineType($type) {
		
		if($type == "SMS προς εθνικά δίκτυα") { $this->type = "SMS"; return; }
		if($type == "Κινητά Vodafone" || $type == "Άλλα Kινητά") { $this->type = "CALL"; return; }
		if(contains($type, "SMS")) { $this->type = "SMS"; return; }
        if(contains($type, "Κινητά")) { $this->type = "CALL"; return; }
        if(contains($type, "CALL")) { $this->type = "CALL"; return; }
		$this->type = "OTHER";
		
	}
	
	public function isValid() {
		
		if(!$this->caller->isValid()) {
		    $this->validity = "Invalid Caller! " . $this->caller->getValidity();
			return false;
		}
		
		if(!$this->called->isValid()) {
            $this->validity = "Invalid Called! " . $this->called->getValidity();
		    return false;
		}

        if($this->caller->getNumber() == $this->getCalled()->getNumber()) {
            $this->validity = "Caller number cannot be the same as called! ";
            return false;
        }
		
		if(!isDate($this->date)) {
            $this->validity = "Wrong date format! " . $this->date;
			return false;
		}
		
		if(!isTime($this->time)) {
            $this->validity = "Wrong time format! " . $this->time;
			return false;
		}

		if($this->weight > 1 || $this->weight < 0) {
            $this->validity = "Wrong weight! " . $this->weight;
            return false;
        }
				
		return true;
		
	}
	
}

?>