<?php
require_once 'config.php';
requireLogin();
requireAdmin();

// Date filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Revenue statistics
$revenue_query = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status != 'dibatalkan' THEN total_harga ELSE 0 END) as total_revenue,
    SUM(CASE WHEN status = 'dikonfirmasi' THEN total_harga ELSE 0 END) as confirmed_revenue,
    SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as cancelled_orders,
    AVG(CASE WHEN status != 'dibatalkan' THEN total_harga ELSE NULL END) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$revenue_stats = $conn->query($revenue_query)->fetch_assoc();

// Orders by status
$status_query = "SELECT status, COUNT(*) as count, SUM(total_harga) as total
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY status";
$status_stats = $conn->query($status_query);

// Orders by type
$type_query = "SELECT tipe_order, COUNT(*) as count, SUM(total_harga) as total
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND status != 'dibatalkan'
    GROUP BY tipe_order";
$type_stats = $conn->query($type_query);

// Payment methods
$payment_query = "SELECT payment_method, COUNT(*) as count, SUM(total_harga) as total
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND status != 'dibatalkan'
    GROUP BY payment_method";
$payment_stats = $conn->query($payment_query);

// Top selling menu
$menu_query = "SELECT m.nama, m.harga, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date' AND o.status != 'dibatalkan'
    GROUP BY oi.menu_id
    ORDER BY total_sold DESC
    LIMIT 10";
$top_menu = $conn->query($menu_query);

// Daily revenue
$daily_query = "SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_harga) as revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND status != 'dibatalkan'
    GROUP BY DATE(created_at)
    ORDER BY date DESC";
$daily_stats = $conn->query($daily_query);

// Category sales
$category_query = "SELECT c.nama, COUNT(oi.id) as items_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN categories c ON m.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date' AND o.status != 'dibatalkan'
    GROUP BY c.id
    ORDER BY revenue DESC";
$category_stats = $conn->query($category_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm">
                <div class="px-8 py-4">
                    <h2 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h2>
                    <p class="text-gray-600">Analisis dan statistik penjualan</p>
                </div>
            </header>

            <div class="p-8">
                <!-- Date Filter -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-gray-700 font-semibold mb-2">Dari Tanggal</label>
                            <input type="date" name="start_date" value="<?= $start_date ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-gray-700 font-semibold mb-2">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="<?= $end_date ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <button type="submit" class="bg-amber-600 text-white px-6 py-2 rounded-lg hover:bg-amber-700 transition">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </form>
                </div>

                <!-- Revenue Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 text-sm font-semibold">Total Pendapatan</h3>
                            <i class="fas fa-money-bill-wave text-2xl text-green-500"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800"><?= formatRupiah($revenue_stats['total_revenue']) ?></p>
                        <p class="text-xs text-gray-500 mt-2">Semua pesanan</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 text-sm font-semibold">Pendapatan Terkonfirmasi</h3>
                            <i class="fas fa-check-circle text-2xl text-purple-500"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800"><?= formatRupiah($revenue_stats['confirmed_revenue']) ?></p>
                        <p class="text-xs text-gray-500 mt-2">Pesanan dikonfirmasi</p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 text-sm font-semibold">Total Pesanan</h3>
                            <i class="fas fa-shopping-bag text-2xl text-blue-500"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800"><?= $revenue_stats['total_orders'] ?></p>
                        <p class="text-xs text-gray-500 mt-2">
                            <span class="text-red-600"><?= $revenue_stats['cancelled_orders'] ?> dibatalkan</span>
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 text-sm font-semibold">Rata-rata Pesanan</h3>
                            <i class="fas fa-chart-line text-2xl text-amber-500"></i>
                        </div>
                        <p class="text-3xl font-bold text-gray-800"><?= formatRupiah($revenue_stats['avg_order_value']) ?></p>
                        <p class="text-xs text-gray-500 mt-2">Per transaksi</p>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Orders by Status -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-pie text-amber-600"></i> Pesanan per Status
                        </h3>
                        <div class="space-y-3">
                            <?php 
                            $status_stats->data_seek(0);
                            $status_colors = [
                                'pending' => 'bg-yellow-500',
                                'diproses' => 'bg-blue-500',
                                'selesai' => 'bg-green-500',
                                'dikonfirmasi' => 'bg-purple-500',
                                'dibatalkan' => 'bg-red-500'
                            ];
                            while ($stat = $status_stats->fetch_assoc()): 
                                $percentage = ($stat['count'] / $revenue_stats['total_orders']) * 100;
                            ?>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-semibold capitalize"><?= $stat['status'] ?></span>
                                        <span class="text-gray-600"><?= $stat['count'] ?> pesanan (<?= number_format($percentage, 1) ?>%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="<?= $status_colors[$stat['status']] ?> h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1"><?= formatRupiah($stat['total']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Orders by Type -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-chart-bar text-amber-600"></i> Tipe Pesanan
                        </h3>
                        <div class="space-y-4">
                            <?php 
                            $total_valid = $revenue_stats['total_orders'] - $revenue_stats['cancelled_orders'];
                            while ($stat = $type_stats->fetch_assoc()): 
                                $percentage = ($stat['count'] / $total_valid) * 100;
                            ?>
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <p class="font-semibold capitalize"><?= $stat['tipe_order'] ?></p>
                                            <p class="text-sm text-gray-600"><?= $stat['count'] ?> pesanan</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-amber-600"><?= formatRupiah($stat['total']) ?></p>
                                            <p class="text-xs text-gray-500"><?= number_format($percentage, 1) ?>%</p>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-amber-500 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods & Category Sales -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Payment Methods -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-credit-card text-amber-600"></i> Metode Pembayaran
                        </h3>
                        <div class="space-y-3">
                            <?php while ($stat = $payment_stats->fetch_assoc()): 
                                $percentage = ($stat['count'] / $total_valid) * 100;
                            ?>
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-semibold uppercase"><?= $stat['payment_method'] ?></p>
                                        <p class="text-sm text-gray-600"><?= $stat['count'] ?> transaksi</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-800"><?= formatRupiah($stat['total']) ?></p>
                                        <p class="text-xs text-gray-500"><?= number_format($percentage, 1) ?>%</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Category Sales -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-list text-amber-600"></i> Penjualan per Kategori
                        </h3>
                        <div class="space-y-3">
                            <?php while ($stat = $category_stats->fetch_assoc()): ?>
                                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-semibold"><?= $stat['nama'] ?></p>
                                        <p class="text-sm text-gray-600"><?= $stat['items_sold'] ?> item terjual</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-amber-600"><?= formatRupiah($stat['revenue']) ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Selling Menu -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-trophy text-amber-600"></i> Menu Terlaris
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rank</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Menu</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Harga</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Terjual</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php 
                                $rank = 1;
                                while ($item = $top_menu->fetch_assoc()): 
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <?php if ($rank <= 3): ?>
                                                <span class="text-2xl">
                                                    <?php
                                                    $medals = ['🥇', '🥈', '🥉'];
                                                    echo $medals[$rank - 1];
                                                    ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="font-semibold text-gray-600">#<?= $rank ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 font-semibold"><?= $item['nama'] ?></td>
                                        <td class="px-4 py-3 text-gray-600"><?= formatRupiah($item['harga']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                <?= $item['total_sold'] ?> pcs
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-bold text-amber-600"><?= formatRupiah($item['total_revenue']) ?></td>
                                    </tr>
                                <?php 
                                    $rank++;
                                endwhile; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daily Revenue -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-calendar-day text-amber-600"></i> Pendapatan Harian
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah Pesanan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Total Pendapatan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($day = $daily_stats->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold">
                                            <?= date('l, d F Y', strtotime($day['date'])) ?>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                                <?= $day['orders'] ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-bold text-amber-600"><?= formatRupiah($day['revenue']) ?></td>
                                        <td class="px-4 py-3 text-gray-600"><?= formatRupiah($day['revenue'] / $day['orders']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        @media print {
            aside, header button, .no-print {
                display: none !important;
            }
            main {
                margin: 0 !important;
            }
        }
    </style>
</body>
</html>