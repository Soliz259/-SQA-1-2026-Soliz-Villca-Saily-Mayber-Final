<?php
// public/login.php

session_start();
require_once __DIR__ . '/../config/database.php'; // Aquí debe definir $conn como mysqli

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && strpos($contentType, 'application/json') !== false
) {
    $data = json_decode(file_get_contents('php://input'));
    $correo    = $data->correo    ?? '';
    $contrasena = $data->contrasena ?? '';

    // Preparar consulta MySQLi
    $sql = "SELECT c.idCredenciales, c.contrasena, u.idRol
            FROM credenciales AS c
            INNER JOIN usuario AS u
              ON u.idCredenciales = c.idCredenciales
            WHERE c.correo = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'mensaje' => 'Error en la consulta']);
        exit;
    }

    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();

    header('Content-Type: application/json; charset=utf-8');

    if ($user && password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['correo'] = $correo;
        $_SESSION['rol'] = (int)$user['idRol'];

        echo json_encode([
            'success' => true,
            'rol'     => $_SESSION['rol'],
            'correo'  => $_SESSION['correo'],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => $user
                ? 'Contraseña incorrecta.'
                : 'Usuario no encontrado.'
        ]);
    }

    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Pollos Express</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="decor-circle"></div>
    <div class="decor-circle2"></div>

    <div class="container">
        <div class="header">
            <div class="logo-container">
               <a href="index.php" target="_self"><img id="logo-pollo" src="assets/img/logo-pollo.png" alt="Logo Pollos Express" /></a>
            </div>
            <div class="logo">POLLOS <span>EXPRESS</span></div>
        </div>

        <h2>Iniciar Sesión</h2>

        <form id="formLogin">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo" required />
            </div>
            <br>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required />
            </div>
            <button type="submit" id="btnLogin" class="wave">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>
            
            <button type="button" onclick="location.href='index.php'" class="wave">
                <i class="fas fa-arrow-left"></i> Volver
            </button>

            <div class="footer-links">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </div>
        </form>
    </div>

    <!-- Animaciones -->
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script>
        // Efectos de entrada
        anime({
            targets: '#logo-pollo',
            opacity: [0, 1],
            scale: [0.5, 1],
            duration: 1500,
            easing: 'easeOutExpo'
        });
        const btn = document.querySelector('#btnLogin');
        ['mouseenter', 'mouseleave', 'mousedown', 'mouseup'].forEach(evt => {
            btn.addEventListener(evt, () => {
                const scale = evt === 'mouseenter' ? 1.05 : evt === 'mouseleave' ? 1 : evt === 'mousedown' ? 0.95 : 1.05;
                gsap.to(btn, {
                    scale,
                    duration: 0.2
                });
            });
        });

        // Lógica de login por AJAX
        document.getElementById("formLogin").addEventListener("submit", function(e) {
            e.preventDefault();
            const datos = {
                correo: document.getElementById("correo").value,
                contrasena: document.getElementById("contrasena").value
            };
            fetch("login.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {

                        // Guardar datos en sesión
                        //localStorage.setItem("correoLogeado", data.correo);
                        switch (data.rol) {
                            case 1:
                                location.href = "Views/admin/panelAdmin.html";
                                break;
                            case 2:
                                location.href = "index.php";
                                break;
                            case 3:
                                location.href = "Views/repartidor/panel_repartidor.html";
                                break;
                            default:
                                alert("Rol no reconocido");
                        }
                    } else {
                        alert(data.mensaje);
                    }
                })
                .catch(() => alert("Error al conectar con el servidor."));
        });
    </script>
</body>

</html>