<?php
require_once 'config.php';
redirectIfNotLoggedIn();

// Статистика
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalValue = $pdo->query("SELECT SUM(price * stock) FROM products")->fetchColumn();
$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= min_stock AND min_stock > 0")->fetchColumn();
$todayReceipt = $pdo->query("SELECT SUM(quantity) FROM movements WHERE type='receipt' AND DATE(created_at)=DATE('now')")->fetchColumn();
$todayShipment = $pdo->query("SELECT SUM(quantity) FROM movements WHERE type='shipment' AND DATE(created_at)=DATE('now')")->fetchColumn();

// Товары с низким остатком
$lowStockProducts = $pdo->query("SELECT * FROM products WHERE stock <= min_stock AND min_stock > 0 LIMIT 5")->fetchAll();

// Последние движения
$recentMovements = $pdo->query("
    SELECT m.*, p.name as product_name, u.name as user_name 
    FROM movements m 
    JOIN products p ON m.product_id = p.id 
    LEFT JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Складская система - Дашборд</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6>Всего товаров</h6>
                        <h2><?= $totalProducts ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6>Общая стоимость</h6>
                        <h2><?= number_format($totalValue, 0, ',', ' ') ?> ₽</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h6>Низкий остаток</h6>
                        <h2><?= $lowStock ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6>Приход сегодня</h6>
                        <h2><?= $todayReceipt ?: 0 ?></h2>
                        <small>Расход: <?= $todayShipment ?: 0 ?></small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-exclamation-triangle"></i> Товары с низким остатком
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Артикул</th><th>Товар</th><th>Остаток</th><th>Мин.</th> </tr>
                            </thead>
                            <tbody>
                                <?php foreach($lowStockProducts as $p): ?>
                                 <tr>
                                    <td><?= htmlspecialchars($p['sku']) ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td class="text-danger"><?= $p['stock'] ?> <?= $p['unit'] ?></td>
                                    <td><?= $p['min_stock'] ?></td>
                                 </tr>
                                <?php endforeach; ?>
                                <?php if(empty($lowStockProducts)): ?>
                                 <tr><td colspan="4" class="text-center">Все товары в норме</td></tr>
                                <?php endif; ?>
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-history"></i> Последние операции
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                 <tr><th>Дата</th><th>Тип</th><th>Товар</th><th>Кол-во</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($recentMovements as $m): ?>
                                 <tr>
                                    <td><?= date('d.m H:i', strtotime($m['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $m['type'] == 'receipt' ? 'success' : 'danger' ?>">
                                            <?= $m['type'] == 'receipt' ? 'Приход' : 'Расход' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($m['product_name']) ?></td>
                                    <td><?= $m['quantity'] ?></td>
                                 </tr>
                                <?php endforeach; ?>
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Информация о компании -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i> 
                    ООО "Журавли торговля и логистика"
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Получаем данные компании
                $stmt = $pdo->query("SELECT * FROM company WHERE id = 1");
                $company = $stmt->fetch();
                
                // Если в таблице нет данных, выводим из файла
                if (!$company) {
                    $company = [
                        'inn' => '7536089490',
                        'kpp' => '753601001',
                        'ogrn' => '1207700359525',
                        'address' => '672014, Забайкальский край, г.о. город Чита, г Чита, ул 5-я Малая, д. 10'
                    ];
                }
                ?>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                             <tr><th style="width: 120px;">ИНН:</th><td><?= htmlspecialchars($company['inn'] ?? '—') ?></td></tr>
                             <tr><th>КПП:</th><td><?= htmlspecialchars($company['kpp'] ?? '—') ?></td></tr>
                             <tr><th>ОГРН:</th><td><?= htmlspecialchars($company['ogrn'] ?? '—') ?></td></tr>
                             <tr><th>Адрес:</th><td><?= htmlspecialchars($company['address'] ?? '—') ?></td></tr>
                         </table>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            <strong>Финансовые показатели 2024 г.:</strong><br>
                            <div class="row mt-2">
                                <div class="col-6">
                                    📊 Выручка:<br>
                                    <span class="fw-bold">2 050 729 тыс. руб.</span>
                                </div>
                                <div class="col-6">
                                    💰 Чистая прибыль:<br>
                                    <span class="fw-bold">18 401 тыс. руб.</span>
                                </div>
                                <div class="col-6 mt-2">
                                    📦 Запасы:<br>
                                    <span class="fw-bold">29 240 тыс. руб.</span>
                                </div>
                                <div class="col-6 mt-2">
                                    💵 Денежные средства:<br>
                                    <span class="fw-bold">58 109 тыс. руб.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
