<?php
namespace App\Models;

require_once __DIR__ . '/../../config/database.php'; // Ajusta ruta según estructura

use mysqli;

class CredencialModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Verifica la contraseña actual y actualiza a la nueva.
     * @param string $correo
     * @param string $actual
     * @param string $nueva
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public function cambiarContrasena(string $correo, string $actual, string $nueva): array
    {
        // Obtener credenciales
        $sql = "SELECT idCredenciales, contrasena FROM credenciales WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            return ['success' => false, 'mensaje' => 'Credenciales no encontradas.'];
        }
        $row = $res->fetch_assoc();

        // Verificar actual
        if (!password_verify($actual, $row['contrasena'])) {
            return ['success' => false, 'mensaje' => 'Contraseña actual no válida.'];
        }

        // Hash nueva y actualizar
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $id = $row['idCredenciales'];
        $upd = $this->conn->prepare("UPDATE credenciales SET contrasena = ? WHERE idCredenciales = ?");
        $upd->bind_param("si", $hash, $id);
        if (!$upd->execute()) {
            return ['success' => false, 'mensaje' => 'Error al actualizar la contraseña.'];
        }

        return ['success' => true, 'mensaje' => 'Contraseña actualizada con éxito.'];
    }
}
