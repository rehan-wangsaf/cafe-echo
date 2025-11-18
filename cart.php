<?php
// cart.php - Halaman keranjang belanja
require_once 'config.php';

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        if ($qty > 0) {
            $_SESSION['cart'][$index]['quantity'] = (int)$qty;
        }
    }
    redirect('cart.php');
}

// Handle remove item
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    redirect('cart.php');
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">☕ Echo Cafe</h1>
                    <p class="text-amber-100">Keranjang Belanja</p>
                </div>
                <a href="index.php" class="bg-white text-amber-700 px-4 py-2 rounded-lg font-semibold hover:bg-amber-50 transition">
                    <i class="fas fa-arrow-left"></i> Kembali ke Menu
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-20">
                <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Keranjang Kosong</h2>
                <p class="text-gray-600 mb-6">Yuk, pesan menu favoritmu!</p>
                <a href="index.php" class="bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-700 transition inline-block">
                    Lihat Menu
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Pesanan Anda</h2>
                        <form method="POST">
                            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                <div class="flex items-center justify-between border-b pb-4 mb-4">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-800"><?= $item['name'] ?></h3>
                                        <p class="text-amber-600 font-semibold"><?= formatRupiah($item['price']) ?></p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center border rounded-lg">
                                            <button type="button" onclick="updateQty(<?= $index ?>, -1)" class="px-3 py-1 hover:bg-gray-100">-</button>
                                            <input type="number" name="quantity[<?= $index ?>]" id="qty-<?= $index ?>" 
                                                   value="<?= $item['quantity'] ?>" min="1" 
                                                   class="w-16 text-center border-x py-1">
                                            <button type="button" onclick="updateQty(<?= $index ?>, 1)" class="px-3 py-1 hover:bg-gray-100">+</button>
                                        </div>
                                        <span class="font-bold text-gray-800 w-28 text-right">
                                            <?= formatRupiah($item['price'] * $item['quantity']) ?>
                                        </span>
                                        <a href="cart.php?remove=<?= $index ?>" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" name="update_cart" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition mt-4">
                                Update Keranjang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan</h2>
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold"><?= formatRupiah($total) ?></span>
                            </div>
                            <div class="flex justify-between text-xl font-bold border-t pt-3">
                                <span>Total</span>
                                <span class="text-amber-600"><?= formatRupiah($total) ?></span>
                            </div>
                        </div>
                        <a href="checkout.php" class="block w-full bg-amber-600 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                            Lanjut ke Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateQty(index, change) {
            const input = document.getElementById(`qty-${index}`);
            let newValue = parseInt(input.value) + change;
            if (newValue < 1) newValue = 1;
            input.value = newValue;
        }
    </script>
</body>
</html>