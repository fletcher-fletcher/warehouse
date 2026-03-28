<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$id = $_POST['id'] ?? 0;
$sku = $_POST['sku'];
$name = $_POST['name'];
$price = $_POST['price'];
$unit = $_POST['unit'];
$min_stock = $_POST['min_stock'] ?: 0;
$supplier_id = $_POST['supplier_id'] ?: null;

if ($id) {
    $stmt = $pdo->prepare("UPDATE products SET sku=?, name=?, price=?, unit=?, min_stock=?, supplier_id=? WHERE id=?");
    $stmt->execute([$sku, $name, $price, $unit, $min_stock, $supplier_id, $id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO products (sku, name, price, unit, min_stock, supplier_id) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$sku, $name, $price, $unit, $min_stock, $supplier_id]);
}

header('Location: products.php');