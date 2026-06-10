<?php
// config/database.php

$config = require __DIR__ . '/config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_password'],
    $config['db_name']
);

// Verificar conexión
if ($conn->connect_error) {
    die('Error de conexión a la base de datos: ' . $conn->connect_error);
}

// Establecer el charset
$conn->set_charset($config['db_charset']);
