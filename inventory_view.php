<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM inventories WHERE id = ?");
$stmt->execute([$id]);
$inv = $stmt->fetch();

$items = json_decode($inv['items'], true);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр инвентаризации</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5><?= htmlspecialchars($inv['name']) ?></h5>
            </div>
            <div class="card-body">
                <p><strong>Дата:</strong> <?= date('d.m.Y', strtotime($inv['date'])) ?></p>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Товар</th><th>Системный</th><th>Фактический</th><th>Разница</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): 
                            $prod = $pdo->query("SELECT name FROM products WHERE id = " . $item['product_id'])->fetch();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($prod['name'] ?? 'Удален') ?></td>
                            <td><?= $item['system'] ?></td>
                            <td><?= $item['actual'] ?></td>
                            <td class="<?= $item['diff'] != 0 ? 'fw-bold text-danger' : '' ?>"><?= $item['diff'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="inventory_list.php" class="btn btn-secondary">Назад</a>
            </div>
        </div>
    </div>
</body>
</html>