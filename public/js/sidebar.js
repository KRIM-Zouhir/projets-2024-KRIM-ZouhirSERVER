// sidebar.js
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleButton = document.querySelector('.sidebar-toggle');
    
    // State Management
    const STATES = {
        EXPANDED: 'expanded',
        COLLAPSED: 'collapsed'
    };

    let currentState = localStorage.getItem('sidebarState') || STATES.EXPANDED;

    // Initialize sidebar
    function initializeSidebar() {
        updateSidebarState(currentState, false);
        
        // Add transition class after initial state
        setTimeout(() => {
            sidebar.style.transition = 'all 0.3s ease';
            mainContent.style.transition = 'margin-left 0.3s ease';
            toggleButton.style.transition = 'left 0.3s ease';
        }, 100);
    }

    // Update sidebar state
    function updateSidebarState(newState, animate = true) {
        if (!animate) {
            sidebar.style.transition = 'none';
            mainContent.style.transition = 'none';
            toggleButton.style.transition = 'none';
        }

        sidebar.setAttribute('data-state', newState);
        
        if (newState === STATES.COLLAPSED) {
            mainContent.classList.add('sidebar-collapsed');
            toggleButton.querySelector('i').style.transform = 'rotate(180deg)';
        } else {
            mainContent.classList.remove('sidebar-collapsed');
            toggleButton.querySelector('i').style.transform = 'rotate(0)';
        }

        if (!animate) {
            // Force reflow
            sidebar.offsetHeight;
            mainContent.offsetHeight;
            toggleButton.offsetHeight;
            
            sidebar.style.transition = '';
            mainContent.style.transition = '';
            toggleButton.style.transition = '';
        }

        currentState = newState;
        localStorage.setItem('sidebarState', currentState);
        
        // Update tooltips
        updateTooltips();
    }


    // Toggle sidebar
    function toggleSidebar() {
        const newState = currentState === STATES.EXPANDED ? STATES.COLLAPSED : STATES.EXPANDED;
        updateSidebarState(newState);
    }

    // Tooltip Management
    function updateTooltips() {
        const items = document.querySelectorAll('.sidebar-item, .community-item');
        items.forEach(item => {
            if (currentState === STATES.COLLAPSED) {
                const text = item.querySelector('.sidebar-text, .community-name')?.textContent;
                if (text) {
                    item.setAttribute('title', text);
                }
            } else {
                item.removeAttribute('title');
            }
        });
    }

    // Frequent Communities Management
    function updateFrequentCommunities(communityId) {
        const MAX_FREQUENT = 5;
        let frequentCommunities = JSON.parse(localStorage.getItem('frequentCommunities') || '[]');
        
        // Remove if exists
        frequentCommunities = frequentCommunities.filter(id => id !== communityId);
        
        // Add to beginning
        frequentCommunities.unshift(communityId);
        
        // Keep only MAX_FREQUENT
        frequentCommunities = frequentCommunities.slice(0, MAX_FREQUENT);
        
        localStorage.setItem('frequentCommunities', JSON.stringify(frequentCommunities));
    }

    // Mobile Touch Handling
    let touchStartX = 0;
    let touchEndX = 0;

    function handleTouchStart(e) {
        touchStartX = e.touches[0].clientX;
    }

    function handleTouchMove(e) {
        touchEndX = e.touches[0].clientX;
    }

    function handleTouchEnd() {
        const SWIPE_THRESHOLD = 100;
        const swipeDistance = touchEndX - touchStartX;

        if (Math.abs(swipeDistance) > SWIPE_THRESHOLD) {
            if (swipeDistance > 0 && currentState === STATES.COLLAPSED) {
                // Swipe right - expand
                updateSidebarState(STATES.EXPANDED);
            } else if (swipeDistance < 0 && currentState === STATES.EXPANDED) {
                // Swipe left - collapse
                updateSidebarState(STATES.COLLAPSED);
            }
        }
    }

    // Active Item Highlighting
    function highlightActiveItem() {
        const currentPath = window.location.pathname;
        const items = document.querySelectorAll('.sidebar-item, .community-item');
        
        items.forEach(item => {
            if (item.getAttribute('href') === currentPath) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Window Resize Handling
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('show');
                mainContent.style.marginLeft = '0';
            } else {
                updateSidebarState(currentState);
            }
        }, 250);
    }

    // Community Item Click Handling
    function handleCommunityClick(e) {
        const communityItem = e.target.closest('.community-item');
        if (communityItem) {
            const communityId = communityItem.dataset.communityId;
            if (communityId) {
                updateFrequentCommunities(communityId);
            }
        }
    }

    // Event Listeners
    toggleButton.addEventListener('click', toggleSidebar);
    sidebar.addEventListener('touchstart', handleTouchStart);
    sidebar.addEventListener('touchmove', handleTouchMove);
    sidebar.addEventListener('touchend', handleTouchEnd);
    window.addEventListener('resize', handleResize);
    document.addEventListener('click', handleCommunityClick);
    window.addEventListener('popstate', highlightActiveItem);

    // Initialize
    initializeSidebar();
    highlightActiveItem();

    // Mobile menu button handling
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !mobileMenuButton.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Keyboard Navigation
    sidebar.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && currentState === STATES.EXPANDED) {
            updateSidebarState(STATES.COLLAPSED);
        }
    });

    // Theme change handler
    window.addEventListener('themeChange', () => {
        const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
        sidebar.classList.toggle('dark', isDarkTheme);
    });
});