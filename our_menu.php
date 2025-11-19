<?php
require_once 'config.php';

// 1. Ambil semua kategori
$categories_result = $conn->query("SELECT * FROM categories ORDER BY nama");
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat;
}

// 2. Ambil semua menu
$menu_items_result = $conn->query("SELECT m.*, c.nama as category_name FROM menu m 
                                  LEFT JOIN categories c ON m.category_id = c.id 
                                  WHERE m.status = 'tersedia' AND m.stok > 0 
                                  ORDER BY c.nama, m.nama");

// 3. Kelompokkan menu berdasarkan category_id untuk memudahkan loop
$menu_by_category = [];
while ($item = $menu_items_result->fetch_assoc()) {
    $menu_by_category[$item['category_id']][] = $item;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/custom.css">
    <style>
        /* Custom styles specific to match the reference image */
        .menu-card-clean:hover {
            transform: translateY(-5px);
        }
        .btn-outline-custom {
            border: 1px solid #d97706; /* Amber 600 */
            color: #d97706;
            background: white;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background: #d97706;
            color: white;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.2);
        }
        .category-header {
            border-bottom: 2px solid #e5e7eb; /* Gray 200 */
        }
    </style>
</head>

<body class="bg-white text-gray-800 font-sans antialiased overflow-x-hidden">
    
    <?php include 'includes/navbar_public.php'; ?>
    
    <div class="h-24"></div> <section id="menu" class="py-12 bg-white min-h-screen">
        <div class="container mx-auto px-6 max-w-6xl">
            
            <div class="text-center mb-16" data-aos="fade-up">
                 <h1 class="font-serif text-4xl font-bold text-gray-900">Menu Kami</h1>
            </div>

            <?php foreach ($categories as $cat): ?>
                <?php 
                // Cek jika kategori ini punya menu, jika tidak skip
                if (!isset($menu_by_category[$cat['id']])) continue;
                $items = $menu_by_category[$cat['id']];
                ?>

                <div class="mb-20" data-aos="fade-up">
                    <div class="flex flex-col md:flex-row justify-between items-end category-header pb-3 mb-10">
                        <h2 class="font-serif text-3xl font-bold text-gray-800"><?= $cat['nama'] ?></h2>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-6 gap-y-12">
                        <?php foreach ($items as $item): ?>
                            <div class="menu-card-clean flex flex-col items-center text-center group transition-transform duration-300">
                                
                                <div class="relative w-full aspect-[4/5] mb-4 flex items-center justify-center overflow-visible">
                                    <?php if ($item['gambar']): ?>
                                        <img src="uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-full h-full object-contain drop-shadow-lg transform group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-32 h-32 rounded-full bg-amber-50 flex items-center justify-center text-amber-300">
                                            <i class="fas fa-coffee text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="flex justify-center gap-2 mb-3 min-h-[24px]">
                                    <?php if ($item['stok'] < 10): ?>
                                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-500 flex items-center justify-center text-xs animate-pulse" title="Limited Stock">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs" title="Best Seller">
                                            <i class="fas fa-leaf"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center text-xs">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <h3 class="text-gray-900 font-bold text-base mb-4 leading-tight min-h-[40px]">
                                    <?= $item['nama'] ?>
                                </h3>

                                <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nama']) ?>', <?= $item['harga'] ?>)" 
                                        class="btn-outline-custom w-32 py-1.5 rounded-lg text-xs font-bold uppercase tracking-widest mb-3">
                                    ADD
                                </button>

                                <p class="text-gray-900 font-bold text-sm">
                                    <?= formatRupiah($item['harga']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($menu_by_category)): ?>
                <div class="text-center py-20">
                    <i class="fas fa-mug-hot text-6xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500">Menu sedang disiapkan.</p>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <?php include 'includes/footer_public.php'; ?>
</body>
</html>