<?php
session_start();

require_once '../Model/classes/Database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../View/login.php');
    exit;
}

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$user_id = $_SESSION['user_id'];
$recipe_id = $_GET['recipe_id'];

$stmt = $pdo->prepare('DELETE FROM Favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
$stmt->execute([
    ':user_id' => $user_id,
    ':recipe_id' => $recipe_id
]);

header('Location: ../View/profile.php');
exit;
