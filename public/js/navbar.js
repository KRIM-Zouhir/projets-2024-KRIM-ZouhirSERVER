// navbar.js
function toggleDropdown() {
    document.getElementById('profileDropdown').classList.toggle('show');
}

window.onclick = function(event) {
    if (!event.target.matches('.navbar-profile-img')) {
        const dropdowns = document.getElementsByClassName('dropdown-menu');
        for (const dropdown of dropdowns) {
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    }
}
document.querySelector('.create-discussion-btn').addEventListener('click', function(e) {
    const themeId = this.dataset.theme;
    if (themeId) {
        e.preventDefault();
        window.location.href = this.href + '?theme=' + themeId;
    }
});