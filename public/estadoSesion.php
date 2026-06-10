<?php

// public/estadoSesion.php
// Este archivo verifica el estado de la sesión del usuario y devuelve un JSON con la información del estado de sesión.

session_start();

header('Content-Type: application/json');

if (isset($_SESSION['correo'])) {
    echo json_encode([
        'logeado' => true,
        'correo' => $_SESSION['correo'],
        'rol' => $_SESSION['rol'],
    ]);
} else {
    echo json_encode(['logeado' => false]);
}
