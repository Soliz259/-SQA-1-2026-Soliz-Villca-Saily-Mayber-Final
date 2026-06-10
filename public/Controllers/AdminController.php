<?php
// app/Controllers/AdminController.php

namespace App\Controllers;

require_once __DIR__ . '/../models/AdminModel.php';

use App\Models\AdminModel;

class AdminController
{
    private $model;

    public function __construct($conn)
    {
        $this->model = new AdminModel($conn);
    }

    /**
     * Endpoint: /api/ventas-ultimos-7-dias
     */
    public function obtenerVentasUltimos7Dias()
    {
        header('Content-Type: application/json');

        $resultado = $this->model->obtenerVentasUltimos7Dias();

        echo json_encode($resultado);
    }
    /**
     * Endpoint: /api/ventas-ultimos-6-meses
     */

    public function obtenerVentasUltimos6Meses()
    {
        header('Content-Type: application/json');
        $resultado = $this->model->obtenerVentasUltimos6Meses();
        echo json_encode($resultado);
    }

    /**
     * Endpoint: /api/ventas-ultimos-12-meses
     */
    public function obtenerUltimos5Repartidores()
    {
        header('Content-Type: application/json');
        $resultado = $this->model->obtenerUltimos5Repartidores();
        echo json_encode($resultado);
    }

    /**
     * Endpoint: /api/ultimas-ventas
     * Devuelve las últimas ventas realizadas.
     */

    public function obtenerUltimasVentas()
    {
        header('Content-Type: application/json');
        $resultado = $this->model->obtenerUltimasVentas();
        echo json_encode($resultado);
    }


    /**
     * Endpoint: /api/cola-parametros
     * Devuelve los parámetros λ (lambda), μ (mu), c y ρ (rho) basados en las consultas SQL.
     */
    public function obtenerParametrosCola()
    {
        header('Content-Type: application/json');
        $resultado = $this->model->obtenerParametrosCola();
        echo json_encode($resultado);
    }
}
