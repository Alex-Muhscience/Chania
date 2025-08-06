

    <!-- Footer -->
    <?php if (Utilities::isLoggedIn()): ?>
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <small class="text-muted">
                &copy; <?= date('Y') ?> <?= ADMIN_TITLE ?>. All rights reserved.
            </small>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/public/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            // Pages Table
            if ($('#pagesTable').length > 0) {
                var $pagesTable = $('#pagesTable');
                var columnCount = $pagesTable.find('thead tr:first th').length;
                
                try {
                    $pagesTable.DataTable({
                        "paging": true,
                        "lengthChange": true,
                        "searching": true,
                        "ordering": true,
                        "info": true,
                        "autoWidth": false,
                        "responsive": true,
                        "pageLength": 10,
                        "columnDefs": [
                            { "orderable": false, "targets": [columnCount - 1] } // Actions column not sortable
                        ]
                    });
                } catch (e) {
                    console.warn('Could not initialize DataTable on pagesTable:', e);
                }
            }
            
            // Media Table
            if ($('#mediaTable').length > 0) {
                $('#mediaTable').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "ordering": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true,
                    "pageLength": 10
                });
            }
            
            // Generic table initialization for other tables
            $('.table.data-table:not(#pagesTable):not(#mediaTable)').each(function() {
                var $table = $(this);
                if ($table.find('thead').length > 0) {
                    try {
                        $table.DataTable({
                            "paging": true,
                            "lengthChange": true,
                            "searching": true,
                            "ordering": true,
                            "info": true,
                            "autoWidth": false,
                            "responsive": true,
                            "pageLength": 10
                        });
                    } catch (e) {
                        console.warn('Could not initialize DataTable on table:', $table, 'Error:', e);
                    }
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Dark Mode Toggle Functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const body = document.body;

        // Check for saved dark mode preference or default to light mode
        const savedMode = localStorage.getItem('darkMode');
        if (savedMode === 'enabled') {
            body.classList.add('dark-mode');
            darkModeIcon.classList.remove('fa-moon');
            darkModeIcon.classList.add('fa-sun');
        }

        // Toggle dark mode on button click
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                // Switch to dark mode
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                // Switch to light mode
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
                localStorage.setItem('darkMode', 'disabled');
            }
        });

        // Navigation Enhancement
        $(document).ready(function() {
            // Handle sidebar navigation clicks
            $('.sidebar .nav-link').on('click', function(e) {
                const href = $(this).attr('href');
                
                // Only handle internal admin links
                if (href && href.indexOf('/admin/public/') !== -1) {
                    console.log('Navigating to:', href);
                    
                    // Remove active class from all nav items
                    $('.sidebar .nav-item').removeClass('active');
                    
                    // Add active class to clicked item
                    $(this).closest('.nav-item').addClass('active');
                    
                    // Let the browser handle the navigation normally
                    return true;
                }
            });
            
            // Sidebar toggle functionality
            $('#sidebarToggle, #sidebarToggleTop').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-toggled');
                $('.sidebar').toggleClass('toggled');
            });
            
            // Auto-close sidebar on mobile when clicking outside
            $(document).on('click', function(e) {
                if ($(window).width() < 768) {
                    if (!$(e.target).closest('.sidebar, #sidebarToggleTop').length) {
                        $('body').removeClass('sidebar-toggled');
                        $('.sidebar').removeClass('toggled');
                    }
                }
            });
        });
    </script>
