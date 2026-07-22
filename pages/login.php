<?php
require_once __DIR__ . '/../config/init.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $result = loginUser($pdo, $username, $password);
        if ($result === true) {
            header('Location: ' . BASE_URL . '/pages/dashboard.php');
            exit;
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            bg: '#FFFAF3',
                            dark: '#232426',
                            primary: '#EF6351',
                            secondary: '#C9AEF1',
                            success: '#BBC7B6',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-brand-bg text-brand-dark font-sans antialiased min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md border border-brand-dark/10">
        <div class="text-center mb-8">
            <i data-lucide="shield-check" text-brand-primary text-5xl mb-4"></i>
            <h2 class="text-2xl font-bold text-brand-dark">GRC Command Center</h2>
            <p class="text-brand-dark/70 mt-2">Enterprise Authentication</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-black/5 border border-brand-dark/10 text-brand-dark/70 px-4 py-3 rounded mb-6 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5">
                <label class="block text-brand-dark text-sm font-bold mb-2" for="username">
                    Username / Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="user" text-brand-dark/70"></i>
                    </div>
                    <input class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full pl-10 p-2.5" 
                           id="username" name="username" type="text" placeholder="admin" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-brand-dark text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-lock text-brand-dark/70"></i>
                    </div>
                    <input class="bg-slate-50 border border-brand-dark/10 text-brand-dark rounded-lg focus:ring-grc-primary focus:border-grc-primary block w-full pl-10 pr-10 p-2.5" 
                           id="password" name="password" type="password" placeholder="••••••••" required>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-brand-dark/70 hover:text-brand-dark transition-colors" id="togglePassword">
                        <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input id="remember" type="checkbox" class="w-4 h-4 text-brand-primary bg-slate-50 border-brand-dark/10 rounded focus:ring-grc-primary focus:ring-2">
                    <label for="remember" class="ml-2 text-sm font-medium text-brand-dark">Remember me</label>
                </div>
                <a href="#" class="text-sm font-medium text-brand-primary hover:underline">Forgot password?</a>
            </div>
            
            <button class="w-full text-brand-dark bg-brand-primary hover:opacity-90 focus:ring-4 focus:outline-none focus:ring-grc-primary/50 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors" type="submit">
                Sign in to secure environment
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-brand-dark/70">
            <p>&copy; <?= date('Y') ?> Enterprise GRC Systems. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
