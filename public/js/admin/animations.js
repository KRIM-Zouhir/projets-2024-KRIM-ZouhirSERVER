document.addEventListener('DOMContentLoaded', function() {
    // Page Loader
    const pageLoader = document.getElementById('page-loader');
    if (pageLoader) {
        window.addEventListener('load', () => {
            pageLoader.classList.add('fade-out');
            setTimeout(() => {
                pageLoader.style.display = 'none';
            }, 300);
        });
    }

    // Intersection Observer for fade-in animations
    const fadeElements = document.querySelectorAll('.fade-in-element');
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                fadeObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    fadeElements.forEach(element => {
        fadeObserver.observe(element);
    });

    // Toast Notification System
    window.showToast = function(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast animate-slide-in-right`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        const icon = type === 'success' ? 'check-circle' :
                    type === 'error' ? 'exclamation-circle' :
                    type === 'warning' ? 'exclamation-triangle' : 'info-circle';

        toast.innerHTML = `
            <div class="toast-header">
                <i class="fas fa-${icon} me-2 toast-icon-${type}"></i>
                <strong class="me-auto">TalkSphere</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    };

    // Smooth Scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Loading State Handler
    window.showLoading = function(element, type = 'spinner') {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;

        const originalContent = el.innerHTML;
        el.setAttribute('data-original-content', originalContent);

        if (type === 'spinner') {
            el.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>' + originalContent;
        } else if (type === 'skeleton') {
            el.innerHTML = '<div class="loading-skeleton"></div>';
        }
        el.classList.add('loading');
    };

    window.hideLoading = function(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;

        const originalContent = el.getAttribute('data-original-content');
        if (originalContent) {
            el.innerHTML = originalContent;
        }
        el.classList.remove('loading');
    };

    // Page Transition Effects
    if (window.CSS && CSS.supports('animation: name')) {
        document.addEventListener('turbolinks:before-visit', () => {
            document.body.classList.add('page-transitioning');
        });

        document.addEventListener('turbolinks:load', () => {
            document.body.classList.remove('page-transitioning');
            initializeAnimations();
        });
    }

    // Initialize Dynamic Content Animations
    function initializeAnimations() {
        // Staggered animations for lists
        const listContainers = document.querySelectorAll('[data-animate="stagger"]');
        listContainers.forEach(container => {
            const items = container.children;
            Array.from(items).forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
                item.classList.add('animate-fade-in');
            });
        });

        // Parallax scroll effects
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        window.addEventListener('scroll', () => {
            parallaxElements.forEach(element => {
                const speed = element.getAttribute('data-parallax') || 0.5;
                const offset = window.pageYOffset * speed;
                element.style.transform = `translateY(${offset}px)`;
            });
        });
    }

    // Initialize animations on page load
    initializeAnimations();
});