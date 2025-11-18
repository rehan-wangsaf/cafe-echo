<?php
// add_to_cart.php - Menambahkan item ke keranjang
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = (int)$_POST['menu_id'];
    $menu_name = cleanInput($_POST['menu_name']);
    $price = (float)$_POST['price'];
    
    // Cek apakah item sudah ada di cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $menu_id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    
    // Jika belum ada, tambahkan item baru
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $menu_id,
            'name' => $menu_name,
            'price' => $price,
            'quantity' => 1
        ];
    }
    
    echo json_encode([
        'success' => true,
        'cart_count' => count($_SESSION['cart'])
    ]);
    exit();
}
?>