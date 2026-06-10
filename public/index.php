<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pollos Express | Inicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/index.css">

    <script type="module" src="assets/js/header.js"></script>
</head>

<body>

    <!-- ENCABEZADO -->
    <header-component></header-component>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content animate__animated animate__fadeIn">
            <h2>Pollos <span>Express</span></h2>
            <p>El auténtico sabor del pollo crujiente, preparado con nuestra receta secreta que nos ha hecho famosos en toda
                la ciudad.</p>
            <button class="btn floating" onclick="window.location.href='catalogo.php'">
                <i class="fas fa-utensils"></i> Ver Menú Completo
            </button>
        </div>
    </section>

    <!-- MENÚ DESTACADO -->
    <section class="menu" id="menu">
        <h3 class="animate__animated animate__fadeIn">Nuestros Favoritos</h3>
        <div class="productos">
            <div class="producto">
                <img
                    src="https://scontent.flpb3-1.fna.fbcdn.net/v/t39.30808-6/495447455_1238371188295960_1174213894042982498_n.jpg?_nc_cat=101&ccb=1-7&_nc_sid=833d8c&_nc_ohc=Abwqv8JBORAQ7kNvwGOzYJs&_nc_oc=AdmvxYtNcBNKq1Y_VRyf636_zm5wBB5vv8PZ1Q3LNANxugt3cJT9H0WdDcNCTiOIRCg&_nc_zt=23&_nc_ht=scontent.flpb3-1.fna&_nc_gid=PJt65T0UkAhcLUwJjV5uAQ&oh=00_AfP6QF-W4pZaqv5V_QgQTjjBWxGigttXQU_eMkGjdtZAxQ&oe=6856A188"
                    alt="Pollo Clásico" class="producto-img">
                <div class="producto-content">
                    <h4>Ramito Express</h4>
                    <p>Bs 18.00</p>
                    <button class="btn" onclick="window.location.href='catalogo.php'">
                        <i class="fas fa-cart-plus"></i> Ordenar Ahora
                    </button>
                </div>
            </div>
            <div class="producto">
                <img
                    src="https://scontent.flpb3-2.fna.fbcdn.net/v/t39.30808-6/500389650_1247203944079351_242180034821093023_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=127cfc&_nc_ohc=09NlxIeiRbkQ7kNvwGHaxvL&_nc_oc=Adlu99MQ1c4YdkQzlRo-R3EJ7dS-_TqUwLj1TXZpArbWa_QZN6Z6E8zzFOsWidlAe_g&_nc_zt=23&_nc_ht=scontent.flpb3-2.fna&_nc_gid=6drnaBVkX7Od8gBwuZI4lA&oh=00_AfPnYi0ZiKeTq5G5niSVjcvzGGnU4i3PR7-LpTreDiuBPg&oe=6856C131"
                    alt="Combo Familiar" class="producto-img">
                <div class="producto-content">
                    <h4>Pollo a la canasta</h4>
                    <p>Bs 23.00</p>
                    <button class="btn" onclick="window.location.href='catalogo.php'">
                        <i class="fas fa-cart-plus"></i> Ordenar Ahora
                    </button>
                </div>
            </div>
            <div class="producto">
                <img
                    src="https://scontent.flpb3-1.fna.fbcdn.net/v/t39.30808-6/506420430_1266853392114406_7803059230299727894_n.jpg?_nc_cat=111&ccb=1-7&_nc_sid=127cfc&_nc_ohc=f9vll-w119YQ7kNvwHATHQ3&_nc_oc=AdnMruMkR3yJbdZTTlAzo8hTSH59T5T0X98EjjleHQ4CToA8qR9QnQx6ySYOM8Y_sHo&_nc_zt=23&_nc_ht=scontent.flpb3-1.fna&_nc_gid=4x2C_eGguvJqeW3ELC5VKw&oh=00_AfOhX8gaNNUsVnqKKQzMudZt0-PSVX5LPr2QBlnY5c96tA&oe=6856911C"
                    alt="Alitas Picantes" class="producto-img">
                <div class="producto-content">
                    <h4>Super Crocante</h4>
                    <p>Bs 15.00</p>
                    <button class="btn" onclick="window.location.href='catalogo.php'">
                        <i class="fas fa-cart-plus"></i> Ordenar Ahora
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE INFORMACIÓN -->
    <section class="info-section">
        <h3>¿Por qué elegirnos?</h3>
        <div class="info-grid">
            <div class="info-card">
                <i class="fas fa-award"></i>
                <h4>Calidad Premium</h4>
                <p>Utilizamos solo los mejores ingredientes y nuestro pollo es 100% fresco, nunca congelado.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-clock"></i>
                <h4>Servicio Rápido</h4>
                <p>Preparamos tu pedido en minutos para que disfrutes de tu comida caliente y fresca.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-heart"></i>
                <h4>Receta Familiar</h4>
                <p>Nuestra receta secreta ha pasado por generaciones, garantizando un sabor único.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-about">
                <div class="footer-logo">Pollos <span>Express</span></div>
                <p>Desde 2010 sirviendo el mejor pollo frito de la ciudad con nuestra receta secreta y el amor por la buena
                    comida.</p>
                <div class="social-links">
                    <a target="_blank" href="https://www.facebook.com/PolloExpressBolivia"><i class="fab fa-facebook-f"></i></a>
                    <a target="_blank" href="https://www.tiktok.com/@pollos.express?is_from_webapp=1&sender_device=pc"><i
                            class="fab fa-tiktok"></i></a>
                    <a href="https://wa.me/59178915551?text=Hola,%20buenas%20noches%20👋" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>

                </div>
            </div>
            <div class="footer-links">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="catalogo.php">Menú</a></li>
                    <li><a href="https://wa.me/59178915551?text=Hola,%20buenas%20noches%20👋" target="_blank">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contacto</h4>
                <p><i class="fas fa-map-marker-alt"></i> Ciudad de El Alto. C/2 entre Franco Valle y Jorge Carrasco # 42</p>
                <p><i class="fas fa-phone"></i> +591 77709662</p>
                <p><i class="fas fa-envelope"></i> info@pollosexpress.com</p>
                <p><i class="fas fa-clock"></i> Lunes a Domingo: 10:00 - 23:00</p>
            </div>
        </div>
        <div class="copyright">
            <p>© 2025 Pollos Express. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Librerías JS -->
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('mainHeader');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Animaciones al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            // Animación del hero content
            gsap.to('.hero-content', {
                duration: 1,
                y: 0,
                opacity: 1,
                ease: "power2.out",
                delay: 0.3
            });

            // Animación de los productos
            const productos = document.querySelectorAll('.producto');
            productos.forEach((producto, index) => {
                setTimeout(() => {
                    anime({
                        targets: producto,
                        opacity: [0, 1],
                        translateY: [30, 0],
                        duration: 600,
                        easing: 'easeOutExpo'
                    });
                    producto.classList.add('visible');
                }, 300 + (index * 200));
            });

            // Animación de las tarjetas de información
            const infoCards = document.querySelectorAll('.info-card');
            infoCards.forEach((card, index) => {
                setTimeout(() => {
                    anime({
                        targets: card,
                        opacity: [0, 1],
                        translateY: [30, 0],
                        duration: 600,
                        easing: 'easeOutExpo',
                        delay: index * 100
                    });
                }, 1000);
            });
        });
    </script>

    <!-- Tu archivo header.js -->
    <script src="assets/js/header.js"></script>

    <!-- Font Awesome para iconos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <script>
        const sessionData = <?php echo json_encode($_SESSION); ?>;
        console.log("Datos en $_SESSION:", sessionData);
    </script>

</body>

</html>