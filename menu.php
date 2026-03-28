<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-warehouse me-2"></i>
            Складская система
            <small class="text-light ms-2" style="font-size: 12px;">
                ООО "Журавли торговля и логистика"
            </small>
        </a>
        <div class="d-flex">
            <span class="text-light me-3">
                <i class="fas fa-user me-1"></i> <?= $_SESSION['user_name'] ?? 'Пользователь' ?>
            </span>
            
            <a href="products.php" class="btn btn-outline-light btn-sm me-2">Товары</a>
            <a href="movements.php?type=receipt" class="btn btn-outline-success btn-sm me-2">Приход</a>
            <a href="movements.php?type=shipment" class="btn btn-outline-danger btn-sm me-2">Отгрузка</a>
            <a href="history.php" class="btn btn-outline-info btn-sm me-2">История</a>
            <a href="inventory.php" class="btn btn-outline-warning btn-sm me-2">Инвентаризация</a>
            <a href="reports.php" class="btn btn-outline-secondary btn-sm me-2">Отчеты</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Выход</a>
        </div>
    </div>
</nav>