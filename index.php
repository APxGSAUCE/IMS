<<<<<<< HEAD
<?php
/**
 * Dashboard - Main landing page after login
 */

$page_title = 'Dashboard';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

$user = getCurrentUser();
$stats = getDashboardStats();
$recentTransactions = getRecentTransactions(5);
$lowStockItems = getLowStockItems();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Equipment -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Equipment</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['total_items']); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- In Use -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">In Use</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['in_use']); ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Available -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Available</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['available']); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Alerts</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['low_stock']); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
            <a href="transactions.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentTransactions)): ?>
                <p class="text-gray-500 text-center py-8">No recent transactions</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="flex items-start space-x-3 pb-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex-shrink-0">
                                <?php 
                                $iconColors = [
                                    'in_use' => 'bg-blue-100 text-blue-600',
                                    'returned' => 'bg-green-100 text-green-600',
                                    'replacement' => 'bg-red-100 text-red-600',
                                    'added' => 'bg-purple-100 text-purple-600',
                                    'removed' => 'bg-gray-100 text-gray-600'
                                ];
                                $colorClass = $iconColors[$transaction['transaction_type']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <div class="w-10 h-10 rounded-full <?php echo $colorClass; ?> flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($transaction['equipment_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                                    <span class="mx-2">•</span>
                                    <span>Qty: <?php echo $transaction['quantity']; ?></span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo formatDate($transaction['transaction_date'], 'M d, Y g:i A'); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h2>
            <a href="stock.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="p-6">
            <?php if (empty($lowStockItems)): ?>
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">All items are well stocked!</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <?php echo htmlspecialchars($item['serial_number']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-red-600">
                                    <?php echo $item['available_quantity']; ?>
                                </p>
                                <p class="text-xs text-gray-600">available</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="add_equipment.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span class="font-medium">Add New Equipment</span>
        </a>
        
        <a href="view_equipment.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="font-medium">Browse Equipment</span>
        </a>
        
        <?php if ($user['role'] === 'super_admin'): ?>
        <a href="user_management.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <span class="font-medium">Manage Users</span>
        </a>
        <?php else: ?>
        <a href="transactions.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span class="font-medium">View Transactions</span>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

=======
<?php
/**
 * Dashboard - Main landing page after login
 */

$page_title = 'Dashboard';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

$user = getCurrentUser();
$stats = getDashboardStats();
$recentTransactions = getRecentTransactions(5);
$lowStockItems = getLowStockItems();
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-2">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Equipment -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Equipment</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['total_items']); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- In Use -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">In Use</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['in_use']); ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Available -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Available</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['available']); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock -->
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Alerts</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo number_format($stats['low_stock']); ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
            <a href="transactions.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="p-6">
            <?php if (empty($recentTransactions)): ?>
                <p class="text-gray-500 text-center py-8">No recent transactions</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="flex items-start space-x-3 pb-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex-shrink-0">
                                <?php 
                                $iconColors = [
                                    'in_use' => 'bg-blue-100 text-blue-600',
                                    'returned' => 'bg-green-100 text-green-600',
                                    'replacement' => 'bg-red-100 text-red-600',
                                    'added' => 'bg-purple-100 text-purple-600',
                                    'removed' => 'bg-gray-100 text-gray-600'
                                ];
                                $colorClass = $iconColors[$transaction['transaction_type']] ?? 'bg-gray-100 text-gray-600';
                                ?>
                                <div class="w-10 h-10 rounded-full <?php echo $colorClass; ?> flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($transaction['equipment_name']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                                    <span class="mx-2">•</span>
                                    <span>Qty: <?php echo $transaction['quantity']; ?></span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo formatDate($transaction['transaction_date'], 'M d, Y g:i A'); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Low Stock Alerts</h2>
            <a href="stock.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        <div class="p-6">
            <?php if (empty($lowStockItems)): ?>
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 font-medium">All items are well stocked!</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <?php echo htmlspecialchars($item['serial_number']); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-red-600">
                                    <?php echo $item['available_quantity']; ?>
                                </p>
                                <p class="text-xs text-gray-600">available</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="add_equipment.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            <span class="font-medium">Add New Equipment</span>
        </a>
        
        <a href="view_equipment.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="font-medium">Browse Equipment</span>
        </a>
        
        <a href="transactions.php" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-4 rounded-lg flex items-center space-x-3 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span class="font-medium">View Transactions</span>
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

>>>>>>> 688f043e915ac216b709f36b533ff7972babe88b
