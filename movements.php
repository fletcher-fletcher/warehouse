<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$type = $_GET['type'] ?? 'receipt';
$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $document_number = $_POST['document_number'] ?? '';
    $note = $_POST['note'] ?? '';
    
    if ($type == 'shipment') {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $stock = $stmt->fetchColumn();
        if ($stock < $quantity) {
            $message = 'Недостаточно товара! Доступно: ' . $stock;
        }
    }
    
    if (empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO movements (type, product_id, quantity, price, document_number, note, user_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$type, $product_id, $quantity, $price, $document_number, $note, $_SESSION['user_id']]);
        
        if ($type == 'receipt') {
            $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$quantity, $product_id]);
        } else {
            $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$quantity, $product_id]);
        }
        
        header('Location: history.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= $type == 'receipt' ? 'Приход' : 'Отгрузка' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5><?= $type == 'receipt' ? '📥 Приход товара' : '📤 Отгрузка товара' ?></h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-danger"><?= $message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Товар</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Выберите --</option>
                            <?php foreach($products as $p): ?>
                            <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                                <?= htmlspecialchars($p['sku'] . ' - ' . $p['name']) ?> (остаток: <?= $p['stock'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Количество</label>
                            <input type="number" name="quantity" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label>Цена</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Номер накладной</label>
                        <input type="text" name="document_number" class="form-control">
                    </div>
                    <div class="mt-3">
                        <label>Примечание</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-<?= $type == 'receipt' ? 'success' : 'danger' ?>">
                            <?= $type == 'receipt' ? 'Оформить приход' : 'Оформить отгрузку' ?>
                        </button>
                        <a href="history.php" class="btn btn-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    document.querySelector('select[name="product_id"]').addEventListener('change', function() {
        let price = this.options[this.selectedIndex].getAttribute('data-price');
        if (price && !document.querySelector('input[name="price"]').value) {
            document.querySelector('input[name="price"]').value = price;
        }
    });
    </script>
</body>
</html>