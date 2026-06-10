<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catálogo | Pollos Express</title>
    <!-- Librerías para diseño mejorado -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/catalogo.css">
    <script type="module" src="assets/js/header.js"></script>

</head>

<body>

    <!-- HEADER (manejado por header.js) -->
    <header-component></header-component>

    <!-- CATÁLOGO -->
    <div class="menu-container">
        <h2>Nuestro <span>Menú</span></h2>
        <div class="productos" id="productos">
            <!-- Aquí se cargan las tarjetas -->
        </div>
    </div>

    <!-- Librerías para animaciones -->
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>

    <!-- JS para el catálogo -->
    <script>
        // Mostrar productos con animaciones desde el nuevo controlador
        fetch("/sistema/public/api/productos")
            .then(res => res.json())
            .then(data => {
                const contenedor = document.getElementById("productos");
                contenedor.innerHTML = ""; // Limpia antes de insertar nuevos productos

                if (!data.success || !data.productos || data.productos.length === 0) {
                    contenedor.innerHTML = "<p>No hay productos disponibles.</p>";
                    return;
                }

                data.productos.forEach((p, index) => {
                    const card = document.createElement("div");
                    card.className = "producto";
                    card.innerHTML = `
        <img src="${p.imagenUrl || 'https://i.gifer.com/VAyR.gif'}" alt="${p.nombre}">
        <div class="producto-content">
          <h3>${p.nombre}</h3>
          <p>${p.descripcionProducto}</p>
          <div class="precio">Bs ${parseFloat(p.precio).toFixed(2)}</div>
          <button class="btn" onclick="agregarAlCarrito(${p.idProducto})">
            <i class="fas fa-cart-plus"></i> Añadir al carrito
          </button>
        </div>
      `;
                    contenedor.appendChild(card);

                    // Animación escalonada
                    setTimeout(() => {
                        card.classList.add('visible');
                    }, 100 * index);
                });
            })
            .catch(error => {
                console.error("Error al cargar productos:", error);
                document.getElementById("productos").innerHTML = "<p>Error al cargar productos.</p>";
            });


        function agregarAlCarrito(idProducto) {
            fetch("estadoSesion.php")
                .then(res => res.json())
                .then(session => {
                    if (!session.logeado) {
                        // Mostrar mensaje elegante de alerta de login
                        const alerta = document.createElement("div");
                        alerta.textContent = "Debes iniciar sesión para agregar productos.";
                        alerta.className = "alerta-login";
                        document.body.appendChild(alerta);

                        anime({
                            targets: alerta,
                            translateY: [-50, 0],
                            opacity: [0, 1],
                            duration: 500,
                            easing: 'easeOutQuad'
                        });

                        setTimeout(() => {
                            anime({
                                targets: alerta,
                                opacity: 0,
                                duration: 300,
                                complete: () => alerta.remove()
                            });
                            window.location.href = "login.php";
                        }, 2000);

                        return;
                    }

                    // Usuario logueado, hacer la petición para agregar al carrito
                    fetch("api/agregar-producto-carrito", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                correo: session.correo,
                                idProducto: idProducto,
                                cantidad: 1
                            })
                        })
                        .then(async res => {
                            const text = await res.text();
                            try {
                                const data = JSON.parse(text);

                                if (data.success) {
                                    // Mostrar mensaje de éxito elegante
                                    const exito = document.createElement("div");
                                    exito.textContent = data.mensaje || "Producto agregado correctamente.";
                                    exito.className = "alerta-exito";
                                    document.body.appendChild(exito);

                                    anime({
                                        targets: exito,
                                        translateY: [-50, 0],
                                        opacity: [0, 1],
                                        duration: 500,
                                        easing: 'easeOutQuad'
                                    });

                                    setTimeout(() => {
                                        anime({
                                            targets: exito,
                                            opacity: 0,
                                            duration: 300,
                                            complete: () => exito.remove()
                                        });
                                    }, 2000);

                                    // Animación del icono carrito
                                    const cartIcon = document.querySelector('#navegacion a[href="carrito.html"]');
                                    if (cartIcon) {
                                        anime({
                                            targets: cartIcon,
                                            scale: [1, 1.3, 1],
                                            duration: 600,
                                            easing: 'easeOutElastic'
                                        });
                                    }
                                } else {
                                    alert(data.mensaje || "No se pudo agregar al carrito.");
                                }
                            } catch (e) {
                                console.error("Error al parsear respuesta:", e);
                                console.log("Respuesta cruda:", text);
                            }
                        });
                })
                .catch(err => {
                    console.error("Error al verificar sesión:", err);
                });
        }
    </script>

    <!-- Tu archivo header.js externo -->
    <script src="assets/js/header.js" defer></script>

    <!-- Font Awesome para iconos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>

</html>