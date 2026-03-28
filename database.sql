-- Создание базы данных
CREATE DATABASE IF NOT EXISTS `warehouse`;
USE `warehouse`;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'warehouse') DEFAULT 'warehouse',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица поставщиков
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `phone` VARCHAR(50),
    `email` VARCHAR(100),
    `address` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица товаров
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `sku` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT,
    `unit` VARCHAR(20) DEFAULT 'шт',
    `price` DECIMAL(10,2) DEFAULT 0,
    `stock` INT DEFAULT 0,
    `min_stock` INT DEFAULT 0,
    `supplier_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL
);

-- Таблица движений (приход/расход)
CREATE TABLE IF NOT EXISTS `movements` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `type` ENUM('receipt', 'shipment') NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `document_number` VARCHAR(50),
    `note` TEXT,
    `user_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Таблица инвентаризаций
CREATE TABLE IF NOT EXISTS `inventories` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `date` DATE NOT NULL,
    `items` JSON,
    `note` TEXT,
    `user_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
);

-- Вставка тестовых данных
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Администратор', 'admin@warehouse.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Кладовщик', 'worker@warehouse.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warehouse');

INSERT INTO `suppliers` (`name`, `phone`, `email`, `address`) VALUES
('ООО "ТехноПоставка"', '+7 (495) 123-45-67', 'info@tehnopostavka.ru', 'г. Москва, ул. Ленина, д. 10'),
('ИП Петров А.С.', '+7 (495) 987-65-43', 'petrov@mail.ru', 'г. Москва, ул. Садовая, д. 5');

INSERT INTO `products` (`sku`, `name`, `unit`, `price`, `stock`, `min_stock`, `supplier_id`) VALUES
('CPU-001', 'Процессор Intel Core i5', 'шт', 18500.00, 15, 5, 1),
('RAM-001', 'Оперативная память DDR4 16GB', 'шт', 4500.00, 30, 10, 1),
('SSD-001', 'SSD накопитель 512GB', 'шт', 3800.00, 25, 8, 2),
('MON-001', 'Монитор 24" Dell', 'шт', 12400.00, 8, 3, 2);