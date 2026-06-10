<?php

namespace App\Controllers;

require_once __DIR__ . '/../models/CarritoModel.php';

use App\Models\CarritoModel;

class CarritoController
{
    public function verCarrito()
    {
        header("Content-Type: application/json");

        if (!isset($_SESSION['correo'])) {
            echo json_encode(["success" => false, "mensaje" => "Usuario no autenticado."]);
            return;
        }

        $correo = $_SESSION['correo'];
        $modelo = new CarritoModel();
        $respuesta = $modelo->obtenerCarritoPorCorreo($correo);

        echo json_encode($respuesta);
    }


    public function agregarProducto()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['correo'], $data['idProducto'], $data['cantidad'])) {
            echo json_encode(["success" => false, "mensaje" => "Datos incompletos."]);
            return;
        }

        $correo = $data['correo'];
        $idProducto = intval($data['idProducto']);
        $cantidad = intval($data['cantidad']);

        $modelo = new CarritoModel();
        $respuesta = $modelo->agregarProductoAlCarrito($correo, $idProducto, $cantidad);

        echo json_encode($respuesta);
    }

    public function modificarCantidadProducto()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['correo'], $data['idProducto'], $data['cambio'])) {
            echo json_encode(["success" => false, "mensaje" => "Datos incompletos."]);
            return;
        }

        $correo = $data['correo'];
        $idProducto = intval($data['idProducto']);
        $cambio = intval($data['cambio']);

        $modelo = new CarritoModel();
        $respuesta = $modelo->modificarCantidadProducto($correo, $idProducto, $cambio);

        echo json_encode($respuesta);
    }
}
