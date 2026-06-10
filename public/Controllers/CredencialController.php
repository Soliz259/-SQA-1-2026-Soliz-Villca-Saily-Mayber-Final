<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/CredencialModel.php';

use App\Models\CredencialModel;

class CredencialController
{
    private $model;

    public function __construct($conn)
    {
        // $conn es instancia mysqli
        $this->model = new CredencialModel($conn);
    }

    /**
     * Lee JSON {actual, nueva} y usa correo de sesión para cambiar contraseña.
     */
    public function actualizarContrasena()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $correo = $_SESSION['correo'];
        $data = json_decode(file_get_contents('php://input'), true);
        $actual = $data['actual'] ?? '';
        $nueva  = $data['nueva']  ?? '';

        if (!$actual || !$nueva) {
            echo json_encode(['success' => false, 'mensaje' => 'Faltan datos.']);
            return;
        }

        $respuesta = $this->model->cambiarContrasena($correo, $actual, $nueva);
        echo json_encode($respuesta);
    }
}
