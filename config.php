<?php
// config.php
session_start();

$db_path = __DIR__ . '/database/database.sqlite';

// Создаем папку database если нет
if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}

// Проверяем, нужно ли создать базу данных
$need_init = !file_exists($db_path) || filesize($db_path) == 0;

try {
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Если база пустая, создаем таблицы
    if ($need_init) {
        // Проверяем, есть ли таблица users
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (!$result->fetch()) {
            // Создаем таблицы прямо здесь
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    role TEXT DEFAULT 'warehouse',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS suppliers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    phone TEXT,
                    email TEXT,
                    address TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    sku TEXT UNIQUE NOT NULL,
                    name TEXT NOT NULL,
                    description TEXT,
                    unit TEXT DEFAULT 'шт',
                    price REAL DEFAULT 0,
                    stock INTEGER DEFAULT 0,
                    min_stock INTEGER DEFAULT 0,
                    supplier_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
                );
                
                CREATE TABLE IF NOT EXISTS movements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    type TEXT NOT NULL,
                    product_id INTEGER NOT NULL,
                    quantity INTEGER NOT NULL,
                    price REAL NOT NULL,
                    document_number TEXT,
                    note TEXT,
                    user_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                );
                
                CREATE TABLE IF NOT EXISTS inventories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    date DATE NOT NULL,
                    items TEXT,
                    note TEXT,
                    user_id INTEGER,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
                );
                
                CREATE TABLE IF NOT EXISTS company (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    inn TEXT,
                    kpp TEXT,
                    ogrn TEXT,
                    address TEXT,
                    phone TEXT,
                    email TEXT
                );
            ");
            
            // Добавляем тестовые данные
            $pdo->exec("
                INSERT INTO users (name, email, password, role) VALUES
                ('Администратор', 'admin@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                ('Кладовщик', 'worker@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warehouse');
                
                INSERT INTO suppliers (name, phone, email, address) VALUES
                ('ООО \"Продукт-Сервис\"', '+7 (495) 111-22-33', 'info@product-service.ru', 'г. Москва'),
                ('ООО \"Балтик Трейд\"', '+7 (495) 444-55-66', 'sales@baltiktrade.ru', 'г. Санкт-Петербург');
                
                INSERT INTO company (id, name, inn, kpp, ogrn, address) VALUES
                (1, 'ООО \"Журавли торговля и логистика\"', '7536089490', '753601001', '1207700359525', '672014, Забайкальский край, г.о. город Чита, г Чита, ул 5-я Малая, д. 10');
            ");
        }
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
