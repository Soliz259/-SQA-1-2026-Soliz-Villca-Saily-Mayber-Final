<?php

/**
 * PedidoController.php
 * Controlador para manejar pedidos de clientes.
 * Permite listar pedidos del usuario autenticado.
 */


// app/Controllers/PedidoController.php

namespace App\Controllers;

require_once __DIR__ . '/../models/PedidoModel.php';

use App\Models\PedidoModel;

class PedidoController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new PedidoModel($conn);
    }

    /**
     * Endpoint: /api/mis-pedidos
     */
    public function listarPedidos()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $correo = $_SESSION['correo'];
        $respuesta = $this->model->obtenerPedidosPorCorreo($correo);
        echo json_encode($respuesta);
    }


    /**
     * Endpoint: /api/detalle-pedido
     * Devuelve el detalle (productos) de un pedido específico.
     */
    public function detallePedido()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $idPedido = isset($data['idPedido']) ? intval($data['idPedido']) : 0;

        if ($idPedido <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pedido no válido.']);
            return;
        }

        $respuesta = $this->model->obtenerDetallePorPedido($idPedido);
        echo json_encode($respuesta);
    }


    /**
     * Endpoint: /api/generar-pedido
     * Genera un pedido a partir del carrito del usuario autenticado.
     */
    public function generarPedido()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $correo = $_SESSION['correo'];

        try {
            $resultado = $this->model->generarPedidoDesdeCarrito($correo);
            echo json_encode($resultado);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error interno: ' . $e->getMessage()]);
        }
    }


    /**
     * Endpoint: /api/cancelar-pedido
     * Cancela un pedido si está en estado pendiente.
     */
    public function cancelarPedido()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $idVenta = isset($data['idVenta']) ? intval($data['idVenta']) : 0;

        if ($idVenta <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de pedido inválido.']);
            return;
        }

        $respuesta = $this->model->cancelarPedido($idVenta);
        echo json_encode($respuesta);
    }


    /**
     * Endpoint: /api/todas-las-ventas
     * Retorna todas las ventas (solo para admin).
     */
    public function listarTodasLasVentas()
    {
        header('Content-Type: application/json');

        // Validación mínima de sesión
        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        // Aquí puedes añadir validación de rol si lo deseas (admin, root, etc.)
        $respuesta = $this->model->obtenerTodasLasVentas();
        echo json_encode($respuesta);
    }

    /**
     * Endpoint: /api/ver-venta
     * Retorna información general de una venta por su ID (solo para admin).
     */
    public function verVenta()
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['correo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Usuario no autenticado.']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $idVenta = isset($data['idVenta']) ? intval($data['idVenta']) : 0;

        if ($idVenta <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de venta inválido.']);
            return;
        }

        $respuesta = $this->model->obtenerVentaPorId($idVenta);
        echo json_encode($respuesta);
    }
}
