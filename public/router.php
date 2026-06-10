<?php
session_start();

require_once __DIR__ . '/../config/database.php'; // Asegúrate de que la ruta sea correcta
require_once __DIR__ . '/controllers/CarritoController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/CredencialController.php';
require_once __DIR__ . '/controllers/PedidoController.php';
require_once __DIR__ . '/controllers/ProductoController.php';
require_once __DIR__ . '/controllers/SesionController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/RepartidorController.php';
require_once __DIR__ . '/controllers/TurnoController.php';
require_once __DIR__ . '/controllers/VehiculoTipoController.php';
require_once __DIR__ . '/controllers/EmpleadoController.php'; // Asegúrate de que la ruta sea correcta
// Aquí puedes incluir más controladores según los necesites

use App\Controllers\CarritoController;
use App\Controllers\ClienteController;
use App\Controllers\CredencialController;
use App\Controllers\PedidoController;
use App\Controllers\ProductoController;
use App\Controllers\SesionController;
use App\Controllers\UsuarioController;
use App\Controllers\AdminController;
use App\Controllers\RepartidorController;
use App\Controllers\TurnoController;
use App\Controllers\VehiculoTipoController;
use App\Controllers\EmpleadoController;

$basePath = '/sistema/public';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
    if ($path === '') {
        $path = '/';
    }
}

switch ($path) {
    case '/api/ver-carrito':
        $controller = new CarritoController();
        $controller->verCarrito();
        break;

    case '/api/datos-cliente':
        $controller = new ClienteController($conn);
        $controller->obtenerDatosCliente();
        break;

    case '/api/actualizar-datos-cliente':
        $controller = new ClienteController($conn);
        $controller->actualizarDatosCliente();
        break;

    case '/api/actualizar-contrasena':
        $controller = new CredencialController($conn);
        $controller->actualizarContrasena();
        break;

    case '/api/registrar-direccion':
        $controller = new ClienteController($conn);
        $controller->registrarDireccion();
        break;

    case '/api/mis-pedidos':
        $controller = new PedidoController($conn);
        $controller->listarPedidos();
        break;

    case '/api/detalle-pedido':
        $controller = new PedidoController($conn);
        $controller->detallePedido();
        break;

    case '/api/productos':
        $controller = new ProductoController($conn);
        $controller->listarProductosActivos();
        break;

    case '/api/estado-sesion':
        $controller = new SesionController();
        $controller->estadoSesion();
        break;

    case '/api/agregar-producto-carrito':
        $controller = new CarritoController();
        $controller->agregarProducto();
        break;

    case '/api/modificar-cantidad-carrito':
        $controller = new CarritoController();
        $controller->modificarCantidadProducto();
        break;

    case '/api/generar-pedido':
        $controller = new PedidoController($conn);
        $controller->generarPedido();
        break;

    case '/api/cancelar-pedido':
        $controller = new PedidoController($conn);
        $controller->cancelarPedido();
        break;

    case '/api/obtener-usuario':
        $controller = new UsuarioController($conn);
        $controller->obtenerUsuario();
        break;

    case '/api/ventas-ultimos-7-dias':
        $controller = new AdminController($conn);
        $controller->obtenerVentasUltimos7Dias();
        break;

    case '/api/ventas-ultimos-6-meses':
        $controller = new AdminController($conn);
        $controller->obtenerVentasUltimos6Meses();
        break;

    case '/api/repartidores-recientes':
        $controller = new AdminController($conn);
        $controller->obtenerUltimos5Repartidores();
        break;

    case '/api/ventas-recientes':
        $controller = new AdminController($conn);
        $controller->obtenerUltimasVentas();
        break;

    case '/api/clientes':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            (new ClienteController($conn))->index();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ClienteController($conn))->store();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            (new ClienteController($conn))->update();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            (new ClienteController($conn))->destroy();
        }
        break;


    case '/api/productos/tipos':
        (new ProductoController($conn))->listarTipos();
        break;

    case '/api/productos/estados':
        (new ProductoController($conn))->listarEstados();
        break;





    case '/api/productos/listar': // Solo listar productos activos (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            (new ProductoController($conn))->listarProductosActivos();
        }
        break;

    case '/api/productos/crear': // Crear producto (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new ProductoController($conn))->crearProducto();
        }
        break;

    case '/api/productos/update':
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            (new ProductoController($conn))->actualizarProducto(); // Actualizar producto
        }
        break;

    case '/api/productos/delete':
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            (new ProductoController($conn))->eliminarProducto(); // Eliminar producto
        }
        break;






    case '/api/productos-todos':
        $controller = new ProductoController($conn);
        $controller->listarTodosProductos();
        break;

    case '/api/turnos':
        $controller = new TurnoController($conn);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->index();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    case '/api/vehiculotipos':
        $controller = new VehiculoTipoController($conn);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->index();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;


    case '/api/todas-las-ventas':
        $Controller = new PedidoController($conn);
        $Controller->listarTodasLasVentas();
        break;


    case '/api/ver-venta':
        $controller = new PedidoController($conn);
        $controller->verVenta();
        break;


    case '/api/empleados':
        $controller = new EmpleadoController($conn);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Si viene idUsuario por GET o no, manejar listado o detalle
            if (isset($_GET['idUsuario'])) {
                $controller->obtenerEmpleado();
            } else {
                $controller->listarEmpleados();
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->crearEmpleado();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $controller->actualizarEmpleado();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $controller->eliminarEmpleado();
        }
        break;


    case '/api/repartidores':
        $controller = new RepartidorController($conn);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $controller->index();
                break;

            case 'POST':
                $controller->store();
                break;

            case 'PUT':
                // Extrae `idUsuario` desde query string (?idUsuario=12)
                parse_str(file_get_contents("php://input"), $putVars);
                if (isset($_GET['idUsuario'])) {
                    $controller->update($_GET['idUsuario']);
                } elseif (isset($putVars['idUsuario'])) {
                    $controller->update($putVars['idUsuario']);
                } else {
                    http_response_code(400);
                    echo json_encode(["error" => "Falta el parámetro idUsuario en la URL o en el cuerpo."]);
                }
                break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Método no permitido"]);
                break;
        }
        break;

    case '/api/empleados-disponibles':
        $controller = new RepartidorController($conn);
        $controller->empleadosDisponibles();
        break;

        // Otros endpoints aquí




    /**
         * APIS DEL MELVIN
         */

        // Obtener datos del repartidor por correo
    case '/api/repartidores/obtener-por-correo':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller = new RepartidorController($conn);
            $controller->obtenerNombrePorCorreo();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Obtener pedidos asignados al repartidor
    case '/api/repartidores/pedidos':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller = new RepartidorController($conn);
            $controller->obtenerPedidosAsignados();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    // Marcar un pedido como entregado
    case '/api/repartidores/pedido-entregado':
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $controller = new RepartidorController($conn);
            $controller->marcarPedidoEntregado();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;

    //Ubicacion venta
    case '/api/repartidores/ver-mapa-pedido':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'controllers/RepartidorController.php';
            $controller = new RepartidorController($conn);
            $controller->verMapaPedido();
        } else {
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
        }
        break;



    case '/api/cola-parametros':
        $controller = new AdminController($conn);
        $controller->obtenerParametrosCola();
        break;



    default:
        http_response_code(404);
        echo json_encode(["error" => "Ruta no encontrada"]);
        break;
}
