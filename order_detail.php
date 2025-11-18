<?php
// order_detail.php - Detail pesanan
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = (int)$_GET['id'];
$order = $conn->query("SELECT o.*, u.nama as processed_by_name, p.status as payment_status 
                       FROM orders o 
                       LEFT JOIN users u ON o.processed_by = u.id 
                       LEFT JOIN payments p ON o.id = p.order_id 
                       WHERE o.id = $order_id")->fetch_assoc();

if (!$order) {
    redirect('orders.php');
}

$items = $conn->query("SELECT oi.*, m.nama FROM order_items oi 
                       JOIN menu m ON oi.menu_id = m.id 
                       WHERE oi.order_id = $order_id");

// Handle status update
if (isset($_POST['update_status'])) {
    $status = cleanInput($_POST['status']);
    $user_id = $_SESSION['user_id'];
    $conn->query("UPDATE orders SET status = '$status', processed_by = $user_id WHERE id = $order_id");
    redirect("order_detail.php?id=$order_id");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Detail Pesanan</h2>
                        <p class="text-gray-600"><?= $order['order_number'] ?></p>
                    </div>
                    <a href="orders.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </header>

            <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Order Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Customer Info -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">
                                <i class="fas fa-user text-amber-600"></i> Informasi Customer
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Nama:</p>
                                    <p class="font-semibold"><?= $order['nama_customer'] ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">No. HP:</p>
                                    <p class="font-semibold"><?= $order['no_hp'] ?></p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Alamat:</p>
                                    <p class="font-semibold"><?= $order['alamat'] ?: '-' ?></p>
                                </div>
                                <?php if ($order['catatan']): ?>
                                <div class="col-span-2">
                                    <p class="text-sm text-gray-600">Catatan:</p>
                                    <p class="font-semibold"><?= $order['catatan'] ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">
                                <i class="fas fa-shopping-bag text-amber-600"></i> Item Pesanan
                            </h3>
                            <table class="w-full">
                                <thead class="border-b">
                                    <tr>
                                        <th class="text-left py-2">Menu</th>
                                        <th class="text-center py-2">Qty</th>
                                        <th class="text-right py-2">Harga</th>
                                        <th class="text-right py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items->fetch_assoc()): ?>
                                        <tr class="border-b">
                                            <td class="py-3"><?= $item['nama'] ?></td>
                                            <td class="py-3 text-center"><?= $item['quantity'] ?></td>
                                            <td class="py-3 text-right"><?= formatRupiah($item['harga']) ?></td>
                                            <td class="py-3 text-right font-semibold"><?= formatRupiah($item['subtotal']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <tr class="font-bold text-lg">
                                        <td colspan="3" class="py-3 text-right">Total:</td>
                                        <td class="py-3 text-right text-amber-600"><?= formatRupiah($order['total_harga']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sidebar Info -->
                    <div class="space-y-6">
                        <!-- Status -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Status Pesanan</h3>
                            <form method="POST">
                                <select name="status" class="w-full px-4 py-2 border rounded-lg mb-4">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="diproses" <?= $order['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                    <option value="selesai" <?= $order['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    <option value="dikonfirmasi" <?= $order['status'] === 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                    <option value="dibatalkan" <?= $order['status'] === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                                <button type="submit" name="update_status" class="w-full bg-amber-600 text-white py-2 rounded-lg hover:bg-amber-700 transition">
                                    Update Status
                                </button>
                            </form>
                        </div>

                        <!-- Payment Info -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Pembayaran</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Metode:</span>
                                    <span class="font-semibold uppercase"><?= $order['payment_method'] ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">
                                        <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Info -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Info Lainnya</h3>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-gray-600">Waktu Pesan:</p>
                                    <p class="font-semibold"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Tipe Order:</p>
                                    <p class="font-semibold uppercase"><?= $order['tipe_order'] ?></p>
                                </div>
                                <?php if ($order['processed_by_name']): ?>
                                <div>
                                    <p class="text-gray-600">Diproses oleh:</p>
                                    <p class="font-semibold"><?= $order['processed_by_name'] ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>