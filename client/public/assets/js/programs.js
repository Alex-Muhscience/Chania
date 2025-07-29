document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[method="get"]');
    const programsContainer = document.querySelector('.row.g-4');
    const paginationContainer = document.querySelector('nav[aria-label="Program pagination"]');
    const countDisplay = document.querySelector('.text-muted');
    const emptyState = document.querySelector('.text-center.py-5');
    
    let currentRequest = null;
    let isLoading = false;

    // Initialize event listeners
    initializeEventListeners();

    function initializeEventListeners() {
        if (searchForm) {
            // Real-time search
            const searchInput = searchForm.querySelector('#search');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        performSearch();
                    }, 500); // 500ms delay for better UX
                });
            }

            // Category filter
            const categorySelect = searchForm.querySelector('#category');
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    performSearch();
                });
            }

            // Difficulty filter
            const difficultySelect = searchForm.querySelector('#difficulty');
            if (difficultySelect) {
                difficultySelect.addEventListener('change', function() {
                    performSearch();
                });
            }

            // Prevent form submission - use AJAX instead
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                performSearch();
            });
        }

        // Handle pagination clicks
        document.addEventListener('click', function(e) {
            if (e.target.matches('.page-link') || e.target.closest('.page-link')) {
                e.preventDefault();
                const link = e.target.closest('.page-link');
                if (link && link.href) {
                    const url = new URL(link.href);
                    const page = url.searchParams.get('page');
                    if (page) {
                        performSearch(parseInt(page));
                    }
                }
            }
        });
    }

    function performSearch(page = 1) {
        if (isLoading) {
            if (currentRequest) {
                currentRequest.abort();
            }
        }

        isLoading = true;
        showLoading();

        // Collect form data
        const formData = new FormData(searchForm);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value.trim()) {
                params.append(key, value.trim());
            }
        }
        
        params.set('page', page);
        params.set('ajax', '1'); // Indicate this is an AJAX request

        // Make AJAX request
        currentRequest = fetch(`programs_api.php?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updatePage(data);
            updateURL(params);
        })
        .catch(error => {
            console.error('Error loading programs:', error);
            showError('Failed to load programs. Please try again.');
        })
        .finally(() => {
            isLoading = false;
            hideLoading();
            currentRequest = null;
        });
    }

    function updatePage(data) {
        // Update programs display
        if (data.programs && data.programs.length > 0) {
            updateProgramsDisplay(data.programs);
            updatePagination(data.pagination);
            updateCount(data.pagination);
            hideEmptyState();
        } else {
            showEmptyState();
            hidePagination();
        }
    }

    function updateProgramsDisplay(programs) {
        if (!programsContainer) return;

        const programsHTML = programs.map(program => createProgramCard(program)).join('');
        programsContainer.innerHTML = programsHTML;
        
        // Animate cards in
        const cards = programsContainer.querySelectorAll('.col-md-6');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    function createProgramCard(program) {
        const baseURL = document.querySelector('meta[name="base-url"]')?.content || '';
        const categoryBadge = program.category_name ? 
            `<span class="badge" style="background-color: ${program.category_color || '#007bff'}; color: white;">
                ${program.category_icon ? `<i class="${program.category_icon}"></i> ` : ''}
                ${program.category_name}
            </span>` : 
            '<span class="badge bg-secondary">No Category</span>';

        const featuredBadge = program.is_featured ? 
            '<span class="badge bg-primary">Featured</span>' : '';

        return `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-top overflow-hidden" style="height: 180px;">
                        <img src="${baseURL}/uploads/${program.image_path}" 
                             alt="${program.title}" 
                             class="img-fluid w-100 h-100 object-fit-cover"
                             loading="lazy">
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">${program.title}</h5>
                            ${featuredBadge}
                        </div>
                        <p class="card-text text-muted small">${program.short_description}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            ${categoryBadge}
                            <span class="text-muted small">${program.duration}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-grid">
                            <a href="${baseURL}/client/public/program_detail.php?id=${program.id}" 
                               class="btn btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function updatePagination(pagination) {
        if (!paginationContainer || !pagination) return;

        if (pagination.total_pages <= 1) {
            hidePagination();
            return;
        }

        const paginationHTML = createPaginationHTML(pagination);
        paginationContainer.innerHTML = paginationHTML;
        paginationContainer.style.display = 'block';
    }

    function createPaginationHTML(pagination) {
        const baseURL = document.querySelector('meta[name="base-url"]')?.content || '';
        const currentPage = pagination.current_page;
        const totalPages = pagination.total_pages;
        
        let html = '<ul class="pagination justify-content-center">';
        
        // Previous button
        html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">`;
        if (currentPage > 1) {
            html += `<a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous page">
                        <i class="fas fa-chevron-left"></i> Previous
                     </a>`;
        } else {
            html += '<span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>';
        }
        html += '</li>';

        // Page numbers
        const showPages = generatePageNumbers(currentPage, totalPages);
        showPages.forEach(pageNum => {
            if (pageNum === '...') {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            } else {
                const isActive = pageNum === currentPage;
                html += `<li class="page-item ${isActive ? 'active' : ''}">`;
                if (isActive) {
                    html += `<span class="page-link">${pageNum}</span>`;
                } else {
                    html += `<a class="page-link" href="#" data-page="${pageNum}" aria-label="Page ${pageNum}">${pageNum}</a>`;
                }
                html += '</li>';
            }
        });

        // Next button
        html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">`;
        if (currentPage < totalPages) {
            html += `<a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next page">
                        Next <i class="fas fa-chevron-right"></i>
                     </a>`;
        } else {
            html += '<span class="page-link">Next <i class="fas fa-chevron-right"></i></span>';
        }
        html += '</li>';
        html += '</ul>';

        // Page info
        html += `<div class="text-center mt-3">
                    <small class="text-muted">
                        Page ${currentPage} of ${totalPages} (${pagination.total_items} total programs)
                    </small>
                 </div>`;

        return html;
    }

    function generatePageNumbers(current, total) {
        const pages = [];
        
        if (total <= 7) {
            for (let i = 1; i <= total; i++) {
                pages.push(i);
            }
        } else {
            pages.push(1);
            if (current > 4) pages.push('...');
            
            const start = Math.max(2, current - 1);
            const end = Math.min(total - 1, current + 1);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            if (current < total - 3) pages.push('...');
            pages.push(total);
        }
        
        return pages;
    }

    function updateCount(pagination) {
        if (!countDisplay || !pagination) return;

        const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
        const end = Math.min(start + pagination.per_page - 1, pagination.total_items);
        
        countDisplay.textContent = `Showing ${start} - ${end} of ${pagination.total_items} programs`;
    }

    function updateURL(params) {
        const newURL = new URL(window.location);
        
        // Clear existing search params
        newURL.searchParams.delete('search');
        newURL.searchParams.delete('category');
        newURL.searchParams.delete('difficulty');
        newURL.searchParams.delete('page');
        
        // Add new params
        for (let [key, value] of params.entries()) {
            if (key !== 'ajax' && value) {
                newURL.searchParams.set(key, value);
            }
        }
        
        // Update URL without page reload
        window.history.replaceState({}, '', newURL.toString());
    }

    function showLoading() {
        // Add loading spinner to search button
        const submitBtn = searchForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            submitBtn.dataset.originalHTML = originalHTML;
        }

        // Add loading overlay to programs container
        if (programsContainer) {
            programsContainer.style.opacity = '0.5';
            programsContainer.style.pointerEvents = 'none';
        }
    }

    function hideLoading() {
        const submitBtn = searchForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.dataset.originalHTML) {
                submitBtn.innerHTML = submitBtn.dataset.originalHTML;
            }
        }

        if (programsContainer) {
            programsContainer.style.opacity = '1';
            programsContainer.style.pointerEvents = 'auto';
        }
    }

    function showEmptyState() {
        if (emptyState) {
            emptyState.style.display = 'block';
        }
        if (programsContainer) {
            programsContainer.style.display = 'none';
        }
    }

    function hideEmptyState() {
        if (emptyState) {
            emptyState.style.display = 'none';
        }
        if (programsContainer) {
            programsContainer.style.display = 'flex';
        }
    }

    function hidePagination() {
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }
    }

    function showError(message) {
        // Create or update error message
        let errorDiv = document.querySelector('.programs-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger programs-error';
            programsContainer.parentNode.insertBefore(errorDiv, programsContainer);
        }
        
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Error:</strong> ${message}
            <button type="button" class="btn-close float-end" aria-label="Close"></button>
        `;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
});
