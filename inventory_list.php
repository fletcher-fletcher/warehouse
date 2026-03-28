<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$inventories = $pdo->query("SELECT * FROM inventories ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Инвентаризации</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>Инвентаризации</h5>
                <a href="inventory.php" class="btn btn-primary btn-sm">Новая</a>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr><th>Дата</th><th>Название</th><th>Действия</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($inventories as $i): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($i['date'])) ?></td>
                            <td><?= htmlspecialchars($i['name']) ?></td>
                            <td>
                                <a href="inventory_view.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-info">Просмотр</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>