<?php

class Users
{
    public $userId;
    public $username;
    public $login;
    public $password;

    public function __construct($username, $login, $password, $userId = null)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->login = $login;
        $this->password = $password;
    }

    public function getInfo()
    {
        return "User ID: {$this->userId}, Username: {$this->username}, Login: {$this->login}";
    }

    public static function getUserByLogin($db, $login, $password)
    {
        error_log("Trying to get user with login: $login and password: $password");
        $stmt = $db->getPDO()->prepare('SELECT * FROM User WHERE login = :login AND password = :password');
        $stmt->execute([':login' => $login, ':password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            error_log("User found: " . print_r($user, true));
            return new self($user['username'], $user['login'], $user['password'], $user['user_id']);
        }
        error_log("No user found");
        return null;
    }

    public static function userExists($db, $login)
    {
        $stmt = $db->getPDO()->prepare('SELECT * FROM User WHERE login = :login');
        $stmt->execute([':login' => $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function save($db)
    {
        $stmt = $db->getPDO()->prepare('INSERT INTO User (username, login, password) VALUES (:username, :login, :password)');
        $stmt->execute([':username' => $this->username, ':login' => $this->login, ':password' => $this->password]);
    }
}
