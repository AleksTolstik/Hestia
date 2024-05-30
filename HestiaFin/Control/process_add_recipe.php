<?php
session_start();
require_once '../Model/classes/Database.php';
require '../vendor/autoload.php'; // Завантаження Composer автозавантажувача

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = 'You must log in to add a recipe.';
    header('Location: ../View/login.php');
    exit;
}

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$recipeName = $_POST['recipeName'];
$recipeDescription = $_POST['recipeDescription'];
$recipeText = $_POST['recipeText'];
$ingredientsNames = $_POST['ingredientName'];
$ingredientsAmounts = $_POST['ingredientAmount'];

// Handle file upload
$targetDir = "../images/";
$targetFile = $targetDir . basename($_FILES["recipeImage"]["name"]);
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
if (move_uploaded_file($_FILES["recipeImage"]["tmp_name"], $targetFile)) {
    $recipeImage = $targetFile;
} else {
    $_SESSION['message'] = 'Sorry, there was an error uploading your file.';
    header('Location: ../View/add_recipe.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert recipe
    $stmt = $pdo->prepare("INSERT INTO Recipe (recipe_name, recipe_description, recipe_text, foto, user_id) VALUES (:name, :description, :text, :foto, :user_id)");
    $stmt->execute([
        ':name' => $recipeName,
        ':description' => $recipeDescription,
        ':text' => $recipeText,
        ':foto' => $recipeImage,
        ':user_id' => $_SESSION['user_id'] // Assuming user_id is stored in session
    ]);

    $recipeId = $pdo->lastInsertId();

    // Insert ingredients
    $stmt = $pdo->prepare("INSERT INTO Ingredient (ingrName, nummber, recipe_id) VALUES (:name, :amount, :recipe_id)");
    for ($i = 0; $i < count($ingredientsNames); $i++) {
        $stmt->execute([
            ':name' => $ingredientsNames[$i],
            ':amount' => $ingredientsAmounts[$i],
            ':recipe_id' => $recipeId
        ]);
    }

    $pdo->commit();

    // Send email to all users
    $users = $pdo->query("SELECT login FROM User")->fetchAll(PDO::FETCH_ASSOC);
    sendEmails($users, $recipeName);

    $_SESSION['message'] = 'Recipe added successfully!';
    header('Location: ../View/profile.php');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = 'Failed to add recipe: ' . $e->getMessage();
    header('Location: ../View/add_recipe.php');
    exit;
}

function sendEmails($users, $recipeName)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'oleksii.tolstik@nure.ua';
        $mail->Password = 'Oleksiy.2004';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('noreply@culinarysite.com', 'Culinary Site');

        foreach ($users as $user) {
            $mail->addAddress($user['login']);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Recipe Added';
        $mail->Body    = "A new recipe '$recipeName' has been added. Check it out on our site! We are sure you will like it!\n\nHave delicious meals and a good day!";

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}