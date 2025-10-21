    </main>
    
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-gray-600">
                &copy; <?php echo date('Y'); ?> Equipment Inventory Management System. All rights reserved.
            </p>
        </div>
    </footer>
    
    <script src="assets/js/app.js"></script>
    
    <?php
    // Display flash message if exists
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <script>
        showToast('<?php echo addslashes($flash['message']); ?>', '<?php echo $flash['type']; ?>');
    </script>
    <?php endif; ?>
</body>
</html>

