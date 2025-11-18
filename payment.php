<?php
// payment.php - Halaman pembayaran
require_once 'config.php';

if (!isset($_GET['order'])) {
    redirect('index.php');
}

$order_number = cleanInput($_GET['order']);
$order = $conn->query("SELECT o.*, p.status as payment_status FROM orders o 
                       LEFT JOIN payments p ON o.id = p.order_id 
                       WHERE o.order_number = '$order_number'")->fetch_assoc();

if (!$order) {
    redirect('index.php');
}

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $order_id = $order['id'];
    $conn->query("UPDATE orders SET status = 'diproses' WHERE id = $order_id");
    $conn->query("UPDATE payments SET status = 'success' WHERE order_id = $order_id");
    redirect("confirmation.php?order={$order_number}");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <header class="bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold">☕ Echo Cafe - Pembayaran</h1>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <!-- Success Message -->
            <div class="bg-green-50 border-l-4 border-green-500 p-6 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-3xl text-green-500 mr-4"></i>
                    <div>
                        <h2 class="text-xl font-bold text-green-800">Pesanan Berhasil Dibuat!</h2>
                        <p class="text-green-700">No. Pesanan: <strong><?= $order['order_number'] ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Detail Pesanan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nama:</span>
                        <span class="font-semibold"><?= $order['nama_customer'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">No. HP:</span>
                        <span class="font-semibold"><?= $order['no_hp'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipe:</span>
                        <span class="font-semibold uppercase"><?= $order['tipe_order'] ?></span>
                    </div>
                    <?php if ($order['alamat']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Alamat:</span>
                        <span class="font-semibold text-right"><?= $order['alamat'] ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-xl pt-4 border-t">
                        <span class="font-bold">Total:</span>
                        <span class="font-bold text-amber-600"><?= formatRupiah($order['total_harga']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                
                <?php if ($order['payment_method'] === 'qris'): ?>
                    <div class="text-center">
                        <div class="bg-gray-100 p-8 rounded-lg mb-4 inline-block">
                            <!-- Placeholder untuk QR Code -->
                            <div class="w-64 h-64 bg-white border-4 border-amber-600 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-qrcode text-6xl text-amber-600 mb-2"></i>
                                    <p class="text-sm text-gray-600">Scan QR Code</p>
                                    <p class="text-xs text-gray-500 mt-2">dengan aplikasi<br>pembayaran Anda</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">Scan QR Code di atas untuk melakukan pembayaran</p>
                        <p class="text-sm text-gray-500">Total: <strong class="text-xl text-amber-600"><?= formatRupiah($order['total_harga']) ?></strong></p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <i class="fas fa-money-bill-wave text-6xl text-amber-600 mb-4"></i>
                        <h4 class="text-2xl font-bold text-gray-800 mb-2">Cash on Delivery (COD)</h4>
                        <p class="text-gray-600 mb-4">Pembayaran dilakukan saat pesanan tiba</p>
                        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 text-left">
                            <p class="text-amber-800 font-semibold">Siapkan uang pas:</p>
                            <p class="text-3xl font-bold text-amber-600 mt-2"><?= formatRupiah($order['total_harga']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Confirmation Form -->
            <form method="POST" class="bg-white rounded-lg shadow-md p-6">
                <div class="text-center mb-6">
                    <i class="fas fa-info-circle text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Pembayaran</h3>
                    <p class="text-gray-600">
                        <?php if ($order['payment_method'] === 'qris'): ?>
                            Setelah melakukan pembayaran via QRIS, klik tombol di bawah untuk melanjutkan
                        <?php else: ?>
                            Klik tombol di bawah untuk konfirmasi pesanan Anda
                        <?php endif; ?>
                    </p>
                </div>
                <button type="submit" name="confirm_payment" class="w-full bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                    <i class="fas fa-check-circle"></i> Lanjutkan ke Konfirmasi Pesanan
                </button>
            </form>
        </div>
    </div>
</body>
</html>