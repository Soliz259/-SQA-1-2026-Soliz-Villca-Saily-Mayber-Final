// Carga el navbar en todos los contenedores con clase 'navbar-container'
document.querySelectorAll('.navbar-container').forEach(container => {
    fetch('../../Views/admin/navbarAdmi.html')
        .then(res => res.text())
        .then(data => {
            container.innerHTML = data;
            // Marcar enlace activo después de cargar
            setTimeout(() => {
                const currentPage = location.pathname.split("/").pop() || 'index.html';
                const links = container.querySelectorAll('a[href]');
                
                links.forEach(link => {
                    const linkPage = link.getAttribute('href').split("/").pop();
                    if (linkPage === currentPage) {
                        link.classList.add('active');
                        // Subir en la jerarquía para marcar también el ítem padre si es necesario
                        let parent = link.closest('.list-group-item');
                        if (parent) {
                            parent.classList.add('active');
                        }
                    }
                });
            }, 10);
        })
        .catch(error => {
            console.error('Error al cargar el navbar:', error);
            container.innerHTML = '<div class="alert alert-danger">Error al cargar la barra de navegación</div>';
        });
});