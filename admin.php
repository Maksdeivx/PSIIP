<?php
session_start();
require_once 'db.php'; // Общее подключение к базе

$admin_password = "admin123"; // Ваш пароль

// 1. Логика входа
if (isset($_POST['do_login'])) {
    if ($_POST['pass'] === $admin_password) {
        $_SESSION['admin_auth'] = true;
    } else {
        $auth_error = "Неверный пароль!";
    }
}

// 2. Логика выхода
if (isset($_GET['admin_logout'])) {
    unset($_SESSION['admin_auth']);
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель | Феста Мебель</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <style>
        body { background-color: #f4f7f6; }
        .container { margin-top: 50px; max-width: 1000px; }
        .table-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <?php if (!isset($_SESSION['admin_auth'])): ?>
        <!-- Форма авторизации -->
        <div class="card mx-auto shadow" style="max-width: 400px;">
            <div class="card-body">
                <h4 class="text-center mb-4">Вход в систему</h4>
                <?php if (isset($auth_error)) echo "<div class='alert alert-danger'>$auth_error</div>"; ?>
                <form method="POST">
                    <input type="password" name="pass" class="form-control mb-3" placeholder="Введите пароль" required>
                    <button type="submit" name="do_login" class="btn btn-primary w-100">Войти</button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted small">Вернуться на сайт</a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Содержимое админки -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Список заказов</h2>
            <div>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">На сайт</a>
                <a href="?admin_logout=1" class="btn btn-danger btn-sm">Выйти</a>
            </div>
        </div>

        <div class="table-card">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Имя клиента</th>
                        <th>Телефон</th>
                        <th>Товары (ID:Кол-во)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Забираем заказы из БД
                    $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
                    if (empty($orders)): ?>
                        <tr><td colspan="5" class="text-center">Заказов пока нет</td></tr>
                    <?php else: 
                        foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td><strong><?= htmlspecialchars($order['name']) ?></strong></td>
                            <td><?= htmlspecialchars($order['phone']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= $order['items'] ?></span></td>
                        </tr>
                        <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 table-card">
            <h5>📩 Заявки из текстового файла (Консультации)</h5>
            <pre class="bg-light p-3 border rounded" style="max-height: 200px; overflow-y: auto;"><?php 
                if (file_exists("textfile.txt")) {
                    echo htmlspecialchars(file_get_contents("textfile.txt"));
                } else {
                    echo "Файл логов пуст.";
                }
            ?></pre>
        </div>
    <?php endif; ?>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>