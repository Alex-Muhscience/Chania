            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php if (Utilities::isLoggedIn()): ?>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>&copy; <?= date('Y') ?> <?= defined('ADMIN_TITLE') ? ADMIN_TITLE : 'Chania Admin' ?>. All rights reserved.</span>
                    </div>
                </div>
            </footer>
            <?php endif; ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom SB Admin 2 JS -->
    <script src="<?= BASE_URL ?>/admin/public/assets/js/sb-admin-2.min.js"></script>

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

        // Subtle Real-time Notifications System
        class SubtleNotifications {
            constructor() {
                this.eventSource = null;
                this.retryAttempts = 0;
                this.maxRetries = 5;
                this.baseRetryDelay = 1000;
                this.lastUpdateTime = Date.now();
                this.lastIncrements = {}; // Track last increment times to prevent duplicates
                
                this.init();
            }
            
            init() {
                this.setupEventSource();
                this.setupNotificationContainer();
            }
            
            setupEventSource() {
                if (typeof EventSource === 'undefined') {
                    return; // Silently fail
                }
                
                const baseUrl = '<?= BASE_URL ?>';
                const lastCheck = localStorage.getItem('lastNotificationCheck') || new Date().toISOString();
                
                this.eventSource = new EventSource(`${baseUrl}/admin/ajax/realtime_notifications.php?lastCheck=${lastCheck}`);
                
                this.eventSource.onopen = () => {
                    this.retryAttempts = 0;
                };
                
                // Handle specific event types with subtle updates only
                this.eventSource.addEventListener('new_event_registration', (event) => {
                    const data = JSON.parse(event.data);
                    this.updateSubtleNotifications(data);
                    this.updateSidebarBadge('event_registrations');
                });
                
                this.eventSource.addEventListener('new_contact', (event) => {
                    const data = JSON.parse(event.data);
                    this.updateSubtleNotifications(data);
                    this.updateSidebarBadge('contacts');
                });
                
                this.eventSource.addEventListener('new_application', (event) => {
                    const data = JSON.parse(event.data);
                    this.updateSubtleNotifications(data);
                    this.updateSidebarBadge('applications');
                });
                
                this.eventSource.addEventListener('new_newsletter_subscription', (event) => {
                    const data = JSON.parse(event.data);
                    this.updateSubtleNotifications(data);
                    // Newsletter doesn't have sidebar badge, just topbar
                    this.updateTopbarBadge();
                });
                
                this.eventSource.addEventListener('heartbeat', (event) => {
                    localStorage.setItem('lastNotificationCheck', new Date().toISOString());
                });
                
                this.eventSource.onerror = (error) => {
                    this.handleConnectionError();
                };
            }
            
            handleConnectionError() {
                if (this.eventSource) {
                    this.eventSource.close();
                }
                
                if (this.retryAttempts < this.maxRetries) {
                    const delay = this.baseRetryDelay * Math.pow(2, this.retryAttempts);
                    
                    setTimeout(() => {
                        this.retryAttempts++;
                        this.setupEventSource();
                    }, delay);
                }
            }
            
            updateSubtleNotifications(notification) {
                // Only update the dropdown list - no sounds or popups
                this.addToDropdown(notification);
                this.updateTopbarBadge();
            }
            
            addToDropdown(notification) {
                const dropdown = document.querySelector('#alertsDropdown + .dropdown-menu');
                if (!dropdown) return;
                
                // Remove "No new notifications" message if it exists
                const noNotificationMsg = dropdown.querySelector('.text-gray-500');
                if (noNotificationMsg && noNotificationMsg.textContent.includes('No new notifications')) {
                    noNotificationMsg.remove();
                }
                
                // Create new notification item
                const notificationItem = document.createElement('a');
                notificationItem.className = 'dropdown-item d-flex align-items-center';
                
                let url = '<?= BASE_URL ?>/admin/public/';
                if (notification.type === 'event_registration') {
                    url += 'event_registrations.php';
                } else if (notification.type === 'newsletter_subscription') {
                    url += 'newsletter.php';
                } else if (notification.type === 'contact_submission') {
                    url += 'contacts.php';
                } else if (notification.type === 'application_submission') {
                    url += 'applications.php';
                }
                
                notificationItem.href = url;
                notificationItem.innerHTML = `
                    <div class="mr-3">
                        <div class="icon-circle bg-${notification.color}">
                            <i class="${notification.icon} text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">${new Date().toLocaleString()}</div>
                        <span class="font-weight-bold">${notification.message.substring(0, 50)}...</span>
                    </div>
                `;
                
                // Insert at the top (after header)
                const header = dropdown.querySelector('.dropdown-header');
                if (header && header.nextSibling) {
                    dropdown.insertBefore(notificationItem, header.nextSibling);
                } else {
                    dropdown.appendChild(notificationItem);
                }
                
                // Keep only the 5 most recent notifications
                const items = dropdown.querySelectorAll('a.dropdown-item');
                if (items.length > 5) {
                    for (let i = 5; i < items.length; i++) {
                        items[i].remove();
                    }
                }
            }
            
            updateTopbarBadge() {
                const badge = document.querySelector('#alertsDropdown .badge-counter');
                if (badge) {
                    const currentCount = parseInt(badge.textContent) || 0;
                    const newCount = currentCount + 1;
                    badge.textContent = newCount > 9 ? '9+' : newCount;
                    badge.style.display = 'inline';
                } else {
                    // Create badge if it doesn't exist
                    const alertsLink = document.querySelector('#alertsDropdown');
                    if (alertsLink) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge badge-danger badge-counter';
                        newBadge.textContent = '1';
                        alertsLink.appendChild(newBadge);
                    }
                }
            }
            
            updateSidebarBadge(type) {
                // Instead of continuously incrementing, we'll refresh the count from server
                // or track unique notifications to avoid multiple increments
                const refreshUrl = `<?= BASE_URL ?>/admin/ajax/get_notification_counts.php`;
                
                fetch(refreshUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update sidebar badges with actual counts from database
                            if (type === 'applications' && data.counts.applications > 0) {
                                this.updateSidebarBadgeCount('a[href*="applications.php"] .notification-badge', data.counts.applications);
                            }
                            if (type === 'contacts' && data.counts.contacts > 0) {
                                this.updateSidebarBadgeCount('a[href*="contacts.php"] .notification-badge', data.counts.contacts);
                            }
                            if (type === 'event_registrations' && data.counts.event_registrations > 0) {
                                this.updateSidebarBadgeCount('a[href*="event_registrations.php"] .notification-badge', data.counts.event_registrations);
                            }
                        }
                    })
                    .catch(error => {
                        console.log('Could not fetch notification counts:', error);
                        // Fallback: increment by 1 only if badge doesn't exist or this is a new unique notification
                        if (type === 'applications') {
                            this.safeIncrementSidebarBadge('a[href*="applications.php"] .notification-badge');
                        } else if (type === 'contacts') {
                            this.safeIncrementSidebarBadge('a[href*="contacts.php"] .notification-badge');
                        } else if (type === 'event_registrations') {
                            this.safeIncrementSidebarBadge('a[href*="event_registrations.php"] .notification-badge');
                        }
                    });
            }
            
            updateSidebarBadgeCount(selector, count) {
                const badge = document.querySelector(selector);
                if (badge) {
                    badge.textContent = count;
                } else if (count > 0) {
                    // Create badge if it doesn't exist and count > 0
                    const link = document.querySelector(selector.replace(' .notification-badge', ''));
                    if (link) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = count;
                        link.appendChild(newBadge);
                    }
                }
            }
            
            safeIncrementSidebarBadge(selector) {
                // Only increment if the notification is genuinely new
                const badge = document.querySelector(selector);
                if (badge) {
                    const currentCount = parseInt(badge.textContent) || 0;
                    // Check if we've already incremented recently to avoid duplicates
                    const now = Date.now();
                    const lastIncrement = this.lastIncrements?.[selector] || 0;
                    
                    // Only increment if more than 2 seconds have passed since last increment
                    if (now - lastIncrement > 2000) {
                        badge.textContent = currentCount + 1;
                        this.lastIncrements = this.lastIncrements || {};
                        this.lastIncrements[selector] = now;
                    }
                } else {
                    // Create badge if it doesn't exist
                    const link = document.querySelector(selector.replace(' .notification-badge', ''));
                    if (link) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'notification-badge';
                        newBadge.textContent = '1';
                        link.appendChild(newBadge);
                        this.lastIncrements = this.lastIncrements || {};
                        this.lastIncrements[selector] = Date.now();
                    }
                }
            }
            
            setupNotificationContainer() {
                // Add CSS for subtle notifications only
                const style = document.createElement('style');
                style.textContent = `
                    .notification-pulse {
                        animation: pulse 2s infinite;
                    }
                    
                    @keyframes pulse {
                        0% { opacity: 1; }
                        50% { opacity: 0.5; }
                        100% { opacity: 1; }
                    }
                    
                    .icon-circle {
                        width: 2rem;
                        height: 2rem;
                        border-radius: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .notification-badge {
                        position: absolute;
                        top: 50%;
                        right: 10px;
                        transform: translateY(-50%);
                        background-color: #e74c3c;
                        color: white;
                        border-radius: 50%;
                        width: 22px;
                        height: 22px;
                        font-size: 12px;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                        border: 2px solid #fff;
                        z-index: 10;
                        transition: all 0.3s ease;
                        animation: pulse-badge 2s infinite;
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Initialize real-time notifications when document is ready
        $(document).ready(function() {
            <?php if (Utilities::isLoggedIn()): ?>
                new SubtleNotifications();
            <?php endif; ?>
        });
    </script>

</body>
</html>
