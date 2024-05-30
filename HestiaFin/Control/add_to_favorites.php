<?php
require_once '../Model/classes/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['recipe_id'])) {
    echo json_encode(['success' => false, 'message' => 'No recipe ID provided']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipeId = $input['recipe_id'];

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

// Check if the recipe is already in favorites
$stmt = $pdo->prepare('SELECT * FROM Favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
$stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['success' => false, 'message' => 'Recipe already in favorites']);
    exit;
}

// Add the recipe to favorites
$stmt = $pdo->prepare('INSERT INTO Favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)');
if ($stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add recipe to favorites']);
}
?>
