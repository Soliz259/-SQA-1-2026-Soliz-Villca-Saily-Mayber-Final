<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/UsuarioModel.php';

use App\Models\UsuarioModel;
use SessionHandler;

class UsuarioController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new UsuarioModel($conn);
    }

    public function obtenerUsuario()
    {
        header('Content-Type: application/json');
        // Leer JSON recibido
        $data = json_decode(file_get_contents('php://input'), true);
        $correo = $data['correo'] ?? null;

        if (!$correo) {
            echo json_encode([
                "success" => false,
                "message" => "No se recibió correo"
            ]);
            return;
        }

        $resultado = $this->model->obtenerUsuarioPorCorreo($correo);
        echo json_encode($resultado);
    }
}
