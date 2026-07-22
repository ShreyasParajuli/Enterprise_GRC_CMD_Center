// Main JavaScript for GRC Command Center

document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle
    const mobileMenuButton = document.querySelector('header button');
    const sidebar = document.querySelector('aside');
    
    if (mobileMenuButton && sidebar) {
        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            // If we are making it visible on mobile, we might need absolute positioning
            if (!sidebar.classList.contains('hidden')) {
                sidebar.classList.add('absolute', 'z-50', 'h-full');
            } else {
                sidebar.classList.remove('absolute', 'z-50', 'h-full');
            }
        });
    }

    // Dropdown toggles (basic implementation)
    const userMenuButton = document.getElementById('user-menu-button');
    // For a real app, you would dynamically inject or toggle the dropdown menu here.
    if (userMenuButton) {
        userMenuButton.addEventListener('click', () => {
            // Toggle dropdown logic
        });
    }
});
