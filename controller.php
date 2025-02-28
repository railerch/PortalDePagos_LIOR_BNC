<?php

spl_autoload_register(function ($class) {
    require_once("classes/{$class}.php");
});

###################################################################### PROCESOS
$config     = json_decode(file_get_contents('config.json'));
$proceso    = Proceso::get_instance($config);

// INICIAR SESION
if (@$_GET['iniciar-sesion']) {
    echo $proceso->iniciar_sesion($_POST);
    exit();
}

// REGISTRAR CLIENTE
if (@$_GET['registrar-cliente']) {
    echo $proceso->registrar_cliente($_POST);
    exit();
}

// ELIMINAR CLIENTE
if (@$_GET['eliminar-cliente']) {
    echo $proceso->eliminar_cliente($_POST);
    exit();
}

// SOLICITAR CAMBIO DE CLAVE
if (@$_GET['solicitar-cambio-clave']) {
    echo $proceso->solicitar_cambio_clave($_POST);
    exit();
}

// VERIFICAR ID DE RECUPERACION
if (@$_GET['verificar-id']) {
    // Validar caducidad del enlace (10min)
    $valido = time() - $_GET['t'] < 600 ? true : false;
    if ($valido) {
        if ($proceso->verificar_id_recuperacion($_GET['id'])) {
            header('location:pass-change.php');
        } else {
            echo '<h3 style="padding:10px;background-color:crimson;color:#fff;border-radius:10px;box-shadow:2px 2px 3px #00000050">Enlace no autorizado.</h3>';
            header("refresh:2; url=login.php");
        };
    } else {
        echo '<h3 style="padding:10px;background-color:crimson;color:#fff;border-radius:10px;box-shadow:2px 2px 3px #00000050">Enlace caducado.</h>';
        header("refresh:2; url=login.php");
    }

    exit();
}

// RESTABLECER CLAVE
if (@$_GET['cambiar-clave']) {
    echo $proceso->cambiar_clave(file_get_contents('php://input'));
    exit();
}

// REFRESCAR TOKEN DE SESION
if (@$_GET['refrescar-token-de-sesion']) {
    echo json_encode($proceso->refrescar_token_sesion());
    exit();
}

// CERRAR SESION
if (@$_GET['cerrar-sesion']) {
    echo $proceso->cerrar_sesion();
    exit();
}

// VALIDAR DOCUMENTO
if (@$_GET['validar-documentos']) {
    $data = json_decode(file_get_contents('php://input'));
    $docs = [];

    foreach ($data as $doc) {
        $tmp =  $proceso->validar_nota_entrega($doc[0]);
        $tmp = array_merge($tmp, ['abono' => $doc[1]]);
        array_push($docs, $tmp);
    }

    echo json_encode($docs);
    exit();
};

// PROCESAR PAGO POR VPOS
if (@$_GET['procesar-pago-vpos']) {
    if ($proceso->autorizar_usuario()) {
        $data = file_get_contents('php://input');
        echo $proceso->procesar_pago_vpos($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

/* PROCESAR PAGO p2p
if (@$_GET['procesar-pago-p2p']) {
    if ($proceso->autorizar_usuario()) {
        $data = file_get_contents('php://input');
        echo $proceso->procesar_pago_p2p($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}
*/

// REGISTRAR PAGOS DESDE OTROS BANCOS (P2P/TRF)
if (@$_GET['registrar-pagos-otros-bancos']) {
    if ($proceso->autorizar_usuario()) {
        $data = file_get_contents('php://input');
        echo $proceso->registrar_pagos_otros_bancos($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

// PROCESAR PAGO c2p
if (@$_GET['procesar-pago-c2p']) {
    if ($proceso->autorizar_usuario()) {
        $data = file_get_contents('php://input');
        echo $proceso->procesar_pago_c2p($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

// HISTORIAL DE TRANSACCIONES
if (@$_GET['historial-transacciones']) {
    if ($proceso->autorizar_usuario()) {
        echo $proceso->consultar_historial_pagos($_POST);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

// DETALLES DE TRANSACCION
if (@$_GET['detalles-transaccion']) {
    if ($proceso->autorizar_usuario()) {
        echo $proceso->consultar_detalles_pago($_GET);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

// HISTORIAL DE PAGOS Y DOCUMENTOS PARA EMISION DE REPORTE
if (@$_GET['historial-pagos-documentos']) {
    if ($proceso->autorizar_usuario()) {
        echo $proceso->historial_pagos_documentos();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

// CONSULTAR TASA BCV
if (@$_GET['consultar-tasa-bcv']) {
    if ($proceso->autorizar_usuario()) {
        echo json_encode($proceso->consultar_tasa_bcv());
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}

if (@$_GET['logs-sistema']) {
    if ($proceso->autorizar_usuario()) {
        echo $proceso->logs_sistema();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Petición no autorizada.']);
    }
    exit();
}
