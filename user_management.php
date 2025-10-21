<?php
/**
 * User Management - Super Admin interface for managing users
 */

$page_title = 'User Management';

require_once 'includes/auth_check.php';
require_once 'includes/header.php';

// Check if user is super admin
$user = getCurrentUser();
if ($user['role'] !== 'super_admin') {
    setFlashMessage('error', 'Access denied. Super admin privileges required.');
    redirect('index.php');
}

$errors = [];
$success = [];

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_user') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitizeInput($_POST['role'] ?? 'staff');
        
        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if (!in_array($role, ['admin', 'staff'])) {
            $errors[] = 'Invalid role selected.';
        }
        
        if (empty($errors)) {
            try {
                $pdo = getDBConnection();
                
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username already exists.';
                } else {
                    // Create new user
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                    $result = $stmt->execute([$username, $passwordHash, $role]);
                    
                    if ($result) {
                        $success[] = "User '$username' created successfully with role '$role'.";
                    } else {
                        $errors[] = 'Failed to create user. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                error_log("User creation error: " . $e->getMessage());
                $errors[] = 'An error occurred while creating user. Please try again.';
            }
        }
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $userId = (int)($_POST['user_id'] ?? 0);
        
        if ($userId > 0) {
            try {
                $pdo = getDBConnection();
                
                // Prevent deletion of super admin and current user
                $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $targetUser = $stmt->fetch();
                
                if ($targetUser) {
                    if ($targetUser['role'] === 'super_admin') {
                        $errors[] = 'Cannot delete super admin user.';
                    } elseif ($targetUser['username'] === $user['username']) {
                        $errors[] = 'Cannot delete your own account.';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $result = $stmt->execute([$userId]);
                        
                        if ($result) {
                            $success[] = "User '{$targetUser['username']}' deleted successfully.";
                        } else {
                            $errors[] = 'Failed to delete user. Please try again.';
                        }
                    }
                } else {
                    $errors[] = 'User not found.';
                }
            } catch (PDOException $e) {
                error_log("User deletion error: " . $e->getMessage());
                $errors[] = 'An error occurred while deleting user. Please try again.';
            }
        }
    }
}

// Get all users
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$csrf_token = generateCSRFToken();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600 mt-2">Manage system users and permissions</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="toggleCreateForm()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create New User
            </button>
        </div>
    </div>
</div>

<!-- Messages -->
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

<?php if (!empty($success)): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Success:</h3>
                <ul class="mt-2 text-sm text-green-700 list-disc list-inside">
                    <?php foreach ($success as $msg): ?>
                        <li><?php echo htmlspecialchars($msg); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Create User Form (Hidden by default) -->
<div id="createUserForm" class="bg-white rounded-lg shadow-md p-6 mb-6" style="display: none;">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Create New User</h2>
    <form method="POST" action="user_management.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="create_user">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter username"
                >
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    minlength="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter password"
                >
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select 
                    id="role" 
                    name="role"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        
        <div class="mt-6 flex items-center justify-end space-x-4">
            <button type="button" onclick="toggleCreateForm()" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition shadow-md">
                Create User
            </button>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">System Users</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $userItem): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($userItem['username']); ?>
                                <?php if ($userItem['username'] === $user['username']): ?>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">You</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $roleColors = [
                                'super_admin' => 'bg-purple-100 text-purple-800',
                                'admin' => 'bg-red-100 text-red-800',
                                'staff' => 'bg-green-100 text-green-800'
                            ];
                            $roleLabels = [
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin',
                                'staff' => 'Staff'
                            ];
                            ?>
                            <span class="px-2 py-1 text-xs font-medium rounded <?php echo $roleColors[$userItem['role']]; ?>">
                                <?php echo $roleLabels[$userItem['role']]; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo formatDate($userItem['created_at']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <?php if ($userItem['role'] !== 'super_admin' && $userItem['username'] !== $user['username']): ?>
                                <form method="POST" action="user_management.php" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $userItem['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete User">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleCreateForm() {
    const form = document.getElementById('createUserForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
