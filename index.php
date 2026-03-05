<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

session_start();

require_once 'db.php';

// ЛОГИКА КОРЗИНЫ 
if (isset($_GET['add_to_cart'])) {
    $p_id = (int)$_GET['add_to_cart'];
    $_SESSION['cart'][$p_id] = ($_SESSION['cart'][$p_id] ?? 0) + 1;
    header("Location: index.php#catalog-section"); // Возврат к каталогу
    exit;
}

// ПОИСК, ФИЛЬТРАЦИЯ И ПАГИНАЦИЯ
$limit = 4; // Товаров на странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$cat_id = $_GET['cat'] ?? 0;
$sort = $_GET['sort'] ?? 'id_desc';

$query_parts = "FROM products WHERE name LIKE :search";
$params = [':search' => "%$search%"];

if ($cat_id > 0) {
    $query_parts .= " AND category_id = :cat";
    $params[':cat'] = $cat_id;
}

// Сортировка
$sort_sql = " ORDER BY id DESC";
if ($sort == 'price_asc') $sort_sql = " ORDER BY price ASC";
if ($sort == 'price_desc') $sort_sql = " ORDER BY price DESC";

// Получаем товары
$products_stmt = $pdo->prepare("SELECT * $query_parts $sort_sql LIMIT $limit OFFSET $offset");
$products_stmt->execute($params);
$products = $products_stmt->fetchAll();

// Считаем страницы
$total_stmt = $pdo->prepare("SELECT COUNT(*) $query_parts");
$total_stmt->execute($params);
$total_pages = ceil($total_stmt->fetchColumn() / $limit);

// ОБРАБОТКА ЗАКАЗА
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_order'])) {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $cart_data = json_encode($_SESSION['cart'] ?? []);
    
    // Получаем ID пользователя, если он вошел в систему
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;

    // Сохраняем в БД (добавили колонку user_id)
    $stmt = $pdo->prepare("INSERT INTO orders (name, phone, items, user_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $cart_data, $user_id]);

    unset($_SESSION['cart']); 
    $order_success = true;
}
// Проверяем, была ли отправлена форма
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // Настройки сервера
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'maks.kozeyko@gmail.com'; 
        $mail->Password   = 'ykxu ihpb ptwp edmk ';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                    


        $mail->setFrom('maks.kozeyko@gmail.com', 'Сайт Портфолио');
        $mail->addAddress('maks.kozeyko@gmail.com'); // Получатель — вы сами

        // Данные из формы (проверяем наличие данных)
        $name    = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : 'Аноним';
        $email   = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 'Не указан';
        $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

        // Содержание письма
        $mail->isHTML(true);
        $mail->Subject = 'Новая заявка: ' . $name;
        $mail->Body    = "
            <h2>Детали сообщения:</h2>
            <b>Имя:</b> $name <br>
            <b>Email отправителя:</b> $email <br>
            <b>Текст сообщения:</b><br> $message
        ";

        $mail->send();
        echo '<p style="color: green;">Письмо успешно отправлено на maks.kozeyko@gmail.com!</p>';
    } catch (Exception $e) {
        echo "<p> Ошибка отправки: {$mail->ErrorInfo}</p>";
    }
}
?>
<?php

// 1. Логика счетчика посещений
if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 1; // Если зашел первый раз
} else {
    $_SESSION['counter']++; // Если обновил страницу
}

// 2. Логика запоминания имени
if (isset($_POST['set_name'])) {
    $_SESSION['user_name'] = htmlspecialchars($_POST['client_name']);
}

// 3. Логика сброса сессии (выход)
if (isset($_GET['do_logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php"); // Перезагружаем страницу после удаления данных
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>Мебельный интернет-магазин корпусной мебели в СПб «Феста Мебель»</title>

        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <link rel="stylesheet" href="./css/fonts.css">
        <link rel="stylesheet" type="text/css" href="./css/style.css">
        <link href="https://fonts.cdnfonts.com/css/blogger-sans" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <meta charset = "UTF-8">
        <meta name="description" content="Фабрика мебели «Феста-Мебель». Продажа готовой корпусной мебели, а также изготовление на заказ с доставкой. Высокое качество, быстрые сроки и низкие цены">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="Козейко Максим">
        <meta name="keyword" content="изготовление корпусной мебели, корпусная мебель на заказ в спб, корпусная мебель от производителя, 
        корпусная мебель купить, корпусная мебель недорого, недорогая корпусная мебель спб, производство корпусной мебели, санкт-петербург, питер, спб, феста">
        <link rel="icon" type="image/png" sizes="32x32" href="img/logo.png">
    </head>
    <body>
        <!--Шапка для мобильных устройств-->
        <header class="main-header" id="header">
            <div class="container align-items-center mobile-header g-1">
              <div class="d-flex align-items-center gap-2">
                <div class="header-logo">
                  <img alt="Логотип" class="img" src="./img/logo.png">
                </div>
                <div>
                  <div class="city">Санкт-Петербург</div>
                  <div class="header-phone">+7 (812) 614-87-80</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-3">
                  <img alt="Лупа" src="./img/мобильная_лупа.png">
                  <button class="btn" data-bs-toggle="modal" data-bs-target="#feedback"><img alt="Телефон" src="./img/телефон_моб.png"></button>
                  <button class="btn" data-bs-toggle="modal" data-bs-target="#burger-menu"><img alt="Меню" src="./img/бургер_меню.png"></button>
              </div>
            </div>




            <!--Верхняя шапка для десктопа-->
            <div class="logo-header">
                <img alt="Логотип" src="./img/logo.png" class="img-fluid rotate-logo">
                <p>Производство корпусной мебели</p>
            </div>
            <div class="info-header">
                <div class="first-nav">
                    <nav>
                        <a href="#">Доставка и сборка</a>
                        <a href="#">Оплата и гарантия</a>
                        <a href="#">О компании</a>
                        <a href="#">Отзывы</a>
                        <a href="#">Контакты</a>
                    </nav>
                    <div class="infos">
                        <p class="post">info@festa-mebel.ru</p>
                        <p class="time">Ежедневно 09:00-20:00</p>
                        <p class="number">+7 (812) 614-87-80</p>
                    </div>
                </div>
                <div class="find-form">
<form action="index.php" method="GET" class="search-group">
    <input type="text" name="search" placeholder="Поиск по каталогу" value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><img alt="Лупа" src="./img/лупа.png" width="100%"></button>
</form>
                      <button>Заказать звонок</button>
                      <img alt="Избранное" src="./img/Group_4519.png" width="36" height="32"><!--Для избранного-->
<!-- Добавили cursor:pointer и атрибуты для открытия модалки -->
<div class="position-relative d-inline-block" style="cursor:pointer" data-bs-toggle="modal" data-bs-target="#orderModal">
    <img src="./img/Group(1).png" width="32" height="32" alt="Корзина">
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        <?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?>
    </span>
</div>
<div class="user-panel ms-3 d-inline-block">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="profile.php" class="btn btn-sm btn-outline-light">Кабинет (<?= $_SESSION['user_name'] ?>)</a>
    <?php else: ?>
        <a href="profile.php" class="btn btn-sm btn-light">Войти / Регистрация</a>
    <?php endif; ?>
</div>

                </div>
                <div>
                  <nav class="last-nav">
                      <a href="#">Каталог мебели</a>
                      <a href="#">Мебель на заказ</a>
                      <a href="#">Наши работы</a>
                      <a href="#">Акции</a>
                  </nav>
                </div>
            </div>
        </header>
        <!-- Ваша форма -->
<form method="POST" class="d-flex flex-column justify-content-center align-items-center">
    <input type="text" name="name" placeholder="Имя" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <textarea name="message" placeholder="Сообщение"></textarea><br>
    <button type="submit">Отправить</button>
</form>
        <div class="container mt-3">
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>
            <!-- Демонстрация данных сессии -->
            <strong>Сессия:</strong> 
            Вы обновили эту страницу <u><?php echo $_SESSION['counter']; ?></u> раз(а).
            
            <?php if (isset($_SESSION['user_name'])): ?>
                | Добро пожаловать, <strong><?php echo $_SESSION['user_name']; ?></strong>! 
                <a href="?do_logout=1" class="btn btn-sm btn-outline-danger ms-2">Забыть меня</a>
            <?php else: ?>
                | Мы пока не знаем вашего имени.
            <?php endif; ?>
        </div>
        
        <!-- Форма для записи данных в сессию -->
        <?php if (!isset($_SESSION['user_name'])): ?>
        <form method="POST" class="d-flex gap-2">
            <input type="text" name="client_name" class="form-control form-control-sm" placeholder="Ваше имя" required>
            <button type="submit" name="set_name" class="btn btn-sm btn-dark">Запомнить</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<!--Окно обратной связи-->
<div class="modal fade" id="feedback" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body modal-form">
          <h5 class="text-center">Обратная связь</h5>
          <form id="ModalForm" action="#" method="POST" class="modal-form">
            <label for="name" class="label">Имя</label><br>
            <input type="text" name="login" id="modal-name" maxlength="30">
            <label for="phone-number" class="label-2">Телефон<span style="color:red;">*</span></label><br>
            <input type="tel" name="phone" id="phone-number-modal">
            <p style="color:#9B9B9B"><span style="color:red;">*</span>Поля обязательные для заполнения</p>
            <input type="submit" id="submit-modal" value="Отправить">
            <label for="agree-modal" class="agree">
              <input type="checkbox" name="request" id="agree-modal" value="agree">
                Согласен на обработку персональных данных*
              </label>
            <div id="form3-errors" style="color: red; margin-top: 10px;"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<!--Окно бургер меню-->
<div class="modal fade" id="burger-menu" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body burger-menu">
          <img class="burger-logo align-self-center" src="./img/logo.png" alt="Логотип">
          <nav class="burger-nav-1 pb-4">
            <a href="#">Главная</a>
            <a href="#">Каталог мебели</a>
            <a href="#">Мебель на заказ</a>
            <a href="#">Наши работы</a>
            <a href="#">Акции</a>
          </nav>
          <nav class="burger-nav-2 pb-4">
            <a href="#">Доставка и сборка</a>
            <a href="#">Оплата и гарантия</a>
            <a href="#">О компании</a>
            <a href="#">Отзывы</a>
            <a href="#">Контакты</a>
          </nav>
          <div>
            <p class="post" style="white-space: nowrap;">info@festa-mebel.ru</p>
            <p class="time" style="white-space: nowrap;">Ежедневно 09:00-20:00</p>
            <p class="number" style="white-space: nowrap;">+7 (812) 614-87-80</p>
          </div>
          <button class="modal-feedback align-self-center">Заказать звонок</button>
          <div class="socials pt-4">
             <a href="#">
              <img class="VK-modal" src="./img/VK.png" alt="ВК">
            </a>
            <a href="#">
              <img class="instagram-modal" src="./img/Instagram.png" alt="Инстаграм">
            </a>
            <a href="#">
              <img class="whatsapp-modal" src="./img/whatsapp.png" alt="WhatsApp">
            </a>
          </div>
      </div>
    </div>
  </div>
</div>

<!--Окно помощи-->
<div class="position-fixed bottom-0 start-0 p-3" style="z-index: 11">
  <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <img src="./img/manager.png" class="rounded me-2" alt="Менеджер" width="16" height="16">
      <strong class="me-auto">Менеджер Алексей</strong>
      <small>1 секунду назад</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      Привет! Я могу тебе чем-то помочь?
    </div>
  </div>
</div>



<!--Кнопки скролла-->
  <div class="scroll-buttons">
  <a id="scrollToTop" href="#header" title="Наверх">
    <img src="./img/стрелка_вверх_белая.png" alt="Стрелка">
  </a>

  <a id="scrollToBottom" href="#footer" title="Вниз">
    <img src="./img/стрелка_вниз_белая.png" alt="Стрелка">
  </a>
</div>
<!--Главный слайдер-->
<div id="carouselExampleCaptions" class="carousel slide">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="slide-content d-flex align-items-center justify-content-between p-5">
        <div class="text-block mx-5">
          <h2>Скидка 45% на все готовые шкафы-купе!</h2>
          <p>Акция продлена до конца октября.</p>
          <button class="btn">Подробнее</button>
        </div>
        <img src="img/шкафы_купе-removebg_3.png" alt="Шкаф-купе" class="slide-image" style="background-color: #D9DFE8;">
        <button class="btn btn-mobile">Подробнее</button>
      </div>
    </div>

    <div class="carousel-item">
      <div class="slide-content d-flex align-items-center justify-content-between p-5">
        <div class="text-block mx-5">
          <h2>Широкий ассортимент!</h2>
          <p>Распродажа комплектов мебели.</p>
          <button class="btn">Подробнее</button>
        </div>
        <img src="img/Group_6071.png" alt="Шкаф-купе" class="slide-image" style="background-color: #D9DFE8;">
        <button class="btn btn-mobile">Подробнее</button>
      </div>
    </div>

    <div class="carousel-item">
      <div class="slide-content d-flex align-items-center justify-content-between p-5">
        <div class="text-block mx-5">
          <h2>Офисная мебель!</h2>
          <p>Широкий каталог мебели для офиса.</p>
          <button class="btn">Подробнее</button>
        </div>
        <img src="img/03_2.png" alt="Монитор" class="slide-image" style="background-color: #D9DFE8;">
        <button class="btn btn-mobile">Подробнее</button>
      </div>
    </div>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>
        <!--Блок с иконками-->
        <section class="achiv-icons">
            <div class="container">
                <div class="row">
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок.png" alt="Значок">
                        <h6 class="text-center">Качество проверенное временем</h6>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок_(2).png" alt="Значок">
                        <h6 class="text-center">Современное производство</h6>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок_(3).png" alt="Значок">
                        <h6 class="text-center">Гарантия на всю мебель 18 месяцев</h6>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок_(4).png" alt="Значок">
                        <h6 class="text-center">Собственная служба доставки</h6>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок_(5).png" alt="Значок">
                        <h6 class="text-center">Профессиональная сборка и установка</h6>
                    </div>
                    <div class="col-xl-2 col-md-4 col-sm-6 col-6 icons">
                        <img src="./img/значок_(6).png" alt="Значок">
                        <h6 class="text-center">Оплата при получении и рассрочка</h6>                        
                    </div>
                </div>
            </div>
        </section>
        <h2>Каталог мебели</h2>
<!--Блок каталога-->
<section class="catalog">
  <div class="container-fluid">
    <div class="row g-3">
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3 pe-md-3">
        <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/шкафы_купе.png" alt="Фото-карточка">
          <div class="catalog-caption">Шкафы-купе</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3">
        <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/Кухни_1.png" alt="Фото-карточка">
          <div class="catalog-caption">Кухни</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3 pe-md-3">
        <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/image_392.png" alt="Фото-карточка">
          <div class="catalog-caption">Шкафы распашные</div>
        </div>
      </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item">
                <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/image_393.png" alt="Фото-карточка">
          <div class="catalog-caption">Комоды и тумбы</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3 pe-md-3">
                <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/Прихожие_1.png" alt="Фото-карточка">
          <div class="catalog-caption">Мебель для прихожей</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3">
                <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper" >
          <img src="./img/гостинная.png" alt="Фото-карточка">
          <div class="catalog-caption">Мебель для гостинной</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item pe-xl-3">
        <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/спальня.png" alt="Фото-карточка">
          <div class="catalog-caption">Мебель для спальни</div>
        </div>
        </a>
      </div>
      <div class="col-xl-3 col-md-6 col-sm-12 catalog-item">
                <a href="./ссылка" class="d-block h-100 text-decoration-none text-dark">
        <div class="catalog-img-wrapper">
          <img src="./img/рулетка.png" alt="Фото-карточка">
          <div class="catalog-caption" style="color: black;">Мебель под заказ</div>
        </div>
        </a>
      </div>
    </div>
  </div>
</section>
        <section id="catalog-section" class="container mt-5">
    <h2>Каталог продукции</h2>
    
    <!-- Фильтры и сортировка -->
    <div class="d-flex gap-3 mb-4">
        <a href="?sort=price_asc" class="btn btn-outline-secondary btn-sm">Дешевле</a>
        <a href="?sort=price_desc" class="btn btn-outline-secondary btn-sm">Дороже</a>
        <a href="index.php" class="btn btn-outline-danger btn-sm">Сбросить</a>
    </div>

    <div class="row">
        <?php if (empty($products)): ?>
            <p>Товары не найдены.</p>
        <?php else: ?>
            <?php foreach ($products as $row): ?>
            <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="./img/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
                    <div class="card-body text-center">
                        <h6 class="card-title"><?= htmlspecialchars($row['name']) ?></h6>
                        <p class="card-text fw-bold"><?= number_format($row['price'], 0, '.', ' ') ?> ₽</p>
                        
                        <!-- Кнопка купить -->
                        <a href="?add_to_cart=<?= $row['id'] ?>" class="btn btn-dark btn-sm">В корзину</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Пагинация -->
    <nav>
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
      </ul>
    </nav>
</section>
  

  <!-- Кнопки навигации -->
  <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel3" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#productCarousel3" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
  </button>
  </div>
  </div>
  <div class="tab-pane fade" id="wardrobe-tab-pane" role="tabpanel" aria-labelledby="wardrobe-tab" tabindex="0">В разработке</div>
  <div class="tab-pane fade" id="dresser-tab-pane" role="tabpanel" aria-labelledby="dresser-tab" tabindex="0">В разработке</div>
  <div class="tab-pane fade" id="hallway-tab-pane" role="tabpanel" aria-labelledby="hallway-tab" tabindex="0">В разработке</div>
  <div class="tab-pane fade" id="lounge-tab-pane" role="tabpanel" aria-labelledby="lounge-tab" tabindex="0">В разработке</div>
  <div class="tab-pane fade" id="bedroom-tab-pane" role="tabpanel" aria-labelledby="bedroom-tab" tabindex="0">В разработке</div>
</div>
<!--Блок с иконками-->
<section class="p-0">
  <div class="container-fluid">
    <div class="row">
      <div class="col-6 col-xl-4 col-md-4 col-12 pb-3 ps-xl-0">
        <div class="icons-2">
        <img src="./img/комп_зеленый.png" alt="Иконка">
        <h6 class="text-center">Оплата не выходя из дома</h6>
        <p class="text-center">Подобрать нужную мебель, сделать заказ и оплатить можно не выходя из дома. А в живую посмотреть мебель можно у нас в выставочном зале</p>
        </div>
      </div>
      <div class="col-6 col-xl-4 col-md-4 col-12 pb-3">
        <div class="icons-2">
        <img src="./img/иконка_(2).png" alt="Иконка">
        <h6 class="text-center">Без предоплаты</h6>
        <p class="text-center">Платите когда получите мебель — мы отменили предоплату 20% на готовую мебель</p>
        </div>
      </div>
      <div class="col-6 col-xl-4 col-md-4 col-12 pb-3 pe-xl-0">
        <div class="icons-2">
        <img src="./img/иконка_(3).png" alt="Иконка">
        <h6 class="text-center">Современное производство</h6>
        <p class="text-center">Современное оборудование позволяет не только снизить процент брака, но и повышает общее качество. Мебель выглядит более дорогой и служит дольше</p>
        </div>
      </div>
      <div class="col-6 col-xl-4 col-md-4 col-12 pb-3 ps-xl-0">
        <div class="icons-2">
        <img src="./img/иконка_(4).png" alt="Иконка">
        <h6 class="text-center">Обьемы закупок основа низкой себестоимостиа</h6>
        <p class="text-center">Длительное сотрудничество и объемы закупок основа низкой себестоимости. Покупая у нас - вы выбираете лучших производителей комплектующих на рынке по очень низкой цене</p>
        </div>
      </div>
      <div class="col-6 col-xl-4 col-md-4 pb-3 col-12">
        <div class="icons-2">
        <img src="./img/иконка_(5).png" alt="Иконка">
        <h6 class="text-center">Доставка от 1 дня</h6>
        <p class="text-center">Вам не приходится ждать готовности мебели — чётко соблюдаем сроки производства</p>
        </div>
      </div>
      <div class="col-6 col-xl-4 col-md-4 col-12 pb-3 pe-xl-0">
        <div class="icons-2">
        <img src="./img/иконка_(6).png" alt="Иконка">
        <h6 class="text-center">Более 300 довольных клиентов в месяц</h6>
        <p class="text-center">Нас рекомендуют родным и друзьям. У нас 300-400 довольных клиентов каждый месяц и много обратившихся повторно</p>
        </div>
      </div>
  </div>
  </div>
</section>
<!--Первый блок с формой-->
<section class="form-1">
  <div class="container-fluid">
    <div class="row">
      <h2 class="pb-4">Проконсультируем и подберем мебель для любой комнаты из каталога</h2>
    </div>
    <div class="row">
      <div class="col-xl-4 col-md-4 col-12 align-items-center d-flex justify-content-center order-2 order-md-1">
        <ul class="list">
          <li class="pb-3">Низкая стоимость</li>
          <li class="pb-3">Короткий срок производства</li>
          <li class="pb-3">Наличие на складе</li>
          <li class="pb-3">Простота сборки</li>
          <li class="pb-3">Высокое качество</li>
        </ul>
      </div>
      <div class="col-xl-4 col-md-4 col-12 order-1 order-md-2 d-flex justify-content-center align-items-center">
        <img src="img/Group_6071.png" class="img-fluid" alt="Мебель">
      </div>
      <div class="col-xl-4 col-md-4 col-12 order-3">
        <h4 class="caption">Оставьте заявку на консультацию!</h4>
        <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
        <form id="authForm" action="#" method="POST" class="auth-form">
          <label for="name" class="label"> Ваше имя</label><br>
          <input type="text" name="login" id="name" maxlength="30">
          <p> <label for="phone-number" class="label"> Номер телефона</label><br>
          <input type="tel" name="phone" id="phone-number"></p>
          <input type="submit" id="submit-1" value="Получить консультацию">
          <p class="mt-1"><input type="checkbox" name="request" id="agree1" value="agree1">  Согласен на обработку персональных данных*</p>
          <div id="form-1-errors" style="color: red; margin-top: 10px;"></div>
        </form>
      </div>
    </div>
  </div>
</section>

<!--Блок партнеров-->
<h2 class="text-center">Партнеры</h2>
<section class="p-0">
  <div class="container-fluid g-3">
    <div class="row g-3">
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="img/4-removebg_7.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="img/11_1117.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="img/image_1003.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="img/image_1004.png" alt="Партнер">
      </div>
    </div>
    <div class="row g-3">
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="./img/image_1005.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="./img/image_1006.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="./img/image_1007.png" alt="Партнер">
      </div>
      <div class="col-xl-3 col-md-3 col-6">
        <img class="img-fluid" src="./img/xLogo_green_color.png.pagespeed.ic_6.png" alt="Партнер">
      </div>
    </div>
  </div>
</section>
<!--Блок второй формы-->
<section class="form-2">
  <div class="container-fluid">
    <div class="row">
      <h2 class="pb-4">Любая корпусная мебель под заказ</h2>
    </div>
    <div class="row">
      <div class="col-xl-4 col-md-4 col-12 align-items-center d-flex justify-content-center order-2 order-md-1">
        <ul class="list">
          <li class="pb-3">Бесплатный дизайн проект</li>
          <li class="pb-3">Бесплатный замер помещения</li>
          <li class="pb-3">Большой выбор материалов и цветов</li>
          <li class="pb-3">Рассчет стоимости по вашему проекту</li>
        </ul>
      </div>
      <div class="col-xl-4 col-md-4 col-12 order-1 order-md-2 d-flex justify-content-center align-items-center">
        <img src="img/03_2.png" class="img-fluid" alt="Мониторы">
      </div>
      <div class="col-xl-4 col-md-4 col-12 order-3">
        <div class="gift">
          <img src="./img/Group_6383.png" alt="Подарок">
          <h4 class="caption2">Получите дизайн проект в подарок!</h4>
        </div>
        <form id="authForm-2" action="#" method="POST" class="auth-form">
          <label for="name" class="label"> Ваше имя</label><br>
          <input type="text" name="login" id="name-2" maxlength="30"><br>
          <label for="phone-number" class="label"> Номер телефона</label><br>
          <input type="tel" name="phone" id="phone-number-2">
          <p>Уже есть проект? Пришлите его на расчет</p>
          <p><button type="button" class="load_button"><label for="file" style="color:#333333">
            <img src="./img/скрепка.png" alt="Иконка" style="width: 16px; height: 16px;">
            Загрузить файл</label></button>
          <input id="file" type="file" style="display: none;"></p>
          <input type="submit" id="submit-2" value="Отправить заявку">
          <p class="mt-1"><input type="checkbox" id="agree2" name="request" value="agree">  Согласен на обработку персональных данных*</p>
          <div id="form-2-errors" style="color: red; margin-top: 10px;"></div>
        </form>
      </div>
    </div>
  </div>
</section>
<!--Блок с картой-->
<h2 class="text-center mb-4">Корпусная мебель от фабрики «ФЕСТА-МЕБЕЛЬ»</h2>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2002.3295013372908!2d30.34269637758033!3d59.
87687887488408!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x469630232561b08b%3A0x9db880bf446ea7c6!2z0KT
QldCh0KLQkCDQnNCV0JHQldCb0Kw!5e0!3m2!1sru!2sby!4v1749126624566!5m2!1sru!2sby" height="600" style="border:0;  width:100%; padding-left: 10%; padding-right:10%" 
allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
<!--Блок с аккордеоном-->
<section class="block-text">
  <div class="container">
    <h2 class="mb-4">Стильная корпусная мебель от производителя</h2>

    <div class="accordion" id="festaAccordion">

      <div class="accordion-item">
        <h2 class="accordion-header" id="headingIntro">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIntro" aria-expanded="true" aria-controls="collapseIntro">
            О фабрике «Феста»
          </button>
        </h2>
        <div id="collapseIntro" class="accordion-collapse collapse show" aria-labelledby="headingIntro" data-bs-parent="#festaAccordion">
<div class="accordion-body">
    <strong>Информация из файла 1.txt:</strong><br>
    <?php
    if (file_exists("1.txt")) {
        $f = fopen("1.txt", "r");
        while(!feof($f)) {
            echo fgets($f) . "<br />";
        }
        fclose($f);
    } else {
        echo "Файл 1.txt не найден.";
    }
    ?>
</div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="headingBenefits">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBenefits" aria-expanded="false" aria-controls="collapseBenefits">
            Почему покупать напрямую выгодно
          </button>
        </h2>
        <div id="collapseBenefits" class="accordion-collapse collapse" aria-labelledby="headingBenefits" data-bs-parent="#festaAccordion">
          <div class="accordion-body">
            Покупка мебели от производителя – это экономия времени и бюджета. Специалисты фабрики проконсультируют по ассортименту, техническим характеристикам, дизайну и процессу заказа.
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="headingConsultation">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseConsultation" aria-expanded="false" aria-controls="collapseConsultation">
            Что можно уточнить у специалистов
          </button>
        </h2>
        <div id="collapseConsultation" class="accordion-collapse collapse" aria-labelledby="headingConsultation" data-bs-parent="#festaAccordion">
          <div class="accordion-body">
            <ul>
              <li><strong>Ассортимент:</strong> Новинки, особенности серий, методика подбора мебели.</li>
              <li><strong>Технические характеристики:</strong> Материалы, фурнитура, прочность, особенности.</li>
              <li><strong>Дизайн:</strong> Классика и современный стиль, цветовые решения, стекло и зеркало.</li>
              <li><strong>Заказ:</strong> Готовая мебель или индивидуальный проект. Возможность выбора цвета из палитры фабрики.</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="headingQuiz">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseQuiz" aria-expanded="false" aria-controls="collapseQuiz">
            Как выбрать: готовая мебель или индивидуальный заказ?
          </button>
        </h2>
        <div id="collapseQuiz" class="accordion-collapse collapse" aria-labelledby="headingQuiz" data-bs-parent="#festaAccordion">
          <div class="accordion-body">
            Чтобы определиться, ответьте на следующие вопросы:
            <ol>
              <li><strong>Есть ли готовая мебель нужных габаритов?</strong><br>Если нет – лучше сделать заказ.</li>
              <li><strong>Есть ли нужный вид мебели?</strong><br>Каталог обширен, но если вы ищете что-то уникальное – рассмотрите заказ.</li>
              <li><strong>Нравится ли дизайн готовых решений?</strong><br>Если не устраивает, закажите проект с индивидуальным стилем.</li>
              <li><strong>Устраивает ли комплектация?</strong><br>Готовые комплектации можно дополнять, но лучше сразу продумать всё в заказе.</li>
            </ol>
            <p>Если хотя бы на один вопрос вы ответили «Нет» – закажите корпусную мебель по эскизу. Дизайнеры подберут идеальные материалы, формы и размеры под ваш бюджет.</p>
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header" id="headingHelp">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHelp" aria-expanded="false" aria-controls="collapseHelp">
            Нужна помощь?
          </button>
        </h2>
        <div id="collapseHelp" class="accordion-collapse collapse" aria-labelledby="headingHelp" data-bs-parent="#festaAccordion">
          <div class="accordion-body">
            Если вы затрудняетесь с выбором или хотите уточнить детали покупки, доставки, сборки или гарантии — свяжитесь с менеджерами фабрики по телефону или через форму на сайте.
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
<section><a href="admin.php" class="text-muted" style="text-decoration: none; font-size: 10px;">Вход для персонала</a> </section>
  <!--Подвал сайта-->
  <footer id="footer">
    <div class="container-fluid p-0">
      <div class="row">
        <div class="col d-flex justify-content-center mt-3"><button type="button">Онлайн-заказ</button></div></div>
      <div class="row align-items-center mb-3">
        <div class="col line-with-title">
          <p class="mb-0 line-title">Каталог готовой мебели</p>
          <div class="horizontal-line"></div>
        </div>
      </div>
        <div class="row mb-3">
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Шкафы-купе и кухни</h6>
            <nav>
            <ul>
              <li><a href="#">Двухдверные</a></li>
              <li><a href="#">Трехдверные</a></li>
              <li><a href="#">С зеркалом</a></li>
              <li><a href="#">Кухни прямые</a></li>
              <li><a href="#">Кухни угловые</a></li>
            </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Шкафы распашные</h6>
            <nav>  
              <ul>
              <li><a href="#">Одностворчатые</a></li>
              <li><a href="#">Двухстворчатые</a></li>
              <li><a href="#">Трехстворчатые</a></li>
              <li><a href="#">Четырехстворчатые</a></li>
              <li><a href="#">Угловые</a></li>
              <li><a href="#">С зеркалом</a></li>
              <li><a href="#">Полки</a></li>
            </ul>
            </nav>         
          </div>
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Комоды и тумбы</h6>
            <nav>
              <ul>
              <li><a href="#">Комоды</a></li>
              <li><a href="#">Тумбочки</a></li>
              <li><a href="#">Тумбы под ТВ</a></li>
              <li><a href="#">Антресоли</a></li>
              <li><a href="#">Столы</a></li>
              <li><a href="#">Кухонные столы</a></li>
            </ul>
            </nav>              
          </div>
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Мебель для прихожей</h6>
            <nav>
              <ul>
              <li><a href="#">Готовые прихожие</a></li>
              <li><a href="#">Шкафы-купе</a></li>
              <li><a href="#">Распашные шкафы</a></li>
              <li><a href="#">Комоды</a></li>
              <li><a href="#">Обувницы</a></li>
              <li><a href="#">Тумбы</a></li>
              <li><a href="#">Вешалки для одежды</a></li>
              <li><a href="#">Пуфы</a></li>
              <li><a href="#">Зеркала</a></li>
            </ul>
            </nav>              
          </div>
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Мебель для гостинной</h6>
            <nav>     
              <ul>
              <li><a href="#">Готовые гостинные</a></li>
              <li><a href="#">Стелажи</a></li>
              <li><a href="#">Комоды</a></li>
              <li><a href="#">Шкафы</a></li>
              <li><a href="#">Шкафы-купе</a></li>
            </ul>
            </nav>         
          </div>
          <div class="col-xl-2 col-md-6 col-6 p-0">
            <h6>Мебель для спальни</h6>
            <nav>
              <ul>
              <li><a href="#">Прикроватные тумбы</a></li>
              <li><a href="#">Комоды</a></li>
              <li><a href="#">Шкафы</a></li>
              <li><a href="#">Шкафы-купе</a></li>
              <li><a href="#">Кровати</a></li>
              <li><a href="#">Зеркала</a></li>
            </ul>
            </nav>          
          </div>
        </div>
        <div class="row align-items-center mb-3">
          <div class="col line-with-title"><p class="mb-0 line-title">Мебель на заказ</p>
            <div class="horizontal-line"></div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Шкафы-купе</a></li>
                <li><a href="#">Комоды</a></li>
                <li><a href="#">Обувницы</a></li>
              </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Кухни</a></li>
                <li><a href="#">Мебель для гостинной</a></li>
                <li><a href="#">Детская мебель</a></li>
              </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Шкафы</a></li>
                <li><a href="#">Мебель для прихожей</a></li>
                <li><a href="#">Столы</a></li>
              </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Встроенные шкафы</a></li>
                <li><a href="#">Мебель для спальни</a></li>
                <li><a href="#">Книжные шкафы</a></li>
              </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Угловые шкафы</a></li>
                <li><a href="#">Мебель для ванной</a></li>
                <li><a href="#">Тумбы</a></li>
              </ul>
            </nav>
          </div>
          <div class="col-xl-2 col-md-6 col-6">
            <nav>
              <ul>
                <li><a href="#">Угловые кухни</a></li>
                <li><a href="#">Мебель для балконов</a></li>
                <li><a href="#">Гардеробные</a></li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="row align-items-center mb-3">
          <div class="col line-with-title">
            <p class="mb-0 line-title">Основное меню</p>
            <div class="horizontal-line"></div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">Каталог мебели</a></div>
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">Мебель на заказ</a></div>
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">Наши работы</a></div>
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">О компании</a></div>
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">Доставка и сборка</a></div>
          <div class="col-xl-2 col-md-6 col-6 navigation"><a href="#">Оплата и гарантия</a></div>
        </div>
        <div class="row">
          <div class="horizontal-line"></div>
        </div>
        <div class="row align-items-center">
          <div class="col-12 col-md-12 order-2 order-md-2 horizontal-line keep-line mb-3 mt-3"></div>
          <div class="col-xl-4 col-md-6 col-6 politic-copyright order-3 order-md-3 order-xl-1"><p class="mb-0">Политика компании в отношении обработки персональных данных</p>
          </div>
          <div class="col-xl-4 col-md-6 col-6 politic-copyright text-center order-3 order-md-3 order-xl-2"><p class="mb-0">Copyright © 2008-2022. Производство корпусной мебели «ФЕСТА-МЕБЕЛЬ».<br>Все права защищены</p>
          </div>
          <div class="col-xl-4 col-12 col-md-12 social-icons order-1 order-md-1 order-xl-3">
            <a href="#">
              <img class="VK" src="./img/VK.png" alt="ВК">
            </a>
            <a href="#">
              <img class="instagram" src="./img/Instagram.png" alt="Instagram">
            </a>
            <a href="#">
              <img class="whatsapp" src="./img/whatsapp.png" alt="WhatsApp">
            </a>
          </div>
        </div>
    </div>
  </footer>
        <!--Подключение скриптов-->
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/header-toggle.js"></script>
        <!-- <script src="js/validator_form.js"></script> -->
        <script>
          window.addEventListener('DOMContentLoaded', function () {
          const toastEl = document.getElementById('liveToast');
          const toast = new bootstrap.Toast(toastEl);
          toast.show();
          });
        </script>
        <!-- Модальное окно оформления заказа -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Оформление заказа</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Форма отправляет данные на этот же файл (index.php) -->
      <form method="POST" action="index.php">
        <div class="modal-body">
          <p>Всего товаров в корзине: <b><?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0 ?></b></p>
          <hr>
          <div class="mb-3">
            <label class="form-label">Ваше имя</label>
            <input type="text" name="name" class="form-control" placeholder="Иван Иванов" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Номер телефона</label>
            <input type="tel" name="phone" class="form-control" placeholder="+7 (___) ___-__-__" required>
          </div>
          <p class="text-muted small">Нажимая кнопку, вы соглашаетесь на обработку данных.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
          <!-- Важно: name="send_order" чтобы PHP поймал этот запрос -->
          <button type="submit" name="send_order" class="btn btn-primary">Отправить заказ</button>
        </div>
      </form>
    </div>
  </div>
</div>
    </body>
    </html>