<?php
// Dapatkan nama file saat ini untuk set class 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-4 px-4 md:px-8">
    <div class="max-w-7xl mx-auto bg-white/80 backdrop-blur-md border border-white/20 rounded-full shadow-sm px-6 py-3 flex justify-between items-center transition-all" id="nav-container">
        <a href="index.php" class="flex items-center gap-2 group">
            <img src="assets/logo.png" alt="Logo" class="pl-5 h-14 w-auto">
        </a>

        <div class="hidden md:flex items-center gap-8 font-medium text-sm text-gray-600">
            <a href="index.php" class="hover:text-amber-600 transition-colors nav-link <?= $current_page == 'index.php' ? 'text-amber-600 font-bold' : '' ?>">Beranda</a>
            <!-- <a href="story.php" class="hover:text-amber-600 transition-colors nav-link <?= $current_page == 'story.php' ? 'text-amber-600 font-bold' : '' ?>">Cerita Kami</a> -->
            <a href="our_menu.php" class="hover:text-amber-600 transition-colors nav-link <?= $current_page == 'our_menu.php' ? 'text-amber-600 font-bold' : '' ?>">Menu</a>
        </div>

        <div class="flex items-center gap-4">
            <a href="cart.php" class="relative group bg-amber-900 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-amber-600 transition-all duration-300 flex items-center gap-2 shadow-lg hover:shadow-amber-lg">
                <i class="fas fa-shopping-bag"></i>
                <span class="hidden sm:inline">Keranjang</span>
                <span class="bg-white text-gray-900 min-w-[20px] h-5 rounded-full flex items-center justify-center text-xs font-bold px-1 transition-transform group-hover:scale-110" id="cart-count">
                    <?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>
                </span>
            </a>
        </div>
    </div>
</nav>