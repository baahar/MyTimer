<?php

class TaskTime {
	public $hours;
	public $minutes;
	public $seconds;

	function __construct($minutes) {
		$this->hours = intval($minutes/60);
		$this->minutes = $minutes - $this->hours*60;
		$this->seconds = 0;
	}

	public function __toString() {
		return $this->hours . ':' . $this->minutes . ':' . $this->seconds;
		//return "test";
	}

}

?>