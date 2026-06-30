Warning: A partial dump from a server that has GTIDs will by default include the GTIDs of all transactions, even those that changed suppressed parts of the database. If you don't want to restore GTIDs, pass --set-gtid-purged=OFF. To make a complete dump, pass --all-databases --triggers --routines --events. 
Warning: A dump from a server that has GTIDs enabled will by default include the GTIDs of all transactions, even those that were executed during its extraction and might not be represented in the dumped data. This might result in an inconsistent data dump. 
In order to ensure a consistent backup of the database, pass --single-transaction or --lock-all-tables or --source-data. 
-- MySQL dump 10.13  Distrib 9.6.0, for macos26.4 (arm64)
--
-- Host: localhost    Database: restaurant_db
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ 'adaa7e66-6443-11f1-80ce-cc67a1f0d24b:1-78';

--
-- Table structure for table `access_rights`
--

DROP TABLE IF EXISTS `access_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_rights` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_rights`
--

LOCK TABLES `access_rights` WRITE;
/*!40000 ALTER TABLE `access_rights` DISABLE KEYS */;
INSERT INTO `access_rights` VALUES (1,'ADMIN'),(2,'USER');
/*!40000 ALTER TABLE `access_rights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guests` int NOT NULL DEFAULT '1',
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'Анна Петрова','+79991234567',NULL,2,'2026-06-11','19:00:00',NULL,'confirmed','2026-06-09 20:42:36'),(2,'Иван Сидоров','+79997654321',NULL,4,'2026-06-12','20:00:00',NULL,'pending','2026-06-09 20:42:36');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dish_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `dish_id` (`dish_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (3,'Горячие блюда'),(4,'Десерты'),(5,'Напитки'),(1,'Салаты'),(2,'Супы'),(6,'Холодные блюда');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dishes`
--

DROP TABLE IF EXISTS `dishes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dishes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `weight` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `dishes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dishes`
--

LOCK TABLES `dishes` WRITE;
/*!40000 ALTER TABLE `dishes` DISABLE KEYS */;
INSERT INTO `dishes` VALUES (1,1,'Цезарь с курицей','Классический салат с куриным филе, пармезаном и соусом',450.00,250,'uploads/dishes/1-caesar.jpg','2026-06-09 20:42:36'),(2,1,'Греческий салат','Свежие овощи с сыром фета и оливками',380.00,220,'uploads/dishes/2-greek.jpg','2026-06-09 20:42:36'),(3,2,'Том Ям','Острый тайский суп с креветками на кокосовом молоке',550.00,300,'uploads/dishes/3-tom-yam.jpg','2026-06-09 20:42:36'),(4,2,'Борщ','Традиционный русский суп со сметаной',320.00,350,'uploads/dishes/4-borscht.jpg','2026-06-09 20:42:36'),(5,3,'Стейк Рибай','Мраморная говядина с овощами гриль',1200.00,300,'uploads/dishes/5-steak.jpg','2026-06-09 20:42:36'),(6,3,'Паста Карбонара','Итальянская паста с беконом и сливочным соусом',480.00,280,'uploads/dishes/6-carbonara.jpg','2026-06-09 20:42:36'),(7,4,'Тирамису','Итальянский десерт с маскарпоне',350.00,150,'uploads/dishes/7-tiramisu.jpg','2026-06-09 20:42:36'),(8,4,'Чизкейк','Нью-йоркский чизкейк с ягодным соусом',320.00,180,'uploads/dishes/8-cheesecake.jpg','2026-06-09 20:42:36'),(9,5,'Лимонад','Домашний лимонад (лимон/апельсин/ягоды)',180.00,400,'uploads/dishes/9-lemonade.jpg','2026-06-09 20:42:36'),(10,5,'Кофе','Эспрессо / Капучино / Латте',200.00,200,'uploads/dishes/10-coffee.jpg','2026-06-09 20:42:36'),(11,1,'Боул с киноа','Полезный боул с киноа, авокадо и овощами',420.00,0,'uploads/dishes/11-bowl.jpg','2026-06-10 06:04:27'),(12,3,'Пицца Маргарита','Классическая итальянская пицца с моцареллой',550.00,0,'uploads/dishes/12-pizza.jpg','2026-06-10 06:04:27'),(13,2,'Рамен','Японский суп с лапшой, свининой и яйцом',480.00,0,'uploads/dishes/13-ramen.jpg','2026-06-10 06:04:27'),(14,1,'Нисуаз','Французский салат с тунцом и яйцом',390.00,0,'uploads/dishes/14-salad.jpg','2026-06-10 06:04:27'),(15,3,'Лосось с овощами','Запечённый лосось с сезонными овощами',890.00,0,'uploads/dishes/15-fish.jpg','2026-06-10 06:04:27'),(16,3,'Куриный рулет','Куриный рулет с грибами и сыром',520.00,0,'uploads/dishes/16-chicken.jpg','2026-06-10 06:04:27'),(17,6,'Суши-сет','Ассорти из 8 видов суши и роллов',950.00,0,'uploads/dishes/17-sushi.jpg','2026-06-10 06:04:27'),(18,3,'Бургер','Говяжий бургер с сыром и карамелизированным луком',490.00,0,'uploads/dishes/18-burger.jpg','2026-06-10 06:04:27'),(19,5,'Смузи','Ягодный смузи с бананом и мятой',250.00,0,'uploads/dishes/19-smoothie.jpg','2026-06-10 06:04:27'),(20,4,'Мороженое','Пломбир с ягодным топпингом',280.00,0,'uploads/dishes/20-icecream.jpg','2026-06-10 06:04:27'),(21,6,'Холодец','Домашний холодец из говядины с хреном',350.00,0,'uploads/dishes/21-kholodets.jpg','2026-06-10 06:30:30'),(22,6,'Окрошка','Классическая окрошка на квасе с овощами и колбасой',280.00,0,'uploads/dishes/22-okroshka.jpg','2026-06-10 06:43:55');
/*!40000 ALTER TABLE `dishes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'Новое сезонное меню','Попробуйте наши новые летние блюда из свежих сезонных продуктов',NULL,'2026-06-09 20:42:36'),(2,'Скидка 20% на первый заказ','Для новых клиентов скидка на первый заказ через сайт',NULL,'2026-06-09 20:42:36'),(3,'Мастер-класс от шеф-повара','Научитесь готовить фирменные блюда нашего ресторана',NULL,'2026-06-09 20:42:36');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `dish_id` int NOT NULL,
  `count` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `dish_id` (`dish_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `personal_id` int DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','processing','delivering','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `total_price` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `personal_id` (`personal_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal`
--

DROP TABLE IF EXISTS `personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position_id` int NOT NULL,
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal`
--

LOCK TABLES `personal` WRITE;
/*!40000 ALTER TABLE `personal` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (4,'Администратор'),(5,'Курьер'),(3,'Официант'),(2,'Повар'),(1,'Шеф-повар');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `promotions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES (1,'Скидка 20% на первый заказ','Для новых клиентов скидка на первый заказ через сайт','2026-06-10','2026-07-10','2026-06-09 20:42:36'),(2,'Бизнес-ланч за 350 ₽','С 12:00 до 15:00 в будние дни — комплексный обед','2026-06-10',NULL,'2026-06-09 20:42:36'),(3,'Десерт в подарок','Фирменный десерт в подарок в день рождения','2026-06-10',NULL,'2026-06-09 20:42:36');
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,NULL,'Анна','Очень вкусно! Обязательно вернусь ещё!',5,'2026-06-09 20:42:36'),(2,NULL,'Иван','Отличное место для ужина с семьёй.',4,'2026-06-09 20:42:36'),(3,NULL,'Мария','Лучший ресторан в городе!',5,'2026-06-09 20:42:36');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shopping_cart`
--

DROP TABLE IF EXISTS `shopping_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shopping_cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dish_id` int NOT NULL,
  `count` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `dish_id` (`dish_id`),
  CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shopping_cart`
--

LOCK TABLES `shopping_cart` WRITE;
/*!40000 ALTER TABLE `shopping_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `shopping_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_rights_id` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`),
  KEY `access_rights_id` (`access_rights_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`access_rights_id`) REFERENCES `access_rights` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'+79990000001',NULL,NULL,NULL,'$2y$12$llzwV9kcLPH7qobUz48wje0Z5efr2SVHTYWvvZJkiX00gxOFZal06',1,'2026-06-09 20:42:36'),(2,'+79990000002','Руслан','программист веб разработчик','avatar_2_1781040426.jpeg','$2y$12$H7pouL8eE8egGw4xB9hluuH2OPYeOf8/VMBpMJAHWHdvZcCpAUqSu',2,'2026-06-09 20:42:36');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10 11:46:53

-- Обновление составов
UPDATE dishes SET ingredients = 'Куриное филе, салат айсберг, пармезан, гренки, соус Цезарь' WHERE id = 1;
UPDATE dishes SET ingredients = 'Огурцы, помидоры, перец, оливки, сыр фета, оливковое масло' WHERE id = 2;
UPDATE dishes SET ingredients = 'Креветки, кокосовое молоко, том ям паста, грибы, лемонграсс' WHERE id = 3;
UPDATE dishes SET ingredients = 'Свекла, капуста, картофель, морковь, сметана, зелень' WHERE id = 4;
UPDATE dishes SET ingredients = 'Мраморная говядина, розмарин, тимьян, овощи гриль, соус' WHERE id = 5;
UPDATE dishes SET ingredients = 'Паста, бекон, яйцо, пармезан, сливки, черный перец' WHERE id = 6;
UPDATE dishes SET ingredients = 'Маскарпоне, савоярди, кофе эспрессо, какао, яйца' WHERE id = 7;
UPDATE dishes SET ingredients = 'Сливочный сыр, печенье, сливки, сахар, ягоды' WHERE id = 8;
UPDATE dishes SET ingredients = 'Лимоны, апельсины, мята, сахар, газированная вода' WHERE id = 9;
UPDATE dishes SET ingredients = 'Зерна арабики, вода' WHERE id = 10;
UPDATE dishes SET ingredients = 'Киноа, авокадо, овощи, зелень, оливковое масло' WHERE id = 11;
UPDATE dishes SET ingredients = 'Тесто, моцарелла, томатный соус, базилик' WHERE id = 12;
UPDATE dishes SET ingredients = 'Лапша, свинина, яйцо, бульон, нори, зелёный лук' WHERE id = 13;
UPDATE dishes SET ingredients = 'Тунец, яйцо, фасоль, оливки, лук, горчичный соус' WHERE id = 14;
UPDATE dishes SET ingredients = 'Филе лосося, цукини, перец, лимон, сливочный соус' WHERE id = 15;
UPDATE dishes SET ingredients = 'Куриное филе, грибы, сыр, сливки, специи' WHERE id = 16;
UPDATE dishes SET ingredients = 'Лосось, тунец, креветка, рис, нори, васаби, имбирь' WHERE id = 17;
UPDATE dishes SET ingredients = 'Говяжья котлета, булочка, сыр, салат, помидор, соус' WHERE id = 18;
UPDATE dishes SET ingredients = 'Банан, ягоды, йогурт, мед, мята' WHERE id = 19;
UPDATE dishes SET ingredients = 'Пломбир, клубника, шоколадный топпинг, вафля' WHERE id = 20;
UPDATE dishes SET ingredients = 'Говядина, чеснок, морковь, перец горошком, лавровый лист' WHERE id = 21;
UPDATE dishes SET ingredients = 'Квас, огурцы, редис, яйцо, колбаса, зелень, сметана' WHERE id = 22;

