<?php
/**
 * Equipment Details - View detailed information about a single equipment
 */

$page_title = 'Equipment Details';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

$user = getCurrentUser();
$equipmentId = $_GET['id'] ?? 0;

// Get equipment data
$equipment = getEquipmentById($equipmentId);
if (!$equipment) {
    setFlashMessage('error', 'Equipment not found.');
    redirect('view_equipment.php');
}

// Get equipment transactions
$pdo = getDBConnection();
$stmt = $pdo->prepare("
    SELECT * FROM equipment_transactions 
    WHERE equipment_id = ? 
    ORDER BY transaction_date DESC 
    LIMIT 20
");
$stmt->execute([$equipmentId]);
$transactions = $stmt->fetchAll();

// Handle new transaction submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_type'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        $transactionData = [
            'equipment_id' => $equipmentId,
            'transaction_type' => sanitizeInput($_POST['transaction_type']),
            'quantity' => (int)$_POST['quantity'],
            'assigned_to' => sanitizeInput($_POST['assigned_to'] ?? ''),
            'notes' => sanitizeInput($_POST['notes'] ?? ''),
            'created_by' => $user['username']
        ];
        
        $result = createTransaction($transactionData);
        if ($result) {
            setFlashMessage('success', 'Transaction recorded successfully!');
            redirect('equipment_details.php?id=' . $equipmentId);
        } else {
            setFlashMessage('error', 'Failed to record transaction. Check available stock.');
        }
    }
}

// Refresh equipment data after potential transaction
$equipment = getEquipmentById($equipmentId);
$csrf_token = generateCSRFToken();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="view_equipment.php" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($equipment['name']); ?></h1>
                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($equipment['serial_number']); ?></p>
            </div>
        </div>
        <a href="edit_equipment.php?id=<?php echo $equipmentId; ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-md">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Equipment Information -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Equipment Information</h2>
            </div>
            <div class="px-6 py-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Category</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-medium">
                                <?php echo htmlspecialchars($equipment['category']); ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                        <dd class="text-sm text-gray-900"><?php echo getStatusBadge($equipment['status']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Purchase Date</dt>
                        <dd class="text-sm text-gray-900">
                            <?php echo $equipment['purchase_date'] ? formatDate($equipment['purchase_date']) : 'N/A'; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">Purchase Price</dt>
                        <dd class="text-sm text-gray-900">
                            <?php echo $equipment['purchase_price'] ? formatCurrency($equipment['purchase_price']) : 'N/A'; ?>
                        </dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1">Description</dt>
                        <dd class="text-sm text-gray-900">
                            <?php echo $equipment['description'] ? htmlspecialchars($equipment['description']) : 'No description available'; ?>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Stock Status Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Stock Status</h2>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $equipment['total_quantity']; ?></p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Available</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $equipment['available_quantity']; ?></p>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">In Use</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $equipment['in_use_quantity']; ?></p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">In Replacement</p>
                        <p class="text-3xl font-bold text-red-600"><?php echo $equipment['in_replacement_quantity']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Transaction History</h2>
            </div>
            <div class="px-6 py-6">
                <?php if (empty($transactions)): ?>
                    <p class="text-center text-gray-500 py-8">No transactions recorded</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <?php echo getTransactionTypeBadge($transaction['transaction_type']); ?>
                                        <span class="ml-3 text-sm font-semibold text-gray-900">
                                            Qty: <?php echo $transaction['quantity']; ?>
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        <?php echo formatDate($transaction['transaction_date'], 'M d, Y g:i A'); ?>
                                    </span>
                                </div>
                                <?php if ($transaction['assigned_to']): ?>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Assigned to:</span> <?php echo htmlspecialchars($transaction['assigned_to']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($transaction['notes']): ?>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Notes:</span> <?php echo htmlspecialchars($transaction['notes']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 mt-1">
                                    By: <?php echo htmlspecialchars($transaction['created_by']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-4">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
            </div>
            <div class="px-6 py-6">
                <form method="POST" action="equipment_details.php?id=<?php echo $equipmentId; ?>" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div>
                        <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Transaction Type
                        </label>
                        <select 
                            id="transaction_type" 
                            name="transaction_type"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="in_use">In Use</option>
                            <option value="returned">Returned</option>
                            <option value="replacement">Replacement</option>
                            <option value="added">Added Stock</option>
                            <option value="removed">Removed Stock</option>
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantity
                        </label>
                        <input 
                            type="number" 
                            id="quantity" 
                            name="quantity" 
                            required
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                            Assigned To (Optional)
                        </label>
                        <input 
                            type="text" 
                            id="assigned_to" 
                            name="assigned_to" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Person or department"
                        >
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea 
                            id="notes" 
                            name="notes" 
                            rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Additional notes"
                        ></textarea>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-md">
                        Record Transaction
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

