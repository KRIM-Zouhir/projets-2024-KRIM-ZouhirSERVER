document.addEventListener('DOMContentLoaded', function() {
    // Feed toggle functionality
    const feedButtons = document.querySelectorAll('.feed-toggle button');
    const followingFeed = document.getElementById('following-feed');
    const exploreFeed = document.getElementById('explore-feed');

    feedButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Toggle active state
            feedButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Show/hide appropriate feed with fade effect
            if (button.dataset.feed === 'following') {
                fadeOut(exploreFeed, () => {
                    followingFeed.style.display = 'flex';
                    fadeIn(followingFeed);
                });
            } else {
                fadeOut(followingFeed, () => {
                    exploreFeed.style.display = 'flex';
                    fadeIn(exploreFeed);
                });
            }
        });
    });

    // Add hover animations to discussion cards
    const discussionCards = document.querySelectorAll('.discussion-card');
    discussionCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-4px)';
            card.style.boxShadow = '0 6px 12px rgba(0,0,0,0.1)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
        });
    });

    // Utility functions for fade effects
    function fadeOut(element, callback) {
        element.style.opacity = '1';
        (function fade() {
            if ((element.style.opacity -= .1) < 0) {
                element.style.display = 'none';
                if (callback) callback();
            } else {
                requestAnimationFrame(fade);
            }
        })();
    }

    function fadeIn(element, display) {
        element.style.opacity = '0';
        element.style.display = display || 'flex';
        (function fade() {
            let val = parseFloat(element.style.opacity);
            if (!((val += .1) > 1)) {
                element.style.opacity = val;
                requestAnimationFrame(fade);
            }
        })();
    }

    // Add lazy loading for images
    const images = document.querySelectorAll('.community-avatar');
    images.forEach(img => {
        img.loading = 'lazy';
    });

    // Add smooth scroll behavior for pagination
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            setTimeout(() => {
                window.location.href = href;
            }, 500);
        });
    });

    // Add intersection observer for infinite scroll
    const observeLastCard = () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Load more content logic would go here
                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '100px'
        });

        const lastCard = document.querySelector('.discussion-card:last-child');
        if (lastCard) {
            observer.observe(lastCard);
        }
    };

    observeLastCard();
});