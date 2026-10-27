<?php
require_once __DIR__ . '/../../config/init.php';
requireLogin();

$pageTitle = 'Asset Categories';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Breadcrumbs and Header Actions
$breadcrumbs = [
    'Asset Register' => BASE_URL . '/modules/assets/index.php',
    'Categories' => ''
];
$primaryAction = ['label' => 'Add Category', 'url' => '#', 'icon' => 'plus'];
?>

<div class="flex-1 flex flex-col min-w-0 overflow-hidden">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-6 bg-[#FFFAF3]">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <?php 
            $pageSubtitle = "Manage classifications for enterprise assets.";
            require_once __DIR__ . '/components/asset_header.php'; 
            ?>

            <!-- Categories Table Shell -->
            <div class="bg-white rounded-xl border border-[#232426]/10 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#232426]/10">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Category Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Number of Assets</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#232426]/50 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#232426]/10">
                            <tr>
                                <td colspan="5" class="p-0">
                                    <?php 
                                    $emptyMessage = "No categories to display yet.";
                                    $emptySubMessage = "Categories will be populated from the database in Phase 3.";
                                    require __DIR__ . '/components/asset_empty_state.php'; 
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
