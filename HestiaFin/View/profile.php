<?php
session_start();

require_once '../Model/classes/Database.php';
require_once '../Model/classes/Users.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../Control/login.php');
    exit;
}

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$user_id = $_SESSION['user_id'];
$user_query = $pdo->prepare('SELECT username, login, password FROM User WHERE user_id = :user_id');
$user_query->execute([':user_id' => $user_id]);
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);

// Отримання рецептів користувача
$recipes_query = $pdo->prepare('SELECT recipe_id, recipe_name, recipe_description, foto FROM Recipe WHERE user_id = :user_id');
$recipes_query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$recipes_query->execute();
$recipes = $recipes_query->fetchAll(PDO::FETCH_ASSOC);

// Отримання обраних рецептів користувача
$favorites_query = $pdo->prepare('SELECT Recipe.recipe_id, Recipe.recipe_name, Recipe.recipe_description, Recipe.foto 
                                 FROM Favorites 
                                 JOIN Recipe ON Favorites.recipe_id = Recipe.recipe_id 
                                 WHERE Favorites.user_id = :user_id');
$favorites_query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$favorites_query->execute();
$favorites = $favorites_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .recipe-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .recipe-item img {
            max-width: 200px;
            max-height: auto;
            margin-right: 10px;
        }

        .overlay {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2;
            cursor: pointer;
        }

        .update-form-container {
            position: fixed;
            display: none;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            z-index: 3;
            width: 300px;
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
    <div class="profile-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></h1>

        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
            <p><strong>Login:</strong> <?php echo htmlspecialchars($user_data['login']); ?></p>
            <p><strong>Password:</strong> <?php echo htmlspecialchars($user_data['password']); ?></p>
        </div>

        <div class="profile-actions">
            <a href="../View/add_recipe.php" class="btn btn-success"><i class="fas fa-plus"></i> Додати рецепт</a>
            <button class="btn btn-warning" id="editProfileButton"><i class="fas fa-edit"></i> Edit Profile</button>
            <form action="../Control/logout.php" method="post" style="display: inline;">
                <button type="submit" class="btn btn-danger btn-sm" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>

        <div class="user-recipes">
            <h2>Ваші рецепти</h2>
            <ul>
                <?php foreach ($recipes as $recipe) : ?>
                    <li class="recipe-item">
                        <div>
                            <strong><?php echo htmlspecialchars($recipe['recipe_name']); ?></strong>
                            <p><?php echo htmlspecialchars($recipe['recipe_description']); ?></p>
                            <a href="../View/edit_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="../Control/delete_recipe.php?id=<?php echo $recipe['recipe_id']; ?>" class="btn btn-danger">Delete</a>
                        </div>
                        <?php if (!empty($recipe['foto'])) : ?>
                            <img src="<?php echo htmlspecialchars($recipe['foto']); ?>" alt="Recipe Image">
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="favorite-recipes">
            <h2>Обрані рецепти</h2>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($favorites as $favorite) : ?>
                    <li class="recipe-item" style="display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #ddd; border-radius: 5px; padding: 10px; margin-bottom: 10px;">
                        <a href="../View/recipe.php?id=<?php echo $favorite['recipe_id']; ?>" style="text-decoration: none; color: inherit; flex-grow: 1;">
                            <div style="display: flex; align-items: center;">
                                <div style="flex-grow: 1;">
                                    <strong><?php echo htmlspecialchars($favorite['recipe_name']); ?></strong>
                                    <p><?php echo htmlspecialchars($favorite['recipe_description']); ?></p>
                                </div>
                                <?php if (!empty($favorite['foto'])) : ?>
                                    <div style="margin-left: 10px;">
                                        <img src="<?php echo htmlspecialchars($favorite['foto']); ?>" alt="Favorite Recipe Image" style="max-width: 100px; border-radius: 5px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <a href="../Control/remove_favorite.php?recipe_id=<?php echo $favorite['recipe_id']; ?>" class="btn btn-danger" style="margin-left: 10px;">Remove from favorites</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Edit Profile Form and Overlay -->
    <div class="overlay" id="overlay"></div>
    <div class="update-form-container" id="updateForm">
        <h2>Edit Profile</h2>
        <form action="../Control/update_profile.php" method="post">
            <div class="mb-3">
                <label for="editUsername" class="form-label">Username</label>
                <input type="text" class="form-control" id="editUsername" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="editLogin" class="form-label">Login</label>
                <input type="text" class="form-control" id="editLogin" name="login" value="<?php echo htmlspecialchars($user_data['login']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="editPassword" class="form-label">Password</label>
                <input type="password" class="form-control" id="editPassword" name="password" value="<?php echo htmlspecialchars($user_data['password']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" id="cancelUpdateButton">Cancel</button>
        </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editProfileButton = document.getElementById('editProfileButton');
            const overlay = document.getElementById('overlay');
            const updateForm = document.getElementById('updateForm');
            const cancelUpdateButton = document.getElementById('cancelUpdateButton');

            editProfileButton.addEventListener('click', function() {
                overlay.style.display = 'block';
                updateForm.style.display = 'block';
            });

            overlay.addEventListener('click', function() {
                overlay.style.display = 'none';
                updateForm.style.display = 'none';
            });

            cancelUpdateButton.addEventListener('click', function() {
                overlay.style.display = 'none';
                updateForm.style.display = 'none';
            });
        });
    </script>

    <script src="../scripts/logout.js"></script>
    <script src="../scripts/list.js"></script>
</body>

</html>
