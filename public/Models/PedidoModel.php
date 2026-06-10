<?php
// app/Models/PedidoModel.php

namespace App\Models;

use mysqli;

class PedidoModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Obtiene los pedidos de un cliente según su correo
     * @param string $correo
     * @return array ['success'=>bool, 'pedidos'=>array]
     */
    public function obtenerPedidosPorCorreo(string $correo): array
    {
        // 1) Obtener idUsuario
        $sqlUser = "SELECT u.idUsuario FROM usuario u
                    INNER JOIN credenciales c ON u.idCredenciales = c.idCredenciales
                    WHERE c.correo = ?";
        $stmt = $this->conn->prepare($sqlUser);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            return ['success' => false, 'mensaje' => 'Usuario no encontrado.'];
        }
        $idUsuario = $res->fetch_assoc()['idUsuario'];

        // 2) Obtener pedidos
        $sqlPedidos = "SELECT v.idVenta AS idPedido, v.fecha, e.nombre AS estado, v.total
                       FROM venta v
                       INNER JOIN estadoventa e ON v.idEstadoVenta = e.idEstadoVenta
                       WHERE v.idCliente = ?
                       ORDER BY v.fecha DESC";
        $stmt = $this->conn->prepare($sqlPedidos);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resPedidos = $stmt->get_result();

        $pedidos = [];
        while ($row = $resPedidos->fetch_assoc()) {
            $pedidos[] = $row;
        }

        return ['success' => true, 'pedidos' => $pedidos];
    }


    /**
     * Obtiene el detalle de un pedido dado su ID
     * @param int $idPedido
     * @return array ['success'=>bool, 'detalle'=>array]
     */
    public function obtenerDetallePorPedido(int $idPedido): array
    {
        $sql = "SELECT p.nombre, dv.cantidad, dv.precioUnitario
                FROM detalleventa dv
                INNER JOIN producto p ON dv.idProducto = p.idProducto
                WHERE dv.idVenta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idPedido);
        $stmt->execute();
        $res = $stmt->get_result();

        $detalle = [];
        while ($row = $res->fetch_assoc()) {
            $detalle[] = $row;
        }

        return ['success' => true, 'detalle' => $detalle];
    }


    public function generarPedidoDesdeCarrito(string $correo): array
    {
        try {
            // Todo tu código...
            // 1. Obtener ID del cliente
            $sql = "SELECT u.idUsuario FROM usuario u
            JOIN credenciales c ON u.idCredenciales = c.idCredenciales
            WHERE c.correo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                return ["success" => false, "mensaje" => "Cliente no encontrado."];
            }
            $idCliente = $res->fetch_assoc()['idUsuario'];

            // 1.5 Verificar si el cliente tiene dirección registrada
            // Verificar si el cliente tiene idDireccion no nulo
            $sqlDir = "SELECT idDireccion FROM cliente WHERE idUsuario = ? AND idDireccion IS NOT NULL";
            $stmtDir = $this->conn->prepare($sqlDir);
            $stmtDir->bind_param("i", $idCliente);
            $stmtDir->execute();
            $resDir = $stmtDir->get_result();

            if ($resDir->num_rows === 0) {
                return [
                    "success" => false,
                    "mensaje" => "Debe registrar una dirección antes de realizar el pedido.",
                    "redirigir" => "direccionCliente.html"
                ];
            }


            // 2. Carrito activo
            $sql = "SELECT idCarrito FROM carrito WHERE idCliente = ? AND idEstadoCarrito = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idCliente);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                return ["success" => false, "mensaje" => "No hay carrito activo."];
            }
            $idCarrito = $res->fetch_assoc()['idCarrito'];

            // 3. Productos del carrito
            $sql = "SELECT cp.idProducto, cp.cantidad, p.precio
            FROM carritoproducto cp
            JOIN producto p ON cp.idProducto = p.idProducto
            WHERE cp.idCarrito = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idCarrito);
            $stmt->execute();
            $res = $stmt->get_result();

            $productos = [];
            $total = 0;
            while ($row = $res->fetch_assoc()) {
                $subtotal = $row['precio'] * $row['cantidad'];
                $total += $subtotal;
                $productos[] = $row;
            }

            if (empty($productos)) {
                return ["success" => false, "mensaje" => "No hay productos en el carrito."];
            }

            // 4. Insertar venta
            $idMetodoPago = 1; // puedes parametrizarlo después
            $idEstadoVenta = 1;
            $sql = "INSERT INTO venta (idCliente, total, idMetodoPago, idEstadoVenta) 
            VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("idii", $idCliente, $total, $idMetodoPago, $idEstadoVenta);
            if (!$stmt->execute()) {
                return ["success" => false, "mensaje" => "Error al registrar la venta."];
            }
            $idVenta = $this->conn->insert_id;

            // 5. Insertar detalles de venta
            $sql = "INSERT INTO detalleventa (idVenta, idProducto, cantidad, precioUnitario)
            VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            foreach ($productos as $p) {
                $stmt->bind_param("iiid", $idVenta, $p['idProducto'], $p['cantidad'], $p['precio']);
                $stmt->execute();
            }

            // 6. Finalizar carrito
            $sql = "UPDATE carrito SET idEstadoCarrito = 2 WHERE idCarrito = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $idCarrito);
            $stmt->execute();

            return ["success" => true, "mensaje" => "Pedido generado con éxito.", "idVenta" => $idVenta];
        } catch (\Exception $e) {
            return [
                "success" => false,
                "mensaje" => "Error inesperado: " . $e->getMessage()
            ];
        }
    }


    /**
     * Cancela un pedido si está en estado pendiente (idEstadoVenta = 1)
     */
    public function cancelarPedido(int $idVenta): array
    {
        // Verificar el estado actual del pedido
        $sqlEstado = "SELECT idEstadoVenta FROM venta WHERE idVenta = ?";
        $stmt = $this->conn->prepare($sqlEstado);
        $stmt->bind_param("i", $idVenta);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return ["success" => false, "mensaje" => "Pedido no encontrado."];
        }

        $estadoActual = $res->fetch_assoc()["idEstadoVenta"];
        if ($estadoActual != 1) {
            return ["success" => false, "mensaje" => "Este pedido ya no se puede cancelar."];
        }

        // Cancelar el pedido (asumimos 3 = cancelado)
        $idEstadoCancelado = 5;
        $sqlCancelar = "UPDATE venta SET idEstadoVenta = ? WHERE idVenta = ?";
        $stmt = $this->conn->prepare($sqlCancelar);
        $stmt->bind_param("ii", $idEstadoCancelado, $idVenta);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return ["success" => true, "mensaje" => "Pedido cancelado exitosamente."];
        } else {
            return ["success" => false, "mensaje" => "No se pudo cancelar el pedido."];
        }
    }


    /**
     * Obtiene las ventas de los últimos 7 días
     */
    /**
     * esta funcion solo obtiene las ventas par alos Administradores
     */
    public function obtenerTodasLasVentas(): array
    {
        $sql = "SELECT 
              v.idVenta,
              CONCAT(u.nombre, ' ', u.apellido) AS Cliente,
              v.fecha,
              v.total,
              m.nombre AS MetodoPago,
              e.nombre AS EstadoVenta
            FROM 
              venta v
            JOIN 
              cliente c ON v.idCliente = c.idUsuario
            JOIN 
              usuario u ON c.idUsuario = u.idUsuario
            JOIN 
              metodopago m ON v.idMetodoPago = m.idMetodoPago
            JOIN 
              estadoventa e ON v.idEstadoVenta = e.idEstadoVenta
            LIMIT 25";

        $result = $this->conn->query($sql);
        $ventas = [];

        while ($row = $result->fetch_assoc()) {
            $ventas[] = $row;
        }

        return ['success' => true, 'ventas' => $ventas];
    }

    /**
     * Obtiene los detalles de una venta por su ID
     * @param int $idVenta
     * @return array ['success'=>bool, 'venta'=>array]
     */
    public function obtenerVentaPorId(int $idVenta): array
    {
        $sql = "SELECT 
              v.idVenta, 
              CONCAT(u.nombre, ' ', u.apellido) AS Cliente, 
              v.fecha, 
              v.total, 
              m.nombre AS MetodoPago, 
              e.nombre AS EstadoVenta 
            FROM venta v 
            JOIN cliente c ON v.idCliente = c.idUsuario 
            JOIN usuario u ON c.idUsuario = u.idUsuario 
            JOIN metodopago m ON v.idMetodoPago = m.idMetodoPago 
            JOIN estadoventa e ON v.idEstadoVenta = e.idEstadoVenta 
            WHERE v.idVenta = ?
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idVenta);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            return ['success' => true, 'venta' => $res->fetch_assoc()];
        } else {
            return ['success' => false, 'mensaje' => 'Venta no encontrada.'];
        }
    }
}
