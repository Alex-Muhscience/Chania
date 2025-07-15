// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Initialize DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
    
    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
    
    // Scroll to top functionality
    const scrollToTop = document.querySelector('.scroll-to-top');
    
    if (scrollToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                scrollToTop.classList.add('show');
            } else {
                scrollToTop.classList.remove('show');
            }
        });
        
        scrollToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.querySelector('.btn-close')) {
                alert.querySelector('.btn-close').click();
            } else {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Image preview functionality
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Status update functionality
    const statusSelects = document.querySelectorAll('.status-update');
    statusSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            const id = this.getAttribute('data-id');
            const type = this.getAttribute('data-type');
            const status = this.value;
            
            if (id && type && status) {
                updateStatus(id, type, status, this);
            }
        });
    });
    
    // Bulk actions
    const bulkActionForm = document.getElementById('bulk-action-form');
    if (bulkActionForm) {
        const selectAllCheckbox = document.getElementById('select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const bulkActionSelect = document.getElementById('bulk-action');
        const bulkActionButton = document.getElementById('bulk-action-btn');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateBulkActionButton();
            });
        }
        
        itemCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', updateBulkActionButton);
        });
        
        function updateBulkActionButton() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (bulkActionButton) {
                bulkActionButton.disabled = checkedItems.length === 0;
            }
        }
        
        if (bulkActionButton) {
            bulkActionButton.addEventListener('click', function(e) {
                const checkedItems = document.querySelectorAll('.item-checkbox:checked');
                const action = bulkActionSelect ? bulkActionSelect.value : '';
                
                if (checkedItems.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one item.');
                    return;
                }
                
                if (!action) {
                    e.preventDefault();
                    alert('Please select an action.');
                    return;
                }
                
                const confirmMessage = `Are you sure you want to ${action} ${checkedItems.length} item(s)?`;
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return;
                }
            });
        }
    }
});

// Status update function
function updateStatus(id, type, status, element) {
    const originalValue = element.getAttribute('data-original-value');
    
    fetch(`${window.location.origin}/chania/admin/public/api/update-status.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: id,
            type: type,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status updated successfully', 'success');
            element.setAttribute('data-original-value', status);
        } else {
            showAlert('Failed to update status: ' + (data.message || 'Unknown error'), 'danger');
            element.value = originalValue;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating status', 'danger');
        element.value = originalValue;
    });
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.container-fluid');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.querySelector('.btn-close')) {
            alert.querySelector('.btn-close').click();
        }
    }, 5000);
}

// Export functionality
function exportData(format, type) {
    const url = `${window.location.origin}/chania/admin/public/export.php?format=${format}&type=${type}`;
    window.open(url, '_blank');
}

// Print functionality
function printPage() {
    window.print();
}