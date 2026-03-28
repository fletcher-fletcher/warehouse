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
            // Создаем таблицы
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
            
            // Добавляем пользователей
            $pdo->exec("
                INSERT INTO users (name, email, password, role) VALUES
                ('Администратор', 'admin@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                ('Кладовщик', 'worker@warehouse.ru', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'warehouse');
                
                INSERT INTO company (id, name, inn, kpp, ogrn, address) VALUES
                (1, 'ООО \"Журавли торговля и логистика\"', '7536089490', '753601001', '1207700359525', '672014, Забайкальский край, г.о. город Чита, г Чита, ул 5-я Малая, д. 10');
            ");
        }
        
        // Проверяем, есть ли товары
        $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($productCount == 0) {
            // Добавляем поставщиков молочной продукции
            $pdo->exec("
                INSERT INTO suppliers (name, phone, email, address) VALUES
                ('ООО \"Вимм-Билль-Данн\"', '+7 (495) 777-88-99', 'sales@wbd.ru', 'г. Москва'),
                ('АО \"Данон Россия\"', '+7 (495) 555-66-77', 'info@danone.ru', 'г. Москва'),
                ('ООО \"Молочный Комбинат\"', '+7 (495) 333-44-55', 'market@mk.ru', 'г. Московская обл.');
            ");
            
            // Добавляем молочные товары (оптовая торговля)
            $pdo->exec("
                INSERT INTO products (sku, name, description, unit, price, stock, min_stock, supplier_id) VALUES
                ('MILK-001', 'Молоко \"Домик в деревне\" 3.2%', 'Пастеризованное, 1 л', 'шт', 89.90, 500, 100, 1),
                ('MILK-002', 'Молоко \"Простоквашино\" 2.5%', 'Пастеризованное, 1 л', 'шт', 79.90, 450, 90, 1),
                ('KEFIR-001', 'Кефир \"Био-Баланс\" 2.5%', '1 л', 'шт', 85.00, 300, 60, 2),
                ('SOUR-001', 'Сметана \"Простоквашино\" 20%', '400 г', 'шт', 129.90, 200, 40, 1),
                ('COTTAGE-001', 'Творог \"Савушкин\" 5%', '200 г', 'шт', 99.90, 250, 50, 2),
                ('BUTTER-001', 'Масло сливочное \"Крестьянское\" 82.5%', '180 г', 'шт', 149.90, 150, 30, 1),
                ('YOGURT-001', 'Йогурт \"Activia\" натуральный', '120 г', 'шт', 59.90, 400, 80, 2),
                ('CHEESE-001', 'Сыр \"Российский\" 50%', 'кг', 'шт', 599.90, 100, 20, 3),
                ('CHEESE-002', 'Сыр \"Маасдам\"', 'кг', 'шт', 699.90, 80, 15, 3),
                ('BUTTERMILK-001', 'Пахта', '1 л', 'шт', 69.90, 120, 25, 2),
                ('RYAZHENKA-001', 'Ряженка 4%', '1 л', 'шт', 89.90, 200, 40, 1),
                ('SOUR-002', 'Сметана \"Данон\" 15%', '300 г', 'шт', 109.90, 180, 35, 2),
                ('COTTAGE-002', 'Творог \"Просто\" 9%', '200 г', 'шт', 89.90, 220, 45, 1);
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
