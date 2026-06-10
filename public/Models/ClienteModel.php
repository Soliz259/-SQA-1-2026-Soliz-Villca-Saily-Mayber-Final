<?php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php'; // Ajusta ruta según estructura

use mysqli;

class ClienteModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Devuelve todos los clientes
     */
    public function getAll(): array
    {
        $sql = "SELECT 
                    u.idUsuario,
                    u.nombre,
                    u.apellido,
                    u.telefono,
                    u.ci,
                    c.fechaRegistro,
                    c.idDireccion
                FROM cliente c
                JOIN usuario u ON c.idUsuario = u.idUsuario";
        $result = $this->conn->query($sql);
        $out = [];
        while ($row = $result->fetch_assoc()) {
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Crea un nuevo cliente (y sus credenciales/usuario)
     */
    public function create(array $data): array
    {
        if (!isset(
            $data['correo'],
            $data['contrasena'],
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['ci'],
            $data['idDireccion']
        )) {
            return ['success' => false, 'error' => 'Faltan datos'];
        }

        // 1) credenciales
        $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO credenciales (correo, contrasena) VALUES (?, ?)");
        $stmt->bind_param("ss", $data['correo'], $hash);
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => 'Error en credenciales'];
        }
        $idCred = $this->conn->insert_id;

        // 2) usuario
        $idRol    = 2;
        $idEstado = 1;
        $stmt = $this->conn->prepare(
            "INSERT INTO usuario (idCredenciales, idRol, idEstado, nombre, apellido, telefono, ci)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiissss",
            $idCred,
            $idRol,
            $idEstado,
            $data['nombre'],
            $data['apellido'],
            $data['telefono'],
            $data['ci']
        );
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => 'Error en usuario'];
        }
        $idUsr = $this->conn->insert_id;

        // 3) cliente
        $stmt = $this->conn->prepare(
            "INSERT INTO cliente (idUsuario, idDireccion, fechaRegistro) VALUES (?, ?, NOW())"
        );
        $stmt->bind_param("ii", $idUsr, $data['idDireccion']);
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => 'Error en cliente'];
        }

        return ['success' => true, 'message' => 'Cliente registrado'];
    }

    /**
     * Actualiza datos básicos del cliente
     */
    public function update(array $data): array
    {
        if (!isset($data['idUsuario'], $data['telefono'], $data['ci'], $data['idDireccion'])) {
            return ['success' => false, 'error' => 'Faltan datos'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE usuario SET telefono = ?, ci = ? WHERE idUsuario = ?"
        );
        $stmt->bind_param("ssi", $data['telefono'], $data['ci'], $data['idUsuario']);

        $stmt2 = $this->conn->prepare(
            "UPDATE cliente SET idDireccion = ? WHERE idUsuario = ?"
        );
        $stmt2->bind_param("ii", $data['idDireccion'], $data['idUsuario']);

        if ($stmt->execute() && $stmt2->execute()) {
            return ['success' => true, 'message' => 'Cliente actualizado'];
        }
        return ['success' => false, 'error' => 'Error al actualizar'];
    }

    /**
     * Elimina un cliente (y cascada a usuario/credenciales)
     */
    public function delete(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return ['success' => false, 'error' => 'Falta idUsuario'];
        }
        // obtener credenciales
        $stmt = $this->conn->prepare(
            "SELECT idCredenciales FROM usuario WHERE idUsuario = ?"
        );
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->bind_result($idCred);
        $stmt->fetch();
        $stmt->close();

        // borrar en cliente, usuario, credenciales
        $this->conn->query("DELETE FROM cliente WHERE idUsuario = $idUsuario");
        $this->conn->query("DELETE FROM usuario  WHERE idUsuario = $idUsuario");
        $this->conn->query("DELETE FROM credenciales WHERE idCredenciales = $idCred");

        return ['success' => true, 'message' => 'Cliente eliminado'];
    }

    /**
     * Obtiene los datos del cliente por correo electrónico.
     * Devuelve un array asociativo con los datos del cliente.
     */
    public function obtenerClientePorCorreo($correo)
    {
        $sql = "SELECT u.nombre, u.apellido, u.telefono, u.ci, u.idUsuario, c.fechaRegistro, c.idDireccion, cr.correo
                FROM usuario u
                INNER JOIN cliente c ON u.idUsuario = c.idUsuario
                INNER JOIN credenciales cr ON u.idCredenciales = cr.idCredenciales
                WHERE cr.correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Obtiene la dirección del cliente por su ID.
     * Devuelve un array asociativo con los datos de la dirección.
     */
    public function obtenerDireccionPorId($idDireccion)
    {
        $sql = "SELECT direccion, latitud, longitud, referencia FROM direccion WHERE idDireccion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idDireccion);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Actualiza los datos personales del cliente.
     * Requiere el correo electrónico del cliente para identificarlo.
     */
    public function actualizarDatosCliente($correo, $nombre, $apellido, $telefono): array
    {
        // Buscar idUsuario
        $sqlUsuario = "SELECT u.idUsuario FROM usuario u 
                   INNER JOIN credenciales c ON u.idCredenciales = c.idCredenciales 
                   WHERE c.correo = ?";
        $stmt = $this->conn->prepare($sqlUsuario);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            return ["success" => false, "mensaje" => "Usuario no encontrado."];
        }

        $idUsuario = $resultado->fetch_assoc()["idUsuario"];

        // Actualizar usuario
        $sqlActualizar = "UPDATE usuario SET nombre = ?, apellido = ?, telefono = ? WHERE idUsuario = ?";
        $stmt = $this->conn->prepare($sqlActualizar);
        $stmt->bind_param("sssi", $nombre, $apellido, $telefono, $idUsuario);

        if ($stmt->execute()) {
            return ["success" => true, "mensaje" => "Datos personales actualizados."];
        } else {
            return ["success" => false, "mensaje" => "Error al actualizar."];
        }
    }


    /**
     * Registra o actualiza la dirección del cliente.
     */
    public function registrarDireccionCliente(
        string $correo,
        string $direccion,
        string $referencia,
        float $latitud,
        float $longitud
    ): array {
        // 1) Obtener idUsuario e idDireccion actual del cliente
        $sql = "SELECT u.idUsuario, c.idDireccion FROM usuario u
            INNER JOIN credenciales cr ON u.idCredenciales = cr.idCredenciales
            INNER JOIN cliente c ON u.idUsuario = c.idUsuario
            WHERE cr.correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            return ['success' => false, 'mensaje' => 'Usuario no encontrado.'];
        }
        $row = $res->fetch_assoc();
        $idUsuario = $row['idUsuario'];
        $idDireccion = $row['idDireccion'];  // Puede ser NULL o 0 si no tiene dirección

        if ($idDireccion) {
            // 2a) Actualizar dirección existente
            $sqlUpd = "UPDATE direccion SET direccion = ?, referencia = ?, latitud = ?, longitud = ? WHERE idDireccion = ?";
            $stmtUpd = $this->conn->prepare($sqlUpd);
            $stmtUpd->bind_param("ssddi", $direccion, $referencia, $latitud, $longitud, $idDireccion);
            if (!$stmtUpd->execute()) {
                return ['success' => false, 'mensaje' => 'Error al actualizar la dirección.'];
            }
        } else {
            // 2b) Insertar nueva dirección
            $sqlIns = "INSERT INTO direccion (direccion, referencia, latitud, longitud) VALUES (?, ?, ?, ?)";
            $stmtIns = $this->conn->prepare($sqlIns);
            $stmtIns->bind_param("ssdd", $direccion, $referencia, $latitud, $longitud);
            if (!$stmtIns->execute()) {
                return ['success' => false, 'mensaje' => 'No se pudo registrar la dirección.'];
            }
            $idDireccion = $this->conn->insert_id;

            // 3) Actualizar cliente con la nueva dirección
            $sqlClienteUpd = "UPDATE cliente SET idDireccion = ? WHERE idUsuario = ?";
            $stmtClienteUpd = $this->conn->prepare($sqlClienteUpd);
            $stmtClienteUpd->bind_param("ii", $idDireccion, $idUsuario);
            if (!$stmtClienteUpd->execute()) {
                return ['success' => false, 'mensaje' => 'Error al actualizar la dirección del cliente.'];
            }
        }

        return ['success' => true, 'mensaje' => 'Dirección guardada correctamente.'];
    }
}
