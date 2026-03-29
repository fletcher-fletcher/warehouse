<?php
require_once 'config.php';
redirectIfNotLoggedIn();
requireAdmin();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        $message = "Пользователь добавлен";
    }
    
    if ($_POST['action'] === 'change_role') {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['new_role'];
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $user_id]);
        $message = "Роль изменена";
    }
    
    if ($_POST['action'] === 'delete') {
        $user_id = $_POST['user_id'];
        // Нельзя удалить первого администратора (id=1)
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != 1");
        $stmt->execute([$user_id]);
        $message = "Пользователь удален";
    }
}

$users = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление пользователями</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-users me-2"></i>Управление пользователями</h5>
            </div>
            <div class="card-body">
                <?php if(isset($message)): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Роли:</strong>
                    <span class="badge bg-primary ms-2">Администратор</span> - полный доступ
                    <span class="badge bg-secondary ms-2">Кладовщик</span> - только складские операции
                </div>
                
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if($u['role'] == 'admin'): ?>
                                    <span class="badge bg-primary">👑 Администратор</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">📦 Кладовщик</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="new_role" class="form-select form-select-sm d-inline w-auto">
                                        <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Админ</option>
                                        <option value="warehouse" <?= $u['role'] == 'warehouse' ? 'selected' : '' ?>>Кладовщик</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-warning">Изменить</button>
                                </form>
                                
                                <?php if($u['id'] != 1): ?>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Удалить пользователя?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <hr>
                <h6>➕ Добавить нового пользователя</h6>
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Имя" required>
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-2">
                        <input type="password" name="password" class="form-control" placeholder="Пароль" value="password" required>
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select">
                            <option value="warehouse">Кладовщик</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">+ Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
