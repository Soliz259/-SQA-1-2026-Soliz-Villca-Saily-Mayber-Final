<?php
// app/Models/ProductoModel.php

namespace App\Models;

use mysqli;

class ProductoModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }


    /**
     * Obtiene **todos** los productos, con nombre de tipo y estado.
     */
    public function obtenerTodosLosProductos(): array
    {
        $sql = "SELECT 
                p.idProducto, 
                p.nombre AS nombre,
                p.precio, 
                p.stock, 
                p.descripcionProducto, 
                p.imagenUrl, 
                p.idTipoProducto, 
                tp.nombre AS tipoProducto, 
                p.idEstadoProducto, 
                ep.nombre AS estadoProducto 
            FROM producto p 
            JOIN tipoproducto tp ON p.idTipoProducto = tp.idTipoProducto 
            JOIN estadoproducto ep ON p.idEstadoProducto = ep.idEstadoProducto";

        $resultado = $this->conn->query($sql);
        if (!$resultado) {
            return ['success' => false, 'mensaje' => 'Error al obtener todos los productos: ' . $this->conn->error];
        }

        $productos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }

        return ['success' => true, 'productos' => $productos];
    }


    /**
     * Obtiene los productos activos desde la base de datos.
     * @return array
     */
    public function obtenerProductosActivos(): array
    {
        $sql = "SELECT idProducto, nombre, precio, stock, descripcionProducto, imagenUrl
                FROM producto
                WHERE idEstadoProducto = 1";

        $resultado = $this->conn->query($sql);
        if (!$resultado) {
            return ['success' => false, 'mensaje' => 'Error al obtener productos'];
        }

        $productos = [];
        while ($row = $resultado->fetch_assoc()) {
            $productos[] = $row;
        }

        return ['success' => true, 'productos' => $productos];
    }

    /**
     * Obtiene los tipos de productos desde la base de datos.
     * @return array
     */
    public function obtenerTipos(): array
    {
        $sql = "SELECT idTipoProducto, nombre FROM tipoproducto";
        $res = $this->conn->query($sql);
        $tipos = $res->fetch_all(MYSQLI_ASSOC);
        return ['success' => true, 'tipos' => $tipos];
    }

    /**
     * Obtiene los estados de productos desde la base de datos.
     * @return array
     */
    public function obtenerEstados(): array
    {
        $sql = "SELECT idEstadoProducto, nombre FROM estadoproducto";
        $res = $this->conn->query($sql);
        $estados = $res->fetch_all(MYSQLI_ASSOC);
        return ['success' => true, 'estados' => $estados];
    }

    /**
     * Crea un nuevo producto en la base de datos.
     * @param string $nombre
     * @param float $precio
     * @param int $stock
     * @param string $descripcion
     * @param string $imagenUrl
     * @param int $idTipo
     * @param int $idEstado
     * @return array
     */
    public function crearProducto(
        string $nombre,
        float $precio,
        int $stock,
        string $descripcion,
        string $imagenUrl,
        int $idTipo,
        int $idEstado
    ): array {
        $sql = "INSERT INTO producto 
            (nombre, precio, stock, descripcionProducto, imagenUrl, idTipoProducto, idEstadoProducto)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sdissii",
            $nombre,
            $precio,
            $stock,
            $descripcion,
            $imagenUrl,
            $idTipo,
            $idEstado
        );
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'mensaje' => $stmt->error];
    }

    /**
     * Actualiza un producto existente en la base de datos.
     * @param int $id
     * @param string $nombre
     * @param float $precio
     * @param int $stock
     * @param string $descripcion
     * @param string $imagenUrl
     * @param int $idTipo
     * @param int $idEstado
     * @return array
     */
    public function actualizarProducto(
        int $id,
        string $nombre,
        float $precio,
        int $stock,
        string $descripcion,
        string $imagenUrl,
        int $idTipo,
        int $idEstado
    ): array {
        $sql = "UPDATE producto SET
            nombre=?, precio=?, stock=?, descripcionProducto=?, imagenUrl=?, 
            idTipoProducto=?, idEstadoProducto=?
            WHERE idProducto=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "sdissiii",
            $nombre,
            $precio,
            $stock,
            $descripcion,
            $imagenUrl,
            $idTipo,
            $idEstado,
            $id
        );
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'mensaje' => $stmt->error];
    }

    /**
     * Elimina un producto de la base de datos.
     * @param int $id
     * @return array
     */
    public function eliminarProducto(int $id): array
    {
        $sql = "DELETE FROM producto WHERE idProducto=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return ['success' => true];
        }
        return ['success' => false, 'mensaje' => $stmt->error];
    }
}
