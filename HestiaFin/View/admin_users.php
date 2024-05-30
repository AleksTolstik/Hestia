<?php
session_start();
require_once '../Model/classes/Database.php';

// Завантаження або створення XML-документа
function loadOrCreateXML($filePath)
{
    if (file_exists($filePath)) {
        $dom = new DOMDocument();
        $dom->load($filePath);
    } else {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElement('users');
        $dom->appendChild($root);
        $dom->save($filePath);
    }
    return [$dom, $dom->documentElement];
}

// Отримання даних користувачів з бази даних
function getUsersFromDatabase($pdo)
{
    $stmt = $pdo->query("SELECT user_id, username, login FROM User");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Збереження даних користувачів у XML
function saveUsersToXML($users, $dom, $root)
{
    foreach ($users as $user) {
        $userElement = $dom->createElement('user');

        $idElement = $dom->createElement('id', htmlspecialchars($user['user_id']));
        $nameElement = $dom->createElement('name', htmlspecialchars($user['username']));
        $emailElement = $dom->createElement('email', htmlspecialchars($user['login']));

        $userElement->appendChild($idElement);
        $userElement->appendChild($nameElement);
        $userElement->appendChild($emailElement);

        $root->appendChild($userElement);
    }
    $dom->save('../users.xml');
}

list($dom, $root) = loadOrCreateXML('../users.xml');

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$users = getUsersFromDatabase($pdo);

// Очищення старих даних
while ($root->firstChild) {
    $root->removeChild($root->firstChild);
}

// Збереження нових даних у XML
saveUsersToXML($users, $dom, $root);

$userData = [];
foreach ($users as $user) {
    $userData[] = ['ID' => $user['user_id'], 'NAME' => $user['username'], 'EMAIL' => $user['login']];
}

function displayUsers($users)
{
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>ID</th><th>Name</th><th>Email</th></tr></thead>";
    echo "<tbody>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['ID']}</td><td>{$user['NAME']}</td><td>{$user['EMAIL']}</td></tr>";
    }
    echo "</tbody>";
    echo "</table>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin - Users</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .my_container {
            margin-top: 100px;
        }

        .container {
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
        }

        img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .favorite-button {
            cursor: pointer;
            color: <?php echo $isFavorite ? 'red' : 'lightgray'; ?>;
            font-size: 40px;
            margin-left: 10px;
        }

        .favorite-button:hover {
            color: red;
        }
    </style>

</head>

<body>
    <div class="header">
        <img src="../img/logo.png" alt="Hestia Logo" class="logo">
        <ul class="navigation">
            <li><a href="../index.php"><i class="fas fa-home"></i> Головна</a></li>
            <li><a href="../View/chat.php"><i class="fas fa-comments"></i> Форум</a></li>
            <li><button id="shoppingListButton" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Shopping List</button></li>
            <?php if (isset($_SESSION['user'])) : ?>
                <li id="userButton" class="profile"><a href="../View/profile.php"><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></a></li>
            <?php else : ?>
                <li id="loginButton"><i class="fas fa-sign-in-alt"></i> Login</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="my_container">
        <div class="container mt-5">
            <h1>Список користувачів</h1>
            <?php displayUsers($userData); ?>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div>
                <h4>Про нас</h4>
                <p>Ласкаво просимо до Hestia, вашого ідеального місця для кулінарного натхнення та смачних рецептів. У Hestia ми віримо, що їжа – це більше ніж просто їжа, це досвід, спосіб з'єднатися з близькими та святкувати найкращі моменти життя.</p>
            </div>
            <div>
                <h4>Основні розділи</h4>
                <ul>
                    <li><a href="View/admin_users.php">Home</a></li>
                </ul>
            </div>
            <div>
                <h4>Зворотній зв'язок</h4>
                <ul>
                    <li>Email: info@culinarysite.com</li>
                    <li>Телефон: +123 456 7890</li>
                    <li>Адреса: 123 Culinary St, Food City</li>
                </ul>
            </div>
        </div>
        <div class="socials">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <p>&copy; 2024 Hestia. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../scripts/logout.js"></script>
</body>

</html>