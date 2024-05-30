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
        fetch('send_email.php', {
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

    // Show/hide logout button
    const userButton = document.getElementById('userButton');
    if (userButton) {
        userButton.addEventListener('click', function() {
            const logoutButton = document.getElementById('logoutButton');
            if (logoutButton.style.display === 'block') {
                logoutButton.style.display = 'none';
            } else {
                logoutButton.style.display = 'block';
            }
        });
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
        console.log('Sorting by', criteria);
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