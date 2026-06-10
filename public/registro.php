<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    session_start();

    require_once __DIR__ . '/../config/database.php'; // debe definir $conn (mysqli)

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !isset($data['nombre'], $data['apellido'], $data['telefono'], $data['ci'], $data['correo'], $data['contrasena'])
    ) {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios."]);
        exit;
    }

    $nombre = trim($data['nombre']);
    $apellido = trim($data['apellido']);
    $telefono = trim($data['telefono']);
    $ci = trim($data['ci']);
    $correo = trim($data['correo']);
    $contrasena = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Insertar en credenciales
        $stmt = $conn->prepare("INSERT INTO credenciales (correo, contrasena) VALUES (?, ?)");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("ss", $correo, $contrasena);
        $stmt->execute();
        $idCredenciales = $conn->insert_id;
        $stmt->close();

        // Insertar en usuario
        $stmt2 = $conn->prepare("INSERT INTO usuario (idCredenciales, idRol, idEstado, nombre, apellido, telefono, ci)
                                VALUES (?, 2, 1, ?, ?, ?, ?)");
        if (!$stmt2) throw new Exception($conn->error);
        $stmt2->bind_param("issss", $idCredenciales, $nombre, $apellido, $telefono, $ci);
        $stmt2->execute();
        $idUsuario = $conn->insert_id;
        $stmt2->close();

        // Insertar en cliente
        $stmt3 = $conn->prepare("INSERT INTO cliente (idUsuario) VALUES (?)");
        if (!$stmt3) throw new Exception($conn->error);
        $stmt3->bind_param("i", $idUsuario);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();

        $_SESSION['correo'] = $correo;
        $_SESSION['rol'] = 2;
        $_SESSION['idUsuario'] = $idUsuario;

        echo json_encode(["success" => true, "mensaje" => "Registro exitoso."]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "mensaje" => "Error al registrar: " . $e->getMessage()]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Pollos Express</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <link rel="stylesheet" href="assets/css/registro.css">
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <a href="index.php" target="_self"><img id="logo-pollo" src="assets/img/logo-pollo.png" alt="Logo Pollos Express" /></a>
            </div>
            <div class="logo">POLLOS <span>EXPRESS</span></div>
        </div>

        <h2>Formulario de Registro</h2>

        <form id="formRegistro">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="nombre" placeholder="Nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}" title="Solo letras y espacios. Mínimo 2 caracteres.">

            </div>

            <div class="input-group">
                <i class="fas fa-user-tag"></i>
               <input type="text" id="apellido" placeholder="Apellido" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}" title="Solo letras y espacios. Mínimo 2 caracteres.">
            </div>

            <div class="input-group">
                <i class="fas fa-phone-alt"></i>
                <input type="tel" id="telefono" placeholder="Teléfono" required pattern="[0-9]{7,15}" title="Debe contener entre 8 y 15 números.">
            </div>

            <div class="input-group">
                <i class="fas fa-id-card"></i>
                <input type="text" id="ci" placeholder="Cédula de Identidad" required pattern="[0-9]{6,12}" title="Solo números. Entre 7 y 12 dígitos.">

            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
              
                <input type="email" id="correo" placeholder="Correo electrónico" required title="Ingresa un correo válido.">
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
               <input type="password" id="contrasena" placeholder="Contraseña" required
                    pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&/#¡¿])[A-Za-z\d@$!%*?&/#¡¿]{8,}"
                    title="Mínimo 8 caracteres. Debe incluir mayúsculas, minúsculas, números y símbolos.">
            </div>

            <button type="submit" class="wave">
                <i class="fas fa-user-plus"></i> Registrarse
            </button>
        </form>
        <button type="button" class="wave"  onclick="window.history.back()" >
            <i class="fas fas fa-arrow-left"></i> Volver
        </button>
        <div class="footer-links">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Animación de entrada para los campos
            anime({
                targets: '.input-group',
                opacity: [0, 1],
                translateY: [15, 0],
                delay: anime.stagger(80, {
                    start: 200
                }),
                duration: 600,
                easing: 'easeOutQuad'
            });

            // Efecto al enfocar campos
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    anime({
                        targets: input,
                        scale: [1, 1.02],
                        duration: 200,
                        easing: 'easeOutQuad'
                    });
                });

                input.addEventListener('blur', () => {
                    anime({
                        targets: input,
                        scale: [1.02, 1],
                        duration: 200,
                        easing: 'easeOutQuad'
                    });
                });
            });

            // Efecto al interactuar con el botón
            const submitBtn = document.querySelector('button[type="submit"]');

            submitBtn.addEventListener('mouseenter', () => {
                anime({
                    targets: submitBtn,
                    scale: 1.03,
                    duration: 150,
                    easing: 'easeOutQuad'
                });
            });

            submitBtn.addEventListener('mouseleave', () => {
                anime({
                    targets: submitBtn,
                    scale: 1,
                    duration: 150,
                    easing: 'easeOutQuad'
                });
            });
        });
    </script>

    <script>
        document.getElementById("formRegistro").addEventListener("submit", function(e) {
            e.preventDefault();

            const datos = {
                nombre: document.getElementById("nombre").value,
                apellido: document.getElementById("apellido").value,
                telefono: document.getElementById("telefono").value,
                ci: document.getElementById("ci").value,
                correo: document.getElementById("correo").value,
                contrasena: document.getElementById("contrasena").value
            };

            fetch("registro.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.mensaje);
                    if (data.success) {
                        window.location.href = "index.php"; // Redirigir al inicio si el registro es exitoso
                    }
                });
        });
    </script>
</body>

</html>