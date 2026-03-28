<?php
require_once 'config.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $items = [];
    foreach ($_POST['items'] as $item) {
        $diff = $item['actual'] - $item['system'];
        $items[] = [
            'product_id' => $item['product_id'],
            'system' => $item['system'],
            'actual' => $item['actual'],
            'diff' => $diff
        ];
    }
    
    $stmt = $pdo->prepare("INSERT INTO inventories (name, date, items, user_id) VALUES (?,?,?,?)");
    $stmt->execute([$name, $date, json_encode($items, JSON_UNESCAPED_UNICODE), $_SESSION['user_id']]);
    header('Location: inventory_list.php');
    exit;
}

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Инвентаризация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5>Проведение инвентаризации</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Название</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Дата</label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>Товар</th><th>Системный</th><th>Фактический</th><th>Разница</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= $p['stock'] ?></td>
                                    <td>
                                        <input type="hidden" name="items[<?= $p['id'] ?>][product_id]" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="items[<?= $p['id'] ?>][system]" value="<?= $p['stock'] ?>">
                                        <input type="number" name="items[<?= $p['id'] ?>][actual]" class="form-control actual" style="width:100px" value="<?= $p['stock'] ?>">
                                    </td>
                                    <td class="diff">0</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.querySelectorAll('.actual').forEach(input => {
        input.addEventListener('input', function() {
            let system = this.closest('tr').querySelector('td:first-child + td').innerText;
            let diff = parseInt(this.value) - parseInt(system);
            this.closest('tr').querySelector('.diff').innerText = diff;
        });
    });
    </script>
</body>
</html>