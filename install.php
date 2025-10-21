<<<<<<< HEAD
<?php
/**
 * Installation Script for Equipment Inventory Management System
 * This script creates the database, tables, and initial data
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ims_db');

$errors = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    $errors[] = "PHP 8.0 or higher is required. Current version: " . PHP_VERSION;
}

// Check PDO extension
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    $errors[] = "PDO and PDO_MYSQL extensions are required.";
}

if (empty($errors)) {
    try {
        // Connect to MySQL server (without database)
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Database '" . DB_NAME . "' created successfully.";

        // Use the database
        $pdo->exec("USE `" . DB_NAME . "`");

        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) UNIQUE NOT NULL,
                `password_hash` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'staff') DEFAULT 'staff',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'users' created successfully.";

        // Create categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) UNIQUE NOT NULL,
                `description` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'categories' created successfully.";

        // Create equipment table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `equipment` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `description` TEXT,
                `serial_number` VARCHAR(100) UNIQUE NOT NULL,
                `purchase_date` DATE,
                `purchase_price` DECIMAL(10,2),
                `total_quantity` INT DEFAULT 0,
                `available_quantity` INT DEFAULT 0,
                `in_use_quantity` INT DEFAULT 0,
                `in_replacement_quantity` INT DEFAULT 0,
                `status` ENUM('active', 'discontinued') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_serial` (`serial_number`),
                INDEX `idx_category` (`category`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'equipment' created successfully.";

        // Create equipment_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `equipment_transactions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `equipment_id` INT NOT NULL,
                `transaction_type` ENUM('in_use', 'returned', 'replacement', 'added', 'removed') NOT NULL,
                `quantity` INT NOT NULL,
                `assigned_to` VARCHAR(255),
                `notes` TEXT,
                `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_by` VARCHAR(100),
                FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE,
                INDEX `idx_equipment_id` (`equipment_id`),
                INDEX `idx_transaction_type` (`transaction_type`),
                INDEX `idx_transaction_date` (`transaction_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'equipment_transactions' created successfully.";

        // Insert sample categories
        $categories = [
            ['Electronics', 'Electronic devices and components'],
            ['Furniture', 'Office furniture and fixtures'],
            ['Tools', 'Hand tools and power tools'],
            ['Computers', 'Computer hardware and peripherals'],
            ['Laboratory', 'Laboratory equipment and instruments'],
            ['Safety', 'Safety equipment and protective gear'],
            ['Office Supplies', 'General office supplies and equipment'],
            ['Vehicles', 'Company vehicles and transport equipment']
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO `categories` (`name`, `description`) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        $success[] = "Sample categories inserted successfully.";

        // Create default admin user (username: admin, password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 'admin']);
        $success[] = "Default admin user created (username: admin, password: admin123).";

        // Insert sample equipment data
        $sampleEquipment = [
            ['Laptop Dell XPS 15', 'Computers', 'High-performance laptop for development', 'DELL-XPS-001', '2024-01-15', 1299.99, 10, 7, 2, 1],
            ['Office Chair Ergonomic', 'Furniture', 'Adjustable ergonomic office chair', 'CHAIR-ERG-001', '2024-02-01', 299.99, 25, 20, 5, 0],
            ['Wireless Mouse Logitech', 'Electronics', 'Bluetooth wireless mouse', 'LOG-MOUSE-001', '2024-01-20', 49.99, 50, 35, 12, 3],
            ['Monitor 27" 4K', 'Computers', '27-inch 4K resolution monitor', 'MON-4K-001', '2024-01-10', 449.99, 15, 10, 4, 1],
            ['Multimeter Digital', 'Laboratory', 'Professional digital multimeter', 'MULT-DIG-001', '2023-12-15', 89.99, 8, 4, 3, 1],
            ['Safety Goggles', 'Safety', 'Impact-resistant safety goggles', 'SAFE-GOG-001', '2024-03-01', 15.99, 100, 85, 10, 5],
            ['Cordless Drill', 'Tools', '18V cordless drill with battery', 'DRILL-18V-001', '2024-02-10', 129.99, 12, 8, 3, 1],
            ['Projector HD', 'Electronics', 'HD projector for presentations', 'PROJ-HD-001', '2023-11-20', 599.99, 5, 3, 2, 0]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `equipment` 
            (`name`, `category`, `description`, `serial_number`, `purchase_date`, `purchase_price`, 
             `total_quantity`, `available_quantity`, `in_use_quantity`, `in_replacement_quantity`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($sampleEquipment as $equipment) {
            $stmt->execute($equipment);
        }
        $success[] = "Sample equipment data inserted successfully.";

        // Insert sample transactions
        $sampleTransactions = [
            [1, 'in_use', 2, 'John Doe', 'Assigned for development project', 'admin'],
            [2, 'in_use', 5, 'Multiple Staff', 'Office furniture distribution', 'admin'],
            [3, 'in_use', 12, 'IT Department', 'Standard issue mice', 'admin'],
            [4, 'replacement', 1, NULL, 'Display malfunction', 'admin'],
            [5, 'in_use', 3, 'Lab Team', 'Equipment testing', 'admin']
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `equipment_transactions` 
            (`equipment_id`, `transaction_type`, `quantity`, `assigned_to`, `notes`, `created_by`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($sampleTransactions as $transaction) {
            $stmt->execute($transaction);
        }
        $success[] = "Sample transaction data inserted successfully.";

    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Equipment Inventory Management System</h1>
                    <p class="text-blue-100 text-sm">Installation Script</p>
                </div>
                
                <div class="px-6 py-8">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-6">
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                                <h3 class="font-bold mb-2">Installation Errors:</h3>
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="mb-6">
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                                <h3 class="font-bold mb-2">Installation Successful!</h3>
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($success as $msg): ?>
                                        <li><?php echo htmlspecialchars($msg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                            <h3 class="font-bold text-blue-900 mb-2">Default Login Credentials:</h3>
                            <p class="text-blue-800"><strong>Username:</strong> admin</p>
                            <p class="text-blue-800"><strong>Password:</strong> admin123</p>
                            <p class="text-sm text-blue-600 mt-2">⚠️ Please change the password after first login!</p>
                        </div>

                        <div class="space-y-3">
                            <a href="login.php" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded transition duration-200">
                                Proceed to Login
                            </a>
                            <a href="index.php" class="block w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded transition duration-200">
                                Go to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($errors) && empty($success)): ?>
                        <div class="text-center">
                            <p class="text-gray-600 mb-4">Click the button below to start the installation process.</p>
                            <form method="POST">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                    Install System
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Equipment Inventory Management System v1.0</p>
            </div>
        </div>
    </div>
</body>
</html>

=======
<?php
/**
 * Installation Script for Equipment Inventory Management System
 * This script creates the database, tables, and initial data
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ims_db');

$errors = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    $errors[] = "PHP 8.0 or higher is required. Current version: " . PHP_VERSION;
}

// Check PDO extension
if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
    $errors[] = "PDO and PDO_MYSQL extensions are required.";
}

if (empty($errors)) {
    try {
        // Connect to MySQL server (without database)
        $pdo = new PDO(
            "mysql:host=" . DB_HOST,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Database '" . DB_NAME . "' created successfully.";

        // Use the database
        $pdo->exec("USE `" . DB_NAME . "`");

        // Create users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) UNIQUE NOT NULL,
                `password_hash` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'staff') DEFAULT 'staff',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'users' created successfully.";

        // Create categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(100) UNIQUE NOT NULL,
                `description` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'categories' created successfully.";

        // Create equipment table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `equipment` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `description` TEXT,
                `serial_number` VARCHAR(100) UNIQUE NOT NULL,
                `purchase_date` DATE,
                `purchase_price` DECIMAL(10,2),
                `total_quantity` INT DEFAULT 0,
                `available_quantity` INT DEFAULT 0,
                `in_use_quantity` INT DEFAULT 0,
                `in_replacement_quantity` INT DEFAULT 0,
                `status` ENUM('active', 'discontinued') DEFAULT 'active',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_serial` (`serial_number`),
                INDEX `idx_category` (`category`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'equipment' created successfully.";

        // Create equipment_transactions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `equipment_transactions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `equipment_id` INT NOT NULL,
                `transaction_type` ENUM('in_use', 'returned', 'replacement', 'added', 'removed') NOT NULL,
                `quantity` INT NOT NULL,
                `assigned_to` VARCHAR(255),
                `notes` TEXT,
                `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `created_by` VARCHAR(100),
                FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE,
                INDEX `idx_equipment_id` (`equipment_id`),
                INDEX `idx_transaction_type` (`transaction_type`),
                INDEX `idx_transaction_date` (`transaction_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $success[] = "Table 'equipment_transactions' created successfully.";

        // Insert sample categories
        $categories = [
            ['Electronics', 'Electronic devices and components'],
            ['Furniture', 'Office furniture and fixtures'],
            ['Tools', 'Hand tools and power tools'],
            ['Computers', 'Computer hardware and peripherals'],
            ['Laboratory', 'Laboratory equipment and instruments'],
            ['Safety', 'Safety equipment and protective gear'],
            ['Office Supplies', 'General office supplies and equipment'],
            ['Vehicles', 'Company vehicles and transport equipment']
        ];

        $stmt = $pdo->prepare("INSERT IGNORE INTO `categories` (`name`, `description`) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
        $success[] = "Sample categories inserted successfully.";

        // Create default admin user (username: admin, password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $adminPassword, 'admin']);
        $success[] = "Default admin user created (username: admin, password: admin123).";

        // Insert sample equipment data
        $sampleEquipment = [
            ['Laptop Dell XPS 15', 'Computers', 'High-performance laptop for development', 'DELL-XPS-001', '2024-01-15', 1299.99, 10, 7, 2, 1],
            ['Office Chair Ergonomic', 'Furniture', 'Adjustable ergonomic office chair', 'CHAIR-ERG-001', '2024-02-01', 299.99, 25, 20, 5, 0],
            ['Wireless Mouse Logitech', 'Electronics', 'Bluetooth wireless mouse', 'LOG-MOUSE-001', '2024-01-20', 49.99, 50, 35, 12, 3],
            ['Monitor 27" 4K', 'Computers', '27-inch 4K resolution monitor', 'MON-4K-001', '2024-01-10', 449.99, 15, 10, 4, 1],
            ['Multimeter Digital', 'Laboratory', 'Professional digital multimeter', 'MULT-DIG-001', '2023-12-15', 89.99, 8, 4, 3, 1],
            ['Safety Goggles', 'Safety', 'Impact-resistant safety goggles', 'SAFE-GOG-001', '2024-03-01', 15.99, 100, 85, 10, 5],
            ['Cordless Drill', 'Tools', '18V cordless drill with battery', 'DRILL-18V-001', '2024-02-10', 129.99, 12, 8, 3, 1],
            ['Projector HD', 'Electronics', 'HD projector for presentations', 'PROJ-HD-001', '2023-11-20', 599.99, 5, 3, 2, 0]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `equipment` 
            (`name`, `category`, `description`, `serial_number`, `purchase_date`, `purchase_price`, 
             `total_quantity`, `available_quantity`, `in_use_quantity`, `in_replacement_quantity`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($sampleEquipment as $equipment) {
            $stmt->execute($equipment);
        }
        $success[] = "Sample equipment data inserted successfully.";

        // Insert sample transactions
        $sampleTransactions = [
            [1, 'in_use', 2, 'John Doe', 'Assigned for development project', 'admin'],
            [2, 'in_use', 5, 'Multiple Staff', 'Office furniture distribution', 'admin'],
            [3, 'in_use', 12, 'IT Department', 'Standard issue mice', 'admin'],
            [4, 'replacement', 1, NULL, 'Display malfunction', 'admin'],
            [5, 'in_use', 3, 'Lab Team', 'Equipment testing', 'admin']
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `equipment_transactions` 
            (`equipment_id`, `transaction_type`, `quantity`, `assigned_to`, `notes`, `created_by`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($sampleTransactions as $transaction) {
            $stmt->execute($transaction);
        }
        $success[] = "Sample transaction data inserted successfully.";

    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="bg-blue-600 px-6 py-4">
                    <h1 class="text-2xl font-bold text-white">Equipment Inventory Management System</h1>
                    <p class="text-blue-100 text-sm">Installation Script</p>
                </div>
                
                <div class="px-6 py-8">
                    <?php if (!empty($errors)): ?>
                        <div class="mb-6">
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                                <h3 class="font-bold mb-2">Installation Errors:</h3>
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="mb-6">
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                                <h3 class="font-bold mb-2">Installation Successful!</h3>
                                <ul class="list-disc list-inside space-y-1">
                                    <?php foreach ($success as $msg): ?>
                                        <li><?php echo htmlspecialchars($msg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                            <h3 class="font-bold text-blue-900 mb-2">Default Login Credentials:</h3>
                            <p class="text-blue-800"><strong>Username:</strong> admin</p>
                            <p class="text-blue-800"><strong>Password:</strong> admin123</p>
                            <p class="text-sm text-blue-600 mt-2">⚠️ Please change the password after first login!</p>
                        </div>

                        <div class="space-y-3">
                            <a href="login.php" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded transition duration-200">
                                Proceed to Login
                            </a>
                            <a href="index.php" class="block w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded transition duration-200">
                                Go to Dashboard
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($errors) && empty($success)): ?>
                        <div class="text-center">
                            <p class="text-gray-600 mb-4">Click the button below to start the installation process.</p>
                            <form method="POST">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                    Install System
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Equipment Inventory Management System v1.0</p>
            </div>
        </div>
    </div>
</body>
</html>

>>>>>>> 688f043e915ac216b709f36b533ff7972babe88b
