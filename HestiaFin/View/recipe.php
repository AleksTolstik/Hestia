<?php
require_once '../Model/classes/Database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error
    echo "User not logged in.";
    exit;
}

if (!isset($_GET['id'])) {
    echo "No recipe ID provided.";
    exit;
}

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();
$recipeId = $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM Recipe WHERE recipe_id = :id');
$stmt->execute([':id' => $recipeId]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    echo "Recipe not found.";
    exit;
}

$ingredientsStmt = $pdo->prepare('SELECT * FROM Ingredient WHERE recipe_id = :recipe_id');
$ingredientsStmt->execute([':recipe_id' => $recipeId]);
$ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the recipe is already in favorites
$favStmt = $pdo->prepare('SELECT * FROM Favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
$favStmt->execute([':user_id' => $_SESSION['user_id'], ':recipe_id' => $recipeId]);
$isFavorite = $favStmt->fetch(PDO::FETCH_ASSOC) !== false;
?>

<!DOCTYPE html>
<html>

<head>
    <title><?php echo htmlspecialchars($recipe['recipe_name']); ?></title>
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

    <div class="shopping-list-panel" id="shoppingListPanel">
        <div class="shopping-list-header">
            <h3>Shopping List</h3>
            <button class="btn btn-close" id="closeShoppingList">&times;</button>
        </div>
        <div class="shopping-list-content" id="shoppingListContent">
            <!-- Shopping list items will be appended here by JavaScript -->
        </div>
        <div class="shopping-list-footer">
            <button class="btn btn-danger" id="clearShoppingListButton">Clear Shopping List</button>
            <button class="btn btn-primary" id="sendShoppingListButton">Send to Email</button>
        </div>
    </div>

    <div class="my_container">
        <div class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?php echo htmlspecialchars($recipe['recipe_name']); ?></h1>
                <i class="fas fa-heart favorite-button <?php echo $isFavorite ? 'favorite' : ''; ?>" data-recipe-id="<?php echo $recipe['recipe_id']; ?>"></i>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <img src="<?php echo htmlspecialchars($recipe['foto']); ?>" alt="Recipe Image" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h2>Ingredients</h2>
                    <form id="ingredientsForm">
                        <ul class="list-unstyled">
                            <?php foreach ($ingredients as $ingredient) : ?>
                                <li>
                                    <input type="checkbox" name="ingredient[]" value="<?php echo htmlspecialchars($ingredient['ingrName']); ?>">
                                    <?php echo htmlspecialchars($ingredient['ingrName']) . " - " . htmlspecialchars($ingredient['nummber']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn btn-secondary" onclick="addIngredientsToShoppingList()">Add to Shopping List</button>
                    </form>
                </div>
            </div>
            <div class="mt-5">
                <h2>Steps</h2>
                <p id="recipeText"><?php echo nl2br(htmlspecialchars($recipe['recipe_text'])); ?></p>
                <button type="button" class="btn btn-warning" onclick="replaceWord()">Замінити слово</button>
            </div>
        </div>
    </div>

    <!-- Login Form and Overlay -->
    <div class="overlay" id="overlay"></div>
    <div class="login-form-container" id="loginForm">
        <h2>Login</h2>
        <form action="../Control/login.php" method="post">
            <div class="mb-3">
                <label for="exampleInputLogin" class="form-label">Login</label>
                <input type="text" class="form-control" id="exampleInputLogin" name="login" required>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <button id="showRegisterForm" class="btn btn-link">Немає аккаунту? Зареєструватись</button>
    </div>

    <!-- Registration Form -->
    <div class="register-form-container" id="registerForm" style="display: none;">
        <h2>Register</h2>
        <form action="../Control/register.php" method="post">
            <div class="mb-3">
                <label for="registerName" class="form-label">Name</label>
                <input type="text" class="form-control" id="registerName" name="name" required>
            </div>
            <div class="mb-3">
                <label for="registerLogin" class="form-label">Login</label>
                <input type="text" class="form-control" id="registerLogin" name="login" required>
            </div>
            <div class="mb-3">
                <label for="registerPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="registerPassword" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <button id="showLoginForm" class="btn btn-link">Вже маєте аккаунт? Увійти</button>
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
                    <li><a href="admin_users.php">Home</a></li>
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
    <script>
        window.replaceWord = function() {
            const recipeTextElement = document.getElementById('recipeText');
            const text = recipeTextElement.innerHTML;
            const replacedText = text.replace(/слово/g, "друге слово");
            recipeTextElement.innerHTML = replacedText;
        };
    </script>
    <script src="../scripts/logout.js"></script>
    <script src="../scripts/list.js"></script>
    <script src="../scripts/favorites.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>