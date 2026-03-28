<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, s.name as supplier_name FROM products p 
        LEFT JOIN suppliers s ON p.supplier_id = s.id";
if ($search) {
    $sql .= " WHERE p.name LIKE '%$search%' OR p.sku LIKE '%$search%'";
}
$sql .= " ORDER BY p.name";
$products = $pdo->query($sql)->fetchAll();

$totalValue = 0;
foreach($products as $p) {
    $totalValue += $p['price'] * $p['stock'];
}

$lowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= min_stock AND min_stock > 0")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчеты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Всего товаров</h6>
                        <h3><?= count($products) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Общая стоимость</h6>
                        <h3><?= number_format($totalValue, 0, ',', ' ') ?> ₽</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6>Низкий остаток</h6>
                        <h3 class="text-warning"><?= $lowStock ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Остатки товаров</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <form method="GET" class="row">
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control" placeholder="Поиск..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Найти</button>
                        </div>
                    </form>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Артикул</th><th>Товар</th><th>Цена</th><th>Остаток</th><th>Стоимость</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= number_format($p['price'], 2) ?> ₽</td>
                            <td class="<?= $p['stock'] <= $p['min_stock'] && $p['min_stock'] > 0 ? 'text-danger' : '' ?>">
                                <?= $p['stock'] ?> <?= $p['unit'] ?>
                            </td>
                            <td><?= number_format($p['price'] * $p['stock'], 2) ?> ₽</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>