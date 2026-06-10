<?php
namespace App\Models;

use mysqli;

class UsuarioModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function obtenerUsuarioPorCorreo(string $correo): array
    {
        $sql = "SELECT 
                    u.nombre,
                    u.apellido,
                    u.telefono,
                    u.ci,
                    c.correo,
                    e.salario,
                    e.fechaContratacion,
                    r.nombreRol AS rol,
                    esu.estadoNombre AS estado
                FROM usuario u
                JOIN credenciales c ON u.idCredenciales = c.idCredenciales
                LEFT JOIN empleado e ON u.idUsuario = e.idUsuario
                JOIN rol r ON u.idRol = r.idRol
                JOIN estadousuario esu ON u.idEstado = esu.idEstado
                WHERE c.correo = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ["success" => false, "message" => "Usuario no encontrado"];
        }

        $usuario = $result->fetch_assoc();
        return ["success" => true, "usuario" => $usuario];
    }
}
