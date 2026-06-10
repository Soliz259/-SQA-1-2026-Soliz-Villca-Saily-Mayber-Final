<?php

// public/logout.php

session_start();
session_unset();    // limpia todas las variables de sesión
session_destroy();  // destruye la sesión en servidor
header('Location: index.php');
exit;
