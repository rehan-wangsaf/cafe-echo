<?php
require_once 'config.php';

// Redirect jika cart kosong
if (empty($_SESSION['cart'])) {
    redirect('index.php');
}

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = cleanInput($_POST['nama']);
    $no_hp = cleanInput($_POST['no_hp']);
    $tipe_order = cleanInput($_POST['tipe_order']);
    $alamat = $tipe_order === 'delivery' ? cleanInput($_POST['alamat']) : '';
    $payment_method = cleanInput($_POST['payment_method']);
    $catatan = cleanInput($_POST['catatan']);
    
    // Generate order number
    $order_number = generateOrderNumber();
    
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, nama_customer, no_hp, alamat, tipe_order, payment_method, total_harga, catatan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssds", $order_number, $nama, $no_hp, $alamat, $tipe_order, $payment_method, $total, $catatan);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        
        // Insert order items
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt2->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
            $stmt2->execute();
            
            // Update stok
            $conn->query("UPDATE menu SET stok = stok - {$item['quantity']} WHERE id = {$item['id']}");
        }
        
        // Insert payment record
        $stmt3 = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount) VALUES (?, ?, ?)");
        $stmt3->bind_param("isd", $order_id, $payment_method, $total);
        $stmt3->execute();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to payment page
        redirect("payment.php?order={$order_number}");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <header class="bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold">☕ Echo Cafe - Checkout</h1>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12">
        <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Data Customer -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Pemesan -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-user text-amber-600"></i> Informasi Pemesan
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="Masukkan nama Anda">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">No. HP/WhatsApp *</label>
                            <input type="tel" name="no_hp" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>

                <!-- Tipe Order -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-shopping-bag text-amber-600"></i> Tipe Pesanan
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe_order" value="delivery" class="peer hidden" required onchange="toggleAlamat(true)">
                            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-amber-500 peer-checked:border-amber-600 peer-checked:bg-amber-50 transition">
                                <i class="fas fa-truck text-4xl text-amber-600 mb-2"></i>
                                <h3 class="font-bold text-gray-800">Delivery</h3>
                                <p class="text-sm text-gray-600">Diantar ke lokasi</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe_order" value="dine_in" class="peer hidden" required onchange="toggleAlamat(false)">
                            <div class="border-2 border-gray-300 rounded-lg p-6 text-center hover:border-amber-500 peer-checked:border-amber-600 peer-checked:bg-amber-50 transition">
                                <i class="fas fa-store text-4xl text-amber-600 mb-2"></i>
                                <h3 class="font-bold text-gray-800">Dine In</h3>
                                <p class="text-sm text-gray-600">Makan di tempat</p>
                            </div>
                        </label>
                    </div>
                    <div id="alamat-field" class="mt-4 hidden">
                        <label class="block text-gray-700 font-semibold mb-2">Alamat Pengiriman *</label>
                        <textarea name="alamat" rows="3" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                  placeholder="Masukkan alamat lengkap untuk pengiriman"></textarea>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-credit-card text-amber-600"></i> Metode Pembayaran
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="qris" class="peer hidden" required>
                            <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-amber-500 peer-checked:border-amber-600 peer-checked:bg-amber-50 transition">
                                <i class="fas fa-qrcode text-3xl text-amber-600 mb-2"></i>
                                <h3 class="font-bold text-gray-800">QRIS</h3>
                                <p class="text-xs text-gray-600">Scan & Pay</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method" value="cod" class="peer hidden" required>
                            <div class="border-2 border-gray-300 rounded-lg p-4 text-center hover:border-amber-500 peer-checked:border-amber-600 peer-checked:bg-amber-50 transition">
                                <i class="fas fa-money-bill-wave text-3xl text-amber-600 mb-2"></i>
                                <h3 class="font-bold text-gray-800">COD</h3>
                                <p class="text-xs text-gray-600">Bayar di Tempat</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-sticky-note text-amber-600"></i> Catatan (Opsional)
                    </h2>
                    <textarea name="catatan" rows="3" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Contoh: Pedas sedang, tanpa bawang, dll"></textarea>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Pesanan</h2>
                    <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">
                                    <?= $item['name'] ?> x<?= $item['quantity'] ?>
                                </span>
                                <span class="font-semibold">
                                    <?= formatRupiah($item['price'] * $item['quantity']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="border-t pt-4 mb-6">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total</span>
                            <span class="text-amber-600"><?= formatRupiah($total) ?></span>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                        <i class="fas fa-check-circle"></i> Buat Pesanan
                    </button>
                    <a href="cart.php" class="block text-center text-gray-600 mt-4 hover:text-gray-800">
                        <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleAlamat(show) {
            const alamatField = document.getElementById('alamat-field');
            const alamatTextarea = alamatField.querySelector('textarea');
            if (show) {
                alamatField.classList.remove('hidden');
                alamatTextarea.required = true;
            } else {
                alamatField.classList.add('hidden');
                alamatTextarea.required = false;
            }
        }
    </script>
</body>
</html>