<?php
error_reporting(E_ALL);
date_default_timezone_set('America/Caracas');

// =========================================================================================
// ************************************ API REST SERVICE ***********************************
// =========================================================================================

// ========================================================================================= TIPO DE CONTENIDO 
header('content-type: application/json');

// ========================================================================================= CARGAR CLASES
spl_autoload_register(function ($class) {
    include("classes/{$class}.php");
});

// ========================================================================================= CONEXION BD
$conexion = new Conexion('config/config.json');
$conn     = $conexion->db_conn("sqlsrv");

if (@$conexion->error) {
    die($conexion->error);
}

// ========================================================================================= RESPUESTA
if (isset($_GET['query']) && $_GET['query'] != '') {

    // Comando de consulta recibido por URL
    $query  = explode('/', $_GET['query']);
    @$cmd    = $query[0];
    @$num    = $query[1];

    try {
        switch ($cmd) {
            case 'validar_documento':
                $stmt = $conn->prepare("SELECT * FROM CRM_BOSTO.dbo.f_bnc_consulta_documento($num)");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    http_response_code(200);
                    echo json_encode(['code' => 200, 'data' => $row]);
                } else {
                    http_response_code(200);
                    echo json_encode(['code' => 404, 'data' => "Documento #$num no encontrado"]);
                }
                break;
            case 'detalles_documento':
                $stmt = $conn->query("SELECT * FROM CRM_BOSTO.dbo.f_bnc_consulta_documento($num)");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    http_response_code(200);
                    echo json_encode(['code' => 200, 'data' => $row]);
                } else {
                    http_response_code(200);
                    echo json_encode(['code' => 404, 'data' => "Documento #$num no encontrado"]);
                }
                break;
            default:
                die('EndPoint invalido');
                break;
        }
    } catch (PDOException $e) {
        http_response_code(404);
        header('(HTTP) 404 consulta invalida, parametros de busqueda incorrectos.');
        echo json_encode(['code' => 404, 'data' => '(HTTP 404) consulta invalida, parametros de busqueda incorrectos.']);
    }
} else {
    http_response_code(502);
    header('(HTTP) 502 consulta invalida, los parametro no pueden estar vacios.');
    echo json_encode(['code' => 502, 'data' => '(HTTP 502) consulta invalida, los parametro no pueden estar vacios.']);
}
