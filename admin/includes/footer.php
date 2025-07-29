

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
            // Only initialize DataTables on tables that have proper structure (thead and tbody)
            $('.table:not(.no-datatables)').each(function() {
                var $table = $(this);
                // Check if table has thead or at least th elements for proper DataTables structure
                if ($table.find('thead').length > 0 || $table.find('th').length > 0) {
                    try {
                        $table.DataTable({
                            "paging": false,
                            "info": false,
                            "searching": false,
                            "ordering": true,
                            "columnDefs": [
                                { "orderable": false, "targets": "no-sort" }
                            ]
                        });
                    } catch (e) {
                        console.warn('Could not initialize DataTable on table:', $table, 'Error:', e);
                    }
                } else {
                    // Add no-datatables class to prevent future attempts
                    $table.addClass('no-datatables');
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
