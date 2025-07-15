document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const newsEventsGrid = document.getElementById('news-events-grid');
    const upcomingEvents = document.getElementById('upcoming-events');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const pageInfo = document.getElementById('page-info');
    const adminButton = document.createElement('div');
    adminButton.className = 'admin-button';
    adminButton.innerHTML = '<i class="fas fa-plus"></i>';
    document.body.appendChild(adminButton);

    // Modal Elements
    const modal = document.getElementById('admin-modal');
    const closeModal = document.querySelector('.close-modal');
    const addNewsEventForm = document.getElementById('add-news-event-form');
    const neTypeSelect = document.getElementById('ne-type');
    const eventDateGroup = document.getElementById('event-date-group');

    // Pagination Variables
    let currentPage = 1;
    const itemsPerPage = 6;
    let currentFilter = 'all';
    let allNewsEvents = [];

    // Sample data - in a real app, this would come from your backend API
    // For now, we'll use sample data that mimics what you'd get from the database
    const sampleNewsEvents = [
        {
            id: 1,
            type: 'news',
            title: 'New Digital Skills Center Opens in Nairobi',
            description: 'We are excited to announce the opening of our newest digital skills training center in Nairobi, Kenya. The center will provide free training to 500 youth annually.',
            image: 'images/news1.jpg',
            date: '2023-06-15',
            location: '',
            excerpt: 'Our newest training center in Nairobi is now open, offering free digital skills training to local youth.'
        },
        {
            id: 2,
            type: 'event',
            title: 'Graduation Ceremony for Cohort 2023',
            description: 'Join us as we celebrate the achievements of our 2023 graduates. The ceremony will feature keynote speeches, project demonstrations, and networking opportunities with potential employers.',
            image: 'images/event1.jpg',
            date: '2023-07-28',
            location: 'Nairobi Convention Center',
            excerpt: 'Celebrate with our 2023 graduates at our annual graduation ceremony in Nairobi.'
        },
        {
            id: 3,
            type: 'success-story',
            title: 'From Student to Tech Entrepreneur: Jane\'s Story',
            description: 'Jane Wanjiku, a graduate of our web development program, has launched her own digital agency that now employs 5 other Skills for Africa graduates. Read her inspiring journey from student to entrepreneur.',
            image: 'images/success1.jpg',
            date: '2023-05-10',
            location: '',
            excerpt: 'How one graduate turned her training into a successful business employing others.'
        },
        {
            id: 4,
            type: 'news',
            title: 'Partnership with Tech Giant Announced',
            description: 'Skills for Africa has partnered with a global tech company to provide advanced cloud computing training to our students. The partnership includes curriculum development, instructor training, and job placement opportunities.',
            image: 'images/news2.jpg',
            date: '2023-05-22',
            location: '',
            excerpt: 'New partnership brings advanced cloud computing training to our programs.'
        },
        {
            id: 5,
            type: 'event',
            title: 'Women in Tech Conference',
            description: 'Our annual Women in Tech Conference brings together female tech professionals from across Africa to share their experiences, mentor young women, and discuss strategies for increasing gender diversity in the tech sector.',
            image: 'images/event2.jpg',
            date: '2023-08-15',
            location: 'Virtual Event',
            excerpt: 'Join us for a day of inspiration and networking at our Women in Tech Conference.'
        },
        {
            id: 6,
            type: 'success-story',
            title: 'From Refugee Camp to Remote Developer',
            description: 'Peter\'s journey from a refugee camp to a remote software developer for a European company shows the transformative power of digital skills. He completed our program while living in the camp and now earns 5x the average local wage.',
            image: 'images/success2.jpg',
            date: '2023-04-05',
            location: '',
            excerpt: 'How digital skills transformed one refugee\'s life and career prospects.'
        },
        {
            id: 7,
            type: 'news',
            title: 'Expansion to 5 New Countries',
            description: 'Thanks to new funding, Skills for Africa is expanding operations to Ethiopia, Senegal, Zambia, Tunisia, and Cameroon. This brings our total country count to 25 across Africa.',
            image: 'images/news3.jpg',
            date: '2023-03-18',
            location: '',
            excerpt: 'Our programs are now available in 25 African countries after latest expansion.'
        },
        {
            id: 8,
            type: 'event',
            title: 'Hackathon for Social Good',
            description: 'Join our 48-hour hackathon where teams will develop tech solutions to address social challenges in their communities. Winners will receive seed funding and mentorship to develop their ideas further.',
            image: 'images/event3.jpg',
            date: '2023-09-10',
            location: 'Lagos, Nigeria',
            excerpt: 'Compete to create tech solutions for social challenges in your community.'
        }
    ];

    // Initialize page
    allNewsEvents = sampleNewsEvents;
    loadNewsEvents();
    loadUpcomingEvents();

    // Load news and events based on current filter and page
    function loadNewsEvents() {
        // Show loading spinner
        newsEventsGrid.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading content...</p></div>';
        
        // In a real app, you would fetch from your backend API:
        // fetch(`get_news_events.php?type=${currentFilter}&page=${currentPage}&limit=${itemsPerPage}`)
        //     .then(response => response.json())
        //     .then(data => {
        //         allNewsEvents = data.items;
        //         displayNewsEvents(data.items);
        //         updatePagination(data.total, data.page, data.pages);
        //     });
        
        // For demo purposes, we'll filter the sample data
        setTimeout(() => {
            let filteredItems = allNewsEvents;
            
            if (currentFilter !== 'all') {
                filteredItems = allNewsEvents.filter(item => {
                    if (currentFilter === 'news') return item.type === 'news';
                    if (currentFilter === 'events') return item.type === 'event';
                    if (currentFilter === 'success-stories') return item.type === 'success-story';
                    return true;
                });
            }
            
            // Simulate pagination
            const startIndex = (currentPage - 1) * itemsPerPage;
            const paginatedItems = filteredItems.slice(startIndex, startIndex + itemsPerPage);
            const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
            
            displayNewsEvents(paginatedItems);
            updatePagination(filteredItems.length, currentPage, totalPages);
        }, 500);
    }

    // Display news and events in the grid
    function displayNewsEvents(items) {
        if (items.length === 0) {
            newsEventsGrid.innerHTML = '<div class="no-items"><p>No items found matching your selection.</p></div>';
            return;
        }

        newsEventsGrid.innerHTML = '';
        
        items.forEach(item => {
            const date = new Date(item.date);
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            const newsEventCard = document.createElement('div');
            newsEventCard.className = 'news-event-card';
            
            let badgeClass = '';
            let badgeText = '';
            
            if (item.type === 'news') {
                badgeClass = 'news';
                badgeText = 'News';
            } else if (item.type === 'event') {
                badgeClass = 'event';
                badgeText = 'Event';
            } else if (item.type === 'success-story') {
                badgeClass = 'success-story';
                badgeText = 'Success Story';
            }
            
            newsEventCard.innerHTML = `
                <div class="news-event-image">
                    <img src="${item.image}" alt="${item.title}">
                    <span class="news-event-badge ${badgeClass}">${badgeText}</span>
                    ${item.type === 'event' ? `
                    <div class="event-date">
                        <div class="event-date-day">${date.getDate()}</div>
                        <div class="event-date-month">${monthNames[date.getMonth()]}</div>
                    </div>
                    ` : ''}
                </div>
                <div class="news-event-content">
                    <div class="news-event-meta">
                        <span>${date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                        ${item.location ? `<span class="news-event-location"><i class="fas fa-map-marker-alt"></i> ${item.location}</span>` : ''}
                    </div>
                    <h3>${item.title}</h3>
                    <p class="news-event-excerpt">${item.excerpt}</p>
                    <div class="news-event-footer">
                        <a href="news-detail.html?id=${item.id}" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            `;
            
            newsEventsGrid.appendChild(newsEventCard);
        });
    }

    // Load upcoming events (events with future dates)
    function loadUpcomingEvents() {
        // Show loading spinner
        upcomingEvents.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading upcoming events...</p></div>';
        
        // In a real app, you would fetch from your backend API:
        // fetch('get_upcoming_events.php')
        //     .then(response => response.json())
        //     .then(events => displayUpcomingEvents(events));
        
        // For demo purposes, we'll filter the sample data
        setTimeout(() => {
            const today = new Date();
            const upcoming = allNewsEvents.filter(item => {
                if (item.type !== 'event') return false;
                const eventDate = new Date(item.date);
                return eventDate >= today;
            }).sort((a, b) => new Date(a.date) - new Date(b.date)).slice(0, 3);
            
            displayUpcomingEvents(upcoming);
        }, 500);
    }

    // Display upcoming events
    function displayUpcomingEvents(events) {
        if (events.length === 0) {
            upcomingEvents.innerHTML = '<div class="no-events"><p>No upcoming events at this time. Please check back later.</p></div>';
            return;
        }

        upcomingEvents.innerHTML = '';
        
        events.forEach(event => {
            const date = new Date(event.date);
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            const eventCard = document.createElement('div');
            eventCard.className = 'event-card';
            eventCard.innerHTML = `
                <div class="event-date-box">
                    <div class="event-date-day">${date.getDate()}</div>
                    <div class="event-date-month">${monthNames[date.getMonth()]}</div>
                </div>
                <div class="event-details">
                    <h3>${event.title}</h3>
                    <p>${event.excerpt}</p>
                    <div class="event-meta">
                        <span><i class="fas fa-clock"></i> ${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
                        <span><i class="fas fa-map-marker-alt"></i> ${event.location}</span>
                    </div>
                    <a href="event-detail.html?id=${event.id}" class="register-btn">Register</a>
                </div>
            `;
            
            upcomingEvents.appendChild(eventCard);
        });
    }

    // Update pagination controls
    function updatePagination(totalItems, currentPage, totalPages) {
        pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
    }

    // Event listeners for tabs
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active tab
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update filter and reload
            currentFilter = this.dataset.tab;
            currentPage = 1;
            loadNewsEvents();
        });
    });

    // Event listeners for pagination
    prevPageBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadNewsEvents();
        }
    });

    nextPageBtn.addEventListener('click', function() {
        currentPage++;
        loadNewsEvents();
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

    // Show/hide event date field based on type selection
    neTypeSelect.addEventListener('change', function() {
        eventDateGroup.style.display = this.value === 'event' ? 'block' : 'none';
    });

    // Form submission for adding new news/event
    addNewsEventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // In a real app, this would send data to your backend API
        const newItem = {
            id: allNewsEvents.length + 1,
            type: document.getElementById('ne-type').value,
            title: document.getElementById('ne-title').value,
            description: document.getElementById('ne-description').value,
            image: document.getElementById('ne-image').value || 'images/default-news.jpg',
            location: document.getElementById('ne-location').value || '',
            date: document.getElementById('ne-event-date').value || new Date().toISOString().split('T')[0],
            excerpt: document.getElementById('ne-description').value.substring(0, 100) + '...'
        };

        // Add to local array (in real app, this would be an API call)
        allNewsEvents.unshift(newItem);
        
        // Refresh display
        currentPage = 1;
        loadNewsEvents();
        if (newItem.type === 'event') {
            loadUpcomingEvents();
        }
        
        // Close modal and reset form
        modal.style.display = 'none';
        this.reset();
        eventDateGroup.style.display = 'none';
        
        alert('Item added successfully!');
    });
});