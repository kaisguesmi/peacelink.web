<?php
try {
    $dsn = 'mysql:host=localhost;dbname=reclamations_db;charset=utf8';
    $pdo = new PDO($dsn, 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $newPass = 'AdminPass2025!';
    $hash = password_hash($newPass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);

    echo "Updated admin password hash.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>