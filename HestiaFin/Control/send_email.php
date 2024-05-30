<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "User not logged in";
    exit;
}

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../Model/classes/Database.php';

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$stmt = $pdo->prepare("SELECT login FROM User WHERE username = :username");
$stmt->execute(['username' => $_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found";
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo "No shopping list data";
    exit;
}

$shoppingListContent = "";
foreach ($data as $item) {
    $shoppingListContent .= $item['name'] . " - " . $item['quantity'] . "\n";
}

$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';  
    $mail->SMTPAuth = true;
    $mail->Username = 'oleksii.tolstik@nure.ua';  
    $mail->Password = 'Oleksiy.2004';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //Recipients
    $mail->setFrom('oleksii.tolstik@nure.ua', 'Culinary Site');
    $mail->addAddress($user['login']);

    //Content
    $mail->isHTML(false);
    $mail->Subject = 'Your Shopping List';
    $mail->Body  = "Hello from your best Culinary Site!\nHere is your shopping list:\n\n" . $shoppingListContent . "\nHave delicious meals and a good day!";

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
}
