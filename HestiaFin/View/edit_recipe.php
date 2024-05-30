<?php
session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = 'You must log in to edit a recipe.';
    header('Location: ../Control/login.php');
    exit;
}

require_once '../Model/classes/Database.php';
require_once '../Model/classes/Users.php';

$dbFilePath = __DIR__ . '/../db/site.db';
$db = new Database($dbFilePath);
$pdo = $db->getPDO();

$recipe_id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_name = $_POST['recipe_name'];
    $recipe_description = $_POST['recipe_description'];
    $recipe_text = $_POST['recipe_text'];

    // Handle the file upload
    if (!empty($_FILES['recipe_image']['name'])) {
        $recipe_image = $_FILES['recipe_image'];
        $image_path = '../images/' . basename($recipe_image['name']);
        move_uploaded_file($recipe_image['tmp_name'], $image_path);

        // Update the recipe including the image path
        $stmt = $pdo->prepare('UPDATE Recipe SET recipe_name = :recipe_name, recipe_description = :recipe_description, recipe_text = :recipe_text, foto = :foto WHERE recipe_id = :recipe_id AND user_id = :user_id');
        $stmt->execute([
            ':recipe_name' => $recipe_name,
            ':recipe_description' => $recipe_description,
            ':recipe_text' => $recipe_text,
            ':foto' => $image_path,
            ':recipe_id' => $recipe_id,
            ':user_id' => $_SESSION['user_id']
        ]);
    } else {
        // Update the recipe without changing the image path
        $stmt = $pdo->prepare('UPDATE Recipe SET recipe_name = :recipe_name, recipe_description = :recipe_description, recipe_text = :recipe_text WHERE recipe_id = :recipe_id AND user_id = :user_id');
        $stmt->execute([
            ':recipe_name' => $recipe_name,
            ':recipe_description' => $recipe_description,
            ':recipe_text' => $recipe_text,
            ':recipe_id' => $recipe_id,
            ':user_id' => $_SESSION['user_id']
        ]);
    }

    // Оновлення інгредієнтів
    $submitted_ingredient_ids = [];
    foreach ($_POST['ingredientName'] as $index => $name) {
        $amount = $_POST['ingredientAmount'][$index];
        $ingredient_id = $_POST['ingredientId'][$index] ?? null;

        if ($ingredient_id) {
            // Оновлення існуючого інгредієнта
            $stmt = $pdo->prepare('UPDATE Ingredient SET ingrName = :ingredient_name, nummber = :ingredient_amount WHERE ingredient_id = :ingredient_id');
            $stmt->execute([
                ':ingredient_name' => $name,
                ':ingredient_amount' => $amount,
                ':ingredient_id' => $ingredient_id
            ]);
            $submitted_ingredient_ids[] = $ingredient_id;
        } else {
            // Додавання нового інгредієнта
            $stmt = $pdo->prepare('INSERT INTO Ingredient (recipe_id, ingrName, nummber) VALUES (:recipe_id, :ingredient_name, :ingredient_amount)');
            $stmt->execute([
                ':recipe_id' => $recipe_id,
                ':ingredient_name' => $name,
                ':ingredient_amount' => $amount
            ]);
            $submitted_ingredient_ids[] = $pdo->lastInsertId();
        }
    }

    // Видалення інгредієнтів, яких немає у формі
    if (!empty($submitted_ingredient_ids)) {
        $placeholders = implode(',', array_fill(0, count($submitted_ingredient_ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM Ingredient WHERE recipe_id = ? AND ingredient_id NOT IN ($placeholders)");
        $stmt->execute(array_merge([$recipe_id], $submitted_ingredient_ids));
    } else {
        // Якщо в формі немає жодного інгредієнта, видалити всі інгредієнти
        $stmt = $pdo->prepare("DELETE FROM Ingredient WHERE recipe_id = ?");
        $stmt->execute([$recipe_id]);
    }

    header('Location: ../View/profile.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM Recipe WHERE recipe_id = :recipe_id AND user_id = :user_id');
$stmt->execute([
    ':recipe_id' => $recipe_id,
    ':user_id' => $_SESSION['user_id']
]);
$recipe = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recipe) {
    header('Location: ../View/profile.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM Ingredient WHERE recipe_id = :recipe_id');
$stmt->execute([':recipe_id' => $recipe_id]);
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Recipe</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .content-block {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 100px;
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

    <div class="container mt-5">
        <div class="content-block">
            <h1>Редагувати рецепт</h1>
            <form id="recipeForm" action="edit_recipe.php?id=<?php echo $recipe_id; ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="recipe_name" class="form-label">Назва рецепту</label>
                    <input type="text" class="form-control" id="recipe_name" name="recipe_name" value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="recipe_description" class="form-label">Опис</label>
                    <textarea class="form-control" id="recipe_description" name="recipe_description" rows="3" required><?php echo htmlspecialchars($recipe['recipe_description']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="recipe_text" class="form-label">Текст рецепту</label>
                    <textarea class="form-control" id="recipe_text" name="recipe_text" rows="5" required><?php echo htmlspecialchars($recipe['recipe_text']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="recipe_image" class="form-label">Картинка</label>
                    <input type="file" class="form-control" id="recipe_image" name="recipe_image">
                    <?php if (!empty($recipe['foto'])) : ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($recipe['foto']); ?>" alt="Recipe Image" style="max-width: 200px; max-height: 200px;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="ingredients" class="form-label">Інгредієнти</label>
                    <div id="ingredients">
                        <?php foreach ($ingredients as $ingredient) : ?>
                            <div class="input-group mb-2 ingredient-group">
                                <input type="hidden" name="ingredientId[]" value="<?php echo htmlspecialchars($ingredient['ingredient_id']); ?>">
                                <input type="text" class="form-control" name="ingredientName[]" value="<?php echo htmlspecialchars($ingredient['ingrName']); ?>" placeholder="Назва інгредієнта" required>
                                <input type="text" class="form-control" name="ingredientAmount[]" value="<?php echo htmlspecialchars($ingredient['nummber']); ?>" placeholder="Кількість (наприклад: 200 гр, 2 ст.л.)" required>
                                <button type="button" class="btn btn-danger btn-sm remove-ingredient">Видалити</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addIngredientButton">Додати інгредієнт</button>
                </div>
                <button type="submit" class="btn btn-primary">Зберегти</button>
            </form>
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
            const ingredientsContainer = document.getElementById('ingredients');
            const addIngredientButton = document.getElementById('addIngredientButton');

            addIngredientButton.addEventListener('click', function() {
                const ingredientDiv = document.createElement('div');
                ingredientDiv.className = 'input-group mb-2 ingredient-group';
                ingredientDiv.innerHTML = `
                    <input type="hidden" name="ingredientId[]" value="">
                    <input type="text" class="form-control" name="ingredientName[]" placeholder="Назва інгредієнта" required>
                    <input type="text" class="form-control" name="ingredientAmount[]" placeholder="Кількість (наприклад: 200 гр, 2 ст.л.)" required>
                    <button type="button" class="btn btn-danger btn-sm remove-ingredient">Видалити</button>
                `;
                ingredientsContainer.appendChild(ingredientDiv);
            });

            ingredientsContainer.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-ingredient')) {
                    const ingredientGroup = event.target.closest('.ingredient-group');
                    ingredientsContainer.removeChild(ingredientGroup);
                }
            });
        });
    </script>
    <script src="../scripts/list.js"></script>
    <script src="../scripts/logout.js"></script>
</body>

</html>