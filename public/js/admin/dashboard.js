// public/js/admin/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // Animate statistics cards on load
    const statsCards = document.querySelectorAll('.stat-card');
    statsCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            requestAnimationFrame(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });
        }, index * 100);
    });

    // Add hover effect to stat cards
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px)';
            card.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.12)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
        });
    });

    // Add smooth entrance animation for content sections
    const contentSections = document.querySelectorAll('.content-section');
    contentSections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            section.style.transition = 'all 0.5s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, (index + statsCards.length) * 100);
    });

    // Add table row hover animations
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.backgroundColor = '#f8f9fa';
            row.style.transform = 'scale(1.01)';
            row.style.transition = 'all 0.3s ease';
        });

        row.addEventListener('mouseleave', () => {
            row.style.backgroundColor = '';
            row.style.transform = 'scale(1)';
        });
    });

    // Add interactive delete confirmation
    const deleteButtons = document.querySelectorAll('[data-confirm="true"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            } else {
                button.innerHTML = '<span class="spinner"></span> Deleting...';
                button.disabled = true;
            }
        });
    });

    // Add smooth animations for role change buttons
    const roleButtons = document.querySelectorAll('.btn-promote, .btn-demote');
    roleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (confirm('Are you sure you want to change this user\'s role?')) {
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner"></span> Updating...';
                button.disabled = true;

                // Simulate loading state (remove in production)
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 1000);
            } else {
                e.preventDefault();
            }
        });
    });

    // Add search input animations
    const searchInputs = document.querySelectorAll('input[type="text"], input[type="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transform = 'scale(1.02)';
            input.parentElement.style.transition = 'all 0.3s ease';
        });

        input.addEventListener('blur', () => {
            input.parentElement.style.transform = 'scale(1)';
        });
    });

    // Add real-time search filtering
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const display = text.includes(searchTerm) ? '' : 'none';
                row.style.display = display;
            });
        }, 300));
    }

    // Utility function for debouncing
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add floating quick action menu
    const quickActions = document.createElement('div');
    quickActions.className = 'quick-actions';
    quickActions.innerHTML = `
        <button class="quick-action-btn" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
            <span>↑</span>
        </button>
    `;
    document.body.appendChild(quickActions);

    // Show/hide quick actions based on scroll
    window.addEventListener('scroll', debounce(() => {
        if (window.scrollY > 300) {
            quickActions.style.display = 'block';
            setTimeout(() => quickActions.style.opacity = '1', 0);
        } else {
            quickActions.style.opacity = '0';
            setTimeout(() => quickActions.style.display = 'none', 300);
        }
    }, 100));

    // Add CSS for new elements
    const style = document.createElement('style');
    style.textContent = `
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1000;
        }

        .quick-action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .spinner {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .highlight {
            background-color: yellow;
            transition: background-color 0.3s ease;
        }
    `;
    document.head.appendChild(style);
});