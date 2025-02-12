document.addEventListener('DOMContentLoaded', function() {
    const profileImg = document.querySelector('.navbar-profile-img');
    const dropdown = document.querySelector('.dropdown-menu');
    let isDropdownOpen = false;

    // Add subtle hover effect to profile image
    if (profileImg) {
        profileImg.addEventListener('mouseenter', () => {
            profileImg.style.transform = 'scale(1.05)';
        });
        
        profileImg.addEventListener('mouseleave', () => {
            if (!isDropdownOpen) {
                profileImg.style.transform = 'scale(1)';
            }
        });
    }

    function toggleDropdown(event) {
        if (event) {
            event.stopPropagation();
        }
        
        isDropdownOpen = !isDropdownOpen;
        
        if (dropdown) {
            dropdown.classList.toggle('active', isDropdownOpen);
            
            // Keep the profile image slightly scaled when dropdown is open
            if (profileImg) {
                profileImg.style.transform = isDropdownOpen ? 'scale(1.05)' : 'scale(1)';
            }
        }
    }

    // Toggle dropdown on profile image click
    profileImg?.addEventListener('click', toggleDropdown);

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (isDropdownOpen && dropdown && !dropdown.contains(e.target)) {
            toggleDropdown();
        }
    });

    // Close dropdown on ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isDropdownOpen) {
            toggleDropdown();
        }
    });

    // Add hover animation for navbar links
    const navLinks = document.querySelectorAll('.navbar-link, .dropdown-item');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.transition = 'all 0.2s ease';
            link.style.transform = 'translateY(-1px)';
        });
        
        link.addEventListener('mouseleave', () => {
            link.style.transform = 'translateY(0)';
        });
    });

    // Smooth search input focus animation
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.style.transition = 'all 0.2s ease';
            searchInput.parentElement.style.transform = 'scale(1.02)';
        });
        
        searchInput.addEventListener('blur', () => {
            searchInput.parentElement.style.transform = 'scale(1)';
        });
    }
});