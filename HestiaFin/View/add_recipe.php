<?php
session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = 'You must log in to add a recipe.';
    header('Location: ../Control/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Add Recipe</title>
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
            <h1>Додати рецепт</h1>
            <form id="recipeForm" action="../Control/process_add_recipe.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="recipeName" class="form-label">Назва рецепту</label>
                    <input type="text" class="form-control" id="recipeName" name="recipeName" required>
                    <div class="error-message" id="recipeNameError"></div>
                </div>
                <div class="mb-3">
                    <label for="recipeDescription" class="form-label">Опис</label>
                    <textarea class="form-control" id="recipeDescription" name="recipeDescription" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="recipeText" class="form-label">Текст рецепту</label>
                    <textarea class="form-control" id="recipeText" name="recipeText" rows="5" required></textarea>
                    <div class="error-message" id="recipeTextError"></div>
                </div>
                <div class="mb-3">
                    <label for="recipeImage" class="form-label">Фото</label>
                    <input type="file" class="form-control" id="recipeImage" name="recipeImage" required>
                </div>
                <div class="mb-3">
                    <label for="ingredients" class="form-label">Інгредієнти</label>
                    <div id="ingredients">
                        <div class="input-group mb-2">
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addIngredientButton">Додати інгредієнт</button>
                </div>
                <button type="submit" class="btn btn-primary" style="text-align: center;">Підтвердити</button>
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
            const shoppingListButton = document.getElementById('shoppingListButton');
            const shoppingListPanel = document.getElementById('shoppingListPanel');
            const closeShoppingList = document.getElementById('closeShoppingList');
            const shoppingListContent = document.getElementById('shoppingListContent');
            const clearShoppingListButton = document.getElementById('clearShoppingListButton');
            const sendShoppingListButton = document.getElementById('sendShoppingListButton');
            const profileButton = document.getElementById('profileButton');

            let shoppingList = JSON.parse(localStorage.getItem('shoppingList')) || [];

            function toggleShoppingListPanel() {
                shoppingListPanel.classList.toggle('open');
            }

            function closeShoppingListPanel() {
                shoppingListPanel.classList.remove('open');
            }

            function addToShoppingList(ingredient) {
                const existingItem = shoppingList.find(item => item.name === ingredient.name);
                if (existingItem) {
                    existingItem.quantity += ingredient.quantity;
                } else {
                    shoppingList.push({
                        name: ingredient.name,
                        quantity: ingredient.quantity
                    });
                }
                saveShoppingList();
                renderShoppingList();
            }

            function updateQuantity(name, delta) {
                const item = shoppingList.find(item => item.name === name);
                if (item) {
                    item.quantity += delta;
                    if (item.quantity <= 0) {
                        shoppingList = shoppingList.filter(item => item.name !== name);
                    }
                    saveShoppingList();
                    renderShoppingList();
                }
            }

            function clearShoppingList() {
                shoppingList = [];
                saveShoppingList();
                renderShoppingList();
            }

            function saveShoppingList() {
                localStorage.setItem('shoppingList', JSON.stringify(shoppingList));
            }

            function renderShoppingList() {
                shoppingListContent.innerHTML = '';
                shoppingList.forEach(item => {
                    const itemElement = document.createElement('div');
                    itemElement.classList.add('shopping-list-item');
                    itemElement.innerHTML = `
                        <span>${item.name}</span>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="updateQuantity('${item.name}', -1)">-</button>
                            <span>${item.quantity}</span>
                            <button class="btn btn-sm btn-secondary" onclick="updateQuantity('${item.name}', 1)">+</button>
                        </div>
                    `;
                    shoppingListContent.appendChild(itemElement);
                });
            }

            // Open sidebar on profile button click
            profileButton.addEventListener('click', function() {
                openSidebar();
            });

            function openSidebar() {
                document.getElementById('mySidebar').style.width = '250px';
            }

            function closeSidebar() {
                document.getElementById('mySidebar').style.width = '0';
            }

            window.addEventListener('scroll', function() {
                var mainContent = document.getElementById('mainContent');
                var contactInfo = document.getElementById('contactInfo');
                var mainContentRect = mainContent.getBoundingClientRect();
                var contactInfoRect = contactInfo.getBoundingClientRect();
                var distance = contactInfoRect.top - mainContentRect.bottom;
                if (distance <= 0) {
                    mainContent.style.marginBottom = -distance + 'px';
                } else {
                    mainContent.style.marginBottom = '0';
                }
            });

            // Load initial shopping list
            renderShoppingList();

            // Event listeners
            shoppingListButton.addEventListener('click', toggleShoppingListPanel);
            closeShoppingList.addEventListener('click', closeShoppingListPanel);
            clearShoppingListButton.addEventListener('click', clearShoppingList);
            sendShoppingListButton.addEventListener('click', function() {
                fetch('../Control/send_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(shoppingList)
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Expose functions to global scope for inline event handlers
            window.addToShoppingList = addToShoppingList;
            window.updateQuantity = updateQuantity;

            // Add selected ingredients to shopping list
            window.addIngredientsToShoppingList = function() {
                const form = document.getElementById('ingredientsForm');
                const formData = new FormData(form);
                const selectedIngredients = formData.getAll('ingredient[]');

                selectedIngredients.forEach(ingredientName => {
                    addToShoppingList({
                        name: ingredientName,
                        quantity: 1
                    });
                });

                alert('Ingredients added to your shopping list!');
            }

            // Validate recipe name for too many uppercase letters
            const recipeName = document.getElementById('recipeName');
            const recipeNameError = document.getElementById('recipeNameError');


        });

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

            document.getElementById('recipeForm').addEventListener('submit', function(event) {
                const form = document.getElementById('recipeForm');
                const formData = new FormData(form);
                const ingredientNames = formData.getAll('ingredientName[]');
                const ingredientAmounts = formData.getAll('ingredientAmount[]');

                let valid = true;
                ingredientNames.forEach((name, index) => {
                    if (!name || !ingredientAmounts[index]) {
                        valid = false;
                    }
                });

                if (!valid) {
                    event.preventDefault();
                    alert('Please fill out all ingredient fields.');
                }
            });

            // Validate recipe name for too many uppercase letters
            const recipeName = document.getElementById('recipeName');
            const recipeNameError = document.getElementById('recipeNameError');

            function checkRecipeName() {
                const recipeName = document.getElementById('recipeName');
                const recipeNameError = document.getElementById('recipeNameError');

                function checkRecipeName() {
                    const name = recipeName.value;
                    const uppercaseLetters = name.match(/[A-ZА-ЯЁЇЄІҐ]/g) || [];
                    const totalLetters = name.match(/[A-Za-zА-Яа-яЁёЇїЄєІіҐґ]/g) || [];

                    if (uppercaseLetters.length > 5 || (uppercaseLetters.length / totalLetters.length) > 0.8) {
                        recipeNameError.innerText = 'Назва рецепту містить занадто багато великих літер.';
                        return false;
                    } else {
                        recipeNameError.innerText = '';
                        return true;
                    }
                }

                recipeName.addEventListener('input', checkRecipeName);

                document.getElementById('recipeForm').addEventListener('submit', function(event) {
                    const errors = checkSpelling();
                    const isRecipeNameValid = checkRecipeName();
                    if (errors.length > 0 || !isRecipeNameValid) {
                        event.preventDefault();
                        recipeTextError.innerHTML = errors.join('<br>');
                    }
                });
            }

            recipeName.addEventListener('input', checkRecipeName);
            

            // Spell-checking logic
            const recipeText = document.getElementById('recipeText');
            const recipeTextError = document.getElementById('recipeTextError');
            const fixErrorsButton = document.getElementById('fixErrorsButton');

            function checkSpelling() {
                const text = recipeText.value;
                let errors = [];

                // Check for no space after punctuation
                const noSpaceAfterPunctuation = /[.,;!?…](?!\s)/g;
                if (noSpaceAfterPunctuation.test(text)) {
                    errors.push('Немає пробілу після коми, крапки з комою, знака оклику, знака питання, двокрапки.');
                }

                // Check for "жи" or "ши" with "и"
                const incorrectZhSh = /[жш]и/g;
                if (incorrectZhSh.test(text)) {
                    errors.push('«жи» та «ши» пишемо з літерою і.');
                }

                // Check for forbidden words
                const forbiddenWords = /(координально|тут|корочє)/gi;
                if (forbiddenWords.test(text)) {
                    errors.push('В тексті є слово «координально», «тут», «корочє» тощо.');
                }

                // Check for "а" without a comma before
                const missingCommaA = /(?:^|\s)(а)(?=\s)/gi;
                if (missingCommaA.test(text)) {
                    errors.push('Слова «а» без коми перед ним.');
                }

                // Check for "що" without a comma before
                const missingCommaScho = /(?:^|\s)(що)(?=\s)/gi;
                if (missingCommaScho.test(text)) {
                    errors.push('Слова «що» без коми перед ним.');
                }

                return errors;
            }

        recipeText.addEventListener('input', function() {
            const errors = checkSpelling();
            if (errors.length > 0) {
                recipeTextError.innerHTML = errors.join('<br>');
            } else {
                recipeTextError.innerHTML = '';
            }
        });
    });
    </script>
    <script src="../scripts/list.js"></script>
    <script src="../scripts/logout.js"></script>
</body>

</html>