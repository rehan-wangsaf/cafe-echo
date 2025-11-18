<?php
require_once 'config.php';
requireLogin();
requireAdmin();

// Handle Add/Edit Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = cleanInput($_POST['nama']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $category_id = (int)$_POST['category_id'];
    $stok = (int)$_POST['stok'];
    $status = cleanInput($_POST['status']);
    
    // Handle image upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $gambar = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar);
        }
    }
    
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = (int)$_POST['id'];
        $update_gambar = $gambar ? ", gambar = '$gambar'" : '';
        $conn->query("UPDATE menu SET nama = '$nama', deskripsi = '$deskripsi', harga = $harga, 
                     category_id = $category_id, stok = $stok, status = '$status' $update_gambar WHERE id = $id");
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO menu (nama, deskripsi, harga, category_id, gambar, stok, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisis", $nama, $deskripsi, $harga, $category_id, $gambar, $stok, $status);
        $stmt->execute();
    }
    redirect('menu.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM menu WHERE id = $id");
    redirect('menu.php');
}

// Get menu items
$menu_items = $conn->query("SELECT m.*, c.nama as category_name FROM menu m 
                           LEFT JOIN categories c ON m.category_id = c.id 
                           ORDER BY c.nama, m.nama");

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY nama");

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM menu WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm">
                <div class="px-8 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Kelola Menu</h2>
                        <p class="text-gray-600">Tambah, edit, atau hapus menu</p>
                    </div>
                    <button onclick="toggleModal()" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                        <i class="fas fa-plus"></i> Tambah Menu
                    </button>
                </div>
            </header>

            <div class="p-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Gambar</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($item = $menu_items->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <?php if ($item['gambar']): ?>
                                                <img src="uploads/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-16 h-16 object-cover rounded">
                                            <?php else: ?>
                                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold"><?= $item['nama'] ?></div>
                                            <div class="text-sm text-gray-500"><?= substr($item['deskripsi'], 0, 50) ?>...</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                                <?= $item['category_name'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-amber-600"><?= formatRupiah($item['harga']) ?></td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="font-semibold <?= $item['stok'] < 10 ? 'text-red-600' : 'text-gray-800' ?>">
                                                <?= $item['stok'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs font-semibold 
                                                <?= $item['status'] === 'tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="menu.php?edit=<?= $item['id'] ?>" onclick="toggleModal()" 
                                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition text-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="menu.php?delete=<?= $item['id'] ?>" 
                                                   onclick="return confirm('Yakin ingin menghapus menu ini?')" 
                                                   class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition text-sm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Add/Edit -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">
                    <?= $edit_data ? 'Edit Menu' : 'Tambah Menu Baru' ?>
                </h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Nama Menu *</label>
                        <input type="text" name="nama" required 
                               value="<?= $edit_data['nama'] ?? '' ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Kategori *</label>
                        <select name="category_id" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            <option value="">Pilih Kategori</option>
                            <?php 
                            $categories->data_seek(0);
                            while ($cat = $categories->fetch_assoc()): 
                            ?>
                                <option value="<?= $cat['id'] ?>" <?= ($edit_data['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= $cat['nama'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Harga *</label>
                        <input type="number" name="harga" required min="0" step="1000" 
                               value="<?= $edit_data['harga'] ?? '' ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Stok *</label>
                        <input type="number" name="stok" required min="0" 
                               value="<?= $edit_data['stok'] ?? 0 ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Status *</label>
                        <select name="status" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                            <option value="tersedia" <?= ($edit_data['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="habis" <?= ($edit_data['status'] ?? '') === 'habis' ? 'selected' : '' ?>>Habis</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Gambar</label>
                        <input type="file" name="gambar" accept="image/*" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                        <?php if ($edit_data && $edit_data['gambar']): ?>
                            <img src="uploads/<?= $edit_data['gambar'] ?>" alt="Current" class="mt-2 w-32 h-32 object-cover rounded">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-6 flex gap-4">
                    <button type="submit" class="flex-1 bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-400 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('modal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }

        <?php if ($edit_data): ?>
            toggleModal();
        <?php endif; ?>
    </script>
</body>
</html>