<?php
ob_start();
session_start();

include_once __DIR__ . '/Lib/helpers.php';

// Detectar si es petición AJAX
function esAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

$isAjax = esAjax();

if (!$isAjax) {
    include_once __DIR__ . '/Views/partials/head.php';
}

echo "<div class='container'>";

if (!isset($_SESSION['usuario'])) {
    $_GET['modulo'] = $_GET['modulo'] ?? 'login';
    $_GET['controlador'] = $_GET['controlador'] ?? 'login';
    $_GET['funcion'] = $_GET['funcion'] ?? 'getLogin';

    resolve();
} else {
    if (!$isAjax) {
        // Sólo mostrar navbar si no es AJAX
        include_once __DIR__ . '/Views/partials/navbar.php';
    }

    if (isset($_GET['modulo']) && isset($_GET['controlador']) && isset($_GET['funcion'])) {
        resolve();
    } else {
        echo "Dashboard";
    }
}

echo "</div>";

if (!$isAjax) {
    include_once __DIR__ . '/Views/partials/footer.php';
}

ob_end_flush();
?>
