document.querySelectorAll('.dropdown').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        let menu = this.nextElementSibling;
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    });
});

document.addEventListener('click', function(e) {
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (!menu.parentElement.contains(e.target)) {
            menu.style.display = 'none';
        }
    });
});
