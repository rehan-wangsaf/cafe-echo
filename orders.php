<?php
// orders.php - Halaman kelola pesanan
require_once 'config.php';
requireLogin();

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = cleanInput($_POST['status']);
    $user_id = $_SESSION['user_id'];
    
    $conn->query("UPDATE orders SET status = '$status', processed_by = $user_id WHERE id = $order_id");
    redirect('orders.php');
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = $filter !== 'all' ? "WHERE o.status = '$filter'" : '';

$orders = $conn->query("SELECT o.*, u.nama as processed_by_name 
                        FROM orders o 
                        LEFT JOIN users u ON o.processed_by = u.id 
                        $where 
                        ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar (sama seperti dashboard) -->
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Kelola Pesanan</h2>
                        <p class="text-gray-600">Daftar semua pesanan</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="dashboard.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </header>

            <div class="p-8">
                <!-- Filter -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Filter Status:</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="orders.php?filter=all" class="px-4 py-2 rounded-lg <?= $filter === 'all' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Semua
                        </a>
                        <a href="orders.php?filter=pending" class="px-4 py-2 rounded-lg <?= $filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Pending
                        </a>
                        <a href="orders.php?filter=diproses" class="px-4 py-2 rounded-lg <?= $filter === 'diproses' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Diproses
                        </a>
                        <a href="orders.php?filter=selesai" class="px-4 py-2 rounded-lg <?= $filter === 'selesai' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Selesai
                        </a>
                        <a href="orders.php?filter=dikonfirmasi" class="px-4 py-2 rounded-lg <?= $filter === 'dikonfirmasi' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Dikonfirmasi
                        </a>
                        <a href="orders.php?filter=dibatalkan" class="px-4 py-2 rounded-lg <?= $filter === 'dibatalkan' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
                            Dibatalkan
                        </a>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No. Pesanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pembayaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($order = $orders->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold"><?= $order['order_number'] ?></td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold"><?= $order['nama_customer'] ?></div>
                                            <div class="text-sm text-gray-500"><?= $order['no_hp'] ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold uppercase">
                                                <?= $order['tipe_order'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold uppercase">
                                                <?= $order['payment_method'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-amber-600"><?= formatRupiah($order['total_harga']) ?></td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $status_colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'diproses' => 'bg-blue-100 text-blue-800',
                                                'selesai' => 'bg-green-100 text-green-800',
                                                'dikonfirmasi' => 'bg-purple-100 text-purple-800',
                                                'dibatalkan' => 'bg-red-100 text-red-800'
                                            ];
                                            ?>
                                            <span class="px-2 py-1 rounded text-xs font-semibold <?= $status_colors[$order['status']] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="order_detail.php?id=<?= $order['id'] ?>" 
                                               class="bg-amber-600 text-white px-3 py-1 rounded hover:bg-amber-700 transition text-sm">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>