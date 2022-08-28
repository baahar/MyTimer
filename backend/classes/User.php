<?php

class User {
  public $id = -1;
  public $username;
  public $password;
  public $eggtimer_start;

  function __construct($id, $username, $password, $eggtimer_start) {
    $this->id = $id;
    $this->username = $username;
    $this->password = $password;
    $this->eggtimer_start = $eggtimer_start;
  }

}

?>