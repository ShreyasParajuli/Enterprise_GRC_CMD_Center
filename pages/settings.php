<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();

// Dummy configuration values
$config = [
    'site_name' => APP_NAME,
    'support_email' => 'support@example.com',
    'timezone' => date_default_timezone_get(),
    'session_timeout' => 30,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process settings form
    // Sleep to simulate processing
    usleep(500000);
    logActivity($pdo, $_SESSION['user_id'], "[System] Updated Settings");
    $_SESSION['success'] = "Application settings have been updated successfully.";
    header("Location: " . BASE_URL . "/pages/settings.php");
    exit;
}

$pageTitle = 'Application Settings';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-4xl mx-auto space-y-6">
            
            <div class="flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-[#232426]">Application Settings</h2>
                    <p class="text-[#232426]/70 mt-1">Manage global system configuration and preferences.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                <form action="<?= BASE_URL ?>/pages/settings.php" method="POST">
                    
                    <div class="p-6 space-y-6">
                        <!-- General Settings Section -->
                        <div>
                            <h3 class="text-lg font-bold text-[#232426] mb-4 flex items-center">
                                <i data-lucide="sliders" class="w-5 h-5 mr-2 text-brand-primary"></i> General Settings
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="site_name" class="block text-sm font-medium text-[#232426]">Application Name</label>
                                    <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($config['site_name']) ?>" class="mt-1 block w-full border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm py-2 px-3 bg-slate-50">
                                </div>
                                <div>
                                    <label for="support_email" class="block text-sm font-medium text-[#232426]">Support Email Address</label>
                                    <input type="email" id="support_email" name="support_email" value="<?= htmlspecialchars($config['support_email']) ?>" class="mt-1 block w-full border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm py-2 px-3 bg-slate-50">
                                </div>
                            </div>
                        </div>
                        
                        <hr class="border-[#232426]/10">

                        <!-- System Settings Section -->
                        <div>
                            <h3 class="text-lg font-bold text-[#232426] mb-4 flex items-center">
                                <i data-lucide="server" class="w-5 h-5 mr-2 text-brand-primary"></i> System Preferences
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-[#232426]">Default Timezone</label>
                                    <select id="timezone" name="timezone" class="mt-1 block w-full border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm py-2 px-3 bg-slate-50">
                                        <option value="UTC" <?= $config['timezone'] === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                        <option value="America/New_York" <?= $config['timezone'] === 'America/New_York' ? 'selected' : '' ?>>Eastern Time (US & Canada)</option>
                                        <option value="Europe/London" <?= $config['timezone'] === 'Europe/London' ? 'selected' : '' ?>>London</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="session_timeout" class="block text-sm font-medium text-[#232426]">Session Timeout (minutes)</label>
                                    <input type="number" id="session_timeout" name="session_timeout" value="<?= htmlspecialchars($config['session_timeout']) ?>" min="5" max="1440" class="mt-1 block w-full border border-[#232426]/20 rounded-lg shadow-sm focus:border-[#EF6351] focus:ring-[#EF6351] sm:text-sm py-2 px-3 bg-slate-50">
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50 border-t border-[#232426]/10 flex justify-end">
                        <button type="submit" class="bg-[#EF6351] text-white px-5 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity shadow-sm">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
