document.addEventListener('DOMContentLoaded', function() {
    // Sample course data - in a real app, this would come from your backend API
    const programsData = [
        {
            id: 1,
            title: "Frontend Web Development",
            category: "web-development",
            level: "beginner",
            format: "online",
            duration: "8 weeks",
            price: "Free",
            description: "Learn HTML, CSS, and JavaScript to build modern, responsive websites. Perfect for beginners with no prior experience.",
            image: "images/web-dev.jpg"
        },
        {
            id: 2,
            title: "Advanced JavaScript Frameworks",
            category: "web-development",
            level: "intermediate",
            format: "hybrid",
            duration: "10 weeks",
            price: "$149.99",
            description: "Master React, Angular, and Vue.js to build complex, interactive web applications.",
            image: "images/js-frameworks.jpg"
        },
        {
            id: 3,
            title: "Mobile App Development with Flutter",
            category: "mobile-development",
            level: "intermediate",
            format: "in-person",
            duration: "12 weeks",
            price: "$199.99",
            description: "Build cross-platform mobile applications for iOS and Android using Flutter framework.",
            image: "images/flutter.jpg"
        },
        {
            id: 4,
            title: "Data Science Fundamentals",
            category: "data-science",
            level: "beginner",
            format: "online",
            duration: "6 weeks",
            price: "Free",
            description: "Introduction to Python, data analysis, and visualization with Pandas and Matplotlib.",
            image: "images/data-science.jpg"
        },
        {
            id: 5,
            title: "Digital Marketing Strategy",
            category: "digital-marketing",
            level: "beginner",
            format: "hybrid",
            duration: "4 weeks",
            price: "$99.99",
            description: "Learn SEO, social media marketing, content strategy, and analytics tools.",
            image: "images/digital-marketing.jpg"
        },
        {
            id: 6,
            title: "UI/UX Design Principles",
            category: "design",
            level: "beginner",
            format: "online",
            duration: "5 weeks",
            price: "$79.99",
            description: "Master the fundamentals of user interface and user experience design.",
            image: "images/ui-ux.jpg"
        },
        {
            id: 7,
            title: "Cloud Computing with AWS",
            category: "cloud-computing",
            level: "advanced",
            format: "hybrid",
            duration: "14 weeks",
            price: "$249.99",
            description: "Comprehensive training on Amazon Web Services infrastructure and services.",
            image: "images/aws.jpg"
        },
        {
            id: 8,
            title: "Backend Development with Node.js",
            category: "web-development",
            level: "intermediate",
            format: "in-person",
            duration: "10 weeks",
            price: "$179.99",
            description: "Learn to build server-side applications using Node.js, Express, and MongoDB.",
            image: "images/nodejs.jpg"
        }
    ];

    // DOM Elements
    const programsContainer = document.getElementById('programs-container');
    const categoryFilter = document.getElementById('category');
    const levelFilter = document.getElementById('level');
    const formatFilter = document.getElementById('format');
    const resetFiltersBtn = document.getElementById('reset-filters');
    const adminButton = document.createElement('div');
    adminButton.className = 'admin-button';
    adminButton.innerHTML = '<i class="fas fa-plus"></i>';
    document.body.appendChild(adminButton);

    // Modal Elements
    const modal = document.getElementById('admin-modal');
    const closeModal = document.querySelector('.close-modal');
    const addProgramForm = document.getElementById('add-program-form');

    // Display all programs initially
    displayPrograms(programsData);

    // Filter programs based on selected filters
    function filterPrograms() {
        const category = categoryFilter.value;
        const level = levelFilter.value;
        const format = formatFilter.value;

        const filteredPrograms = programsData.filter(program => {
            return (category === 'all' || program.category === category) &&
                   (level === 'all' || program.level === level) &&
                   (format === 'all' || program.format === format);
        });

        displayPrograms(filteredPrograms);
    }

    // Display programs in the grid
    function displayPrograms(programs) {
        if (programs.length === 0) {
            programsContainer.innerHTML = '<div class="no-programs"><p>No programs found matching your filters.</p></div>';
            return;
        }

        programsContainer.innerHTML = '';
        
        programs.forEach(program => {
            const programCard = document.createElement('div');
            programCard.className = 'program-card';
            programCard.innerHTML = `
                <div class="program-image">
                    <img src="${program.image}" alt="${program.title}">
                </div>
                <div class="program-content">
                    <span class="program-category">${formatCategory(program.category)}</span>
                    <h3>${program.title}</h3>
                    <p class="program-description">${program.description}</p>
                    <div class="program-meta">
                        <span class="program-level ${program.level}">${formatLevel(program.level)}</span>
                        <span class="program-format">${formatFormat(program.format)}</span>
                    </div>
                    <div class="program-meta">
                        <span>${program.duration}</span>
                        <span>${program.price}</span>
                    </div>
                    <div class="program-footer">
                        <span class="program-price">${program.price}</span>
                        <div class="program-actions">
                            <a href="program-detail.html?id=${program.id}" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            `;
            programsContainer.appendChild(programCard);
        });
    }

    // Format category for display
    function formatCategory(category) {
        const categories = {
            'web-development': 'Web Development',
            'mobile-development': 'Mobile Development',
            'data-science': 'Data Science',
            'digital-marketing': 'Digital Marketing',
            'design': 'Design',
            'cloud-computing': 'Cloud Computing'
        };
        return categories[category] || category;
    }

    // Format level for display
    function formatLevel(level) {
        return level.charAt(0).toUpperCase() + level.slice(1);
    }

    // Format format for display
    function formatFormat(format) {
        const formats = {
            'online': 'Online',
            'in-person': 'In-Person',
            'hybrid': 'Hybrid'
        };
        return formats[format] || format;
    }

    // Event listeners for filters
    categoryFilter.addEventListener('change', filterPrograms);
    levelFilter.addEventListener('change', filterPrograms);
    formatFilter.addEventListener('change', filterPrograms);

    resetFiltersBtn.addEventListener('click', function() {
        categoryFilter.value = 'all';
        levelFilter.value = 'all';
        formatFilter.value = 'all';
        filterPrograms();
    });

    // Admin panel functionality
    adminButton.addEventListener('click', function() {
        modal.style.display = 'block';
    });

    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    addProgramForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // In a real app, this would send data to your backend API
        const newProgram = {
            id: programsData.length + 1,
            title: document.getElementById('program-title').value,
            category: document.getElementById('program-category').value,
            level: document.getElementById('program-level').value,
            format: document.getElementById('program-format').value,
            duration: document.getElementById('program-duration').value,
            price: document.getElementById('program-price').value,
            description: document.getElementById('program-description').value,
            image: document.getElementById('program-image').value || 'images/default-course.jpg'
        };

        // Add to local array (in real app, this would be an API call)
        programsData.unshift(newProgram);
        
        // Refresh display
        filterPrograms();
        
        // Close modal and reset form
        modal.style.display = 'none';
        this.reset();
        
        alert('Program added successfully!');
    });

    // Check URL parameters for initial filters
    function checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category');
        
        if (category) {
            categoryFilter.value = category;
            filterPrograms();
        }
    }

    checkUrlParams();
});