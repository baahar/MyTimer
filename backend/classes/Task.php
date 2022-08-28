<?php

class Task {
  public $id;
  public $userid;
  public $name;
  public $status;
  public $tasktime; // TaskTime object

  function __construct($id, $userid, $name, $status) {
    $this->id = $id;
    $this->userid = $userid;
    $this->name = $name;
    $this->status = $status;
    $this->tasktime = new TaskTime(0);
  }   

  public static function withTotalTasktime($id, $userid, $name, $status, $tasktime) {
    $instance = new self($id, $userid, $name, $status);
    $instance->tasktime = $tasktime;
    return $instance;
  } 

}

?>