<?php

namespace App\Controllers;

require_once __DIR__ . '/../Models/VehiculoTipoModel.php';

use App\Models\VehiculoTipoModel;

class VehiculoTipoController
{
    private $model;

    public function __construct()
    {
        $this->model = new VehiculoTipoModel();
    }

    // GET /api/vehiculotipos
    public function index()
    {
        $vehiculos = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($vehiculos);
    }
}
