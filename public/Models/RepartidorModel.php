<?php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

use mysqli;
use Exception;

class RepartidorModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // 1. Obtener todos los repartidores
    public function obtenerRepartidores()
    {
        $sql = "SELECT 
            u.idUsuario,
            u.nombre,
            u.apellido,
            u.telefono,
            u.ci,
            e.salario,
            e.idTurno,
            vt.tipo AS vehiculo,
            er.estado AS estadoRepartidor,
            r.placaVehiculo,
            r.ultimaLatitud,
            r.ultimaLongitud,
            r.ultimaActualizacion
        FROM usuario u
        INNER JOIN empleado e ON u.idUsuario = e.idUsuario
        INNER JOIN repartidor r ON e.idUsuario = r.idUsuario
        INNER JOIN vehiculotipo vt ON r.idVehiculoTipo = vt.idVehiculoTipo
        INNER JOIN estadorepartidor er ON r.idEstadoRepartidor = er.idEstadoRepartidor";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        $repartidores = [];
        while ($row = $result->fetch_assoc()) {
            $repartidores[] = $row;
        }

        return $repartidores;
    }

    // 2. Obtener empleados que NO son repartidores
    public function obtenerEmpleadosDisponibles()
    {
        $sql = "SELECT u.idUsuario, u.nombre, u.apellido, u.telefono, u.ci, e.salario, e.idTurno
            FROM usuario u
            JOIN empleado e ON u.idUsuario = e.idUsuario
            WHERE u.idRol = 4 AND u.idUsuario NOT IN (SELECT idUsuario FROM repartidor)";
        // 2 = Empleado (ajustar según tu data)

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Crear repartidor (con valores por defecto para tipoVehículo y estado)
    public function crearRepartidor($idUsuario)
    {
        $this->conn->begin_transaction();

        try {
            $idVehiculoTipo = 2; // "Sin asignar"
            $idEstadoRepartidor = 1; // "Disponible"

            $sql1 = "INSERT INTO repartidor (idUsuario, idVehiculoTipo, idEstadoRepartidor)
                 VALUES (?, ?, ?)";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bind_param("iii", $idUsuario, $idVehiculoTipo, $idEstadoRepartidor);
            $stmt1->execute();

            $sql2 = "UPDATE usuario SET idRol = 3 WHERE idUsuario = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bind_param("i", $idUsuario);
            $stmt2->execute();

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }


    // 4. Actualizar disponibilidad o estado repartidor
    public function actualizarRepartidor($idUsuario, $idVehiculoTipo, $idEstadoRepartidor)
    {
        $sql = "UPDATE repartidor
            SET idVehiculoTipo = ?, idEstadoRepartidor = ?
            WHERE idUsuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $idVehiculoTipo, $idEstadoRepartidor, $idUsuario);
        return $stmt->execute();
    }



    public function actualizarRepartidorCompleto(
        $idUsuario,
        $nombre,
        $apellido,
        $telefono,
        $ci,
        $salario,
        $idTurno,
        $idVehiculoTipo,
        $idEstadoRepartidor,
        $placaVehiculo,
        $ultimaLatitud,
        $ultimaLongitud
    ) {
        // Inicia la transacción
        $this->conn->begin_transaction();

        try {
            // Actualiza la tabla usuario
            $sql1 = "UPDATE usuario SET nombre = ?, apellido = ?, telefono = ?, ci = ? WHERE idUsuario = ?";
            $stmt1 = $this->conn->prepare($sql1);
            if (!$stmt1) throw new Exception("Error preparando usuario: " . $this->conn->error);
            $stmt1->bind_param("ssssi", $nombre, $apellido, $telefono, $ci, $idUsuario);
            $stmt1->execute();

            // Actualiza la tabla empleado
            $sql2 = "UPDATE empleado SET salario = ?, idTurno = ? WHERE idUsuario = ?";
            $stmt2 = $this->conn->prepare($sql2);
            if (!$stmt2) throw new Exception("Error preparando empleado: " . $this->conn->error);
            $stmt2->bind_param("dii", $salario, $idTurno, $idUsuario);
            $stmt2->execute();

            // Actualiza la tabla repartidor
            $sql3 = "UPDATE repartidor SET idVehiculoTipo = ?, idEstadoRepartidor = ?, placaVehiculo = ?, ultimaLatitud = ?, ultimaLongitud = ? WHERE idUsuario = ?";
            $stmt3 = $this->conn->prepare($sql3);
            if (!$stmt3) throw new Exception("Error preparando repartidor: " . $this->conn->error);
            $stmt3->bind_param("iisssi", $idVehiculoTipo, $idEstadoRepartidor, $placaVehiculo, $ultimaLatitud, $ultimaLongitud, $idUsuario);
            $stmt3->execute();

            // Si todo fue bien, confirma
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Si algo falla, revierte
            $this->conn->rollback();
            error_log("Error actualizando repartidor: " . $e->getMessage());
            return false;
        }
    }






    // ----------------------------------------
    // MÉTODOS AÑADIDOS PARA PEDIDOS DEL REPARTIDOR
    // ----------------------------------------

    public function obtenerDatosPorCorreo(string $correo): ?array
    {
        $sql = "SELECT u.idUsuario, u.nombre, u.apellido
            FROM usuario u
            INNER JOIN credenciales c ON u.idCredenciales = c.idCredenciales
            WHERE c.correo = ? AND u.idRol = 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();

        return ($res->num_rows === 1) ? $res->fetch_assoc() : null;
    }


    public function obtenerIdUsuarioPorCorreo(string $correo): ?int
    {
        $sql = "SELECT u.idUsuario
                FROM usuario u
                INNER JOIN credenciales c ON u.idCredenciales = c.idCredenciales
                WHERE c.correo = ? AND u.idRol = 3";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res->num_rows > 0) ? (int)$res->fetch_assoc()['idUsuario'] : null;
    }

    public function obtenerPedidosAsignados(int $idUsuario): array
    {
        $sql = "SELECT a.idAsignacion, v.idVenta, v.fecha, v.total, e.nombre AS estadoEntrega
                FROM asignacionenvio a
                INNER JOIN venta v ON v.idVenta = a.idVenta
                INNER JOIN estadoentrega e ON a.idEstadoEntrega = e.idEstadoEntrega
                WHERE a.idRepartidor = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $res = $stmt->get_result();

        $pedidos = [];
        while ($row = $res->fetch_assoc()) {
            $pedidos[] = $row;
        }
        return $pedidos;
    }

    public function marcarPedidoEntregado(int $idAsignacion): bool
    {
        $sql = "UPDATE asignacionenvio SET idEstadoEntrega = 3 WHERE idAsignacion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idAsignacion);
        return $stmt->execute();
    }
    /**
     * Obtener la ubicación y datos del cliente para un pedido (idVenta).
     * Retorna array con:
     *   - nombre (string)
     *   - apellido (string)
     *   - direccion (texto completo)
     *   - referencia (texto)
     *   - latitud (decimal)
     *   - longitud (decimal)
     * o null si no existe.
     */
    public function obtenerUbicacionPedido(int $idVenta): ?array
    {
        $sql = "
        SELECT 
            u.nombre,
            u.apellido,
            d.direccion,
            d.referencia,
            d.latitud,
            d.longitud
        FROM venta v
        INNER JOIN cliente c 
            ON v.idCliente = c.idUsuario
        INNER JOIN usuario u 
            ON c.idUsuario = u.idUsuario
        INNER JOIN direccion d 
            ON c.idDireccion = d.idDireccion
        WHERE v.idVenta = ?
        LIMIT 1
    ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $idVenta);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            return null;
        }

        return $res->fetch_assoc();
    }
}
