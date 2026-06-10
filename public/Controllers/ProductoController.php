<?php
// app/Controllers/ProductoController.php

namespace App\Controllers;

require_once __DIR__ . '/../models/ProductoModel.php';

use App\Models\ProductoModel;

class ProductoController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new ProductoModel($conn);
    }

    /**
     * Endpoint: /api/productos-todos
     * Lista **todos** los productos, con tipo y estado
     */
    public function listarTodosProductos()
    {
        header('Content-Type: application/json');
        echo json_encode($this->model->obtenerTodosLosProductos());
    }

    /**
     * Endpoint: /api/productos
     * Lista todos los productos activos
     */
    public function listarProductosActivos()
    {
        header('Content-Type: application/json');
        $respuesta = $this->model->obtenerProductosActivos();
        echo json_encode($respuesta);
    }

    /** GET /api/productos/tipos */
    public function listarTipos()
    {
        header('Content-Type: application/json');
        $tipos = $this->model->obtenerTipos();
        echo json_encode($tipos);
    }

    /** GET /api/productos/estados */
    public function listarEstados()
    {
        header('Content-Type: application/json');
        $estados = $this->model->obtenerEstados();
        echo json_encode($estados);
    }

    /** POST /api/productos */
    public function crearProducto()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->crearProducto(
            $data['nombre'] ?? '',
            $data['precio'] ?? 0,
            $data['stock'] ?? 0,
            $data['descripcionProducto'] ?? '',
            $data['imagenUrl'] ?? '',
            $data['idTipoProducto'] ?? 0,
            $data['idEstadoProducto'] ?? 0
        );
        echo json_encode($res);
    }

    /** PUT /api/productos */
    public function actualizarProducto()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->actualizarProducto(
            $data['idProducto'] ?? 0,
            $data['nombre'] ?? '',
            $data['precio'] ?? 0,
            $data['stock'] ?? 0,
            $data['descripcionProducto'] ?? '',
            $data['imagenUrl'] ?? '',
            $data['idTipoProducto'] ?? 0,
            $data['idEstadoProducto'] ?? 0
        );
        echo json_encode($res);
    }

    /** DELETE /api/productos */
    public function eliminarProducto()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->model->eliminarProducto(
            $data['idProducto'] ?? 0
        );
        echo json_encode($res);
    }
}
