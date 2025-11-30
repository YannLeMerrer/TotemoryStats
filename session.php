<?php

class Session{
		public $id;
		public $duration;
		public $highest_level;

		public function __construct($id){
			$this->id = $id;
			$this->duration = 0;
			$this->highest_level = 0;
		}
		
		public function set_duration($duration){
			if ($duration > $this->duration){
				$this->duration = $duration;
				}
		}

		public function set_highest_level($level){
			if ($level > $this->highest_level){
				$this->highest_level = $level;
				}
		}

		public function get_average_level_duration(){
			return floor($this->duration / $this->highest_level);
		}
	}

?>