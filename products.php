<?php
require_once 'config.php';
redirectIfNotLoggedIn();
requireAdmin();

// Удаление товара
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: products.php');
    exit;
}

$search = $_GET['search'] ?? '';
$sql = "SELECT p.*, s.name as supplier_name FROM products p 
        LEFT JOIN suppliers s ON p.supplier_id = s.id";
if ($search) {
    $sql .= " WHERE p.name LIKE '%$search%' OR p.sku LIKE '%$search%'";
}
$sql .= " ORDER BY p.name";
$products = $pdo->query($sql)->fetchAll();

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Товары</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>Товары</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productModal" onclick="clearForm()">+ Добавить</button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <form method="GET" class="row g-2">
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
                        <tr>
                            <th>Артикул</th>
                            <th>Наименование</th>
                            <th>Цена</th>
                            <th>Остаток</th>
                            <th>Поставщик</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= number_format($p['price'], 2) ?> ₽</td>
                            <td class="<?= $p['stock'] <= $p['min_stock'] && $p['min_stock'] > 0 ? 'text-danger fw-bold' : '' ?>">
                                <?= $p['stock'] ?> <?= $p['unit'] ?>
                            </td>
                            <td><?= htmlspecialchars($p['supplier_name'] ?? '-') ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editProduct(<?= $p['id'] ?>)">Изм.</button>
                                <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">Уд.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Модальное окно -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Товар</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="save_product.php">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="product_id">
                        <div class="mb-2">
                            <label>Артикул</label>
                            <input type="text" name="sku" id="sku" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Наименование</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Цена</label>
                                <input type="number" step="0.01" name="price" id="price" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Ед. изм.</label>
                                <select name="unit" id="unit" class="form-select">
                                    <option>шт</option><option>кг</option><option>л</option><option>уп</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label>Мин. остаток</label>
                                <input type="number" name="min_stock" id="min_stock" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Поставщик</label>
                                <select name="supplier_id" id="supplier_id" class="form-select">
                                    <option value="">-- Нет --</option>
                                    <?php foreach($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function clearForm() {
        document.getElementById('product_id').value = '';
        document.getElementById('sku').value = '';
        document.getElementById('name').value = '';
        document.getElementById('price').value = '';
        document.getElementById('min_stock').value = '';
        document.getElementById('supplier_id').value = '';
    }
    
    function editProduct(id) {
        fetch('get_product.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                document.getElementById('product_id').value = data.id;
                document.getElementById('sku').value = data.sku;
                document.getElementById('name').value = data.name;
                document.getElementById('price').value = data.price;
                document.getElementById('unit').value = data.unit;
                document.getElementById('min_stock').value = data.min_stock;
                document.getElementById('supplier_id').value = data.supplier_id;
                new bootstrap.Modal(document.getElementById('productModal')).show();
            });
    }
    </script>
</body>
</html>
