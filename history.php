<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$movements = $pdo->query("
    SELECT m.*, p.name as product_name, p.unit, u.name as user_name 
    FROM movements m 
    JOIN products p ON m.product_id = p.id 
    LEFT JOIN users u ON m.user_id = u.id 
    ORDER BY m.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История операций</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>История операций</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Дата</th><th>Тип</th><th>Товар</th><th>Кол-во</th><th>Цена</th><th>Сумма</th><th>Пользователь</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($movements as $m): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($m['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $m['type'] == 'receipt' ? 'success' : 'danger' ?>">
                                    <?= $m['type'] == 'receipt' ? 'Приход' : 'Расход' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($m['product_name']) ?></td>
                            <td><?= $m['quantity'] ?> <?= $m['unit'] ?></td>
                            <td><?= number_format($m['price'], 2) ?> ₽</td>
                            <td><?= number_format($m['quantity'] * $m['price'], 2) ?> ₽</td>
                            <td><?= htmlspecialchars($m['user_name'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>