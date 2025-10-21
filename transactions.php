<?php
/**
 * Transactions - View all transaction history with filtering
 */

$page_title = 'Transactions';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Get filter parameters
$type = $_GET['type'] ?? '';
$equipment_id = $_GET['equipment_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$pdo = getDBConnection();
$query = "
    SELECT 
        t.*,
        e.name as equipment_name,
        e.serial_number,
        e.category
    FROM equipment_transactions t
    INNER JOIN equipment e ON t.equipment_id = e.id
    WHERE 1=1
";
$params = [];

if (!empty($type)) {
    $query .= " AND t.transaction_type = ?";
    $params[] = $type;
}

if (!empty($equipment_id)) {
    $query .= " AND t.equipment_id = ?";
    $params[] = $equipment_id;
}

if (!empty($date_from)) {
    $query .= " AND DATE(t.transaction_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(t.transaction_date) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY t.transaction_date DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get all equipment for filter dropdown
$equipmentList = executeQuery("SELECT id, name, serial_number FROM equipment ORDER BY name ASC");
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Transaction History</h1>
            <p class="text-gray-600 mt-2">View and filter all equipment transactions</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="transactions.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
            <select 
                id="type" 
                name="type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Types</option>
                <option value="in_use" <?php echo $type === 'in_use' ? 'selected' : ''; ?>>In Use</option>
                <option value="returned" <?php echo $type === 'returned' ? 'selected' : ''; ?>>Returned</option>
                <option value="replacement" <?php echo $type === 'replacement' ? 'selected' : ''; ?>>Replacement</option>
                <option value="added" <?php echo $type === 'added' ? 'selected' : ''; ?>>Added</option>
                <option value="removed" <?php echo $type === 'removed' ? 'selected' : ''; ?>>Removed</option>
            </select>
        </div>

        <div>
            <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
            <select 
                id="equipment_id" 
                name="equipment_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Equipment</option>
                <?php foreach ($equipmentList as $eq): ?>
                    <option value="<?php echo $eq['id']; ?>" <?php echo $equipment_id == $eq['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($eq['name']); ?> (<?php echo htmlspecialchars($eq['serial_number']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input 
                type="date" 
                id="date_from" 
                name="date_from" 
                value="<?php echo htmlspecialchars($date_from); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input 
                type="date" 
                id="date_to" 
                name="date_to" 
                value="<?php echo htmlspecialchars($date_to); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="transactions-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-lg font-medium">No transactions found</p>
                            <p class="text-sm">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo formatDate($transaction['transaction_date'], 'M d, Y'); ?><br>
                                <span class="text-xs text-gray-500"><?php echo formatDate($transaction['transaction_date'], 'g:i A'); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="equipment_details.php?id=<?php echo $transaction['equipment_id']; ?>" class="text-blue-600 hover:text-blue-800">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($transaction['equipment_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($transaction['serial_number']); ?></div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                <?php echo $transaction['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $transaction['assigned_to'] ? htmlspecialchars($transaction['assigned_to']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                <?php echo $transaction['notes'] ? htmlspecialchars($transaction['notes']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($transaction['created_by']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Button -->
<?php if (!empty($transactions)): ?>
    <div class="mt-6 flex justify-end">
        <button onclick="exportToCSV('transactions-table', 'transactions.csv')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export to CSV
        </button>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

<?php
/**
 * Transactions - View all transaction history with filtering
 */

$page_title = 'Transactions';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Get filter parameters
$type = $_GET['type'] ?? '';
$equipment_id = $_GET['equipment_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$pdo = getDBConnection();
$query = "
    SELECT 
        t.*,
        e.name as equipment_name,
        e.serial_number,
        e.category
    FROM equipment_transactions t
    INNER JOIN equipment e ON t.equipment_id = e.id
    WHERE 1=1
";
$params = [];

if (!empty($type)) {
    $query .= " AND t.transaction_type = ?";
    $params[] = $type;
}

if (!empty($equipment_id)) {
    $query .= " AND t.equipment_id = ?";
    $params[] = $equipment_id;
}

if (!empty($date_from)) {
    $query .= " AND DATE(t.transaction_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(t.transaction_date) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY t.transaction_date DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get all equipment for filter dropdown
$equipmentList = executeQuery("SELECT id, name, serial_number FROM equipment ORDER BY name ASC");
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Transaction History</h1>
            <p class="text-gray-600 mt-2">View and filter all equipment transactions</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="transactions.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Transaction Type</label>
            <select 
                id="type" 
                name="type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Types</option>
                <option value="in_use" <?php echo $type === 'in_use' ? 'selected' : ''; ?>>In Use</option>
                <option value="returned" <?php echo $type === 'returned' ? 'selected' : ''; ?>>Returned</option>
                <option value="replacement" <?php echo $type === 'replacement' ? 'selected' : ''; ?>>Replacement</option>
                <option value="added" <?php echo $type === 'added' ? 'selected' : ''; ?>>Added</option>
                <option value="removed" <?php echo $type === 'removed' ? 'selected' : ''; ?>>Removed</option>
            </select>
        </div>

        <div>
            <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
            <select 
                id="equipment_id" 
                name="equipment_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Equipment</option>
                <?php foreach ($equipmentList as $eq): ?>
                    <option value="<?php echo $eq['id']; ?>" <?php echo $equipment_id == $eq['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($eq['name']); ?> (<?php echo htmlspecialchars($eq['serial_number']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input 
                type="date" 
                id="date_from" 
                name="date_from" 
                value="<?php echo htmlspecialchars($date_from); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input 
                type="date" 
                id="date_to" 
                name="date_to" 
                value="<?php echo htmlspecialchars($date_to); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="transactions-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-lg font-medium">No transactions found</p>
                            <p class="text-sm">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo formatDate($transaction['transaction_date'], 'M d, Y'); ?><br>
                                <span class="text-xs text-gray-500"><?php echo formatDate($transaction['transaction_date'], 'g:i A'); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="equipment_details.php?id=<?php echo $transaction['equipment_id']; ?>" class="text-blue-600 hover:text-blue-800">
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($transaction['equipment_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($transaction['serial_number']); ?></div>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                <?php echo $transaction['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $transaction['assigned_to'] ? htmlspecialchars($transaction['assigned_to']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                <?php echo $transaction['notes'] ? htmlspecialchars($transaction['notes']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($transaction['created_by']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Button -->
<?php if (!empty($transactions)): ?>
    <div class="mt-6 flex justify-end">
        <button onclick="exportToCSV('transactions-table', 'transactions.csv')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export to CSV
        </button>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

