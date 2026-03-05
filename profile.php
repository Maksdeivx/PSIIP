<?php
session_start();
require_once 'db.php';

$message = "";

// 1. РЕГИСТРАЦИЯ
if (isset($_POST['register'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хешируем пароль

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $pass]);
        $message = "<p style='color:green;'>Регистрация успешна! Теперь войдите.</p>";
    } catch (Exception $e) {
        $message = "<p style='color:red;'>Ошибка: Email уже занят.</p>";
    }
}

// 2. ВХОД
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: profile.php");
        exit;
    } else {
        $message = "<p style='color:red;'>Неверный email или пароль.</p>";
    }
}

// 3. ВЫХОД
if (isset($_GET['logout'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет | Феста Мебель</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <style>body { background:#f8f9fa; padding-top:50px; }</style>
</head>
<body>
<div class="container">
    <a href="index.php" class="btn btn-outline-secondary mb-4">← На главную</a>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Формы входа и регистрации -->
        <div class="row">
            <div class="col-md-5 card p-4 shadow-sm">
                <h4>Вход</h4>
                <?= $message ?>
                <form method="POST">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Пароль" required>
                    <button type="submit" name="login" class="btn btn-primary w-100">Войти</button>
                </form>
            </div>
            <div class="col-md-2 text-center align-self-center">ИЛИ</div>
            <div class="col-md-5 card p-4 shadow-sm">
                <h4>Регистрация</h4>
                <form method="POST">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Ваше Имя" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Пароль" required>
                    <button type="submit" name="register" class="btn btn-success w-100">Зарегистрироваться</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Личный кабинет -->
        <div class="card p-4 shadow-sm">
            <div class="d-flex justify-content-between">
                <h2>Добро пожаловать, <?= $_SESSION['user_name'] ?>!</h2>
                <a href="?logout=1" class="btn btn-danger btn-sm">Выйти</a>
            </div>
            <hr>
            <h4>Мои заказы</h4>
            <table class="table table-hover mt-3">
                <thead>
                    <tr>
                        <th>№ Заказа</th>
                        <th>Дата</th>
                        <th>Товары (ID:Кол-во)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
                    $stmt->execute([$_SESSION['user_id']]);
                    $my_orders = $stmt->fetchAll();

                    if (!$my_orders): ?>
                        <tr><td colspan="3">Вы еще ничего не заказывали.</td></tr>
                    <?php else:
                        foreach ($my_orders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><?= $order['created_at'] ?></td>
                                <td><code><?= $order['items'] ?></code></td>
                            </tr>
                        <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>