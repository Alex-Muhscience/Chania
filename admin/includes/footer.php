

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

    <!-- Custom JavaScript -->
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.table').DataTable({
                "paging": false,
                "info": false,
                "searching": false,
                "ordering": true
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    </script>
