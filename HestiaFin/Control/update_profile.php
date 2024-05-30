<?php
session_start();

require_once '../Model/classes/Database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbFilePath = __DIR__ . '/../db/site.db';
    $db = new Database($dbFilePath);
    $pdo = $db->getPDO();

    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare('UPDATE User SET username = :username, login = :login, password = :password WHERE user_id = :user_id');
    $stmt->execute([
        ':username' => $username,
        ':login' => $login,
        ':password' => $password,
        ':user_id' => $user_id
    ]);

    $_SESSION['user'] = $username; // Update session username

    header('Location: ../View/profile.php');
    exit;
} else {
    header('Location: ../View/profile.php');
    exit;
}
?>
