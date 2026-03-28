<?php
// init_db.php - автоматическое создание базы данных
$db_path = __DIR__ . '/database/database.sqlite';

// Создаем папку если нет
if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0777, true);
}

// Проверяем, есть ли уже база
$need_init = !file_exists($db_path) || filesize($db_path) == 0;

if ($need_init) {
    try {
        $pdo = new PDO("sqlite:" . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Читаем SQL файл
        $sql = file_get_contents(__DIR__ . '/database/init.sql');
        
        // Выполняем SQL
        $pdo->exec($sql);
        
        error_log("Database initialized successfully");
    } catch (PDOException $e) {
        error_log("Database init error: " . $e->getMessage());
    }
}
?>
