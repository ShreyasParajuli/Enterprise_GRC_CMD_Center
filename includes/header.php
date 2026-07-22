<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME) ?></title>
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
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
    <!-- Lucide Icons (script added in footer) -->
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-brand-bg text-brand-dark font-sans antialiased min-h-screen flex flex-col">

<?php if (isset($_SESSION["success_msg"])): ?>
    <div class="bg-brand-success text-brand-dark px-4 py-3 text-center text-sm font-medium">
        <?= htmlspecialchars($_SESSION["success_msg"]) ?>
    </div>
    <?php unset($_SESSION["success_msg"]); ?>
<?php endif; ?>

<div class="flex flex-1 overflow-hidden">
