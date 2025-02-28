<?php
session_start();
date_default_timezone_set('America/Caracas');

require('classes/utils.php');
echo "<pre>";
echo "Estado de cuenta Lior Cosmetics, C.A.";
echo "<br>";
echo "=======================================<br>";

try {
    // DATOS DE CONEXION
    $clienteGUID = '7CE53AE6-9B14-4957-8E28-B0A1C6E6292C';
    $Masterkey = '8192d17376ca5533bff7ed8cbf4c6436';
    $apiURL = 'https://servicios.bncenlinea.com:16000/api';
    $terminalC2P = 12174113;
    $codigoAfiliacionVpos = 860957579;

    // ============================================================> AUTENTICAR
    $_SESSION['clienteGUID'] = $clienteGUID;
    $cliente = '{"ClientGUID":"' . $clienteGUID . '"}';

    //value
    $value = encrypt($cliente, $Masterkey);

    //validation
    $validation = createHash($cliente);
    $_SESSION['validation'] = $validation;

    //solicitud
    $solicitud = array("ClientGUID" => $clienteGUID, "value" => $value, "Validation" => $validation, "Reference" => '', "swTestOperation" => false);
    $jsonSolicitud = json_encode($solicitud);

    $gurl = $apiURL . '/Auth/LogOn';

    $resultado = json_decode(gPost($gurl, $jsonSolicitud), true);

    if (isset($resultado)) {
        if ($resultado['status'] == 'OK') {
            proSession($resultado['value'], $Masterkey);
            echo $resultado['message'] . '<br>';
        } else {
            echo $resultado['message'] . '<br>';
        }
    } else {
        echo 'Sin respuesta del servidor <br>';
    }

    // ============================================================> ESTADO DE CUENTA
    $identidad  = 'J310849900';
    $cuenta     = '01910220312100008999';

    //armar array
    if ($_GET["x"] == "1") {
        $soliPosi = array("ClientID" => $identidad);
        $gurl = "$apiURL/Position/Current";
    } else {
        $soliPosi = array(
            "ClientID" => $identidad,
            "AccountNumber" => $cuenta
        );
        $gurl = "$apiURL/Position/History";
    }
    //armar Json
    $jsonPosi = json_encode($soliPosi);
    //Value
    $Posi_value = encrypt($jsonPosi, $_SESSION['WorkingKey']);
    //referencia
    $Posi_referencia = refere();
    //validation
    $Posi_validation = createHash($jsonPosi);
    //Solicitud
    $Posi_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $Posi_value, "Validation" => $Posi_validation, "Reference" => $Posi_referencia, "swTestOperation" => false);
    $jsonSolicitud = json_encode($Posi_solicitud);
    $gResult = gPost($gurl, $jsonSolicitud);

    // Respuesta
    $res = json_decode($gResult, true);
    $data = json_decode(decrypt($res['value'], $_SESSION['WorkingKey']));
    
    if ($_GET['x'] == 0) {
        echo '================= <br>';
        echo 'MOVIMIENTOS <br>';
        echo '================= <br>';
        echo "<table border=1 width=600>
                <thead>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Banco</th>
                    <th>Ref</th>
                    <th>Telefono</th>
                </thead>
                <tbody>";

        foreach ($data as $pago) {
            echo "<tr>
            <td>$pago->Date</td>
            <td>$pago->Amount</td>
            <td>$pago->BankCode</td>
            <td>$pago->ReferenceA</td>
            <td>$pago->ReferenceC</td>";
        }

        echo "</tbody>
            </table>";
    } else {
        echo '================= <br>';
        echo 'SALDOS <br>';
        echo '================= <br>';
        $tmp = get_object_vars($data);
        var_dump($tmp["01910220312100008999"]);
    }
} catch (Exception $e) {
    echo $e;
}
