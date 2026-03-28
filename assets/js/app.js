document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('navToggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            const links = document.querySelector('.nav-links');
            if (links) links.style.display = links.style.display === 'flex' ? 'none' : 'flex';
        });
    }
});
