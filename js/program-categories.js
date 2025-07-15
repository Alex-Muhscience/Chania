document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const categoriesContainer = document.getElementById('categories-container');
    const categorySearch = document.getElementById('category-search');
    const resetSearch = document.getElementById('reset-search');

    // Sample data - in a real app, this would come from your backend API
    const categoriesData = [
        {
            id: 'web-development',
            name: 'Web Development',
            description: 'Learn to build modern, responsive websites and web applications',
            icon: 'fas fa-laptop-code',
            courses: [
                { name: 'Frontend Fundamentals (HTML, CSS, JavaScript)', level: 'beginner' },
                { name: 'React.js Complete Guide', level: 'intermediate' },
                { name: 'Node.js & Express Backend Development', level: 'intermediate' },
                { name: 'Full Stack Web Development', level: 'advanced' },
                { name: 'Web Performance Optimization', level: 'advanced' }
            ]
        },
        {
            id: 'mobile-development',
            name: 'Mobile Development',
            description: 'Build cross-platform mobile applications for iOS and Android',
            icon: 'fas fa-mobile-alt',
            courses: [
                { name: 'Introduction to Mobile Development', level: 'beginner' },
                { name: 'Flutter App Development', level: 'intermediate' },
                { name: 'React Native Crash Course', level: 'intermediate' },
                { name: 'Advanced Mobile Architecture', level: 'advanced' }
            ]
        },
        {
            id: 'data-science',
            name: 'Data Science',
            description: 'Master data analysis, visualization, and machine learning',
            icon: 'fas fa-database',
            courses: [
                { name: 'Python for Data Science', level: 'beginner' },
                { name: 'Data Analysis with Pandas', level: 'intermediate' },
                { name: 'Machine Learning Fundamentals', level: 'intermediate' },
                { name: 'Deep Learning with TensorFlow', level: 'advanced' },
                { name: 'Big Data with Spark', level: 'advanced' }
            ]
        },
        {
            id: 'digital-marketing',
            name: 'Digital Marketing',
            description: 'Learn SEO, social media, content marketing, and analytics',
            icon: 'fas fa-bullhorn',
            courses: [
                { name: 'Digital Marketing Basics', level: 'beginner' },
                { name: 'SEO & Content Strategy', level: 'intermediate' },
                { name: 'Social Media Marketing', level: 'intermediate' },
                { name: 'Google Analytics & Ads', level: 'advanced' }
            ]
        },
        {
            id: 'ui-ux-design',
            name: 'UI/UX Design',
            description: 'Design intuitive user interfaces and engaging user experiences',
            icon: 'fas fa-paint-brush',
            courses: [
                { name: 'UI Design Principles', level: 'beginner' },
                { name: 'UX Research Methods', level: 'intermediate' },
                { name: 'Figma Masterclass', level: 'intermediate' },
                { name: 'Design Systems', level: 'advanced' }
            ]
        },
        {
            id: 'cloud-computing',
            name: 'Cloud Computing',
            description: 'Deploy and manage applications on cloud platforms',
            icon: 'fas fa-cloud',
            courses: [
                { name: 'Cloud Fundamentals', level: 'beginner' },
                { name: 'AWS Certified Solutions Architect', level: 'intermediate' },
                { name: 'Azure Infrastructure', level: 'intermediate' },
                { name: 'DevOps with Kubernetes', level: 'advanced' }
            ]
        }
    ];

    // Display all categories initially
    displayCategories(categoriesData);

    // Search functionality
    categorySearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        if (searchTerm === '') {
            displayCategories(categoriesData);
            return;
        }

        const filteredCategories = categoriesData.filter(category => {
            return category.name.toLowerCase().includes(searchTerm) ||
                   category.description.toLowerCase().includes(searchTerm) ||
                   category.courses.some(course => course.name.toLowerCase().includes(searchTerm));
        });

        displayCategories(filteredCategories);
    });

    // Reset search
    resetSearch.addEventListener('click', function() {
        categorySearch.value = '';
        displayCategories(categoriesData);
    });

    // Display categories in the container
    function displayCategories(categories) {
        if (categories.length === 0) {
            categoriesContainer.innerHTML = '<div class="no-results"><p>No categories found matching your search.</p></div>';
            return;
        }

        categoriesContainer.innerHTML = '';

        categories.forEach(category => {
            const categoryCard = document.createElement('div');
            categoryCard.className = 'category-card';
            categoryCard.id = category.id;
            
            categoryCard.innerHTML = `
                <div class="category-header">
                    <div class="category-icon">
                        <i class="${category.icon}"></i>
                    </div>
                    <div class="category-title">
                        <h2>${category.name}</h2>
                        <p>${category.description}</p>
                    </div>
                </div>
                <div class="category-courses">
                    <ul class="course-list">
                        ${category.courses.map(course => `
                            <li class="course-item">
                                <span class="course-name">${course.name}</span>
                                <span class="course-meta">
                                    <span class="course-level ${course.level}">${formatLevel(course.level)}</span>
                                </span>
                            </li>
                        `).join('')}
                    </ul>
                    <div class="view-all">
                        <a href="programs.html?category=${category.id}" class="btn btn-outline">View All ${category.name} Courses</a>
                    </div>
                </div>
                <button class="toggle-courses">
                    View Courses
                    <i class="fas fa-chevron-down"></i>
                </button>
            `;

            categoriesContainer.appendChild(categoryCard);

            // Add click event to toggle button
            const toggleBtn = categoryCard.querySelector('.toggle-courses');
            toggleBtn.addEventListener('click', function() {
                categoryCard.classList.toggle('active');
                this.textContent = categoryCard.classList.contains('active') ? 'Hide Courses' : 'View Courses';
            });
        });
    }

    // Format level for display
    function formatLevel(level) {
        return level.charAt(0).toUpperCase() + level.slice(1);
    }

    // Check URL for anchor links to open specific category
    function checkUrlHash() {
        if (window.location.hash) {
            const categoryId = window.location.hash.substring(1);
            const categoryCard = document.getElementById(categoryId);
            
            if (categoryCard) {
                // Scroll to the category
                setTimeout(() => {
                    categoryCard.scrollIntoView({ behavior: 'smooth' });
                    
                    // Open the category
                    categoryCard.classList.add('active');
                    const toggleBtn = categoryCard.querySelector('.toggle-courses');
                    toggleBtn.textContent = 'Hide Courses';
                }, 300);
            }
        }
    }

    // Initialize
    checkUrlHash();
});