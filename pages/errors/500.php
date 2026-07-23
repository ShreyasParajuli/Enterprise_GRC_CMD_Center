<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = '500 Server Error';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 flex items-center justify-center p-6 bg-[#FFFAF3]">
        <div class="max-w-md w-full text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-100 mb-6">
                <i data-lucide="server-crash" class="w-12 h-12 text-red-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-[#232426] mb-3">500</h1>
            <h2 class="text-xl font-semibold text-[#232426] mb-4">Internal Server Error</h2>
            <p class="text-[#232426]/70 mb-8 leading-relaxed">
                The server encountered an unexpected condition that prevented it from fulfilling the request. Our technical team has been notified.
            </p>
            <div class="flex justify-center gap-4">
                <button onclick="window.location.reload()" class="bg-white border border-[#232426]/20 hover:bg-slate-50 text-[#232426] px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    Refresh Page
                </button>
                <a href="<?= BASE_URL ?>/pages/dashboard.php" class="bg-[#EF6351] hover:opacity-90 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
