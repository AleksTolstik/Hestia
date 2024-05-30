<?php
session_start();

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$sortCriteria = isset($_GET['sort']) ? $_GET['sort'] : 'name';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Culinary Site</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="header">
        <img src="img/logo.png" alt="Hestia Logo" class="logo">
        <ul class="navigation">
            <li><a href="index.php"><i class="fas fa-home"></i> Головна</a></li>
            <li><a href="View/chat.php"><i class="fas fa-comments"></i> Форум</a></li>
            <li><button id="shoppingListButton" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Shopping List</button></li>
            <?php if (isset($_SESSION['user'])) : ?>
                <li id="userButton" class="profile"><a href="View/profile.php"><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></a></li>
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

    <div class="main-content" id="mainContent">
        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>" . $_SESSION['message'] . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
            unset($_SESSION['message']);
        }
        ?>
        <div class="main_foto-container">
            <img class="main_foto" src="img/main_foto.jpg" alt="Descriptive Image">
            <div class="main_foto-text">Вітаємо у світі кулінарії</div>
            <div class="search-container">
                <form method="get" action="index.php">
                    <input type="text" id="searchInput" name="search" placeholder="Search recipes" value="<?php echo htmlspecialchars($searchQuery); ?>">
                    <button type="submit">
                        <img src="img/search_icon.png" alt="Search Icon">
                    </button>
                </form>
            </div>
        </div>
        <?php
        require_once 'Model/classes/RecipePage.php';
        try {
            $dbFilePath = realpath(__DIR__ . '/db/site.db');
            if ($dbFilePath === false) {
                throw new Exception("Unable to resolve the path to the database file.");
            }
            $recipePage = new RecipePage($dbFilePath);
            $recipePage->displayBody($sortCriteria, $searchQuery);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        ?>
    </div>

    <!-- Login Form and Overlay -->
    <div class="overlay" id="overlay"></div>
    <div class="login-form-container" id="loginForm">
        <h2>Login</h2>
        <form action="Control/login.php" method="post">
            <div class="mb-3">
                <label for="exampleInputLogin" class="form-label">Login</label>
                <input type="text" class="form-control" id="exampleInputLogin" name="login" required>
            </div>
            <div class="mb-3">
                <label for="exampleInputPassword1" class="form-label">Password</label>
                <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" onclick="logout()">Submit</button>
        </form>
        <button id="showRegisterForm" class="btn btn-link">Немає аккаунту? Зареєструватись</button>
    </div>

    <!-- Registration Form -->
    <div class="register-form-container" id="registerForm" style="display: none;">
        <h2>Register</h2>
        <form action="Control/register.php" method="post">
            <div class="mb-3">
                <label for="registerName" class="form-label">Name</label>
                <input type="text" class="form-control" id="registerName" name="name" required>
            </div>
            <div class="mb-3">
                <label for="registerLogin" class="form-label">Login</label>
                <input type="text" class="form-control" id="registerLogin" name="login" required>
                <div class="error-message" id="emailError"></div>
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
                    <li><a href="View/admin_users.php">Home</a></li>
                    <li><button id="toggleTouchpadButton" class="btn btn-secondary"><i class="fas fa-mouse-pointer"></i> Toggle Touchpad</button></li>
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

    <script src="scripts/client_chat.js"></script>
    <script src="scripts/logout.js"></script>
    <script>
        function sortRecipes(criteria) {
            const searchTerm = document.getElementById('searchInput').value;
            const url = `index.php?sort=${criteria}&search=${searchTerm}`;
            window.location.href = url;
        }
        
    </script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shoppingListButton = document.getElementById('shoppingListButton');
            const shoppingListPanel = document.getElementById('shoppingListPanel');
            const closeShoppingList = document.getElementById('closeShoppingList');
            const shoppingListContent = document.getElementById('shoppingListContent');
            const clearShoppingListButton = document.getElementById('clearShoppingListButton');
            const sendShoppingListButton = document.getElementById('sendShoppingListButton');

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

            // Load initial shopping list
            renderShoppingList();

            // Event listeners
            shoppingListButton.addEventListener('click', toggleShoppingListPanel);
            closeShoppingList.addEventListener('click', closeShoppingListPanel);
            clearShoppingListButton.addEventListener('click', clearShoppingList);
            sendShoppingListButton.addEventListener('click', function() {
                fetch('Control/send_email.php', {
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

            // JavaScript for showing/hiding login and register forms
            document.getElementById('loginButton').addEventListener('click', function() {
                document.getElementById('loginForm').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
            });

            document.getElementById('overlay').addEventListener('click', function() {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('registerForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
            });

            document.getElementById('showRegisterForm').addEventListener('click', function() {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('registerForm').style.display = 'block';
            });

            document.getElementById('showLoginForm').addEventListener('click', function() {
                document.getElementById('registerForm').style.display = 'none';
                document.getElementById('loginForm').style.display = 'block';
            });

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

            function changePage(page) {
                const buttons = document.querySelectorAll('.pagination button');
                buttons.forEach(button => button.classList.remove('active'));

                if (page === 'next') {
                    const currentPageButton = document.querySelector('.pagination button.active');
                    const currentPage = parseInt(currentPageButton.textContent, 10);
                    if (currentPage < buttons.length - 2) {
                        buttons[currentPage].classList.add('active');
                    }
                } else {
                    buttons[page - 1].classList.add('active');
                }

                console.log('Changing to page', page);
            }

            function sortRecipes(criteria) {
                const searchTerm = document.getElementById('searchInput').value;
                const url = `index.php?sort=${criteria}&search=${searchTerm}`;
                window.location.href = url;
            }

            // Show shopping list
            document.getElementById('shoppingListButton').addEventListener('click', function() {
                const shoppingList = JSON.parse(localStorage.getItem('shoppingList')) || [];
                const shoppingListContainer = document.getElementById('shoppingList');
                shoppingListContainer.innerHTML = '';

                shoppingList.forEach(function(item) {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                ${item} 
                <button onclick="changeQuantity('${item}', 1)">+</button>
                <button onclick="changeQuantity('${item}', -1)">-</button>
            `;
                    shoppingListContainer.appendChild(listItem);
                });

                new bootstrap.Modal(document.getElementById('shoppingListModal')).show();
            });

            // Change quantity of items in shopping list
            function changeQuantity(item, change) {
                let shoppingList = JSON.parse(localStorage.getItem('shoppingList')) || [];
                const index = shoppingList.indexOf(item);

                if (index !== -1) {
                    if (change === 1) {
                        shoppingList.push(item);
                    } else if (change === -1) {
                        shoppingList.splice(index, 1);
                    }

                    localStorage.setItem('shoppingList', JSON.stringify(shoppingList));
                    document.getElementById('shoppingListButton').click();
                }
            }
        });
        // Email validation
        const registrationForm = document.getElementById('registrationForm');
        const emailInput = document.getElementById('registerLogin');
        const emailError = document.getElementById('emailError');

        emailInput.addEventListener('blur', function() {
            const email = emailInput.value;
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailPattern.test(email)) {
                emailError.innerText = 'Введіть коректну електронну пошту.';
            } else {
                emailError.innerText = '';
            }
        });

        registrationForm.addEventListener('submit', function(event) {
            const email = emailInput.value;
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (!emailPattern.test(email)) {
                emailError.innerText = 'Введіть коректну електронну пошту.';
                event.preventDefault();
            }
        });
    </script>
    <!-- Додано скрипт для обробки натискання кнопки -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggleTouchpadButton').click(function() {
                $.ajax({
                    url: 'toggle_touchpad.php',
                    type: 'GET',
                    success: function(response) {
                        let result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert('Помилка під час виконання запиту.');
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
