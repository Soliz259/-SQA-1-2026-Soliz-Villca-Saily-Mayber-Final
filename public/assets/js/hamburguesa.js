const toggle = document.getElementById('toggleMenu');
const aside = document.querySelector('aside');

if (toggle && aside) {
    toggle.addEventListener('click', () => {
        aside.classList.toggle('mostrar');
        // Ocultar o mostrar boton segun menu abierto o cerrado
        toggle.style.display = aside.classList.contains('mostrar') ? 'none' : 'block';
    });

    // Detectar clic fuera del aside para cerrar menu y mostrar boton hamburguesa
    document.addEventListener('click', (e) => {
        const clicEnAside = aside.contains(e.target);
        const clicEnToggle = toggle.contains(e.target);

        if (!clicEnAside && !clicEnToggle && aside.classList.contains('mostrar')) {
            aside.classList.remove('mostrar');
            toggle.style.display = 'block';
        }
    });

    // Cerrar menu al hacer clic en un enlace dentro del menu
    aside.querySelectorAll('nav a').forEach(link => {
        link.addEventListener('click', () => {
            aside.classList.remove('mostrar');
            toggle.style.display = 'block';
        });
    });
}
