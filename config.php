<?php
// config.php
session_start();

// Параметры из переменных окружения Render
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'warehouse';
$db_user = getenv('DB_USER') ?: 'postgres';
$db_pass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO(
        "pgsql:host=$db_host;port=$db_port;dbname=$db_name",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Создаем таблицы если их нет
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) DEFAULT 'warehouse',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS suppliers (
            id SERIAL PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            phone VARCHAR(50),
            email VARCHAR(100),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS products (
            id SERIAL PRIMARY KEY,
            sku VARCHAR(50) UNIQUE NOT NULL,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            unit VARCHAR(20) DEFAULT 'шт',
            price DECIMAL(10,2) DEFAULT 0,
            stock INTEGER DEFAULT 0,
            min_stock INTEGER DEFAULT 0,
            supplier_id INTEGER REFERENCES suppliers(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS movements (
            id SERIAL PRIMARY KEY,
            type VARCHAR(10) NOT NULL,
            product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
            quantity INTEGER NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            document_number VARCHAR(50),
            note TEXT,
            user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS inventories (
            id SERIAL PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            date DATE NOT NULL,
            items JSONB,
            note TEXT,
            user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS company (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            inn VARCHAR(12),
            kpp VARCHAR(9),
            ogrn VARCHAR(15),
            address TEXT,
            phone VARCHAR(50),
            email VARCHAR(100)
        );
    ");
    
    // Проверяем, есть ли пользователи
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount == 0) {
        $pdo->exec("
            INSERT INTO users (name, email, password, role) VALUES
            ('Администратор', 'admin@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
            ('Кладовщик', 'worker@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warehouse');
        ");
    }
    
    // Проверяем, есть ли товары
    $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    if ($productCount == 0) {
        $pdo->exec("
            INSERT INTO suppliers (name, phone, email, address) VALUES
            ('ООО \"Вимм-Билль-Данн\"', '+7 (495) 777-88-99', 'sales@wbd.ru', 'г. Москва'),
            ('АО \"Данон Россия\"', '+7 (495) 555-66-77', 'info@danone.ru', 'г. Москва'),
            ('ООО \"Молочный Комбинат\"', '+7 (495) 333-44-55', 'market@mk.ru', 'г. Московская обл.');
            
            INSERT INTO products (sku, name, description, unit, price, stock, min_stock, supplier_id) VALUES
            ('MILK-001', 'Молоко \"Домик в деревне\" 3.2%', 'Пастеризованное, 1 л', 'шт', 89.90, 500, 100, 1),
            ('MILK-002', 'Молоко \"Простоквашино\" 2.5%', 'Пастеризованное, 1 л', 'шт', 79.90, 450, 90, 1),
            ('KEFIR-001', 'Кефир \"Био-Баланс\" 2.5%', '1 л', 'шт', 85.00, 300, 60, 2),
            ('SOUR-001', 'Сметана \"Простоквашино\" 20%', '400 г', 'шт', 129.90, 200, 40, 1),
            ('BUTTER-001', 'Масло сливочное \"Крестьянское\" 82.5%', '180 г', 'шт', 149.90, 150, 30, 1),
            ('YOGURT-001', 'Йогурт \"Activia\" натуральный', '120 г', 'шт', 59.90, 400, 80, 2),
            ('CHEESE-001', 'Сыр \"Российский\" 50%', 'кг', 'шт', 599.90, 100, 20, 3),
            ('CHEESE-002', 'Сыр \"Маасдам\"', 'кг', 'шт', 699.90, 80, 15, 3);
            
            INSERT INTO company (id, name, inn, kpp, ogrn, address) VALUES
            (1, 'ООО \"Журавли торговля и логистика\"', '7536089490', '753601001', '1207700359525', '672014, Забайкальский край, г.о. город Чита, г Чита, ул 5-я Малая, д. 10');
        ");
    }
    
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Функции для ролей
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isWarehouse() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'warehouse';
}

function requireAdmin() {
    if (!isAdmin()) {
        die('Доступ запрещен. Требуются права администратора.');
    }
}
