document.addEventListener('DOMContentLoaded', function () {
    const navUser = document.querySelector('.nav_user');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    navUser.addEventListener('mouseover', () => {
        dropdownMenu.style.display = 'block';
    });

    navUser.addEventListener('mouseleave', () => {
        dropdownMenu.style.display = 'none';
    });
});
