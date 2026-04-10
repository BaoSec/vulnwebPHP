<?php
// ❌ Hardcoded secret
$API_KEY = "sk_test_123456";

// DB (SQLite)
$db = new SQLite3('users.db');
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER, username TEXT, password TEXT)");
$db->exec("INSERT INTO users VALUES (1, 'admin', 'password123')");

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// =========================
// 🚨 1. SQL Injection
// =========================
if ($path == "/user") {
    $id = $_GET['id'];
    $query = "SELECT * FROM users WHERE id = $id"; // ❌ vuln

    $result = $db->query($query);
    $rows = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    echo json_encode($rows);
}

// =========================
// 🚨 2. Command Injection
// =========================
if ($path == "/ping") {
    $host = $_GET['host'];
    system(escapeshellarg("ping -n 1 " . $host)); // ❌ vuln
}

// =========================
// 🚨 3. XSS
// =========================
if ($path == "/search") {
    $q = $_GET['q'];
    echo "<h1>Search result: $q</h1>"; // ❌ vuln
}

// =========================
// 🚨 4. IDOR
// =========================
if ($path == "/profile") {
    $id = $_GET['id'];
    $result = $db->query("SELECT * FROM users WHERE id = $id");

    echo json_encode($result->fetchArray(SQLITE3_ASSOC)); // ❌ no auth
}

// =========================
// 🚨 5. Insecure Login (SQLi)
// =========================
if ($path == "/login") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'"; // ❌ vuln
    $result = $db->query($query);

    if ($result->fetchArray()) {
        echo "Login success";
    } else {
        echo "Fail";
    }
}

// =========================
// 🚨 6. Sensitive Info Leak
// =========================
if ($path == "/debug") {
    echo json_encode([
        "api_key" => $API_KEY,
        "env" => $_SERVER
    ]); // ❌ leak
}

// =========================
// 🚨 7. Brute Force
// =========================
if ($path == "/bruteforce") {
    $password = $_POST['password'];

    if ($password === "admin123") {
        echo "Correct!";
    } else {
        echo "Wrong";
    }
}

// =========================
// 🚨 8. Path Traversal
// =========================
if ($path == "/file") {
    $file = $_GET['name'];
    echo file_get_contents("files/" . $file); // ❌ vuln
}

// =========================
// 🚨 9. Open Redirect
// =========================
if ($path == "/redirect") {
    $url = $_GET['url'];
    header("Location: $url"); // ❌ vuln
}
?>