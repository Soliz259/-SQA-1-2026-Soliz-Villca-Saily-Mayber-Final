<?php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php';


class TurnoModel
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getAll(): array
    {
        $query = "SELECT idTurno, nombreTurno, horaInicio, horaFin, descripcionTurno FROM turno";
        $result = $this->conn->query($query);

        if (!$result) {
            return [];
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
