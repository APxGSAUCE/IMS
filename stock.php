<<<<<<< HEAD
<?php
/**
 * Stock Management - Manage stock levels and view inventory status
 */

$page_title = 'Stock Management';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Get all equipment with stock information
$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT 
        e.*,
        ROUND((e.available_quantity / e.total_quantity * 100), 2) as availability_percent
    FROM equipment e
    WHERE e.status = 'active'
    ORDER BY e.available_quantity ASC, e.name ASC
");
$equipment = $stmt->fetchAll();

// Calculate statistics
$totalValue = 0;
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($equipment as $item) {
    if ($item['purchase_price']) {
        $totalValue += $item['purchase_price'] * $item['total_quantity'];
    }
    if ($item['available_quantity'] < 5) {
        $lowStockCount++;
    }
    if ($item['available_quantity'] == 0) {
        $outOfStockCount++;
    }
}
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Stock Management</h1>
    <p class="text-gray-600 mt-2">Monitor inventory levels and stock status</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Inventory Value</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($totalValue); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Items</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $lowStockCount; ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Out of Stock</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $outOfStockCount; ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Items</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($equipment); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="stock-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Use</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Replacement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($equipment)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <p class="text-lg font-medium">No equipment found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipment as $item): ?>
                        <?php
                        $availPercent = $item['total_quantity'] > 0 ? ($item['available_quantity'] / $item['total_quantity'] * 100) : 0;
                        $stockStatus = '';
                        $stockClass = '';
                        
                        if ($item['available_quantity'] == 0) {
                            $stockStatus = 'Out of Stock';
                            $stockClass = 'bg-red-100 text-red-800';
                        } elseif ($item['available_quantity'] < 5) {
                            $stockStatus = 'Low Stock';
                            $stockClass = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $stockStatus = 'In Stock';
                            $stockClass = 'bg-green-100 text-green-800';
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="equipment_details.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($item['serial_number']); ?></div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                <?php echo $item['total_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-600">
                                <?php echo $item['available_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo $item['in_use_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo $item['in_replacement_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="<?php echo $availPercent > 50 ? 'bg-green-500' : ($availPercent > 20 ? 'bg-yellow-500' : 'bg-red-500'); ?> h-2 rounded-full" style="width: <?php echo $availPercent; ?>%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"><?php echo round($availPercent); ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded <?php echo $stockClass; ?>">
                                    <?php echo $stockStatus; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Button -->
<?php if (!empty($equipment)): ?>
    <div class="mt-6 flex justify-end">
        <button onclick="exportToCSV('stock-table', 'stock-report.csv')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export to CSV
        </button>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

=======
<?php
/**
 * Stock Management - Manage stock levels and view inventory status
 */

$page_title = 'Stock Management';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Get all equipment with stock information
$pdo = getDBConnection();
$stmt = $pdo->query("
    SELECT 
        e.*,
        ROUND((e.available_quantity / e.total_quantity * 100), 2) as availability_percent
    FROM equipment e
    WHERE e.status = 'active'
    ORDER BY e.available_quantity ASC, e.name ASC
");
$equipment = $stmt->fetchAll();

// Calculate statistics
$totalValue = 0;
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($equipment as $item) {
    if ($item['purchase_price']) {
        $totalValue += $item['purchase_price'] * $item['total_quantity'];
    }
    if ($item['available_quantity'] < 5) {
        $lowStockCount++;
    }
    if ($item['available_quantity'] == 0) {
        $outOfStockCount++;
    }
}
?>

<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Stock Management</h1>
    <p class="text-gray-600 mt-2">Monitor inventory levels and stock status</p>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Inventory Value</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo formatCurrency($totalValue); ?></p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Low Stock Items</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $lowStockCount; ?></p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Out of Stock</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $outOfStockCount; ?></p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Items</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo count($equipment); ?></p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Stock Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="stock-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Use</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Replacement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($equipment)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <p class="text-lg font-medium">No equipment found</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($equipment as $item): ?>
                        <?php
                        $availPercent = $item['total_quantity'] > 0 ? ($item['available_quantity'] / $item['total_quantity'] * 100) : 0;
                        $stockStatus = '';
                        $stockClass = '';
                        
                        if ($item['available_quantity'] == 0) {
                            $stockStatus = 'Out of Stock';
                            $stockClass = 'bg-red-100 text-red-800';
                        } elseif ($item['available_quantity'] < 5) {
                            $stockStatus = 'Low Stock';
                            $stockClass = 'bg-yellow-100 text-yellow-800';
                        } else {
                            $stockStatus = 'In Stock';
                            $stockClass = 'bg-green-100 text-green-800';
                        }
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <a href="equipment_details.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($item['serial_number']); ?></div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($item['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                <?php echo $item['total_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-600">
                                <?php echo $item['available_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo $item['in_use_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                <?php echo $item['in_replacement_quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="<?php echo $availPercent > 50 ? 'bg-green-500' : ($availPercent > 20 ? 'bg-yellow-500' : 'bg-red-500'); ?> h-2 rounded-full" style="width: <?php echo $availPercent; ?>%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700"><?php echo round($availPercent); ?>%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded <?php echo $stockClass; ?>">
                                    <?php echo $stockStatus; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Button -->
<?php if (!empty($equipment)): ?>
    <div class="mt-6 flex justify-end">
        <button onclick="exportToCSV('stock-table', 'stock-report.csv')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export to CSV
        </button>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

>>>>>>> 688f043e915ac216b709f36b533ff7972babe88b
