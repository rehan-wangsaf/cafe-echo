<?php
require_once 'config.php';

// Ambil menu berdasarkan kategori
$categories = $conn->query("SELECT * FROM categories ORDER BY nama");
$menu_items = $conn->query("SELECT m.*, c.nama as category_name FROM menu m 
                           LEFT JOIN categories c ON m.category_id = c.id 
                           WHERE m.status = 'tersedia' AND m.stok > 0 
                           ORDER BY c.nama, m.nama");

// Inisialisasi cart jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo Cafe - Order Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .margin-hero {
            margin-top: 110px;
        }

        .navbar-asli {
            z-index: 100;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="navbar-asli fixed top-0 right-0 left-0 bg-yellow-400/50 text-white shadow-lg mx-10 mt-3 rounded-full">
        <div class="container mx-auto px-10 py-0">
            <div class="flex justify-between items-center">
                <div>
                    <img src="assets/logo.png" alt="logo" class="w-20 m-0 p-0">
                </div>
                <div class="flex gap-4">
                    <a href="#" class=" px-3 py-2 font-semibold">
                        <p class="hover:underline transition">Menu</p>
                    </a>
                    <a href="#" class="px-3 py-2 font-semibold">
                        <p class="hover:underline transition">About Us</p>
                    </a>
                    <a href="cart.php" class="bg-white text-amber-700 px-4 py-2 rounded-lg font-semibold hover:bg-amber-50 transition">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs ml-1" id="cart-count">
                            <?= count($_SESSION['cart']) ?>
                        </span>
                    </a>
                    <!-- <a href="login.php" class="bg-amber-800 text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-900 transition">
                        <i class="fas fa-user"></i> Login Staff
                    </a> -->
                </div>
            </div>
        </div>
    </header>

    <section class="relative w-full h-[450px] sm:h-[500px] lg:h-[600px] overflow-hidden margin-hero">

        <img src="assets/foto_kopi.png"
            alt="Latar belakang aneka kopi"
            class="absolute inset-0 w-full h-full object-cover object-center" />

        <div class="relative container mx-auto px-4 h-full">
            <div class="flex h-full items-center justify-center lg:justify-end">

                <div class="w-full max-w-md text-center lg:text-left mt-10">

                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
                        Kopi Nikmat, Momen Sempurna
                    </h2>

                    <p class="text-lg text-gray-600 mb-8">
                        Teman setia untuk kerja, santai, atau kumpul seru.
                    </p>

                    <div class="flex justify-center lg:justify-start gap-4">
                        <a href="#"
                            class="px-6 py-2 border border-gray-800 text-gray-800 font-medium rounded-full hover:bg-gray-800 hover:text-white transition-colors duration-300">
                            learn more
                        </a>
                        <a href="#"
                            class="px-6 py-2 border border-gray-800 text-gray-800 font-medium rounded-full hover:bg-gray-800 hover:text-white transition-colors duration-300">
                            order now
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Menu Kami</h2>

        <!-- Filter Kategori -->
        <div class="flex justify-center gap-4 mb-8 flex-wrap">
            <button onclick="filterMenu('all')" class="filter-btn bg-amber-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-amber-700 transition">
                Semua
            </button>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <button onclick="filterMenu('<?= $cat['id'] ?>')" class="filter-btn bg-gray-200 text-gray-700 px-6 py-2 rounded-full font-semibold hover:bg-gray-300 transition">
                    <?= $cat['nama'] ?>
                </button>
            <?php endwhile; ?>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="menu-grid">
            <?php while ($item = $menu_items->fetch_assoc()): ?>
                <div class="menu-item bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition" data-category="<?= $item['category_id'] ?>">
                    <div class="h-48 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center">
                        <?php if ($item['gambar']): ?>
                            <img src="uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-utensils text-6xl text-amber-400"></i>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">
                            <?= $item['category_name'] ?>
                        </span>
                        <h3 class="text-lg font-bold text-gray-800 mt-2"><?= $item['nama'] ?></h3>
                        <p class="text-sm text-gray-600 mb-3"><?= $item['deskripsi'] ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-amber-600">
                                <?= formatRupiah($item['harga']) ?>
                            </span>
                            <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga'] ?>)"
                                class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Stok: <?= $item['stok'] ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-2xl font-bold mb-2">Echo Cafe</h3>
            <p class="text-gray-400">Jl. Sekolah No. 123, Kota Anda</p>
            <p class="text-gray-400">Buka: Senin - Sabtu, 08:00 - 20:00</p>
            <p class="text-gray-400 mt-4">© 2025 Echo Cafe. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Filter menu by category
        function filterMenu(categoryId) {
            const items = document.querySelectorAll('.menu-item');
            const buttons = document.querySelectorAll('.filter-btn');

            buttons.forEach(btn => {
                btn.classList.remove('bg-amber-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            event.target.classList.remove('bg-gray-200', 'text-gray-700');
            event.target.classList.add('bg-amber-600', 'text-white');

            items.forEach(item => {
                if (categoryId === 'all' || item.dataset.category === categoryId) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Add to cart
        function addToCart(menuId, menuName, price) {
            fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `menu_id=${menuId}&menu_name=${encodeURIComponent(menuName)}&price=${price}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cart-count').textContent = data.cart_count;
                        alert(`${menuName} berhasil ditambahkan ke keranjang!`);
                    }
                });
        }
    </script>
</body>

</html>