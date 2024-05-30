<?php
require_once '../Model/classes/Database.php';

function generateUsersXML($filePath)
{
    $dbFilePath = __DIR__ . '/../db/site.db';
    $db = new Database($dbFilePath);
    $pdo = $db->getPDO();

    $stmt = $pdo->query('SELECT * FROM User');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $root = $dom->createElement('users');
    $dom->appendChild($root);

    foreach ($users as $user) {
        $userElement = $dom->createElement('user');

        $idElement = $dom->createElement('id', $user['user_id']);
        $nameElement = $dom->createElement('name', $user['username']);
        $emailElement = $dom->createElement('email', $user['login']);

        $userElement->appendChild($idElement);
        $userElement->appendChild($nameElement);
        $userElement->appendChild($emailElement);

        $root->appendChild($userElement);
    }

    $dom->save($filePath);
}

// Виклик функції для генерації XML
generateUsersXML(__DIR__ . '/../users.xml');

header('Location: ../View/admin_users.php');
exit();
