<?php
require_once 'config.php';
requireLogin();

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending' OR status = 'diproses'")->fetch_assoc()['total'];
$today_revenue = $conn->query("SELECT SUM(total_harga) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'dibatalkan'")->fetch_assoc()['total'] ?? 0;
$total_menu = $conn->query("SELECT COUNT(*) as total FROM menu")->fetch_assoc()['total'];

// Get recent orders
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <aside class="w-64 bg-gradient-to-b from-amber-700 to-amber-800 text-white">
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-2">☕ Echo Cafe</h1>
                <p class="text-amber-200 text-sm"><?= ucfirst($_SESSION['role']) ?> Panel</p>
            </div>
            <nav class="mt-6">
                <a href="dashboard.php" class="flex items-center px-6 py-3 bg-amber-900 border-l-4 border-white">
                    <i class="fas fa-home mr-3"></i> Dashboard
                </a>
                <a href="orders.php" class="flex items-center px-6 py-3 hover:bg-amber-600 transition">
                    <i class="fas fa-shopping-bag mr-3"></i> Kelola Pesanan
                    <?php if ($pending_orders > 0): ?>
                        <span class="ml-auto bg-red-500 text-white px-2 py-1 rounded-full text-xs">
                            <?= $pending_orders ?>
                        </span>
                    <?php endif; ?>
                </a>
                <?php if (isAdmin()): ?>
                    <a href="menu.php" class="flex items-center px-6 py-3 hover:bg-amber-600 transition">
                        <i class="fas fa-utensils mr-3"></i> Kelola Menu
                    </a>
                    <a href="categories.php" class="flex items-center px-6 py-3 hover:bg-amber-600 transition">
                        <i class="fas fa-list mr-3"></i> Kategori
                    </a>
                    <a href="users.php" class="flex items-center px-6 py-3 hover:bg-amber-600 transition">
                        <i class="fas fa-users mr-3"></i> Pengguna
                    </a>
                    <a href="reports.php" class="flex items-center px-6 py-3 hover:bg-amber-600 transition">
                        <i class="fas fa-chart-bar mr-3"></i> Laporan
                    </a>
                <?php endif; ?>
            </nav>
            <div class="absolute bottom-0 w-64 p-6">
                <div class="bg-amber-900 rounded-lg p-4 mb-4">
                    <p class="text-sm text-amber-200">Login sebagai:</p>
                    <p class="font-semibold"><?= $_SESSION['nama'] ?></p>
                </div>
                <a href="logout.php" class="flex items-center justify-center bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm">
                <div class="px-8 py-4">
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                    <p class="text-gray-600">Selamat datang, <?= $_SESSION['nama'] ?>!</p>
                </div>
            </header>

            <!-- Content -->
            <div class="p-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pesanan</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_orders ?></p>
                            </div>
                            <div class="bg-blue-100 p-4 rounded-full">
                                <i class="fas fa-shopping-bag text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pesanan Pending</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $pending_orders ?></p>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-full">
                                <i class="fas fa-clock text-2xl text-yellow-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pendapatan Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-800"><?= formatRupiah($today_revenue) ?></p>
                            </div>
                            <div class="bg-green-100 p-4 rounded-full">
                                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Menu</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_menu ?></p>
                            </div>
                            <div class="bg-purple-100 p-4 rounded-full">
                                <i class="fas fa-utensils text-2xl text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b">
                        <h3 class="text-xl font-bold text-gray-800">Pesanan Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No. Pesanan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold"><?= $order['order_number'] ?></td>
                                        <td class="px-6 py-4">
                                            <div><?= $order['nama_customer'] ?></div>
                                            <div class="text-sm text-gray-500"><?= $order['no_hp'] ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold uppercase">
                                                <?= $order['tipe_order'] ?>
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
                                               class="text-amber-600 hover:text-amber-700 font-semibold">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t">
                        <a href="orders.php" class="text-amber-600 hover:text-amber-700 font-semibold">
                            Lihat Semua Pesanan <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>