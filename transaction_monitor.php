<?php
/**
 * Transaction Monitor - Super Admin interface for monitoring all transactions
 */

$page_title = 'Transaction Monitor';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Check if user is super admin
$user = getCurrentUser();
if ($user['role'] !== 'super_admin') {
    setFlashMessage('error', 'Access denied. Super admin privileges required.');
    redirect('index.php');
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$transaction_type = $_GET['transaction_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_filter = $_GET['user'] ?? '';

// Build query
$pdo = getDBConnection();
$query = "
    SELECT 
        et.*,
        e.name as equipment_name,
        e.serial_number,
        e.category,
        u.username as created_by_username
    FROM equipment_transactions et
    LEFT JOIN equipment e ON et.equipment_id = e.id
    LEFT JOIN users u ON et.created_by = u.username
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (e.name LIKE ? OR e.serial_number LIKE ? OR et.assigned_to LIKE ? OR et.notes LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($transaction_type)) {
    $query .= " AND et.transaction_type = ?";
    $params[] = $transaction_type;
}

if (!empty($date_from)) {
    $query .= " AND DATE(et.transaction_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(et.transaction_date) <= ?";
    $params[] = $date_to;
}

if (!empty($user_filter)) {
    $query .= " AND et.created_by = ?";
    $params[] = $user_filter;
}

$query .= " ORDER BY et.transaction_date DESC LIMIT 100";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Get all users for filter dropdown
$stmt = $pdo->query("SELECT DISTINCT created_by FROM equipment_transactions WHERE created_by IS NOT NULL ORDER BY created_by");
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get transaction statistics
$statsQuery = "
    SELECT 
        transaction_type,
        COUNT(*) as count,
        SUM(quantity) as total_quantity
    FROM equipment_transactions 
    WHERE DATE(transaction_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY transaction_type
";
$stmt = $pdo->query($statsQuery);
$stats = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Transaction Monitor</h1>
            <p class="text-gray-600 mt-2">Monitor all system transactions and user activities</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
    <?php
    $transactionTypes = [
        'in_use' => ['label' => 'In Use', 'color' => 'blue'],
        'returned' => ['label' => 'Returned', 'color' => 'green'],
        'replacement' => ['label' => 'Replacement', 'color' => 'yellow'],
        'added' => ['label' => 'Added', 'color' => 'purple'],
        'removed' => ['label' => 'Removed', 'color' => 'red']
    ];
    
    $statsData = [];
    foreach ($stats as $stat) {
        $statsData[$stat['transaction_type']] = $stat;
    }
    
    foreach ($transactionTypes as $type => $config):
        $stat = $statsData[$type] ?? ['count' => 0, 'total_quantity' => 0];
        $color = $config['color'];
    ?>
    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-<?php echo $color; ?>-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1"><?php echo $config['label']; ?></p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $stat['count']; ?></p>
                <p class="text-xs text-gray-500"><?php echo $stat['total_quantity']; ?> items</p>
            </div>
            <div class="bg-<?php echo $color; ?>-100 rounded-full p-3">
                <?php
                $icons = [
                    'in_use' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
                    'returned' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>',
                    'replacement' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>',
                    'added' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>',
                    'removed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>'
                ];
                ?>
                <svg class="w-8 h-8 text-<?php echo $color; ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php echo $icons[$type]; ?>
                </svg>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="transaction_monitor.php" class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input 
                type="text" 
                id="search" 
                name="search" 
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Equipment, serial, assigned to..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div>
            <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
            <select 
                id="transaction_type" 
                name="transaction_type"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Types</option>
                <option value="in_use" <?php echo $transaction_type === 'in_use' ? 'selected' : ''; ?>>In Use</option>
                <option value="returned" <?php echo $transaction_type === 'returned' ? 'selected' : ''; ?>>Returned</option>
                <option value="replacement" <?php echo $transaction_type === 'replacement' ? 'selected' : ''; ?>>Replacement</option>
                <option value="added" <?php echo $transaction_type === 'added' ? 'selected' : ''; ?>>Added</option>
                <option value="removed" <?php echo $transaction_type === 'removed' ? 'selected' : ''; ?>>Removed</option>
            </select>
        </div>

        <div>
            <label for="user" class="block text-sm font-medium text-gray-700 mb-2">User</label>
            <select 
                id="user" 
                name="user"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="">All Users</option>
                <?php foreach ($users as $username): ?>
                    <option value="<?php echo htmlspecialchars($username); ?>" <?php echo $user_filter === $username ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($username); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
            <input 
                type="date" 
                id="date_from" 
                name="date_from" 
                value="<?php echo htmlspecialchars($date_from); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
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
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Transactions (Last 100)</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-lg font-medium">No transactions found</p>
                            <p class="text-sm">Try adjusting your filter criteria</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($transaction['equipment_name'] ?? 'Unknown Equipment'); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($transaction['serial_number'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-900">
                                <?php echo $transaction['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $transaction['assigned_to'] ? htmlspecialchars($transaction['assigned_to']) : '-'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($transaction['created_by_username'] ?? $transaction['created_by']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($transaction['transaction_date'], 'M d, Y g:i A'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $transaction['notes'] ? htmlspecialchars(substr($transaction['notes'], 0, 50)) . (strlen($transaction['notes']) > 50 ? '...' : '') : '-'; ?>
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
