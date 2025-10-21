<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user = getCurrentUser();
?>
<nav class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 min-h-16 nav-container">
            <!-- Logo and primary navigation -->
            <div class="flex nav-links">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="flex items-center">
                        <div class="bg-blue-600 w-10 h-10 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <span class="ml-3 text-lg lg:text-xl font-bold text-gray-900 hidden sm:block">MIS Inventory Management System</span>
                        <span class="ml-3 text-lg font-bold text-gray-900 sm:hidden">MIS IMS</span>
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:ml-8 md:flex md:space-x-6">
                    <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 rounded-lg text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard
                    </a>
                    
                    <a href="view_equipment.php" class="<?php echo $current_page === 'view_equipment.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 rounded-lg text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Equipment
                    </a>
                    
                    <a href="transactions.php" class="<?php echo $current_page === 'transactions.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 rounded-lg text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        Transactions
                    </a>
                    
                    <a href="stock.php" class="<?php echo $current_page === 'stock.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 rounded-lg text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        Stock
                    </a>
                    
                    <?php if ($user['role'] === 'super_admin'): ?>
                    <a href="user_management.php" class="<?php echo $current_page === 'user_management.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> px-3 py-2 rounded-lg text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Users
                    </a>
                    <?php endif; ?>
                </div>
            </div>
                <!-- Logout button -->
                <a href="logout.php" class="text-gray-700 hover:text-red-600 px-6 py-2 rounded-lg text-sm font-medium flex items-center transition-colors hover:bg-red-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="ml-2 hidden md:inline">Logout</span>
                </a>
                
                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden text-gray-700 hover:text-gray-900 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-200">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> block px-3 py-2 rounded-lg text-base font-medium">
                Dashboard
            </a>
            <a href="view_equipment.php" class="<?php echo $current_page === 'view_equipment.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> block px-3 py-2 rounded-lg text-base font-medium">
                Equipment
            </a>
            <a href="transactions.php" class="<?php echo $current_page === 'transactions.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> block px-3 py-2 rounded-lg text-base font-medium">
                Transactions
            </a>
            <a href="stock.php" class="<?php echo $current_page === 'stock.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> block px-3 py-2 rounded-lg text-base font-medium">
                Stock
            </a>
            <?php if ($user['role'] === 'super_admin'): ?>
            <a href="user_management.php" class="<?php echo $current_page === 'user_management.php' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> block px-3 py-2 rounded-lg text-base font-medium">
                Users
            </a>
            <?php endif; ?>
        </div>
        <div class="border-t border-gray-200 px-2 py-3">
            <div class="flex items-center px-3 mb-2">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-700 font-semibold text-sm">
                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                    </span>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Ensure navigation elements don't overlap */
    .nav-container {
        min-width: 0; /* Allow flex items to shrink */
        width: 100%;
    }
    
    .nav-user-section {
        flex-shrink: 0; /* Prevent user section from shrinking */
        white-space: nowrap; /* Prevent text wrapping */
        margin-left: 2rem; /* Force minimum spacing */
    }
    
    .nav-links {
        flex-shrink: 1; /* Allow navigation links to shrink if needed */
        max-width: 60%; /* Limit navigation width */
    }
    
    /* Ensure proper spacing on all screen sizes */
    @media (max-width: 1280px) {
        .nav-user-section {
            margin-left: 1.5rem;
        }
    }
    
    @media (max-width: 1024px) {
        .nav-user-section {
            margin-left: 1rem;
        }
    }
    
    @media (max-width: 768px) {
        .nav-user-section {
            margin-left: 0.5rem;
        }
    }
    
    /* Force minimum spacing between elements */
    .nav-user-section > * + * {
        margin-left: 1rem !important;
    }
    
    /* Ensure logout button has enough space */
    .nav-user-section a[href="logout.php"] {
        margin-left: 2rem !important;
    }
</style>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>

