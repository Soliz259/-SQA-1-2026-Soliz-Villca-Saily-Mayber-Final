<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/ClienteModel.php';

use App\Models\ClienteModel;

class ClienteController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new ClienteModel($conn);
    }

    /**
     * GET /api/clientes
     */
    public function index()
    {
        $clientes = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $clientes]);
    }

    /**
     * POST /api/clientes
     */
    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->create($input);
        echo json_encode($res);
    }

    /**
     * PUT /api/clientes
     */
    public function update()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->update($input);
        echo json_encode($res);
    }

    /**
     * DELETE /api/clientes
     */
    public function destroy()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->delete((int)($input['idUsuario'] ?? 0));
        echo json_encode($res);
    }

    /**
     * Métodos específicos para el cliente autenticado
     */
    public function obtenerDatosCliente()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(["success" => false, "mensaje" => "Usuario no autenticado."]);
            return;
        }

        $correo = $_SESSION['correo'];
        $usuario = $this->model->obtenerClientePorCorreo($correo);

        if (!$usuario) {
            echo json_encode(["success" => false, "mensaje" => "Cliente no encontrado."]);
            return;
        }

        $direccion = null;
        if (!empty($usuario["idDireccion"])) {
            $direccion = $this->model->obtenerDireccionPorId($usuario["idDireccion"]);
        }

        echo json_encode([
            "success" => true,
            "usuario" => $usuario,
            "direccion" => $direccion
        ]);
    }

    /**
     * Actualiza los datos del cliente autenticado.
     */
    public function actualizarDatosCliente()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(["success" => false, "mensaje" => "Usuario no autenticado."]);
            return;
        }

        $correo = $_SESSION['correo'];
        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $telefono = $data['telefono'] ?? '';

        if (!$nombre || !$apellido || !$telefono) {
            echo json_encode(["success" => false, "mensaje" => "Datos incompletos."]);
            return;
        }

        $respuesta = $this->model->actualizarDatosCliente($correo, $nombre, $apellido, $telefono);
        echo json_encode($respuesta);
    }


    /**
     * Registra una nueva dirección para el cliente autenticado.
     */
    public function registrarDireccion()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $correo = $_SESSION['correo'];
        $data = json_decode(file_get_contents('php://input'), true);

        $direccion  = trim($data['direccion']  ?? '');
        $referencia = trim($data['referencia'] ?? '');
        $latitud    = isset($data['latitud'])    ? floatval($data['latitud'])    : null;
        $longitud   = isset($data['longitud'])   ? floatval($data['longitud'])   : null;

        if (!$direccion || !$referencia || $latitud === null || $longitud === null) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos.']);
            return;
        }

        $respuesta = $this->model->registrarDireccionCliente(
            $correo,
            $direccion,
            $referencia,
            $latitud,
            $longitud
        );

        echo json_encode($respuesta);
    }
}
