document.addEventListener('DOMContentLoaded', () => {
    // Animate form appearance
    const form = document.querySelector('.create-form');
    setTimeout(() => {
      form.style.transition = 'all 0.5s ease';
      form.style.opacity = '1';
      form.style.transform = 'translateY(0)';
    }, 100);
  
    // Add textarea animation
    const textarea = document.querySelector('textarea');
    if (textarea) {
      textarea.addEventListener('focus', () => {
        textarea.style.borderColor = '#007bff';
        textarea.style.boxShadow = '0 0 0 0.2rem rgba(0,123,255,.25)';
      });
  
      textarea.addEventListener('blur', () => {
        textarea.style.borderColor = '#dee2e6';
        textarea.style.boxShadow = 'none';
      });
    }
  
    // Smooth form submission
    const createForm = document.querySelector('form');
    createForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const button = createForm.querySelector('.btn-primary');
      button.disabled = true;
      button.textContent = 'Creating...';
  
      // Add loading animation
      button.style.position = 'relative';
      button.style.overflow = 'hidden';
      
      // Actually submit the form
      createForm.submit();
    });
  
    // Smooth navigation for back button
    const backButton = document.querySelector('.btn-secondary');
    backButton.addEventListener('click', (e) => {
      e.preventDefault();
      const href = backButton.getAttribute('href');
      
      document.body.style.opacity = '0';
      setTimeout(() => {
        window.location = href;
      }, 300);
    });
  });