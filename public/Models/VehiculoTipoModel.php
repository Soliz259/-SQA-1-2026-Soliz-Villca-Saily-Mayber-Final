<?php

namespace App\Models;

// Solo una vez el require_once, fuera del constructor
require_once __DIR__ . '/../../config/database.php';

class VehiculoTipoModel
{
    private $conn;

    public function __construct()
    {
        global $conn; 
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $query = "SELECT idVehiculoTipo, tipo FROM vehiculotipo";
        $result = $this->conn->query($query);

        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
