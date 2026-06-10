<?php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php'; // Ajusta ruta según estructura

class CarritoModel
{
    private $conn;

    public function __construct()
    {
        global $conn; // Usamos la conexión mysqli global
        $this->conn = $conn;
    }

    public function obtenerCarritoPorCorreo(string $correo): array
    {
        // Obtener ID del usuario
        $sql = "SELECT u.idUsuario FROM usuario u
                JOIN credenciales c ON c.idCredenciales = u.idCredenciales
                WHERE c.correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return ["success" => false, "mensaje" => "Usuario no encontrado."];
        }

        $idUsuario = $res->fetch_assoc()['idUsuario'];

        // Buscar carrito activo
        $sql = "SELECT idCarrito FROM carrito WHERE idCliente = ? AND idEstadoCarrito = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return ["success" => true, "productos" => []];
        }

        $idCarrito = $res->fetch_assoc()['idCarrito'];

        // Obtener productos del carrito
        $sql = "SELECT cp.idProducto, p.nombre, p.precio, cp.cantidad, p.imagenUrl
                FROM carritoproducto cp
                JOIN producto p ON p.idProducto = cp.idProducto
                WHERE cp.idCarrito = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idCarrito);
        $stmt->execute();
        $res = $stmt->get_result();

        $productos = [];
        $total = 0;

        while ($row = $res->fetch_assoc()) {
            $subtotal = floatval($row['precio']) * intval($row['cantidad']);
            $row['subtotal'] = $subtotal;
            $productos[] = $row;
            $total += $subtotal;
        }

        return [
            "success" => true,
            "productos" => $productos,
            "total" => $total
        ];
    }


    public function agregarProductoAlCarrito(string $correo, int $idProducto, int $cantidad): array
    {
        $sql = "SELECT u.idUsuario FROM usuario u
                INNER JOIN credenciales c ON c.idCredenciales = u.idCredenciales
                WHERE c.correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ["success" => false, "mensaje" => "Usuario no encontrado."];
        }

        $row = $result->fetch_assoc();
        $idCliente = $row['idUsuario'];

        $sql = "SELECT idCarrito FROM carrito WHERE idCliente = ? AND idEstadoCarrito = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            $stmt = $this->conn->prepare("INSERT INTO carrito (idCliente, idEstadoCarrito) VALUES (?, 1)");
            $stmt->bind_param("i", $idCliente);
            $stmt->execute();
            $idCarrito = $this->conn->insert_id;
        } else {
            $carrito = $res->fetch_assoc();
            $idCarrito = $carrito['idCarrito'];
        }

        $sql = "INSERT INTO carritoproducto (idCarrito, idProducto, cantidad)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $idCarrito, $idProducto, $cantidad);

        if ($stmt->execute()) {
            return ["success" => true, "mensaje" => "Producto agregado al carrito."];
        } else {
            return ["success" => false, "mensaje" => "Error al agregar el producto."];
        }
    }



    public function modificarCantidadProducto(string $correo, int $idProducto, int $cambio): array
    {
        // Buscar ID del usuario
        $sql = "SELECT u.idUsuario FROM usuario u
            JOIN credenciales c ON u.idCredenciales = c.idCredenciales
            WHERE c.correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            return ["success" => false, "mensaje" => "Usuario no encontrado"];
        }
        $idCliente = $res->fetch_assoc()['idUsuario'];

        // Buscar carrito activo
        $sql = "SELECT idCarrito FROM carrito WHERE idCliente = ? AND idEstadoCarrito = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            return ["success" => false, "mensaje" => "Carrito no encontrado"];
        }
        $idCarrito = $res->fetch_assoc()['idCarrito'];

        // Verificar si el producto ya está en el carrito
        $sql = "SELECT cantidad FROM carritoproducto WHERE idCarrito = ? AND idProducto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $idCarrito, $idProducto);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0 && $cambio > 0) {
            // No estaba en el carrito, pero se desea agregar
            $stmt = $this->conn->prepare("INSERT INTO carritoproducto (idCarrito, idProducto, cantidad) VALUES (?, ?, ?)");
            $cantidad = 1;
            $stmt->bind_param("iii", $idCarrito, $idProducto, $cantidad);
            $stmt->execute();
            return ["success" => true, "mensaje" => "Producto agregado con cantidad 1"];
        } elseif ($res->num_rows === 1) {
            $cantidadActual = $res->fetch_assoc()['cantidad'];
            $nuevaCantidad = $cantidadActual + $cambio;

            if ($nuevaCantidad <= 0) {
                // Eliminar producto del carrito
                $stmt = $this->conn->prepare("DELETE FROM carritoproducto WHERE idCarrito = ? AND idProducto = ?");
                $stmt->bind_param("ii", $idCarrito, $idProducto);
                $stmt->execute();
                return ["success" => true, "mensaje" => "Producto eliminado del carrito"];
            } else {
                // Actualizar cantidad
                $stmt = $this->conn->prepare("UPDATE carritoproducto SET cantidad = ? WHERE idCarrito = ? AND idProducto = ?");
                $stmt->bind_param("iii", $nuevaCantidad, $idCarrito, $idProducto);
                $stmt->execute();
                return ["success" => true, "mensaje" => "Cantidad actualizada a $nuevaCantidad"];
            }
        }

        return ["success" => false, "mensaje" => "No se pudo modificar el carrito"];
    }
}
// Fin del archivo CarritoModel.php
// Asegúrate de que la conexión a la base de datos esté configurada correctamente