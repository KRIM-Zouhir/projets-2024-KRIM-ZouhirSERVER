document.addEventListener('DOMContentLoaded', () => {
    // Animate discussion items on load
    const discussions = document.querySelectorAll('.discussion-item');
    discussions.forEach((item, index) => {
      setTimeout(() => {
        item.style.transition = 'all 0.5s ease';
        item.style.opacity = '1';
        item.style.transform = 'translateY(0)';
      }, index * 100);
    });
  
    // Smooth pagination transitions with proper navigation
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href');
        const content = document.querySelector('.discussion-list');
        
        // Only apply animation if not current page
        if (!link.classList.contains('active')) {
          e.preventDefault();
          content.style.opacity = '0';
          content.style.transform = 'translateY(20px)';
          
          setTimeout(() => {
            window.location.href = href;
          }, 300);
        }
      });
    });
  
    // Form handling remains the same
    const form = document.querySelector('.discussion-form');
    if (form) {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        const button = form.querySelector('button');
        button.disabled = true;
        button.textContent = 'Sending...';
        form.submit();
      });
    }
  });