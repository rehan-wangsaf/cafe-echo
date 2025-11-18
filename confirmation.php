<?php
// confirmation.php - Halaman konfirmasi setelah pembayaran
require_once 'config.php';

if (!isset($_GET['order'])) {
    redirect('index.php');
}

$order_number = cleanInput($_GET['order']);
$order = $conn->query("SELECT * FROM orders WHERE order_number = '$order_number'")->fetch_assoc();

if (!$order) {
    redirect('index.php');
}

$items = $conn->query("SELECT oi.*, m.nama FROM order_items oi 
                       JOIN menu m ON oi.menu_id = m.id 
                       WHERE oi.order_id = {$order['id']}");

// Handle order confirmation by customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_received'])) {
    $order_id = $order['id'];
    $conn->query("UPDATE orders SET status = 'dikonfirmasi', confirmed_at = NOW() WHERE id = $order_id");
    $order['status'] = 'dikonfirmasi';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <header class="bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold">☕ Echo Cafe - Konfirmasi</h1>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <?php if ($order['status'] === 'dikonfirmasi'): ?>
                <!-- Completed -->
                <div class="bg-green-50 border-2 border-green-500 rounded-lg p-8 text-center mb-6">
                    <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">Terima Kasih!</h2>
                    <p class="text-green-700">Pesanan telah dikonfirmasi diterima</p>
                </div>
            <?php else: ?>
                <!-- Waiting Confirmation -->
                <div class="bg-blue-50 border-2 border-blue-500 rounded-lg p-8 text-center mb-6">
                    <i class="fas fa-clock text-6xl text-blue-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-blue-800 mb-2">Pesanan Sedang Diproses</h2>
                    <p class="text-blue-700">Harap konfirmasi setelah pesanan diterima</p>
                </div>
            <?php endif; ?>

            <!-- Order Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Status Pesanan</h3>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-gray-600">No. Pesanan:</span>
                    <span class="font-bold text-lg"><?= $order['order_number'] ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-4 py-2 rounded-full font-semibold
                        <?= $order['status'] === 'dikonfirmasi' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                        <?= ucfirst($order['status']) ?>
                    </span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Detail Pesanan</h3>
                <div class="space-y-3">
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-700"><?= $item['nama'] ?> x<?= $item['quantity'] ?></span>
                            <span class="font-semibold"><?= formatRupiah($item['subtotal']) ?></span>
                        </div>
                    <?php endwhile; ?>
                    <div class="border-t pt-3 flex justify-between text-xl font-bold">
                        <span>Total:</span>
                        <span class="text-amber-600"><?= formatRupiah($order['total_harga']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Confirmation Button -->
            <?php if ($order['status'] !== 'dikonfirmasi'): ?>
                <form method="POST" class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-700 mb-4 text-center">
                        <i class="fas fa-exclamation-circle text-amber-500"></i>
                        Klik tombol di bawah setelah pesanan Anda diterima
                    </p>
                    <button type="submit" name="confirm_received" 
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                        <i class="fas fa-check"></i> Konfirmasi Pesanan Diterima
                    </button>
                </form>
            <?php else: ?>
                <a href="index.php" class="block text-center bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>