<?php
session_start();

require_once '../Model/classes/Database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../Control/login.php');
    exit;
}

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$recipe_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Видалення інгредієнтів
$stmt = $pdo->prepare('DELETE FROM Ingredient WHERE recipe_id = :recipe_id');
$stmt->execute([':recipe_id' => $recipe_id]);

// Видалення рецепту
$stmt = $pdo->prepare('DELETE FROM Recipe WHERE recipe_id = :recipe_id AND user_id = :user_id');
$stmt->execute([
    ':recipe_id' => $recipe_id,
    ':user_id' => $user_id
]);

header('Location: ../View/profile.php');
exit;
