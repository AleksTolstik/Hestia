<?php
session_start();

require_once '../Model/classes/Database.php';
require_once '../Model/classes/Users.php';

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);

$login = $_POST['login'];
$password = $_POST['password'];

error_log("Login attempt with login: $login and password: $password");

$user = Users::getUserByLogin($db, $login, $password);

if ($user) {
    $_SESSION['user'] = $user->username;
    $_SESSION['user_id'] = $user->userId; // Додаємо userId до сесії
    $_SESSION['message'] = 'Login successful';
    error_log('Login successful: user_id = ' . $user->userId);
} else {
    $_SESSION['message'] = 'Спочатку ввійдіть в аккаунт!';
    error_log('Invalid login or password');
}
header('Location: ../index.php');
exit();
