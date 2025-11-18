<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$pending_orders = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending' OR status = 'diproses'")->fetch_assoc()['total'];
?>
<aside class="w-64 bg-gradient-to-b from-amber-700 to-amber-800 text-white flex flex-col">
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-2">☕ Echo Cafe</h1>
        <p class="text-amber-200 text-sm"><?= ucfirst($_SESSION['role']) ?> Panel</p>
    </div>
    <nav class="flex-1 mt-6">
        <a href="dashboard.php" class="flex items-center px-6 py-3 <?= $current_page === 'dashboard.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
            <i class="fas fa-home mr-3"></i> Dashboard
        </a>
        <a href="orders.php" class="flex items-center px-6 py-3 <?= $current_page === 'orders.php' || $current_page === 'order_detail.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
            <i class="fas fa-shopping-bag mr-3"></i> Kelola Pesanan
            <?php if ($pending_orders > 0): ?>
                <span class="ml-auto bg-red-500 text-white px-2 py-1 rounded-full text-xs">
                    <?= $pending_orders ?>
                </span>
            <?php endif; ?>
        </a>
        <?php if (isAdmin()): ?>
            <a href="menu.php" class="flex items-center px-6 py-3 <?= $current_page === 'menu.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
                <i class="fas fa-utensils mr-3"></i> Kelola Menu
            </a>
            <a href="categories.php" class="flex items-center px-6 py-3 <?= $current_page === 'categories.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
                <i class="fas fa-list mr-3"></i> Kategori
            </a>
            <a href="users.php" class="flex items-center px-6 py-3 <?= $current_page === 'users.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
                <i class="fas fa-users mr-3"></i> Pengguna
            </a>
            <a href="reports.php" class="flex items-center px-6 py-3 <?= $current_page === 'reports.php' ? 'bg-amber-900 border-l-4 border-white' : 'hover:bg-amber-600' ?> transition">
                <i class="fas fa-chart-bar mr-3"></i> Laporan
            </a>
        <?php endif; ?>
    </nav>
    <div class="p-6">
        <div class="bg-amber-900 rounded-lg p-4 mb-4">
            <p class="text-sm text-amber-200">Login sebagai:</p>
            <p class="font-semibold"><?= $_SESSION['nama'] ?></p>
        </div>
        <a href="logout.php" class="flex items-center justify-center bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</aside>