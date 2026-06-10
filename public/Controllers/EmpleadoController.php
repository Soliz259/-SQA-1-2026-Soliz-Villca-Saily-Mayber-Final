<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/EmpleadoModel.php';

use App\Models\EmpleadoModel;

class EmpleadoController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new EmpleadoModel($conn);
    }

    // Listar todos los empleados
    public function listarEmpleados()
    {
        header('Content-Type: application/json');
        $empleados = $this->model->obtenerEmpleados();
        echo json_encode(["success" => true, "empleados" => $empleados]);
    }

    // Obtener un empleado por idUsuario (pasado por GET o JSON)
    public function obtenerEmpleado()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $idUsuario = $data['idUsuario'] ?? $_GET['idUsuario'] ?? null;

        if (!$idUsuario) {
            echo json_encode(["success" => false, "message" => "No se recibió idUsuario"]);
            return;
        }

        $empleado = $this->model->obtenerEmpleadoPorId((int)$idUsuario);

        if (!$empleado) {
            echo json_encode(["success" => false, "message" => "Empleado no encontrado"]);
            return;
        }

        echo json_encode(["success" => true, "empleado" => $empleado]);
    }

    // Crear empleado
    public function crearEmpleado()
    {
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos mínimos necesarios
        $camposRequeridos = ['nombre', 'apellido', 'telefono', 'ci', 'correo', 'contrasena', 'salario', 'fechaContratacion', 'idTurno'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode(["success" => false, "message" => "Falta el campo obligatorio: $campo"]);
                return;
            }
        }

        $resultado = $this->model->crearEmpleado($datos);
        echo json_encode($resultado);
    }

    // Actualizar empleado
    public function actualizarEmpleado()
    {
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);

        $camposRequeridos = ['idUsuario', 'idCredenciales', 'nombre', 'apellido', 'telefono', 'ci', 'correo', 'salario', 'fechaContratacion', 'idTurno'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo])) {
                echo json_encode(["success" => false, "message" => "Falta el campo obligatorio: $campo"]);
                return;
            }
        }

        $resultado = $this->model->actualizarEmpleado($datos);
        echo json_encode($resultado);
    }

    // Eliminar empleado (borrado lógico)
    public function eliminarEmpleado()
    {
        header('Content-Type: application/json');
        $datos = json_decode(file_get_contents('php://input'), true);
        $idUsuario = $datos['idUsuario'] ?? null;

        if (!$idUsuario) {
            echo json_encode(["success" => false, "message" => "No se recibió idUsuario"]);
            return;
        }

        $resultado = $this->model->eliminarEmpleado((int)$idUsuario);
        echo json_encode($resultado);
    }
}
