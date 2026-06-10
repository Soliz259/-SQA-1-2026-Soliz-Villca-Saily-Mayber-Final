<?php

namespace App\Controllers;

require_once __DIR__ . '/../Models/TurnoModel.php';


use App\Models\TurnoModel;

class TurnoController
{
    private $model;

    public function __construct()
    {
        $this->model = new TurnoModel();
    }

    // GET /api/turnos
    public function index()
    {
        $turnos = $this->model->getAll();
        header('Content-Type: application/json');
        echo json_encode($turnos);
    }
}
