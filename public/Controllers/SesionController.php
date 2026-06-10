<?php
namespace App\Controllers;

class SesionController {
    public function estadoSesion() {
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
    }
}
