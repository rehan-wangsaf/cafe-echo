<?php
require_once 'config.php';

// Ambil kategori dan menu dari database
$categories = $conn->query("SELECT * FROM categories ORDER BY nama");
$menu_items = $conn->query("SELECT m.*, c.nama as category_name FROM menu m 
                           LEFT JOIN categories c ON m.category_id = c.id 
                           WHERE m.status = 'tersedia' AND m.stok > 0 
                           ORDER BY c.nama, m.nama");

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo Cafe - Premium Coffee Experience</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/custom.css">
</head>

<body class="bg-stone-50 text-gray-800 font-sans antialiased overflow-x-hidden">
    
    <?php include 'includes/navbar_public.php'; ?>

    <section id="home" class="relative min-h-screen flex items-center pt-24 pb-12 overflow-hidden">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-amber-50/50 rounded-l-[100px] -z-10 hidden lg:block"></div>
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-orange-200/20 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-amber-200/20 rounded-full blur-3xl animate-blob animation-delay-2000"></div>

        <div class="container mx-auto px-6 max-w-7xl relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div class="inline-block px-4 py-1.5 bg-amber-100 text-amber-700 rounded-full text-xs font-bold uppercase tracking-wider mb-6 animate-fade-in-up">
                        ☕ Est. 2024 — Authentic Taste
                    </div>
                    <h1 class="font-serif text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 leading-[1.1] mb-6">
                        Seni Menikmati <br>
                        <span class="relative inline-block text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-orange-500">
                            Kopi Sejati
                            <svg class="absolute w-full h-3 -bottom-1 left-0 text-amber-200 -z-10" viewBox="0 0 100 10" preserveAspectRatio="none">
                                <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none" />
                            </svg>
                        </span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed max-w-md">
                        Rasakan kehangatan dalam setiap tegukan. Biji kopi pilihan Nusantara, diseduh dengan hati untuk menemani momen berhargamu.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#menu" class="btn-primary bg-gray-900 text-white px-8 py-4 rounded-full font-semibold hover:bg-amber-600 transition-all duration-300 shadow-lg hover:shadow-amber-lg hover:-translate-y-1">
                            Pesan Sekarang
                        </a>
                        <a href="#story" class="group flex items-center gap-3 px-6 py-4 font-semibold text-gray-900 hover:text-amber-600 transition-colors">
                            <span class="w-10 h-10 rounded-full border-2 border-gray-200 flex items-center justify-center group-hover:border-amber-600 group-hover:bg-amber-50 transition-all">
                                <i class="fas fa-arrow-down text-sm transform group-hover:translate-y-1 transition-transform"></i>
                            </span>
                            Explore
                        </a>
                    </div>
                    
                    <div class="mt-12 grid grid-cols-3 gap-6 border-t border-gray-200 pt-8">
                        <div class="text-center lg:text-left">
                            <h3 class="text-3xl font-bold text-amber-600 font-serif count-up" data-target="15">0</h3>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1 font-semibold">Varian Kopi</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <h3 class="text-3xl font-bold text-amber-600 font-serif count-up" data-target="1200">0</h3>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1 font-semibold">Pelanggan</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <h3 class="text-3xl font-bold text-amber-600 font-serif count-up" data-target="50">0</h3>
                            <p class="text-xs text-gray-500 uppercase tracking-wide mt-1 font-semibold">Menu Lezat</p>
                        </div>
                    </div>
                </div>

                <div class="relative hidden lg:block" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="200">
                    <div class="relative z-10 rounded-[40px] overflow-hidden shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-700 group">
                        <img src="https://images.unsplash.com/photo-1509042239860-f550ce710b93?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80" alt="Echo Cafe Interior" class="w-full h-[600px] object-cover transform group-hover:scale-105 transition-transform duration-1000">
                        
                        <div class="absolute bottom-8 left-8 right-8 bg-white/90 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-xl">
                                    <i class="fas fa-award"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">Kualitas Premium</h4>
                                    <p class="text-sm text-gray-600">100% Biji Arabika Lokal Terbaik</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-orange-100 rounded-full -z-10 mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="story" class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="relative order-2 md:order-1" data-aos="fade-up">
                    <div class="relative z-10 grid grid-cols-2 gap-4">
                        <img src="https://images.unsplash.com/photo-1497935586351-b67a49e012bf?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="rounded-2xl w-full h-64 object-cover shadow-lg transform translate-y-8">
                        <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80" class="rounded-2xl w-full h-64 object-cover shadow-lg">
                    </div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-20 bg-white p-5 rounded-full shadow-xl animate-bounce-slow">
                        <i class="fas fa-heart text-4xl text-amber-600"></i>
                    </div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-amber-50 rounded-full -z-10"></div>
                </div>

                <div class="order-1 md:order-2" data-aos="fade-up" data-aos-delay="200">
                    <span class="text-amber-600 font-bold uppercase tracking-widest text-sm mb-2 block">Tentang Kami</span>
                    <h2 class="font-serif text-4xl md:text-5xl font-bold text-gray-900 mb-6">Lebih dari Sekadar <br>Tempat Ngopi</h2>
                    <p class="text-gray-600 mb-6 leading-relaxed text-lg">
                        Echo Cafe lahir dari keinginan sederhana: menciptakan ruang di mana aroma kopi bisa membangkitkan kenangan dan menciptakan cerita baru. Kami percaya secangkir kopi yang baik berawal dari kepedulian.
                    </p>
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Setiap sudut kafe kami didesain untuk kenyamanan Anda, menjadikannya tempat sempurna untuk bekerja (WFC), berdiskusi, atau sekadar melamun sejenak dari hiruk pikuk kota.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex items-start gap-4 group p-4 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <i class="fas fa-wifi"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-lg">Super Cepat</h5>
                                <p class="text-sm text-gray-500">WiFi Gratis & Stabil</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 group p-4 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                                <i class="fas fa-couch"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-lg">Homey</h5>
                                <p class="text-sm text-gray-500">Suasana Nyaman</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-32 bg-fixed bg-center bg-cover relative" style="background-image: url('https://images.unsplash.com/photo-1447933601403-0c6688de566e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80');">
        <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-[2px]"></div>
        <div class="container mx-auto px-6 relative z-10 text-center text-white" data-aos="zoom-in">
            <i class="fas fa-quote-left text-5xl text-amber-500 mb-8 opacity-80 inline-block"></i>
            <h2 class="font-serif text-3xl md:text-5xl font-bold leading-snug max-w-4xl mx-auto mb-8">
                "Kopi adalah bahasa universal untuk kehangatan, inspirasi, dan persahabatan."
            </h2>
            <div class="w-24 h-1 bg-amber-500 mx-auto mb-4"></div>
            <p class="font-sans text-lg opacity-90 tracking-widest uppercase">Filosofi Echo Cafe</p>
        </div>
    </section>

    <!-- <section id="menu" class="py-24 bg-stone-50">
        <div class="container mx-auto px-6 max-w-7xl">
            <div class="text-center mb-16 max-w-2xl mx-auto" data-aos="fade-up">
                <span class="text-amber-600 font-bold tracking-widest text-sm uppercase mb-2 block">Menu Favorit</span>
                <h2 class="font-serif text-4xl font-bold text-gray-900 mb-4">Jelajahi Rasa Kami</h2>
                <p class="text-gray-600">Dari Espresso yang kuat hingga camilan manis yang menggugah selera, kami sajikan yang terbaik untuk Anda.</p>
            </div>

            <div class="flex justify-center gap-3 mb-12 flex-wrap" data-aos="fade-up" data-aos-delay="100">
                <button onclick="filterMenu('all')" class="filter-btn active px-6 py-2.5 rounded-full border-2 border-amber-600 bg-amber-600 text-white font-semibold transition-all hover:shadow-lg hover:-translate-y-1">
                    Semua
                </button>
                <?php 
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()): 
                ?>
                    <button onclick="filterMenu('<?= $cat['id'] ?>')" class="filter-btn px-6 py-2.5 rounded-full border-2 border-transparent bg-white text-gray-600 font-semibold hover:border-amber-200 hover:text-amber-600 hover:bg-amber-50 transition-all hover:shadow-lg hover:-translate-y-1">
                        <?= $cat['nama'] ?>
                    </button>
                <?php endwhile; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8" id="menu-grid">
                <?php while ($item = $menu_items->fetch_assoc()): ?>
                    <div class="menu-item group bg-white rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden flex flex-col h-full transform hover:-translate-y-2" 
                         data-category="<?= $item['category_id'] ?>"
                         data-aos="fade-up">
                        
                        <div class="relative h-60 overflow-hidden bg-gray-100">
                            <?php if ($item['gambar']): ?>
                                <img src="uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                            <?php else: ?>
                                <div class="w-full h-full flex flex-col items-center justify-center bg-stone-100 text-stone-400">
                                    <i class="fas fa-mug-hot text-4xl mb-2"></i>
                                    <span class="text-xs uppercase tracking-wide">No Image</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-amber-700 shadow-sm">
                                <?= $item['category_name'] ?>
                            </div>

                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center backdrop-blur-[1px]">
                                <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga'] ?>)" 
                                        class="bg-white text-gray-900 px-6 py-3 rounded-full font-bold transform translate-y-10 group-hover:translate-y-0 transition-transform duration-300 hover:bg-amber-600 hover:text-white flex items-center gap-2 shadow-xl">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>

                            <?php if ($item['stok'] < 5): ?>
                                <div class="absolute top-4 right-4 bg-red-500 text-white text-[10px] px-2 py-1 rounded-md font-bold shadow-sm uppercase tracking-wider">
                                    Sisa <?= $item['stok'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-6 flex flex-col flex-grow">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 font-serif group-hover:text-amber-600 transition-colors"><?= $item['nama'] ?></h3>
                            <p class="text-gray-500 text-sm line-clamp-2 mb-4 flex-grow"><?= $item['deskripsi'] ?: 'Menu spesial pilihan chef kami.' ?></p>
                            
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100 mt-auto">
                                <div>
                                    <p class="text-xs text-gray-400 mb-0.5">Harga</p>
                                    <p class="text-lg font-bold text-gray-900 font-serif"><?= formatRupiah($item['harga']) ?></p>
                                </div>
                                <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga'] ?>)" class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center text-stone-600 hover:bg-amber-600 hover:text-white transition-colors">
                                    <i class="fas fa-shopping-basket"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section> -->

    <?php include 'includes/footer_public.php'; ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/main.js"></script>
</body>
</html>