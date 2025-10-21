<?php
/**
 * Add Equipment - Form to add new equipment
 */

$page_title = 'Add Equipment';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

$categories = getAllCategories();
$user = getCurrentUser();

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $formData = [
            'name' => sanitizeInput($_POST['name'] ?? ''),
            'category' => sanitizeInput($_POST['category'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'serial_number' => sanitizeInput($_POST['serial_number'] ?? ''),
            'total_quantity' => $_POST['total_quantity'] ?? 0
        ];

        // Validate required fields
        if (empty($formData['name'])) {
            $errors[] = 'Equipment name is required.';
        }
        if (empty($formData['category'])) {
            $errors[] = 'Category is required.';
        }
        if (empty($formData['serial_number'])) {
            $errors[] = 'Serial number is required.';
        } else {
            // Check if serial number is unique
            if (!validateSerialNumber($formData['serial_number'])) {
                $errors[] = 'Serial number already exists.';
            }
        }
        if (empty($formData['total_quantity']) || $formData['total_quantity'] < 0) {
            $errors[] = 'Valid quantity is required.';
        }

        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                
                // Insert equipment
                $stmt = $pdo->prepare("
                    INSERT INTO equipment 
                    (name, category, description, serial_number, 
                     total_quantity, available_quantity, in_use_quantity, in_replacement_quantity, status)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 0, 'active')
                ");
                
                $result = $stmt->execute([
                    $formData['name'],
                    $formData['category'],
                    $formData['description'],
                    $formData['serial_number'],
                    $formData['total_quantity'],
                    $formData['total_quantity'] // Initially all quantity is available
                ]);
                
                if ($result) {
                    $equipmentId = $pdo->lastInsertId();
                    
                    // Create initial transaction for added stock
                    createTransaction([
                        'equipment_id' => $equipmentId,
                        'transaction_type' => 'added',
                        'quantity' => $formData['total_quantity'],
                        'notes' => 'Initial stock added',
                        'created_by' => $user['username']
                    ]);
                    
                    setFlashMessage('success', 'Equipment added successfully!');
                    redirect('view_equipment.php');
                }
                
            } catch (PDOException $e) {
                error_log("Add equipment error: " . $e->getMessage());
                $errors[] = 'An error occurred while adding equipment. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center space-x-4 mb-4">
        <a href="view_equipment.php" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Add New Equipment</h1>
            <p class="text-gray-600 mt-2">Enter the details of the new equipment</p>
        </div>
    </div>
</div>

<!-- Error Messages -->
<?php if (!empty($errors)): ?>
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Please correct the following errors:</h3>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add Equipment Form -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-8 py-6">
        <form method="POST" action="add_equipment.php" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Equipment Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Equipment Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter equipment name"
                    >
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category" 
                        name="category"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                <?php echo ($formData['category'] ?? '') === $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Serial Number -->
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Serial Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="serial_number" 
                        name="serial_number" 
                        required
                        value="<?php echo htmlspecialchars($formData['serial_number'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter unique serial number"
                    >
                </div>

                <!-- Total Quantity -->
                <div>
                    <label for="total_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Total Quantity <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="total_quantity" 
                        name="total_quantity" 
                        required
                        min="0"
                        value="<?php echo htmlspecialchars($formData['total_quantity'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter quantity"
                    >
                </div>
            </div>

            <!-- Description -->
            <div class="mt-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter equipment description"
                ><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="view_equipment.php" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-md">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Equipment
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

