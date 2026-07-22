<header class="bg-brand-dark h-16 flex items-center justify-between px-6 border-b border-white/10 sticky top-0 z-10">
    <div class="flex items-center md:hidden">
        <button class="text-white/70 hover:text-white focus:outline-none">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <span class="ml-3 text-lg font-bold text-white">GRC Center</span>
    </div>
    
    <!-- Search / Breadcrumbs (Placeholder) -->
    <div class="hidden md:block flex-1">
        <h1 class="text-xl font-semibold text-white"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <!-- Right Navigation -->
    <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <button class="text-white/70 hover:text-white relative">
            <i data-lucide="bell" class="w-5 h-5"></i>
            <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-primary"></span>
            </span>
        </button>
        
        <!-- User Profile Dropdown Placeholder -->
        <div class="relative">
            <button class="flex items-center space-x-3 focus:outline-none group">
                <div class="h-9 w-9 rounded-full bg-brand-primary flex items-center justify-center text-white font-bold border border-white/20">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></p>
                    <p class="text-xs text-white/50"><?= htmlspecialchars($_SESSION['role_name'] ?? 'Role') ?></p>
                </div>
            </button>
        </div>
        
        <!-- Quick Logout (for prototype) -->
        <a href="<?= BASE_URL ?>/pages/logout.php" class="text-white/70 hover:text-brand-primary transition-colors ml-4" title="Logout">
            <i data-lucide="log-out" class="w-5 h-5"></i>
        </a>
    </div>
</header>
