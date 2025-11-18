<?php
require_once 'config.php';
requireLogin();
requireAdmin();

// Handle Add/Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $username = cleanInput($_POST['username']);
    $nama = cleanInput($_POST['nama']);
    $role = cleanInput($_POST['role']);
    
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        
        // Update password only if provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET username = '$username', password = '$password', 
                         role = '$role', nama = '$nama' WHERE id = $id");
        } else {
            $conn->query("UPDATE users SET username = '$username', role = '$role', 
                         nama = '$nama' WHERE id = $id");
        }
        
        $success = "Data user berhasil diupdate!";
    } else {
        // Add new user
        if (empty($_POST['password'])) {
            $error = "Password harus diisi untuk user baru!";
        } else {
            // Check if username exists
            $check = $conn->query("SELECT id FROM users WHERE username = '$username'")->num_rows;
            if ($check > 0) {
                $error = "Username sudah digunakan!";
            } else {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, nama) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $password, $role, $nama);
                $stmt->execute();
                $success = "User baru berhasil ditambahkan!";
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Prevent deleting own account
    if ($id == $_SESSION['user_id']) {
        $error = "Tidak dapat menghapus akun yang sedang digunakan!";
    } else {
        $conn->query("DELETE FROM users WHERE id = $id");
        $success = "User berhasil dihapus!";
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM users WHERE id = $edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Echo Cafe</title>
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
                        <h2 class="text-2xl font-bold text-gray-800">Kelola Pengguna</h2>
                        <p class="text-gray-600">Atur admin dan kasir</p>
                    </div>
                    <button onclick="toggleModal()" class="bg-amber-600 text-white px-4 py-2 rounded-lg hover:bg-amber-700 transition">
                        <i class="fas fa-user-plus"></i> Tambah Pengguna
                    </button>
                </div>
            </header>

            <div class="p-8">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <p class="text-red-700"><i class="fas fa-exclamation-circle"></i> <?= $error ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <p class="text-green-700"><i class="fas fa-check-circle"></i> <?= $success ?></p>
                    </div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Username</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-semibold text-gray-600">#<?= $user['id'] ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="bg-amber-100 p-2 rounded-full mr-3">
                                                    <i class="fas fa-user text-amber-600"></i>
                                                </div>
                                                <span class="font-semibold"><?= $user['nama'] ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700"><?= $user['username'] ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold uppercase">
                                                    <i class="fas fa-crown"></i> Admin
                                                </span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold uppercase">
                                                    <i class="fas fa-cash-register"></i> Kasir
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <a href="users.php?edit=<?= $user['id'] ?>" 
                                                   onclick="return editUser(<?= $user['id'] ?>)"
                                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition text-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <a href="users.php?delete=<?= $user['id'] ?>" 
                                                       onclick="return confirm('Yakin ingin menghapus user ini?')" 
                                                       class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition text-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="bg-gray-300 text-gray-500 px-3 py-1 rounded text-sm cursor-not-allowed">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <?php
                    $stats = $conn->query("SELECT 
                        COUNT(*) as total,
                        SUM(role = 'admin') as admins,
                        SUM(role = 'kasir') as kasirs
                        FROM users")->fetch_assoc();
                    ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Pengguna</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $stats['total'] ?></p>
                            </div>
                            <div class="bg-gray-100 p-4 rounded-full">
                                <i class="fas fa-users text-2xl text-gray-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Admin</p>
                                <p class="text-3xl font-bold text-purple-600"><?= $stats['admins'] ?></p>
                            </div>
                            <div class="bg-purple-100 p-4 rounded-full">
                                <i class="fas fa-crown text-2xl text-purple-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Kasir</p>
                                <p class="text-3xl font-bold text-blue-600"><?= $stats['kasirs'] ?></p>
                            </div>
                            <div class="bg-blue-100 p-4 rounded-full">
                                <i class="fas fa-cash-register text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Add/Edit -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800" id="modal-title">Tambah Pengguna Baru</h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <form method="POST" id="user-form">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id" id="user-id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama" id="user-nama" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Nama lengkap pengguna">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Username *</label>
                    <input type="text" name="username" id="user-username" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Username untuk login">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Password <span id="password-label">*</span>
                    </label>
                    <input type="password" name="password" id="user-password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                           placeholder="Minimal 6 karakter">
                    <p class="text-xs text-gray-500 mt-1" id="password-hint">
                        Kosongkan jika tidak ingin mengubah password
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Role *</label>
                    <select name="role" id="user-role" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <option value="">Pilih Role</option>
                        <option value="admin">Admin</option>
                        <option value="kasir">Kasir</option>
                    </select>
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
            
            if (modal.classList.contains('hidden')) {
                document.getElementById('user-form').reset();
                document.getElementById('form-action').value = 'add';
                document.getElementById('modal-title').textContent = 'Tambah Pengguna Baru';
                document.getElementById('user-password').required = true;
                document.getElementById('password-label').textContent = '*';
                document.getElementById('password-hint').classList.add('hidden');
            }
        }

        function editUser(id) {
            <?php if ($edit_data): ?>
                document.getElementById('form-action').value = 'edit';
                document.getElementById('user-id').value = '<?= $edit_data['id'] ?>';
                document.getElementById('user-nama').value = '<?= addslashes($edit_data['nama']) ?>';
                document.getElementById('user-username').value = '<?= addslashes($edit_data['username']) ?>';
                document.getElementById('user-role').value = '<?= $edit_data['role'] ?>';
                document.getElementById('modal-title').textContent = 'Edit Pengguna';
                document.getElementById('user-password').required = false;
                document.getElementById('password-label').textContent = '';
                document.getElementById('password-hint').classList.remove('hidden');
                toggleModal();
            <?php else: ?>
                window.location.href = 'users.php?edit=' + id;
            <?php endif; ?>
            return false;
        }

        <?php if ($edit_data): ?>
            editUser(<?= $edit_data['id'] ?>);
        <?php endif; ?>
    </script>
</body>
</html>