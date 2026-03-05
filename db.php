<?php
// db.php
$host = 'MySQL-5.7';
$db   = 'festa_mebel';
$user = 'root';        
$pass = '';            
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Ошибка БД: " . $e->getMessage());
}