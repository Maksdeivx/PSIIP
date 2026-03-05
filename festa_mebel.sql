-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-5.7
-- Время создания: Мар 05 2026 г., 09:14
-- Версия сервера: 5.7.44
-- Версия PHP: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `festa_mebel`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Кухни'),
(2, 'Шкафы-купе'),
(3, 'Распашные шкафы'),
(4, 'Комоды и тумбы'),
(5, 'Прихожие'),
(6, 'Гостиные'),
(7, 'Спальни');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `items` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `name`, `phone`, `items`, `created_at`, `user_id`) VALUES
(1, 'Макс', '3426464364747', '{\"25\":1,\"24\":1}', '2026-03-04 09:01:08', NULL),
(2, 'Макс', '2232534643', '{\"25\":2}', '2026-03-05 05:46:30', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_popular` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `old_price`, `image`, `is_popular`) VALUES
(1, 1, 'Кухня ТЕЯ Серебро премиум', 25900.00, 29000.00, 'image_1156.png', 1),
(2, 1, 'Кухня Вега Лайт угловая', 31900.00, 35400.00, 'Кухни_1.png', 1),
(3, 1, 'Кухонный гарнитур Гретта 2.4м', 28400.00, 31600.00, 'image_1156.png', 0),
(4, 1, 'Кухня Лорена Серый шёлк', 34900.00, 39000.00, 'image_1156.png', 0),
(5, 1, 'Кухня Модерн Белый глянец', 42000.00, 48000.00, 'Кухни_1.png', 1),
(6, 2, 'Шкаф-купе Капелла 151 Дуб', 18000.00, 21000.00, 'image_1155.png', 1),
(7, 2, 'Шкаф-купе Невада Венге', 21500.00, 24000.00, 'image_392.png', 1),
(8, 2, 'Шкаф-купе Сити Графит', 19800.00, 22000.00, 'image_1155.png', 0),
(9, 2, 'Шкаф-купе Альянс 1.5м', 17200.00, 19500.00, 'image_392.png', 0),
(10, 2, 'Гардероб Милан Золото', 23700.00, 27000.00, 'image_1155.png', 1),
(11, 3, 'Шкаф 3-х створчатый Верона', 14500.00, 16000.00, 'image_392.png', 0),
(12, 3, 'Шкаф Лофт Антрацит', 12900.00, NULL, 'image_392.png', 0),
(13, 3, 'Шкаф платяной классик', 11000.00, 13000.00, 'image_392.png', 1),
(14, 4, 'Комод со стеклом Элегант', 8500.00, 9800.00, 'image_393.png', 1),
(15, 4, 'Тумба под ТВ Сканди', 5400.00, 6200.00, 'image_393.png', 0),
(16, 4, 'Прикроватная тумба Рио', 2100.00, NULL, 'image_393.png', 0),
(17, 4, 'Комод высокий 5 ящиков', 7800.00, 9000.00, 'image_393.png', 0),
(18, 5, 'Прихожая компакт Ксения', 13400.00, 15000.00, 'Прихожие_1.png', 1),
(19, 5, 'Модульная система Вега', 22600.00, 25000.00, 'Прихожие_1.png', 0),
(20, 5, 'Обувница с сиденьем', 4500.00, 5200.00, 'Прихожие_1.png', 0),
(21, 6, 'Стенка для гостиной Марта', 27000.00, 31000.00, 'гостинная.png', 1),
(22, 6, 'Мини-стенка Глория', 15800.00, 18000.00, 'гостинная.png', 0),
(23, 6, 'Стеллаж открытый Лофт', 6700.00, NULL, 'гостинная.png', 0),
(24, 7, 'Кровать двуспальная Лаура', 19000.00, 22000.00, 'спальня.png', 1),
(25, 7, 'Спальный гарнитур Амели', 45000.00, 52000.00, 'спальня.png', 0),
(26, 7, 'Туалетный столик с зеркалом', 9300.00, 11000.00, 'спальня.png', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'maks', 'maks.kozeyko@gmail.com', '$2y$10$UtGN5TEdl9rTY6jgtD9YLOThHgrSx0Br/X3Ug8epszRmQTWPxDS16', '2026-03-05 05:35:01');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
