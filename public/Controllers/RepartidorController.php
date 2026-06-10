<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/RepartidorModel.php';

use App\Models\RepartidorModel;

class RepartidorController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new RepartidorModel($conn);
    }

    // 1. Obtener todos los repartidores
    public function index()
    {
        $repartidores = $this->model->obtenerRepartidores();
        header('Content-Type: application/json');
        echo json_encode($repartidores);
    }

    // 2. Obtener empleados que pueden ser asignados como repartidores
    public function empleadosDisponibles()
    {
        $empleados = $this->model->obtenerEmpleadosDisponibles();
        header('Content-Type: application/json');
        echo json_encode($empleados);
    }

    // 3. Crear repartidor (POST)
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['idUsuario'])) {
            http_response_code(400);
            echo json_encode(["error" => "idUsuario es obligatorio."]);
            return;
        }

        $idUsuario = $data['idUsuario'];

        // Llama a tu método que ya asigna por defecto los valores
        $success = $this->model->crearRepartidor($idUsuario);

        if ($success) {
            echo json_encode(["success" => true, "mensaje" => "Repartidor creado exitosamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo crear el repartidor."]);
        }
    }


    // 4. Actualizar repartidor (PUT)
    public function update($idUsuario)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Valida campos obligatorios según tu necesidad
        if (!isset($data['nombre'], $data['apellido'], $data['salario'], $data['idTurno'], $data['idVehiculoTipo'], $data['idEstadoRepartidor'])) {
            http_response_code(400);
            echo json_encode(["error" => "Datos incompletos."]);
            return;
        }

        $nombre = $data['nombre'];
        $apellido = $data['apellido'];
        $telefono = $data['telefono'] ?? null;
        $ci = $data['ci'] ?? null;
        $salario = $data['salario'];
        $idTurno = $data['idTurno'];
        $idVehiculoTipo = $data['idVehiculoTipo'];
        $idEstadoRepartidor = $data['idEstadoRepartidor'];
        $placaVehiculo = $data['placaVehiculo'] ?? null;
        $ultimaLatitud = $data['ultimaLatitud'] ?? null;
        $ultimaLongitud = $data['ultimaLongitud'] ?? null;

        // Actualiza todos los datos (necesitas implementar en el modelo)
        $success = $this->model->actualizarRepartidorCompleto(
            $idUsuario,
            $nombre,
            $apellido,
            $telefono,
            $ci,
            $salario,
            $idTurno,
            $idVehiculoTipo,
            $idEstadoRepartidor,
            $placaVehiculo,
            $ultimaLatitud,
            $ultimaLongitud
        );

        if ($success) {
            echo json_encode(["mensaje" => "Repartidor actualizado."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al actualizar el repartidor."]);
        }
    }



    /**
     * CONTROLADORES DEL MELVIN
     */

    /**
     * POST /api/repartidores/obtener-por-correo
     * Body: { "correo": "correo@ejemplo.com" }
     */
    public function obtenerNombrePorCorreo()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $correo = $input['correo'] ?? null;

        if (!$correo) {
            echo json_encode(['success' => false, 'mensaje' => 'Correo no proporcionado']);
            return;
        }

        $datos = $this->model->obtenerDatosPorCorreo($correo);

        if ($datos) {
            echo json_encode(['success' => true, 'data' => $datos]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'No se encontró el repartidor']);
        }
    }

    /**
     * GET /api/repartidores/pedidos?idUsuario=#
     */
    public function obtenerPedidosAsignados()
    {
        $idUsuario = $_GET['idUsuario'] ?? null;

        if (!$idUsuario) {
            http_response_code(400);
            echo json_encode(["success" => false, "mensaje" => "ID de usuario no proporcionado"]);
            return;
        }
        $pedidos = $this->model->obtenerPedidosAsignados((int)$idUsuario);
        echo json_encode(['success' => true, 'data' => $pedidos]);
    }

    /**
     * PUT /api/repartidores/pedido-entregado
     * Body: { "idAsignacion": 5 }
     */
    public function marcarPedidoEntregado()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $idAsignacion = $input['idAsignacion'] ?? null;

        if (!$idAsignacion) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de asignación no proporcionado']);
            return;
        }

        $exito = $this->model->marcarPedidoEntregado((int)$idAsignacion);

        echo json_encode([
            'success' => $exito,
            'mensaje' => $exito ? 'Pedido marcado como entregado' : 'No se pudo actualizar el estado del pedido'
        ]);
    }


    /**
     * POST /api/repartidores/ver-mapa-pedido
     * Body: { "idVenta": 123 }
     */
    public function verMapaPedido()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $idVenta = $data["idVenta"] ?? null;

        if (!$idVenta) {
            echo json_encode([
                "success" => false,
                "mensaje" => "ID de venta no proporcionado."
            ]);
            return;
        }

        $ubicacion = $this->model->obtenerUbicacionPedido((int)$idVenta);

        if ($ubicacion) {
            echo json_encode([
                "success"   => true,
                "nombre"    => $ubicacion["nombre"],
                "apellido"  => $ubicacion["apellido"],
                "direccion" => $ubicacion["direccion"],
                "referencia" => $ubicacion["referencia"],
                "latitud"   => $ubicacion["latitud"],
                "longitud"  => $ubicacion["longitud"]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "mensaje" => "No se encontró ubicación para el pedido."
            ]);
        }
    }
}
