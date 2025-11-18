<?php
// login.php
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];
            redirect('dashboard.php');
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Echo Cafe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-amber-100 to-orange-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-coffee text-4xl text-amber-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Echo Cafe</h1>
                <p class="text-gray-600">Staff Login</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <p class="text-red-700"><i class="fas fa-exclamation-circle"></i> <?= $error ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input type="text" name="username" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               placeholder="Masukkan username">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                               placeholder="Masukkan password">
                    </div>
                    <button type="submit" class="w-full bg-amber-600 text-white py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="index.php" class="text-amber-600 hover:text-amber-700 font-semibold">
                        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                    </a>
                </div>

                <!-- Info Login -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 mb-2"><strong>Default Login:</strong></p>
                    <p class="text-xs text-gray-600">Admin: admin / password</p>
                    <p class="text-xs text-gray-600">Kasir: kasir1 / password</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
