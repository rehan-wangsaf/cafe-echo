<?php
require_once 'config.php';
requireLogin();
requireAdmin();

// Handle Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nama = cleanInput($_POST['nama']);
    $deskripsi = cleanInput($_POST['deskripsi']);
    
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $conn->query("UPDATE categories SET nama = '$nama', deskripsi = '$deskripsi' WHERE id = $id");
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (nama, deskripsi) VALUES (?, ?)");
        $stmt->bind_param("ss", $nama, $deskripsi);
        $stmt->execute();
    }
    redirect('categories.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if category has menu items
    $check = $conn->query("SELECT COUNT(*) as total FROM menu WHERE category_id = $id")->fetch_assoc();
    if ($check['total'] > 0) {
        $error = "Tidak dapat menghapus kategori yang masih memiliki menu!";
    } else {
        $conn->query("DELETE FROM categories WHERE id = $id");
        redirect('categories.php');
    }
}

// Get categories with menu count
$categories = $conn->query("SELECT c.*, COUNT(m.id) as menu_count 
                           FROM categories c 
                           LEFT JOIN menu m ON c.id = m.category_id 
                           GROUP BY c.id 
                           ORDER BY c.nama");

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM categories WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Echo Cafe</title>
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
                        <h2 class="text-2xl font-bold text-gray-800">Kelola Kategori</h2>
                        <p class="text-gray-600">Atur kategori menu cafe</p>
                    </div>
                    <button onclick="toggleModal()" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>
            </header>

            <div class="p-8">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <p class="text-red-700"><i class="fas fa-exclamation-circle"></i> <?= $error ?></p>
                    </div>
                <?php endif; ?>

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-amber-100 p-3 rounded-full">
                                    <i class="fas fa-list text-2xl text-amber-600"></i>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="editCategory(<?= $cat['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="categories.php?delete=<?= $cat['id'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')" 
                                       class="text-red-600 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2"><?= $cat['nama'] ?></h3>
                            <p class="text-gray-600 text-sm mb-4"><?= $cat['deskripsi'] ?: 'Tidak ada deskripsi' ?></p>
                            <div class="flex items-center justify-between pt-4 border-t">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-utensils"></i> <?= $cat['menu_count'] ?> Menu
                                </span>
                                <span class="text-xs text-gray-400">
                                    <?= date('d/m/Y', strtotime($cat['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($categories->num_rows === 0): ?>
                    <div class="text-center py-20">
                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Kategori</h3>
                        <p class="text-gray-500 mb-6">Tambahkan kategori untuk mengorganisir menu Anda</p>
                        <button onclick="toggleModal()" class="bg-amber-600 text-white px-6 py-3 rounded-lg hover:bg-amber-700 transition">
                            <i class="fas fa-plus"></i> Tambah Kategori Pertama
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Add/Edit -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800" id="modal-title">Tambah Kategori Baru</h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form method="POST" id="category-form">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="category-id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Kategori *</label>
                    <input type="text" name="nama" id="category-nama" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Contoh: Minuman, Makanan">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Deskripsi</label>
                    <textarea name="deskripsi" id="category-deskripsi" rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                              placeholder="Deskripsi singkat tentang kategori ini"></textarea>
                </div>

                <div class="flex gap-4">
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
            
            // Reset form when closing
            if (modal.classList.contains('hidden')) {
                document.getElementById('category-form').reset();
                document.getElementById('form-action').value = 'add';
                document.getElementById('modal-title').textContent = 'Tambah Kategori Baru';
            }
        }

        function editCategory(id) {
            // Fetch category data via AJAX or use PHP to populate
            <?php if ($edit_data): ?>
                document.getElementById('form-action').value = 'edit';
                document.getElementById('category-id').value = '<?= $edit_data['id'] ?>';
                document.getElementById('category-nama').value = '<?= addslashes($edit_data['nama']) ?>';
                document.getElementById('category-deskripsi').value = '<?= addslashes($edit_data['deskripsi']) ?>';
                document.getElementById('modal-title').textContent = 'Edit Kategori';
                toggleModal();
            <?php else: ?>
                window.location.href = 'categories.php?edit=' + id;
            <?php endif; ?>
        }

        <?php if ($edit_data): ?>
            editCategory(<?= $edit_data['id'] ?>);
        <?php endif; ?>
    </script>
</body>
</html>