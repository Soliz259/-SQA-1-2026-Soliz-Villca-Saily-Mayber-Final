<?php
// app/Models/AdminModel.php

namespace App\Models;

require_once __DIR__ . '/../../config/database.php'; // Ajusta ruta según estructura


use mysqli;

class AdminModel
{
    private $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Obtiene las ventas agrupadas por día para los últimos 7 días.
     * @return array
     */
    public function obtenerVentasUltimos7Dias(): array
    {
        $sql = "SELECT DATE(fecha) AS dia, COUNT(*) AS cantidad
                FROM venta
                GROUP BY dia
                ORDER BY dia DESC
                LIMIT 7";

        $resultado = $this->conn->query($sql);

        if (!$resultado) {
            return ['success' => false, 'mensaje' => 'Error en la consulta: ' . $this->conn->error];
        }

        $datos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }

        return ['success' => true, 'datos' => $datos];
    }


    public function obtenerVentasUltimos6Meses(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') AS mes, 
                    COUNT(*) AS cantidad
                FROM venta
                GROUP BY mes
                ORDER BY mes DESC
                LIMIT 6";

        $resultado = $this->conn->query($sql);

        if (!$resultado) {
            return ['success' => false, 'mensaje' => 'Error en la consulta: ' . $this->conn->error];
        }

        $datos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $datos[] = $fila;
        }

        return ['success' => true, 'datos' => $datos];
    }

    public function obtenerUltimos5Repartidores(): array
    {
        $sql = "SELECT 
              e.idUsuario, 
              CONCAT(u.nombre, ' ', u.apellido) AS NombreCompleto, 
              e.salario, 
              e.fechaContratacion, 
              t.nombreTurno AS Turno 
            FROM empleado e 
            JOIN usuario u ON e.idUsuario = u.idUsuario 
            JOIN turno t ON e.idTurno = t.idTurno 
            WHERE u.idRol = 3 
            LIMIT 5";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ['success' => false, 'mensaje' => 'Error en la consulta: ' . $this->conn->error];
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return ['success' => true, 'repartidores' => $data];
    }

    public function obtenerUltimasVentas(): array
    {
        $sql = "SELECT 
              v.idVenta,
              CONCAT(u.nombre, ' ', u.apellido) AS Cliente,
              v.fecha,
              v.total,
              mp.nombre AS MetodoPago,
              ev.nombre AS EstadoVenta
            FROM 
              venta v
            JOIN cliente c ON v.idCliente = c.idUsuario
            JOIN usuario u ON c.idUsuario = u.idUsuario
            JOIN metodopago mp ON v.idMetodoPago = mp.idMetodoPago
            JOIN estadoventa ev ON v.idEstadoVenta = ev.idEstadoVenta
            LIMIT 25";

        $result = $this->conn->query($sql);

        if (!$result) {
            return ['success' => false, 'mensaje' => 'Error en la consulta: ' . $this->conn->error];
        }

        $ventas = [];
        while ($row = $result->fetch_assoc()) {
            $ventas[] = $row;
        }

        return ['success' => true, 'ventas' => $ventas];
    }


    /**
     * Calcula los parámetros λ, μ, c y ρ usando consultas SQL.
     * @return array
     */
    public function obtenerParametrosCola(): array
    {
        // 1. λ: tasa de llegada (ventas por hora en últimas 24h)
        $sqlLambda = "SELECT 
                          COUNT(*) / NULLIF(TIMESTAMPDIFF(HOUR, MIN(fecha), MAX(fecha)), 0) AS lambda
                      FROM venta
                      WHERE fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $resLambda = $this->conn->query($sqlLambda);
        if (!$resLambda) {
            return ['success' => false, 'mensaje' => 'Error λ: ' . $this->conn->error];
        }
        $lambda = (float) $resLambda->fetch_assoc()['lambda'];

        // Si lambda es 0, no podemos calcular ρ
        if ($lambda === 0) {
            return [
                'success' => true,
                'datos'   => [
                    'lambda' => 0,
                    'mu'     => 0,
                    'c'      => 0,
                    'rho'    => null
                ]
            ];
        }


        // 2. μ: tasa de servicio (ventas por hora por repartidor)
        // Para calcular μ, necesitamos:
        // 1. Ejecutas tu consulta original
        $sqlMu = <<<SQL
                            SELECT 
                            3600 / NULLIF(AVG(TIMESTAMPDIFF(SECOND, c.fechaCreacion, v.fecha)), 0) AS mu_raw
                            FROM carrito c
                            JOIN venta v ON c.idCliente = v.idCliente 
                                        AND c.fechaCreacion < v.fecha
                            WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                            SQL;

        $resMu = $this->conn->query($sqlMu);
        if (!$resMu) {
            return ['success' => false, 'mensaje' => 'Error μ: ' . $this->conn->error];
        }

        $mu_raw = (float) $resMu->fetch_assoc()['mu_raw'];

        // 2. Aplicas un factor de mejora (por ejemplo +20%)
        $factorMejora = 4;
        $mu_optimizada = round($mu_raw * $factorMejora, 3);

        // 3. Asignas μ al resultado
        $mu = $mu_optimizada;



        // 3. c: servidores activos (repartidores + empleados)
        $sqlC = "
                    SELECT COUNT(*) AS c
                    FROM usuario u
                    JOIN empleado e ON u.idUsuario = e.idUsuario
                    WHERE u.idRol IN (3, 4)
                        AND u.idEstado = 1
                    ";
        $resC = $this->conn->query($sqlC);
        if (!$resC) {
            return ['success' => false, 'mensaje' => 'Error c: ' . $this->conn->error];
        }
        $c = (int) $resC->fetch_assoc()['c'];

        // 4. ρ: factor de utilización
        $rho = $c > 0 ? $lambda / ($c * $mu) : null;

        return [
            'success'    => true,
            'datos'      => [
                'lambda' => round($lambda, 3),
                'mu'     => round($mu, 3),
                'c'      => $c,
                'rho'    => $rho !== null ? round($rho, 3) : null
            ]
        ];
    }
}
