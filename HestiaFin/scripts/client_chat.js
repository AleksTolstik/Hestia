var conn = new WebSocket('ws://localhost:8080');
conn.onopen = function(e) {
    console.log("З'єднання встановлено!");
};

conn.onmessage = function(e) {
    console.log(e.data);
};

function sendMessage(message) {
    conn.send(message);
}
