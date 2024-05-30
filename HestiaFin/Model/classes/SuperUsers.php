<?php
require_once 'Model/classes/Users.php';

class SuperUsers extends Users
{
    public $character;

    public function __construct($name, $login, $password, $character = "admin")
    {
        parent::__construct($name, $login, $password);
        $this->character = $character;
    }

    public function getInfo()
    {
        return parent::getInfo() . ", Character: {$this->character}";
    }
}
