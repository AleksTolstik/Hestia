<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../Control/login.php');
    exit;
}

function getUserColor($username)
{
    // Створимо масив кольорів
    $colors = [
        'alex' => '#d1ecf1',
        'sofa' => '#f8d7da',
        'vitaliy' => '#d4edda',
        'Guest' => '#f8f9fa'
    ];

    // Повертаємо колір для користувача або значення за замовчуванням
    return isset($colors[$username]) ? $colors[$username] : '#e2e3e5';
}

// Завантаження повідомлень
$messages = file_exists('../messages.json') ? json_decode(file_get_contents('../messages.json'), true) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Culinary Chat Forum</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-image: url('../img/forum_back.jpg');
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            margin-top: 100px;
            flex: 1;
        }

        #chatbox,
        .private-chat {
            border: 1px solid #ccc;
            height: 400px;
            overflow-y: scroll;
            padding: 10px;
            background-color: white;
            border-radius: 10px;
        }

        #userlist {
            border: 1px solid #ccc;
            height: 400px;
            overflow-y: scroll;
            padding: 10px;
            background-color: white;
            border-radius: 10px;
        }

        .chat-message {
            margin-bottom: 10px;
            padding: 5px;
            border-radius: 5px;
        }

        .user-item {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid #ccc;
        }

        .user-item:hover {
            background-color: #e9ecef;
        }

        .selected-user {
            background-color: #007bff;
            color: white;
        }

        .my-message {
            background-color: #d1ecf1;
        }

        .other-message {
            background-color: #f8d7da;
        }

        .private-chat {
            display: none;
        }

        .new-message {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
            margin-left: 5px;
        }

        footer {
            background-image: url('../img/back_ground.jpg');
            color: white;
            padding: 20px 0;
            margin-top: 100px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-content div {
            flex: 1;
            margin-right: 20px;
        }

        .footer-content div:last-child {
            margin-right: 0;
        }

        .footer-content h4 {
            margin-bottom: 10px;
        }

        .footer-content p,
        .footer-content ul {
            margin: 0;
            padding: 0;
        }

        .footer-content ul {
            list-style: none;
        }

        .footer-content ul li {
            margin-bottom: 5px;
        }

        .footer-content ul li a {
            color: white;
            text-decoration: none;
        }

        .footer-content ul li a:hover {
            text-decoration: underline;
        }

        .socials {
            text-align: center;
            margin-top: 20px;
        }

        .socials a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 24px;
        }

        .socials a:hover {
            color: #007bff;
        }

        footer p {
            text-align: center;
            margin: 10px 0 0;
        }

        .text-center {
            color: white;
        }

        p {
            font-size: 22px;
            text-align: center;
            color: white;
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

    <div class="container chat_container">
        <h1 class="text-center my-4">Culinary Chat Forum</h1>
        <p>Вітаємо на кулінарному форумі для швидкої допомоги та обміну досвідом у приготуванні будь-яких кулінарних шедеврів!</p>
        <div class="row">
            <div class="col-md-3">
                <div id="userlist">
                    <h4>Users Online</h4>
                    <div class="user-item" id="generalChatButton">Загальний чат</div>
                </div>
            </div>
            <div class="col-md-9">
                <div id="chatbox"></div>
                <div id="privateChats"></div>
                <form id="chatForm" class="mt-3">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="message" placeholder="Enter your message" required>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            </div>
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
        var conn = new WebSocket('ws://localhost:8080');
        var chatbox = document.getElementById('chatbox');
        var userlist = document.getElementById('userlist');
        var privateChats = {};
        var selectedUser = null;
        var currentUser = "<?php echo isset($_SESSION['user']) ? $_SESSION['user'] : 'Guest'; ?>";
        var messages = <?php echo json_encode($messages); ?>;

        function loadMessages() {
            messages.forEach(function(message) {
                displayMessage(message);
            });
        }

        function displayMessage(data) {
            var messageElement = document.createElement('div');
            messageElement.classList.add('chat-message');
            messageElement.style.backgroundColor = getUserColor(data.user);
            messageElement.innerHTML = '<strong>' + data.user + '</strong>: ' + data.message + ' <em>(' + data.timestamp + ')</em>';
            if (data.recipient) {
                messageElement.innerHTML += ' <span class="text-muted">[Private to ' + data.recipient + ']</span>';
                if (data.recipient === currentUser || data.user === currentUser) {
                    if (!privateChats[data.recipient] && data.recipient !== currentUser) {
                        createPrivateChat(data.recipient);
                    }
                    if (!privateChats[data.user] && data.user !== currentUser) {
                        createPrivateChat(data.user);
                    }
                    if (data.recipient === currentUser) {
                        privateChats[data.user].appendChild(messageElement);
                        showNewMessageIndicator(data.user);
                    } else {
                        privateChats[data.recipient].appendChild(messageElement);
                    }
                }
            } else {
                chatbox.appendChild(messageElement);
            }
            chatbox.scrollTop = chatbox.scrollHeight;
        }

        conn.onopen = function(e) {
            console.log("Connection established!");
            conn.send(JSON.stringify({
                type: 'join',
                user: currentUser
            }));
            loadMessages();
        };

        conn.onmessage = function(e) {
            var data = JSON.parse(e.data);
            console.log(data); // Debugging
            if (data.type === 'userlist') {
                updateUserList(data.users);
            } else {
                messages.push(data);
                displayMessage(data);
                saveMessages();
            }
        };

        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var messageInput = document.getElementById('message');
            var message = messageInput.value;
            var recipient = selectedUser ? selectedUser.textContent : null;

            var data = {
                user: currentUser,
                message: message,
                recipient: recipient,
                timestamp: new Date().toLocaleTimeString()
            };

            conn.send(JSON.stringify(data));

            // Відображення повідомлення для відправника
            messages.push(data);
            displayMessage(data);
            saveMessages();

            messageInput.value = '';
        });

        function saveMessages() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../Control/save_messages.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(messages));
        }

        function updateUserList(users) {
            userlist.innerHTML = '<h4>Users Online</h4>';
            var generalChatButton = document.createElement('div');
            generalChatButton.textContent = 'Загальний чат';
            generalChatButton.classList.add('user-item');
            generalChatButton.addEventListener('click', function() {
                if (selectedUser) {
                    selectedUser.classList.remove('selected-user');
                }
                selectedUser = null;
                showGeneralChat();
            });
            userlist.appendChild(generalChatButton);

            users.forEach(function(user) {
                var userElement = document.createElement('div');
                userElement.textContent = user;
                userElement.classList.add('user-item');
                userElement.addEventListener('click', function() {
                    if (selectedUser) {
                        selectedUser.classList.remove('selected-user');
                        hideNewMessageIndicator(selectedUser.textContent);
                    }
                    if (selectedUser === userElement) {
                        selectedUser = null; // Deselect the user if the same user is clicked again
                        showGeneralChat();
                    } else {
                        userElement.classList.add('selected-user');
                        selectedUser = userElement;
                        if (!privateChats[user]) {
                            createPrivateChat(user);
                        }
                        showPrivateChat(user);
                        hideNewMessageIndicator(user);
                    }
                });
                userlist.appendChild(userElement);
            });
        }

        function createPrivateChat(username) {
            var privateChat = document.createElement('div');
            privateChat.classList.add('private-chat');
            privateChat.id = 'private-chat-' + username;
            privateChat.style.display = 'none';
            document.getElementById('privateChats').appendChild(privateChat);
            privateChats[username] = privateChat;
        }

        function showPrivateChat(username) {
            hideAllChats();
            if (privateChats[username]) {
                privateChats[username].style.display = 'block';
            }
        }

        function showGeneralChat() {
            hideAllChats();
            chatbox.style.display = 'block';
        }

        function hideAllChats() {
            chatbox.style.display = 'none';
            for (var chat in privateChats) {
                if (privateChats.hasOwnProperty(chat)) {
                    privateChats[chat].style.display = 'none';
                }
            }
        }

        function showNewMessageIndicator(username) {
            var userItems = document.getElementsByClassName('user-item');
            for (var i = 0; i < userItems.length; i++) {
                if (userItems[i].textContent === username) {
                    var indicator = document.createElement('span');
                    indicator.classList.add('new-message');
                    userItems[i].appendChild(indicator);
                }
            }
        }

        function hideNewMessageIndicator(username) {
            var userItems = document.getElementsByClassName('user-item');
            for (var i = 0; i < userItems.length; i++) {
                if (userItems[i].textContent === username) {
                    var indicator = userItems[i].getElementsByClassName('new-message')[0];
                    if (indicator) {
                        indicator.remove();
                    }
                }
            }
        }

        function getUserColor(username) {
            var colors = {
                'alex': '#d1ecf1',
                'sofa': '#f8d7da',
                'vitaliy': '#d4edda',
                'Guest': '#f8f9fa'
            };
            return colors[username] || '#e2e3e5';
        }
    </script>
    <script src="../scripts/list.js"></script>
    <script src="../scripts/logout.js"></script>
</body>

</html>