<?php
$xmlFilePath = 'users.xml';

// Перевірити, чи існує XML-документ з даними
if (file_exists($xmlFilePath)) {
    $dom = new DOMDocument();
    $dom->load($xmlFilePath);
    $root = $dom->documentElement;
} else {
    // Якщо документ не існує, створити кореневий елемент 'users' та прив'язати його до об'єкта
    $dom = new DOMDocument('1.0', 'UTF-8');
    $root = $dom->createElement('users');
    $dom->appendChild($root);
}

// Додати нового користувача
$newUser = $dom->createElement('user');
$name = $dom->createElement('name', 'new_user');
$email = $dom->createElement('email', 'new_user@example.com');
$newUser->appendChild($name);
$newUser->appendChild($email);
$root->appendChild($newUser);

// Зберегти зміни в XML-файл
$dom->save($xmlFilePath);
?>
