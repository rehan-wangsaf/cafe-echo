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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 px-4 sm:px-6 lg:px-10 pt-4">
        <div class="glass-effect rounded-full shadow-amber-lg border border-amber-100 max-w-7xl mx-auto">
            <div class="container mx-auto px-6 sm:px-8 py-3">
                <div class="flex justify-between items-center">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-coffee text-white text-xl"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="font-bold text-xl gradient-text">Echo Cafe</h1>
                            <p class="text-xs text-gray-600">Premium Coffee & Snacks</p>
                        </div>
                    </div>
                    
                    <!-- Menu -->
                    <div class="flex items-center gap-3 sm:gap-4">
                        <a href="#menu" class="hidden md:block px-4 py-2 text-gray-700 hover:text-amber-600 font-medium transition-colors">
                            Menu
                        </a>
                        <a href="#about" class="hidden md:block px-4 py-2 text-gray-700 hover:text-amber-600 font-medium transition-colors">
                            About
                        </a>
                        <a href="cart.php" class="btn-ripple bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 sm:px-6 py-2.5 rounded-full font-semibold hover:shadow-amber-lg transform hover:scale-105 transition-all duration-300 flex items-center gap-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="hidden sm:inline">Cart</span>
                            <span class="bg-white text-amber-600 px-2.5 py-0.5 rounded-full text-sm font-bold pulse-badge" id="cart-count">
                                <?= count($_SESSION['cart']) ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center px-4 pt-24 pb-12">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,<svg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><path d=&quot;M54.627 0l.83.828-1.415 1.415L51.8 0h2.827zM5.373 0l-.83.828L5.96 2.243 8.2 0H5.374zM48.97 0l3.657 3.657-1.414 1.414L46.143 0h2.828zM11.03 0L7.372 3.657 8.787 5.07 13.857 0H11.03zm32.284 0L49.8 6.485 48.384 7.9l-7.9-7.9h2.83zM16.686 0L10.2 6.485 11.616 7.9l7.9-7.9h-2.83zm20.97 0l9.315 9.314-1.414 1.414L34.828 0h2.83zM22.344 0L13.03 9.314l1.414 1.414L25.172 0h-2.83zM32 0l12.142 12.142-1.414 1.414L30 .828 17.272 13.556l-1.414-1.414L28 0h4zM.284 0l28 28-1.414 1.414L0 2.544V0h.284zM0 5.373l25.456 25.455-1.414 1.415L0 8.2V5.374zm0 5.656L22.627 33.86l-1.414 1.414L0 13.86v-2.83zm0 5.656l19.8 19.8-1.415 1.413L0 19.514v-2.83zm0 5.657l16.97 16.97-1.414 1.415L0 25.172v-2.83zM0 28l14.142 14.142-1.414 1.414L0 30.828V28zm0 5.657L11.314 44.97 9.9 46.386l-9.9-9.9v-2.828zm0 5.657L8.485 47.8 7.07 49.212 0 42.143v-2.83zm0 5.657l5.657 5.657-1.414 1.415L0 47.8v-2.83zM0 51.172l2.828 2.83-1.414 1.414L0 53.8v-2.627zm0 5.656l.142.143H0v-.142zM60 5.373L34.544 30.828l1.414 1.415L60 8.2V5.374zm0 5.656L37.373 33.86l1.414 1.414L60 13.86v-2.83zm0 5.656L40.2 33.86l1.415 1.413L60 19.514v-2.83zm0 5.657L43.03 38.1l1.414 1.415L60 25.172v-2.83zM60 28L45.858 42.142l1.414 1.414L60 30.828V28zm0 5.657L48.686 44.97l1.415 1.415 9.9-9.9v-2.828zm0 5.657L51.515 47.8l1.414 1.415 7.07-7.072v-2.828zm0 5.657L54.343 53.8l1.414 1.415L60 50.657v-2.83zM60 51.172l-2.828 2.83 1.414 1.414L60 53.8v-2.627zm0 5.656l-.142.143H60v-.142z&quot; fill=&quot;%23d97706&quot; fill-opacity=&quot;1&quot; fill-rule=&quot;evenodd&quot;/></svg>');"></div>
        </div>
        
        <div class="container mx-auto grid md:grid-cols-2 gap-12 items-center relative z-10">
            <!-- Content -->
            <div class="text-center md:text-left space-y-6">
                <div class="inline-block">
                    <span class="bg-amber-100 text-amber-700 px-4 py-2 rounded-full text-sm font-semibold float-animation">
                        ☕ Freshly Brewed Daily
                    </span>
                </div>
                
                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-800 leading-tight">
                    Kopi Nikmat,<br>
                    <span class="gradient-text">Momen Sempurna</span>
                </h2>
                
                <p class="text-lg text-gray-600 max-w-lg">
                    Temukan kebahagiaan dalam setiap tegukan. Teman setia untuk kerja, santai, atau kumpul seru bersama keluarga.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                    <a href="#menu" class="btn-ripple bg-gradient-to-r from-amber-500 to-orange-500 text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-amber-lg transform hover:scale-105 transition-all duration-300 inline-flex items-center justify-center gap-2">
                        <i class="fas fa-utensils"></i>
                        Order Now
                    </a>
                    <a href="#about" class="btn-ripple bg-white border-2 border-amber-500 text-amber-600 px-8 py-4 rounded-full font-bold text-lg hover:bg-amber-50 transform hover:scale-105 transition-all duration-300 inline-flex items-center justify-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        Learn More
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 pt-8">
                    <div class="text-center">
                        <div class="text-3xl font-bold gradient-text">500+</div>
                        <div class="text-sm text-gray-600">Happy Customers</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold gradient-text">4.9</div>
                        <div class="text-sm text-gray-600">Rating</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold gradient-text">20+</div>
                        <div class="text-sm text-gray-600">Menu Items</div>
                    </div>
                </div>
            </div>
            
            <!-- Image -->
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-400 to-orange-500 rounded-3xl transform rotate-6 opacity-20"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-transform duration-500">
                    <img src="assets/foto_kopi.png" alt="Coffee" class="w-full h-auto object-cover">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                        <div class="flex items-center gap-3 text-white">
                            <div class="bg-white/20 backdrop-blur-sm rounded-full p-3">
                                <i class="fas fa-star text-yellow-400"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lg">Premium Quality</div>
                                <div class="text-sm opacity-90">100% Arabica Beans</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-20 px-4">
        <div class="container mx-auto max-w-7xl">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <span class="bg-amber-100 text-amber-700 px-4 py-2 rounded-full text-sm font-semibold inline-block mb-4">
                    Our Menu
                </span>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-800 mb-4">
                    Pilihan <span class="gradient-text">Istimewa</span> Kami
                </h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Dari espresso yang kuat hingga latte yang lembut, kami punya semuanya
                </p>
            </div>

            <!-- Filter Kategori -->
            <div class="flex justify-center gap-3 mb-12 flex-wrap">
                <button onclick="filterMenu('all')" class="filter-btn bg-amber-600 text-white shadow-amber px-6 py-3 rounded-full font-semibold hover:shadow-amber-lg transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-th-large mr-2"></i>Semua
                </button>
                <?php 
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()): 
                ?>
                    <button onclick="filterMenu('<?= $cat['id'] ?>')" class="filter-btn bg-white text-gray-700 px-6 py-3 rounded-full font-semibold hover:shadow-lg border border-gray-200 transform hover:scale-105 transition-all duration-300">
                        <?= $cat['nama'] ?>
                    </button>
                <?php endwhile; ?>
            </div>

            <!-- Menu Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="menu-grid">
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                    <div class="menu-item card-hover bg-white rounded-2xl shadow-lg overflow-hidden group" data-category="<?= $item['category_id'] ?>">
                        <!-- Image -->
                        <div class="menu-card-overlay h-56 bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center relative overflow-hidden">
                            <?php if ($item['gambar']): ?>
                                <img src="uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <i class="fas fa-utensils text-7xl text-amber-400 group-hover:scale-110 transition-transform duration-500"></i>
                            <?php endif; ?>
                            
                            <!-- Category Badge -->
                            <div class="absolute top-4 left-4">
                                <span class="glass-effect text-xs bg-white/90 text-amber-700 px-3 py-1.5 rounded-full font-semibold shadow-md">
                                    <?= $item['category_name'] ?>
                                </span>
                            </div>
                            
                            <!-- Stock Badge -->
                            <?php if ($item['stok'] < 10): ?>
                                <div class="absolute top-4 right-4">
                                    <span class="bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-semibold">
                                        <i class="fas fa-exclamation-circle"></i> Stok <?= $item['stok'] ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-amber-600 transition-colors">
                                <?= $item['nama'] ?>
                            </h3>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?= $item['deskripsi'] ?>
                            </p>
                            
                            <!-- Price & Button -->
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div>
                                    <div class="text-sm text-gray-500 mb-1">Harga</div>
                                    <div class="text-2xl font-bold gradient-text">
                                        <?= formatRupiah($item['harga']) ?>
                                    </div>
                                </div>
                                <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga'] ?>)"
                                        class="btn-ripple bg-gradient-to-r from-amber-500 to-orange-500 text-white w-12 h-12 rounded-full hover:shadow-amber-lg transform hover:scale-110 transition-all duration-300 flex items-center justify-center">
                                    <i class="fas fa-plus text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 bg-white">
        <div class="container mx-auto max-w-7xl">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-2xl hover:shadow-amber-lg transition-all duration-300 card-hover">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-amber">
                        <i class="fas fa-shipping-fast text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Fast Delivery</h3>
                    <p class="text-gray-600">Pengiriman cepat langsung ke lokasi Anda dalam waktu 30 menit</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl hover:shadow-amber-lg transition-all duration-300 card-hover">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-amber">
                        <i class="fas fa-award text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Premium Quality</h3>
                    <p class="text-gray-600">100% biji kopi pilihan terbaik dari perkebunan lokal</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl hover:shadow-amber-lg transition-all duration-300 card-hover">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-amber">
                        <i class="fas fa-heart text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Made with Love</h3>
                    <p class="text-gray-600">Setiap cangkir dibuat dengan penuh perhatian dan cinta</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-amber-900 to-orange-900 text-white py-12 px-4">
        <div class="container mx-auto max-w-7xl">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <!-- About -->
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-coffee text-white text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold">Echo Cafe</h3>
                    </div>
                    <p class="text-amber-100 mb-4 max-w-md">
                        Menyajikan kopi terbaik dengan cita rasa istimewa untuk menemani hari-hari Anda.
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-all">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-all">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-all">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="font-bold text-lg mb-4">Kontak</h4>
                    <ul class="space-y-3 text-amber-100">
                        <li class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Sekolah No. 123</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone"></i>
                            <span>0812-3456-7890</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-envelope"></i>
                            <span>info@echocafe.com</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Hours -->
                <div>
                    <h4 class="font-bold text-lg mb-4">Jam Buka</h4>
                    <ul class="space-y-3 text-amber-100">
                        <li>Senin - Jumat: 08:00 - 22:00</li>
                        <li>Sabtu: 08:00 - 23:00</li>
                        <li>Minggu: 10:00 - 20:00</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/20 pt-8 text-center text-amber-100">
                <p>&copy; 2025 Echo Cafe. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>