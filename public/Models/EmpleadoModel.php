<?php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php'; // Ajusta ruta según estructura

use mysqli;

class EmpleadoModel
{
    private $conn;

    // Constantes para rol y estado fijo
    private $ROL_EMPLEADO = 4;
    private $ESTADO_ACTIVO = 1;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // Crear empleado: inserta en credenciales, luego usuario, luego empleado
    public function crearEmpleado(array $datos): array
    {
        // Verificar que el correo no exista ya
        $sqlCheck = "SELECT idCredenciales FROM credenciales WHERE correo = ?";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $datos['correo']);
        $stmtCheck->execute();
        $stmtCheck->store_result();
        if ($stmtCheck->num_rows > 0) {
            return ["success" => false, "message" => "El correo ya está registrado"];
        }
        $stmtCheck->close();

        // Iniciar transacción
        $this->conn->begin_transaction();

        try {
            // Insertar en credenciales
            $sqlCred = "INSERT INTO credenciales (correo, contrasena) VALUES (?, ?)";
            $stmtCred = $this->conn->prepare($sqlCred);
            $hashPassword = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $stmtCred->bind_param("ss", $datos['correo'], $hashPassword);
            $stmtCred->execute();
            $idCredenciales = $stmtCred->insert_id;
            $stmtCred->close();

            // Insertar en usuario
            $sqlUsuario = "INSERT INTO usuario (idCredenciales, idRol, idEstado, nombre, apellido, telefono, ci) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bind_param(
                "iiissss",
                $idCredenciales,
                $this->ROL_EMPLEADO,
                $this->ESTADO_ACTIVO,
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['ci']
            );
            $stmtUsuario->execute();
            $idUsuario = $stmtUsuario->insert_id;
            $stmtUsuario->close();

            // Insertar en empleado
            $sqlEmpleado = "INSERT INTO empleado (idUsuario, salario, fechaContratacion, idTurno) VALUES (?, ?, ?, ?)";
            $stmtEmpleado = $this->conn->prepare($sqlEmpleado);
            $stmtEmpleado->bind_param(
                "idsi",
                $idUsuario,
                $datos['salario'],
                $datos['fechaContratacion'],
                $datos['idTurno']
            );
            $stmtEmpleado->execute();
            $stmtEmpleado->close();

            $this->conn->commit();

            return ["success" => true, "message" => "Empleado creado correctamente"];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Error al crear empleado: " . $e->getMessage()];
        }
    }

    // Obtener todos los empleados (no repartidores)
    public function obtenerEmpleados(): array
    {
        $sql = "SELECT 
                    u.idUsuario,
                    u.nombre,
                    u.apellido,
                    u.telefono,
                    u.ci,
                    e.salario,
                    e.fechaContratacion,
                    t.nombreTurno
                FROM usuario u
                JOIN empleado e ON u.idUsuario = e.idUsuario
                JOIN turno t ON e.idTurno = t.idTurno
                WHERE u.idRol = ? AND u.idEstado = ?
                ORDER BY u.apellido, u.nombre";

        $stmt = $this->conn->prepare($sql);
        $rolEmpleado = $this->ROL_EMPLEADO;
        $estadoActivo = $this->ESTADO_ACTIVO;
        $stmt->bind_param("ii", $rolEmpleado, $estadoActivo);
        $stmt->execute();
        $result = $stmt->get_result();

        $empleados = [];
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
        return $empleados;
    }

    // Obtener un empleado por idUsuario
    public function obtenerEmpleadoPorId(int $idUsuario): ?array
    {
        $sql = "SELECT 
                    u.idUsuario,
                    c.idCredenciales,
                    c.correo,
                    u.nombre,
                    u.apellido,
                    u.telefono,
                    u.ci,
                    e.salario,
                    e.fechaContratacion,
                    e.idTurno
                FROM usuario u
                JOIN credenciales c ON u.idCredenciales = c.idCredenciales
                JOIN empleado e ON u.idUsuario = e.idUsuario
                WHERE u.idUsuario = ? AND u.idRol = ?";

        $stmt = $this->conn->prepare($sql);
        $rolEmpleado = $this->ROL_EMPLEADO;
        $stmt->bind_param("ii", $idUsuario, $rolEmpleado);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    // Actualizar empleado (usuario, credenciales, empleado)
    public function actualizarEmpleado(array $datos): array
    {
        // Iniciar transacción
        $this->conn->begin_transaction();

        try {
            // Actualizar credenciales (correo y opcional contraseña)
            if (!empty($datos['contrasena'])) {
                $sqlCred = "UPDATE credenciales SET correo = ?, contrasena = ? WHERE idCredenciales = ?";
                $stmtCred = $this->conn->prepare($sqlCred);
                $hashPassword = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
                $stmtCred->bind_param("ssi", $datos['correo'], $hashPassword, $datos['idCredenciales']);
            } else {
                $sqlCred = "UPDATE credenciales SET correo = ? WHERE idCredenciales = ?";
                $stmtCred = $this->conn->prepare($sqlCred);
                $stmtCred->bind_param("si", $datos['correo'], $datos['idCredenciales']);
            }
            $stmtCred->execute();
            $stmtCred->close();

            // Actualizar usuario
            $sqlUsuario = "UPDATE usuario SET nombre = ?, apellido = ?, telefono = ?, ci = ? WHERE idUsuario = ?";
            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bind_param(
                "ssssi",
                $datos['nombre'],
                $datos['apellido'],
                $datos['telefono'],
                $datos['ci'],
                $datos['idUsuario']
            );
            $stmtUsuario->execute();
            $stmtUsuario->close();

            // Actualizar empleado
            $sqlEmpleado = "UPDATE empleado SET salario = ?, fechaContratacion = ?, idTurno = ? WHERE idUsuario = ?";
            $stmtEmpleado = $this->conn->prepare($sqlEmpleado);
            $stmtEmpleado->bind_param(
                "dsii",
                $datos['salario'],
                $datos['fechaContratacion'],
                $datos['idTurno'],
                $datos['idUsuario']
            );
            $stmtEmpleado->execute();
            $stmtEmpleado->close();

            $this->conn->commit();

            return ["success" => true, "message" => "Empleado actualizado correctamente"];
        } catch (\Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Error al actualizar empleado: " . $e->getMessage()];
        }
    }

    // Eliminar empleado: para ejemplo, borrado lógico poniendo estado = 2 (inactivo)
    public function eliminarEmpleado(int $idUsuario): array
    {
        $estadoInactivo = 2;

        $sql = "UPDATE usuario SET idEstado = ? WHERE idUsuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $estadoInactivo, $idUsuario);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Empleado desactivado correctamente"];
        } else {
            return ["success" => false, "message" => "Error al desactivar empleado"];
        }
    }
}
