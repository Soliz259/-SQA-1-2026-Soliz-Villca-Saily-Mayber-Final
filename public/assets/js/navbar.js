fetch('navbar.html')
    .then(res => res.text())
    .then(data => {
        const navbarContainer = document.getElementById('navbar-container');
        navbarContainer.innerHTML = data;

        // Espera un pequeño momento para que los enlaces estén en el DOM
        setTimeout(() => {
            const links = navbarContainer.querySelectorAll('a');
            const paginaActual = location.pathname.split("/").pop(); // ej: "index.html"

            links.forEach(link => {
                if (link.getAttribute("href") === paginaActual) {
                    link.classList.add("active");
                }
            });
        }, 10);
    });
