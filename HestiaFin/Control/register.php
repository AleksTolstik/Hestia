<?php
session_start();

require_once '../Model/classes/Database.php';
require_once '../Model/classes/Users.php';

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);

$username = $_POST['name'];
$login = $_POST['login'];
$password = $_POST['password'];

if (Users::userExists($db, $login)) {
    $_SESSION['message'] = 'Login already exists';
    header('Location: ../index.php');
    exit();
}

$newUser = new Users($username, $login, $password);
$newUser->save($db);

$_SESSION['message'] = 'Registration successful, please login';
header('Location: ../index.php');
exit();
