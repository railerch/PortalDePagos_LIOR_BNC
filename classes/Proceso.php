<?php
session_start();
date_default_timezone_set('America/Caracas');

require_once 'vendor/autoload.php';
require 'utils.php';

use Exception as GlobalException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Proceso extends Conexion
{

    private $host = 'liorcosmetics.com';
    private $neAPI = 'enlacesbosto.dnsalias.com';
    private $neAPIurl = 'http://enlacesbosto.dnsalias.com:8083/portalPagosLiorDocsAPI';
    private $clienteGUID = '7CE53AE6-9B14-4957-8E28-B0A1C6E6292C';
    private $Masterkey = '8192d17376ca5533bff7ed8cbf4c6436';
    private $apiURL = 'https://servicios.bncenlinea.com:16000/api';
    private $terminalC2P = 12174113;
    private $codigoAfiliacionVpos = 860957579;
    protected $mysql = null;
    private static $instance = null;

    public function __construct($config)
    {
        $conn = Conexion::get_instance($config);
        $this->mysql = $conn->conn_mysql();
    }

    /**
     * Singleton
     */
    public static function get_instance($config)
    {
        if (self::$instance == null) {
            self::$instance = new Proceso($config);
            $i = 'New instance';
        } else {
            $i = 'Old instance';
        }

        $_SESSION['process_instance'] = $i;
        return self::$instance;
    }

    // METODOS DE SESION Y REGISTRO
    private function autenticar_clienteGUID()
    {
        $met = __METHOD__;

        //datos
        $_SESSION['clienteGUID'] = $this->clienteGUID;
        $cliente = '{"ClientGUID":"' . $this->clienteGUID . '"}';

        //value
        $value = encrypt($cliente, $this->Masterkey);

        //validation
        $validation = createHash($cliente);
        $_SESSION['validation'] = $validation;

        //solicitud
        $solicitud = array("ClientGUID" => $this->clienteGUID, "value" => $value, "Validation" => $validation, "Reference" => '', "swTestOperation" => false);
        $jsonSolicitud = json_encode($solicitud);

        $gurl = $this->apiURL . '/Auth/LogOn';

        $resultado = json_decode(gPost($gurl, $jsonSolicitud), true);

        if (isset($resultado)) {
            if ($resultado['status'] == 'OK') {
                self::crear_log($met, $resultado['message']);
                proSession($resultado['value'], $this->Masterkey);
                return ['status' => 'OK', 'message' => $resultado['message']];
            } else {
                self::crear_log($met, $resultado['message']);
                return ['status' => 'KO', 'message' => $resultado['message']];
            }
        } else {
            self::crear_log($met, 'Autenticación de ClientGUI sin exito.');
            return ['status' => 'KO', 'message' => 'Sin respuesta del servidor'];
        }
    }

    public function refrescar_token_sesion()
    {
        return self::autenticar_clienteGUID();
        exit();
    }

    public function iniciar_sesion($data)
    {
        $met        = __METHOD__;
        $usuario    = strtolower($data['usuario']);
        $clave      = md5($data['clave']);

        // La sesion de reportes no autentica clientGUID
        if ($usuario == 'admin' && $data['clave'] == 'admin123.') {
            // ===========> SESION PARA REPORTES (admin123.)

            $_SESSION['cli_des']        = 'Admin';
            $_SESSION['client_name']    = 'Admin';
            $_SESSION['session_id']     = md5(time());
            $sessionID = base64_encode("Admin" . '-' . $_SESSION['session_id']);

            self::crear_log($met, 'Sesión de reportes inicializada');

            return json_encode([
                'status' => 'success',
                'message' => $sessionID,
                'code' => 200,
                'name' => 'Admin',
                'host' => $this->host,
                'neAPI' => $this->neAPI,
                'auth' => ['status' => 'OK', 'message' => 'Sesión de reportes activa.']
            ]);
        } else {
            // ===========> SESION PARA CLIENTES

            // Autenticar en la api del servicio
            // Un fallo en la autenticacion evita el inicio de sesion
            $auth = self::autenticar_clienteGUID();

            // Procesar inicio de sesion
            try {
                $stmt = $this->mysql->prepare("SELECT clave, nombre, nro_cedula, correo FROM clientes WHERE correo = '$usuario' OR nro_telf = '$usuario'");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Se vallida la clave debido a que si el usuario a intentado recuperarla previamente y no la ha 
                // restablecido esta estara en blanco
                if (isset($row['clave'])) {
                    if ($row['clave'] == $clave) {
                        $_SESSION['client_id']      = $row['nro_cedula'];
                        $_SESSION['correo']         = $row['correo'];
                        $_SESSION['cli_des']        = ucwords($row['nombre']);
                        $_SESSION['client_name']    = str_replace(' ', '', strtolower($row['nombre']));
                        $_SESSION['session_id']     = md5(time());
                        $sessionID = base64_encode($_SESSION['client_name'] . '-' . $_SESSION['session_id']);

                        self::crear_log($met, 'Sesión de cliente inicializada');

                        return json_encode([
                            'status' => 'success',
                            'message' => $sessionID,
                            'code' => 200,
                            'name' => $_SESSION['cli_des'],
                            'auth' => $auth,
                            'host' => $this->host,
                            'neAPI' => $this->neAPI,
                            'conn_instance' => $_SESSION['conn_instance'],
                            'process_instance' => $_SESSION['process_instance']
                        ]);
                    } else {
                        self::crear_log($met, 'Datos invalidos: 401');
                        return json_encode(['status' => 'error', 'message' => 'Datos invalidos', 'code' => 401]);
                    }
                } else {
                    self::crear_log($met, 'Datos invalidos: 404');
                    return json_encode(['status' => 'error', 'message' => 'Datos invalidos', 'code' => 404]);
                }
            } catch (PDOException $e) {
                self::crear_log($met, $e->getMessage());
                return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
            }
        }
    }

    public function cerrar_sesion()
    {
        $met = __METHOD__;
        if (session_destroy()) {
            self::crear_log($met, "Sesión finalizada");

            // Destruir todas las variables de sesión.
            $_SESSION = array();

            // Destruir Cookie de sesión.
            // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            return json_encode(['status' => 'success', 'message' => 'Sesión finalizada.', 'code' => 200]);
        } else {
            self::crear_log($met, 'Error al finalizar sesión');
            return json_encode(['status' => 'error', 'message' => 'Sesión NO finalizada.', 'code' => 502]);
        }
    }

    public function registrar_cliente(array $data)
    {
        $met = __METHOD__;

        // DATOS
        $nombre = $data['nombre'];
        $cedula = $data['cedula'];
        $correo = $data['correo'];
        $telf = str_replace(['-', '.', '+'], '', $data['telefono']);

        // VALIDAR FORMATO DE CONTRASEÑA
        $pattern = '/((?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,15})/';
        if (preg_match($pattern, $data['clave'])) {
            $clave = md5($data['clave']);
        } else {
            return json_encode(['status' => 'error', 'message' => 'La clave debe tener al menos 6 caracteres entre mayusculas, minusculas y numeros, intente nuevamente.']);
        }

        // CONSULTAR EMAIL
        try {
            $stmt = $this->mysql->prepare("SELECT COUNT(id) FROM clientes WHERE correo = '$correo' OR nro_cedula = '$cedula' OR nro_telf = '$telf'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_NUM);

            // REGISTRAR DATOS
            if ($row[0] == 0) {
                $stmt = $this->mysql->prepare("INSERT INTO clientes (
                id, nombre, nro_cedula, correo, nro_telf, clave)
                VALUES (
                NULL, '$nombre', '$cedula', '$correo', '$telf', '$clave')");

                $stmt->execute();

                if ($stmt) {
                    self::crear_log($met, "Registro de nuevo cliente: $nombre");
                    return json_encode(['status' => 'success', 'message' => 'Datos registrados correctamente, ya puede iniciar sesión.']);
                }
            } else {
                self::crear_log($met, "Intento de nuevo registro con cliente ya registrado: $nombre");
                return json_encode(['status' => 'error', 'message' => 'Los datos ingresados ya se encuentran registrados, revise correo, teléfono o número de cedula e intente nuevamente.']);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    public function eliminar_cliente(array $data)
    {
        $met = __METHOD__;

        // DATOS
        $cedula = $data['cedula'];
        $correo = $data['correo'];

        // CONSULTAR EMAIL
        try {
            $stmt = $this->mysql->prepare("DELETE FROM clientes WHERE correo = '$correo' OR nro_cedula = '$cedula'");
            $stmt->execute();
            self::crear_log($met, "Cliente eliminado: $correo");
            return json_encode(['status' => 'success', 'message' => 'Cliente eliminado correctamente.']);
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    public function solicitar_cambio_clave(array $data)
    {
        $met = __METHOD__;

        try {
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $stmt = $this->mysql->prepare("SELECT nombre FROM clientes WHERE correo = '$email'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Enviar Email si la direccion de correo esta registrada
                $mail   = new PHPMailer();
                $urlID  = base64_encode($email);
                $t      = time();

                // Validar si el hospedaje utilizado es 'liorcosmetics.com' para implementar la URL correspondiente
                if ($this->host == 'liorcosmetics.com') {
                    // URL para recuperacion de contraseña (produccion)
                    $link   = "https://liorcosmetics.com/portalpagos/controller.php?verificar-id=true&id=$urlID&t=$t";
                } else {
                    // URL para recuperacion de contraseña (desarrollo)
                    $link   = "http://enlacesbosto.dnsalias.com:8083/portalpagoslior/redirect.php?id=$urlID&t=$t";
                }

                // Configurar servidor SMTP
                $mail->isSMTP();
                $mail->SMTPDebug = false;
                $mail->Host = 'lior.gconex.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sistemas@liorcosmetics.com'; // Tu dirección de correo electrónico
                $mail->Password = 's0p0rt3.12'; // Tu contraseña de correo electrónico
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                // Configurar correo electrónico
                $mail->setFrom('sistemas@liorcosmetics.com', 'Mensaje Lior'); // Emisor
                $mail->addAddress($email); // Destinatario
                $mail->Subject = 'Recuperar clave Punto de venta virtual Lior Cosmetics';

                // Cargar plantilla HTML
                $plantilla = file_get_contents('plantillaCorreo-CLAVE.php');
                $plantilla = str_replace('%NOMBRE%', ucwords($row['nombre']), $plantilla);
                $plantilla = str_replace('%ENLACE%', $link, $plantilla);

                // Configurar plantilla HTML
                $mail->isHTML(true);
                $mail->Body = $plantilla;

                // Evadir la validacion SSL
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' =>
                        false,
                        'allow_self_signed' => true
                    )
                );

                // $to = $email;
                // $subject = 'Recuperar clave Punto de venta virtual Lior Cosmetics';
                // $message = $plantilla;
                // $additional_headers = [];
                // $additional_params = '';
                // mail($to, $subject, $message);

                // Enviar correo electrónico
                if ($mail->send()) {
                    // Eliminar clave existente en caso de existir el cliente y ser efectivo el envio del correo
                    $this->mysql->query("UPDATE clientes SET clave = '' WHERE correo = '$email'");

                    self::crear_log($met, "Correo de recuperación de clave enviado a $email");
                    return json_encode(['status' => 'success', 'message' => 'Se han enviado las instrucciones para recuperación de clave al correo indicado.', 'code' => 200]);
                } else {
                    self::crear_log($met, "$email: $mail->ErrorInfo");
                    return json_encode(['status' => 'error', 'message' => 'Error al enviar el correo de recuperación, intente nuevamente.', 'code' => 403]);
                }
            } else {
                self::crear_log($met, "Correo no registrado: $email");
                return json_encode(['status' => 'error', 'message' => 'Correo no registrado, contacte con un asesor de ventas.', 'code' => 404]);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con nuestro equipo de ventas.', 'code' => 200]);
        }
    }

    public function cambiar_clave($data)
    {
        $met = __METHOD__;

        try {
            // Validar formato de contraseña
            $clave = json_decode($data)->clave;
            $pattern = '/((?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,15})/';
            if (preg_match($pattern, $clave)) {
                $clave = md5($clave);
            } else {
                return json_encode(['status' => 'error', 'message' => 'La clave debe tener al menos 6 caracteres entre mayusculas, minusculas y numeros, intente nuevamente.']);
            }

            $correo = $_SESSION['correo'];
            $stmt   = $this->mysql->prepare("UPDATE clientes SET clave = '$clave' WHERE correo = '$correo'");
            $stmt->execute();

            // Enviar Email si la direccion de correo esta registrada
            self::crear_log($met, "Se cambio de clave del cliente: $correo");
            return json_encode(['status' => 'success', 'message' => 'Cambio de clave exitoso, ya puede iniciar sesión', 'code' => 200]);
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    // METODOS DE PAGO
    public function procesar_pago_vpos($data)
    {
        $met = __METHOD__;

        $data = (array) json_decode($data);

        try {
            $afiliacion     = $this->codigoAfiliacionVpos;
            $cvv            = intval(filter_var($data["cvv"], FILTER_SANITIZE_NUMBER_INT));
            $fechExp        = intval(filter_var($data["fechExp"], FILTER_SANITIZE_NUMBER_INT));
            $identificacion = intval(filter_var($data["identificacion"], FILTER_SANITIZE_NUMBER_INT));
            $refInterna     = 'R' . time();
            $monto          = floatval(filter_var($data["monto"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $nomTarjeta     = htmlspecialchars($data["nomTarjeta"]);
            $pin            = intval(filter_var($data["pin"], FILTER_SANITIZE_NUMBER_INT));
            $tarjeta        = intval(intval(filter_var($data["tarjeta"], FILTER_SANITIZE_NUMBER_INT)));
            $tipCuenta      = intval(filter_var($data["tipCuenta"], FILTER_SANITIZE_NUMBER_INT));
            $tipTarjeta     = intval(filter_var($data["tipTarjeta"], FILTER_SANITIZE_NUMBER_INT));

            $soliVpos = array("TransactionIdentifier" => $refInterna, "Amount" => $monto, "idCardType" => $tipTarjeta, "CardNumber" => $tarjeta, "dtExpiration" => $fechExp, "CardHolderName" => $nomTarjeta, "AccountType" => $tipCuenta, "CVV" => $cvv, "CardPIN" => $pin, "CardHolderID" => $identificacion, "AffiliationNumber" => $afiliacion);
            $jsonVpos = json_encode($soliVpos);
            $vPos_value = encrypt($jsonVpos, $_SESSION['WorkingKey']);
            $vPos_referencia = refere();
            $vPos_validation = createHash($jsonVpos);
            $vPos_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $vPos_value, "Validation" => $vPos_validation, "Reference" => $vPos_referencia, "swTestOperation" => false);
            $jsonSolicitud = json_encode($vPos_solicitud);

            $gurl = $this->apiURL . '/Transaction/Send';
            $gResult = json_decode(gPost($gurl, $jsonSolicitud));

            // Validar efectividad de la transacción para registrar en el historial
            if (!$gResult) {
                return json_encode(['status' => 'error', 'message' => 'Ha ocurrido un error, por favor intente nuevamente', 'code' => 500]);
            }

            if ($gResult->status == 'OK') {
                // Procesar mensaje recibido
                $msgTmp = explode(',', $gResult->message);

                $code       = substr($gResult->message, 0, 6);
                $msg        = substr($gResult->message, 6);
                $refBanco   = preg_replace("/[^0-9]/", '', $msgTmp[1]);
                $cliente    = $_SESSION['cli_des'];
                $cedula     = $_SESSION['client_id'];
                $fecEmis    = date('Y-m-d H:i:s');
                $notas      = $data['notas'];
                $status     = $gResult ? 1 : 0;

                $stmt = $this->mysql->prepare("INSERT INTO historial_pagos (
                id, fec_emis, cliente, nro_cedula, banco, monto, ref_interna, ref_banco, tipo_pago, concepto, estatus) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                $stmt->bindValue(1, NULL);
                $stmt->bindValue(2, $fecEmis);
                $stmt->bindValue(3, $cliente);
                $stmt->bindValue(4, $cedula);
                $stmt->bindValue(5, 'BNC Debito');
                $stmt->bindValue(6, $monto);
                $stmt->bindValue(7, $refInterna);
                $stmt->bindValue(8, $refBanco);
                $stmt->bindValue(9, 'DEB');
                $stmt->bindValue(10, 'Abonos');
                $stmt->bindValue(11, $status);
                $stmt->execute();

                // REGISTRAR DOCUMENTOS ASOCIADAS CON EL PAGO
                // NE activas para pruebas: 68158,68336,68161,68167,68762
                foreach ($notas as $ne) {
                    // Buscar documento en SQL Server
                    $numNE = intval(filter_var($ne->num, FILTER_SANITIZE_NUMBER_INT));
                    $abonoNE = floatval(filter_var($ne->monto, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

                    $row = self::notas_entrega_api('detalles_documento', $numNE);

                    $doc        = $row['data']['documento'];
                    $resp       = $row['data']['responsable'];
                    $monto_us   = $row['data']['monto_us'];
                    $monto_bs   = $row['data']['monto_bs'];

                    // Registrar documento
                    $stmt = $this->mysql->prepare("INSERT INTO detalles_pagos (
                    id, documento, responsable, monto_us, monto_bs, abono_bs, ref_interna, ref_bnc)VALUES(
                    NULL, $doc, '$resp', $monto_us, $monto_bs, $abonoNE, '$refInterna', $refBanco)");
                    $stmt->execute();
                }

                // LOG DEL EVENTO
                self::crear_log($met, "Cod: $code - $msg, Ref: $refBanco");

                // ENVIAR CORREO AL USUARIO
                $mail = self::preparar_email($monto, $refBanco);

                if ($mail->send()) {
                    self::crear_log($met, 'Email de trasacción exitosa enviado.');
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Se han enviado los detalles de la transacción a su dirección de correo.", 'code' => 200]);
                } else {
                    self::crear_log($met, "Email de transacción no enviado: $mail->ErrorInfo");
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Error al enviar el correo con los detalles de su transacción.", 'code' => 403]);
                }
            } else {
                $code   = substr($gResult->message, 0, 6);
                $msg    = substr($gResult->message, 6);
                self::crear_log($met, "Error de transacción: cod: $code - $msg");
                return json_encode(['status' => 'error', 'message' => $msg, 'code' => $code]);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    public function procesar_pago_c2p($data)
    {
        $met = __METHOD__;

        $data = (array) json_decode($data);

        try {
            //valores
            $telefono   = '58' . intval(filter_var($data['telefono'], FILTER_SANITIZE_NUMBER_INT));
            $cedula     = htmlspecialchars($data['cedula']);
            $banco      = intval(filter_var($data["co-banco"], FILTER_SANITIZE_NUMBER_INT));
            $token      = intval(filter_var($data["token"], FILTER_SANITIZE_NUMBER_INT));
            $terminal   = $this->terminalC2P;
            $monto      = floatval(filter_var($data["monto"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $notas      = $data['notas'];
            $refInterna = 'R' . time();
            $concepto   = isset($data['concepto']) != '' ? htmlspecialchars($data['concepto']) : 'Abonos';

            //armar array
            $soliC2p = array(
                "DebtorBankCode" => $banco,
                "DebtorCellPhone" => $telefono,
                "DebtorID" => $cedula,
                "Amount" => $monto,
                "Token" => $token,
                "Terminal" => $terminal
            );

            $jsonC2p = json_encode($soliC2p);
            $c2p_value = encrypt($jsonC2p, $_SESSION['WorkingKey']);
            $c2p_referencia = refere();
            $c2p_validation = createHash($jsonC2p);

            $C2p_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $c2p_value, "Validation" => $c2p_validation, "Reference" => $c2p_referencia, "swTestOperation" => false);
            $jsonSolicitud = json_encode($C2p_solicitud);

            $gurl = $this->apiURL . '/MobPayment/SendC2P';
            $gResult = json_decode(gPost($gurl, $jsonSolicitud));

            // Validar efectividad de la transacción para registrar en el historial
            if (!$gResult) {
                return json_encode(['status' => 'error', 'message' => 'Ha ocurrido un error, por favor intente nuevamente', 'code' => 500]);
            }

            if (@$gResult->status == 'OK') {
                self::crear_log($met, "Se ha realizado un pago por un monto de $monto Bs. referencia: $refInterna");

                // Procesar mensaje recibido
                $msgTmp = explode(',', $gResult->message);

                $code       = substr($gResult->message, 0, 6);
                $msg        = substr($gResult->message, 6);
                $refBanco   = preg_replace("/[^0-9]/", '', $msgTmp[1]);
                $cliente    = $_SESSION['cli_des'];
                $cedula     = $_SESSION['client_id'];
                $fecEmis    = date('Y-m-d H:i:s');
                $notas      = $data['notas'];
                $status     = $gResult ? 1 : 0;

                $stmt = $this->mysql->prepare("INSERT INTO historial_pagos (
                id, fec_emis, cliente, nro_cedula, banco, monto, ref_interna, ref_banco, tipo_pago, concepto, estatus) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                $stmt->bindValue(1, NULL);
                $stmt->bindValue(2, $fecEmis);
                $stmt->bindValue(3, $cliente);
                $stmt->bindValue(4, $cedula);
                $stmt->bindValue(5, self::banco($banco));
                $stmt->bindValue(6, $monto);
                $stmt->bindValue(7, $refInterna);
                $stmt->bindValue(8, $refBanco);
                $stmt->bindValue(9, 'C2P');
                $stmt->bindValue(10, $concepto);
                $stmt->bindValue(11, $status);
                $stmt->execute();

                // REGISTRAR DOCUMENTOS ASOCIADAS CON EL PAGO
                // NE activas para ejemplo: 68158,68336,68161,68167,68762
                foreach ($notas as $ne) {
                    // Buscar documento en SQL Server
                    $numNE = intval(filter_var($ne->num, FILTER_SANITIZE_NUMBER_INT));
                    $abonoNE = floatval(filter_var($ne->monto, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

                    $row = self::notas_entrega_api('detalles_documento', $numNE);

                    $doc        = $row['data']['documento'];
                    $resp       = $row['data']['responsable'];
                    $monto_us   = $row['data']['monto_us'];
                    $monto_bs   = $row['data']['monto_bs'];

                    // Registrar documento
                    $stmt = $this->mysql->prepare("INSERT INTO detalles_pagos (
                    id, documento, responsable, monto_us, monto_bs, abono_bs, ref_interna, ref_bnc)VALUES(
                    NULL, $doc, '$resp', $monto_us, $monto_bs, $abonoNE, '$refInterna', $refBanco)");
                    $stmt->execute();
                }

                // LOG DEL EVENTO
                self::crear_log($met, $msg . " Ref: $refBanco");

                // ENVIAR CORREO AL USUARIO
                $mail = self::preparar_email($monto, $refBanco);

                if ($mail->send()) {
                    self::crear_log($met, 'Email de trasacción exitosa enviado.');
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Se han enviado los detalles de la transacción a su dirección de correo.", 'code' => 200]);
                } else {
                    self::crear_log($met, "Email de transacción no enviado: $mail->ErrorInfo");
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Error al enviar el correo con los detalles de su transacción.", 'code' => 403]);
                }
            } else {
                if (isset($gResult->DebtorCellPhone)) {
                    $msg    = $gResult->DebtorCellPhone[0];
                    $code   = 'N/A';
                } else if (isset($gResult->DebtorID)) {
                    $msg    = $gResult->DebtorID[0];
                    $code   = 'N/A';
                } else {
                    $code   = substr($gResult->message, 0, 6);
                    $msg    = substr($gResult->message, 6);
                }

                self::crear_log($met, "Error de transacción: cod: $code - $msg");
                return json_encode(['status' => 'error', 'message' => $msg, 'code' => $code]);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    /*
    public function procesar_pago_p2p($data)
    {
        $met = __METHOD__;

        $data = (array) json_decode($data);

        try {
            $banco          = intval('0191');
            $monto          = floatval(filter_var($data['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $telefono       = '58' . intval(filter_var($data['telefono'], FILTER_SANITIZE_NUMBER_INT));
            $cedula         = htmlspecialchars($data['cedula']);
            $concepto       = isset($data['concepto']) != '' ? htmlspecialchars($data['concepto']) : 'Abonos';
            $refInterna     = 'R' . time();

            // Arreglo de datos
            $jsonP2p = json_encode(array(
                "BeneficiaryBankCode" => $banco,
                "BeneficiaryCellPhone" => $telefono,
                "BeneficiaryID" => $cedula,
                "BeneficiaryName" => 'Lior Cosmetics, C.A.',
                "Amount" => $monto,
                "Description" => $concepto,
                "BeneficiaryEmail" => 'sistemas@liorcosmetics.com'
            ));

            $p2p_value = encrypt($jsonP2p, $_SESSION['WorkingKey']);
            $p2p_referencia = refere();
            $p2p_validation = createHash($jsonP2p);

            $P2p_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $p2p_value, "Validation" => $p2p_validation, "Reference" => $p2p_referencia, "swTestOperation" => false);
            $jsonSolicitud = json_encode($P2p_solicitud);

            $gurl = $this->apiURL . "/MobPayment/SendP2P";
            $gResult = json_decode(gPost($gurl, $jsonSolicitud));

            // Validar efectividad de la transacción para registrar en el historial
            if (!$gResult) {
                return json_encode(['status' => 'error', 'message' => 'Ha ocurrido un error, por favor intente nuevamente', 'code' => 500]);
            }

            if (@$gResult->status == 'OK') {
                // Procesar mensaje recibido
                $msgTmp = explode(',', $gResult->message);

                $code       = substr($gResult->message, 0, 6);
                $msg        = substr($gResult->message, 6);
                $refBanco   = preg_replace("/[^0-9]/", '', $msgTmp[2]);
                $cliente    = $_SESSION['cli_des'];
                $cedula     = $_SESSION['client_id'];
                $fecEmis    = date('Y-m-d H:i:s');
                $notas      = $data['notas'];
                $status     = $gResult ? 1 : 0;

                $stmt = $this->mysql->prepare("INSERT INTO historial_pagos (
                id, fec_emis, cliente, nro_cedula, banco, monto, ref_interna, ref_banco, tipo_pago, concepto, estatus) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                $stmt->bindValue(1, NULL);
                $stmt->bindValue(2, $fecEmis);
                $stmt->bindValue(3, $cliente);
                $stmt->bindValue(4, $cedula);
                $stmt->bindValue(5, 'BNC');
                $stmt->bindValue(6, $monto);
                $stmt->bindValue(7, $refInterna);
                $stmt->bindValue(8, $refBanco);
                $stmt->bindValue(9, 'P2P');
                $stmt->bindValue(10, $concepto);
                $stmt->bindValue(11, $status);
                $stmt->execute();

                // REGISTRAR DOCUMENTOS ASOCIADAS CON EL PAGO
                // NE activas para ejemplo: 68158,68336,68161,68167,68762
                foreach ($notas as $ne) {
                    // Buscar documento en SQL Server
                    $numNE = intval(filter_var($ne->num, FILTER_SANITIZE_NUMBER_INT));
                    $abonoNE = floatval(filter_var($ne->monto, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

                    $row = self::notas_entrega_api('detalles_documento', $numNE);

                    $doc        = $row['data']['documento'];
                    $resp       = $row['data']['responsable'];
                    $monto_us   = $row['data']['monto_us'];
                    $monto_bs   = $row['data']['monto_bs'];

                    // Registrar documento
                    $stmt = $this->mysql->prepare("INSERT INTO detalles_pagos (
                    id, documento, responsable, monto_us, monto_bs, abono_bs, ref_interna, ref_bnc)VALUES(
                    NULL, $doc, '$resp', $monto_us, $monto_bs, $abonoNE, '$refInterna', $refBanco)");
                    $stmt->execute();
                }

                // LOG DEL EVENTO
                self::crear_log($met, $msg . " Ref: $refBanco");

                // ENVIAR CORREO AL USUARIO
                $mail = self::preparar_email($monto, $refBanco);

                if ($mail->send()) {
                    self::crear_log($met, 'Email de trasacción exitosa enviado.');
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Se han enviado los detalles de la transacción a su dirección de correo.", 'code' => 200]);
                } else {
                    self::crear_log($met, "Email de transacción no enviado: $mail->ErrorInfo");
                    return json_encode(['status' => 'success', 'message' => "Trasacción realizada por un monto de $monto Bs. referencia: $refBanco - Error al enviar el correo con los detalles de su transacción.", 'code' => 403]);
                }
            } else {
                // Validaciones
                if (isset($gResult->BeneficiaryID)) {
                    $msg    = $gResult->BeneficiaryID[0];
                    $code   = 'N/A';
                } else if (isset($gResult->BeneficiaryCellPhone)) {
                    $msg    = $gResult->BeneficiaryCellPhone[0];
                    $code   = 'N/A';
                } else {
                    $code       = substr($gResult->message, 0, 6);
                    $msg        = substr($gResult->message, 6);
                }

                self::crear_log($met, "Error de transacción: cod: $code - $msg");

                return json_encode(['status' => 'error', 'message' => $msg, 'code' => $code]);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }
    */

    public function registrar_pagos_otros_bancos($data)
    {
        $met = __METHOD__;

        $data = (array) json_decode($data);

        try {
            // ===================================> BUSCAR PAGO EN CUENTA
            // Datos de cuenta
            $identidad  = 'J310849900';
            $cuenta     = '01910220312100008999';

            // Datos recibidos del usuario
            $telefono       = @$data['telefono'] ? '58' . intval(filter_var($data['telefono'], FILTER_SANITIZE_NUMBER_INT)) : NULL;
            $banco          = intval(filter_var($data['banco'], FILTER_SANITIZE_NUMBER_INT));
            $monto          = floatval(filter_var($data['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $refBanco       = intval(filter_var($data['referencia'], FILTER_SANITIZE_NUMBER_INT));
            $concepto       = isset($data['concepto']) != '' ? htmlspecialchars($data['concepto']) : 'Abonos';

            // Validar registro del pago en el portal
            $bco = self::banco($banco);
            $stmt = $this->mysql->query("SELECT count(id) FROM historial_pagos WHERE ref_banco = '$refBanco' AND monto = $monto AND banco = '$bco'");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            if ($row[0] > 0) die(json_encode(['status' => 'error', 'message' => "El pago con referencia $refBanco y monto $monto ya se encuentra registrado.", 'code' => 403]));

            // Validar registro del pago en el banco
            if ($data['tipo-pago'] == 'PMOV') {
                $gResult = self::validar_pago_p2p([$identidad, $cuenta, $monto, $banco, $refBanco, $telefono]);
                $tipoPago = "P2P-OTR";
            } else if ($data['tipo-pago'] == 'TRF') {
                $gResult = self::validar_pago_trf([$identidad, $cuenta, $refBanco, $monto]);
                $tipoPago = "TRF-OTR";
            }

            // Error en numero telefonico
            if (@$gResult->PhoneNumber)
                return json_encode(['status' => 'error', 'message' => $gResult->PhoneNumber[0], 'code' => 200]);

            // Registrar pago en caso de ser valido
            $resValue = json_decode(decrypt(@$gResult->value, $_SESSION['WorkingKey']));

            if (@$resValue->MovementExists) {
                $refInterna = 'R' . time();
                $fecEmis    = date('Y-m-d H:i:s');
                $cliente    = $_SESSION['cli_des'];
                $cedula     = $_SESSION['client_id'];
                $notas      = $data['notas'];

                $stmt = $this->mysql->prepare("INSERT INTO historial_pagos (
                id, fec_emis, cliente, nro_cedula, banco, monto, ref_interna, ref_banco, tipo_pago, concepto, estatus) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

                $stmt->bindValue(1, NULL);
                $stmt->bindValue(2, $fecEmis);
                $stmt->bindValue(3, $cliente);
                $stmt->bindValue(4, $cedula);
                $stmt->bindValue(5, self::banco($banco));
                $stmt->bindValue(6, $monto);
                $stmt->bindValue(7, $refInterna);
                $stmt->bindValue(8, $refBanco);
                $stmt->bindValue(9, $tipoPago);
                $stmt->bindValue(10, $concepto);
                $stmt->bindValue(11, 1);
                $stmt->execute();

                // REGISTRAR DOCUMENTOS ASOCIADAS CON EL PAGO
                // NE activas para ejemplo: 68158,68336,68161,68167,68762
                foreach ($notas as $ne) {
                    // Buscar documento en SQL Server
                    $numNE = intval(filter_var($ne->num, FILTER_SANITIZE_NUMBER_INT));
                    $abonoNE = floatval(filter_var($ne->monto, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

                    $row = self::notas_entrega_api('detalles_documento', $numNE);

                    $doc        = $row['data']['documento'];
                    $resp       = $row['data']['responsable'];
                    $monto_us   = $row['data']['monto_us'];
                    $monto_bs   = $row['data']['monto_bs'];

                    // Registrar documento
                    $stmt = $this->mysql->prepare("INSERT INTO detalles_pagos (
                    id, documento, responsable, monto_us, monto_bs, abono_bs, ref_interna, ref_bnc)VALUES(
                    NULL, $doc, '$resp', $monto_us, $monto_bs, $abonoNE, '$refInterna', $refBanco)");
                    $stmt->execute();
                }

                // LOG DEL EVENTO
                self::crear_log($met, "Se ha reportado un PMOV del banco con Cod: $banco, Ref: $refBanco por un monto de: $monto, Ref interna: $refInterna");

                // ENVIAR CORREO AL USUARIO
                $mail = self::preparar_email($monto, $refBanco);

                if ($mail->send()) {
                    self::crear_log($met, 'Email de trasacción exitosa enviado.');
                    return json_encode(['status' => 'success', 'message' => "Su pago por un monto de $monto Bs., Ref $refBanco ha sido registrado correctamente. - Se han enviado los detalles de la transacción a su dirección de correo.", 'code' => 200, '$gResult' => $gResult]);
                } else {
                    self::crear_log($met, "Email de transacción no enviado: $mail->ErrorInfo");
                    return json_encode(['status' => 'success', 'message' => "Su pago por un monto de $monto Bs., Ref $refBanco sido registrado correctamente. - Error al enviar el correo con los detalles de su transacción.", 'code' => 403]);
                }
            } else {
                self::crear_log($met, "Error en datos de pago, Ref: $refBanco");
                return json_encode(['status' => 'error', 'message' => "Pago no encontrado en cuenta, verifique los datos (banco, monto, ref) e intente nuevamente.", 'code' => 401]);
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    private function validar_pago_p2p(array $data)
    {
        // Arreglo de datos para validacion
        $soliPosi = array(
            'ClientID' => $data[0],
            'AccountNumber' => $data[1],
            'Amount' => $data[2],
            'BankCode' => $data[3],
            'Reference' => $data[4],
            'PhoneNumber' => $data[5]
        );

        $jsonPosi = json_encode($soliPosi);
        $Posi_value = encrypt($jsonPosi, $_SESSION['WorkingKey']);
        $Posi_referencia = refere();
        $Posi_validation = createHash($jsonPosi);

        $Posi_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $Posi_value, "Validation" => $Posi_validation, "Reference" => $Posi_referencia, "swTestOperation" => false);
        $jsonSolicitud = json_encode($Posi_solicitud);
        $gurl = $this->apiURL . '/Position/ValidateP2P';

        return json_decode(gPost($gurl, $jsonSolicitud));
    }

    private function validar_pago_trf(array $data)
    {
        // Arreglo de datos para validacion
        $soliPosi = array(
            "ClientID" => $data[0],
            "AccountNumber" => $data[1],
            "Reference" => $data[2],
            'Amount' => $data[3]
        );

        $jsonPosi = json_encode($soliPosi);
        $Posi_value = encrypt($jsonPosi, $_SESSION['WorkingKey']);
        $Posi_referencia = refere();
        $Posi_validation = createHash($jsonPosi);

        $Posi_solicitud = array("ClientGUID" => $_SESSION['clienteGUID'], "value" => $Posi_value, "Validation" => $Posi_validation, "Reference" => $Posi_referencia, "swTestOperation" => false);
        $jsonSolicitud = json_encode($Posi_solicitud);
        $gurl = $this->apiURL . '/Position/Validate';

        return json_decode(gPost($gurl, $jsonSolicitud));
    }

    // HISTORIAL DE PAGOS
    public function consultar_historial_pagos($data)
    {
        $met = __METHOD__;

        $cedula = $_SESSION['client_id'];
        try {
            $sql = "SELECT 
            historial_pagos.fec_emis, 
            historial_pagos.banco, 
            historial_pagos.monto, 
            historial_pagos.ref_banco, 
            historial_pagos.ref_interna, 
            historial_pagos.tipo_pago, 
            detalles_pagos.documento,
            detalles_pagos.abono_bs 
            FROM historial_pagos 
            JOIN detalles_pagos ON historial_pagos.ref_interna = detalles_pagos.ref_interna WHERE historial_pagos.nro_cedula = $cedula ORDER BY fec_emis DESC";

            $stmt = $this->mysql->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['status' => 'success', 'message' => 'Solicitud procesada.',  'data' => $rows, 'code' => 200]);
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    public function consultar_detalles_pago($data)
    {
        $met = __METHOD__;

        $ref = $data['ref'];
        try {
            $stmt = $this->mysql->prepare("SELECT documento,responsable,monto_us,monto_bs,abono_bs FROM detalles_pagos WHERE ref_interna = '$ref'");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_encode(['status' => 'success', 'message' => 'Solicitud procesada.',  'data' => $rows, 'code' => 200]);
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    // =====> Para exportar a Excel (Admin)
    /**
     * Exportar historial de pagos a Excel
     * @param boolean $all - Si es true, se exportan todos los registros, si es false, se exportan solo los registros del cliente
     * @param string $cli - Cedula del cliente
     */
    public function historial_pagos_documentos()
    {
        $met = __METHOD__;

        try {
            $sql = "SELECT 
            historial_pagos.fec_emis, 
            historial_pagos.cliente, 
            historial_pagos.nro_cedula, 
            historial_pagos.banco, 
            historial_pagos.monto, 
            historial_pagos.ref_interna, 
            historial_pagos.ref_banco, 
            historial_pagos.tipo_pago, 
            detalles_pagos.documento, 
            detalles_pagos.responsable, 
            detalles_pagos.monto_us, 
            detalles_pagos.monto_bs, 
            detalles_pagos.abono_bs
            FROM historial_pagos 
            JOIN detalles_pagos ON historial_pagos.ref_interna = detalles_pagos.ref_interna ORDER BY fec_emis DESC";

            $stmt = $this->mysql->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['status' => 'success', 'message' => 'Solicitud procesada.',  'data' => $rows, 'code' => 200]);
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    // =====> Logs del sistema
    public function logs_sistema()
    {
        $file = fopen('logs.txt', 'r');
        $data = [];
        while (!feof($file)) {
            $line = fgets($file);
            $arr = explode('|', $line);
            $reg = [];
            $cols = ['fecha', 'usuario', 'clase_metodo', 'detalle'];
            $i = 0;
            foreach ($arr as $dat) {
                $reg[$cols[$i]] = trim($dat);
                $i++;
            }

            // Filtrar la ultima linea que siempre esta en blanco por el salto que esta al final del registro de cada log
            $reg['fecha'] != '' ? array_push($data, $reg) : null;
        }
        fclose($file);
        return json_encode(['status' => 'success', 'message' => 'Solicitud procesada.',  'data' => $data, 'code' => 200]);
    }

    // METODOS ESPECIALES

    /**
     * Consultar existencia/datos de notas de entrega
     * La API retorna un objeto con los siguientes datos
     * [
     *  code: 200,
     *  data: {
     *      'documento': 0000,
     *      'responsable': Jhon Doe,
     *      'monto_us': 00.00,
     *      'monto_bs': 00.00
     *  }
     * ]
     **/
    public function notas_entrega_api($endPoint, $numDoc)
    {
        $ch = curl_init("{$this->neAPIurl}/$endPoint/$numDoc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $res;
    }

    public function autorizar_usuario()
    {
        $met = __METHOD__;

        if ($_SERVER['PHP_AUTH_USER']) {
            if ($_SERVER['PHP_AUTH_USER'] == $_SESSION['client_name'] && $_SERVER['PHP_AUTH_PW'] == $_SESSION['session_id']) {
                return true;
            } else {
                self::crear_log($met, 'Credenciales de autenticación alteradas.');
                return false;
            };
        }
    }

    public function verificar_id_recuperacion($id)
    {
        $met = __METHOD__;

        try {
            $correo = filter_var(base64_decode($id), FILTER_SANITIZE_EMAIL);
            $stmt = $this->mysql->prepare("SELECT COUNT(id) FROM clientes WHERE correo = '$correo'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_NUM);

            if ($row[0] == 1) {
                $_SESSION['correo'] = $correo;
                return true;
            } else {
                self::crear_log($met, 'El ID en URL de recuperación de clave ha sido alterado.');
                return NULL;
            }
        } catch (PDOException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema']);
        }
    }

    public function validar_nota_entrega($num)
    {
        $met = __METHOD__;

        try {
            $res = self::notas_entrega_api('validar_documento', $num);
            switch ($res['code']) {
                case 200:
                    return $res['data'];
                    break;
                case 404:
                    self::crear_log($met, $res['data']);
                    return ['status' => 'error', 'code' => 404, 'data' => $res['data']];
                    break;
                case 502:
                    self::crear_log($met, $res['data']);
                    return ['status' => 'error', 'code' => 502, 'data' => $res['data']];
                    break;
                default:
                    self::crear_log($met, $res['data']);
                    return ['status' => 'error', 'code' => 403, 'data' => 'Error desconocido'];
                    break;
            }
        } catch (PDOException $e) {
            self::crear_log(
                $met,
                $e->getMessage()
            );
            return json_encode(['status' => 'error', 'message' => 'Error interno, si el mismo persiste contácte con el administrador del sistema', 'code' => 200]);
        }
    }

    private function preparar_email($monto, $refBanco)
    {
        $mail   = new PHPMailer();

        // Configurar servidor SMTP
        $mail->isSMTP();
        $mail->SMTPDebug = false;
        $mail->Host = 'lior.gconex.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'portaldepagos@liorcosmetics.com'; // Tu dirección de correo electrónico
        $mail->Password = 'p0r74ld3p4g0s123.'; // Tu contraseña de correo electrónico
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Configurar correo electrónico
        $mail->setFrom('sistemas@liorcosmetics.com', 'Mensaje Lior'); // Emisor
        $mail->addAddress($_SESSION['correo']); // Destinatario
        $mail->Subject = 'Transacción realizada desde Punto de venta virtual Lior Cosmetics';

        // Cargar plantilla HTML
        $plantilla = file_get_contents('plantillaCorreo-PAGOS.php');
        $plantilla = str_replace('%NOMBRE%', $_SESSION['cli_des'], $plantilla);
        $plantilla = str_replace('%MONTO%', $monto, $plantilla);
        $plantilla = str_replace('%REF%', $refBanco, $plantilla);

        // Configurar plantilla HTML
        $mail->isHTML(true);
        $mail->Body = $plantilla;

        // Evadir la validacion SSL
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' =>
                false,
                'allow_self_signed' => true
            )
        );

        return $mail;
    }

    /**
     * Crear logs de aplicación
     * 
     * @param string - Metodo en el cual se produce el evento
     * @param mixed - Mensaje del log en caso de ser necesario
     * 
     */
    private function crear_log(string $met, mixed $det = 'Sin detalles'): void
    {
        $file = fopen('logs.txt', 'a+');
        $fec = date('Y-m-d H:i:s');
        $usr = isset($_SESSION['cli_des']) != "" ? $_SESSION['cli_des'] : 'offline';
        $log = "$fec | $usr | $met |  Detalle: $det\n";
        fwrite($file, $log);
        fclose($file);
    }

    public function consultar_tasa_bcv()
    {
        $met = __METHOD__;

        try {
            $url = 'https://ve.dolarapi.com/v1/dolares/oficial';
            $method = 'GET';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            $response = json_decode(curl_exec($ch));
            curl_close($ch);
            return ['status' => 'success', 'code' => 200, 'data' => $response->promedio];
        } catch (GlobalException $e) {
            self::crear_log($met, $e->getMessage());
            return json_encode(['status' => 'error', 'code' => 403, 'data' => 'Error al consultar tasa BCV']);
        }
    }

    private function banco($codigo)
    {
        $banco = [
            "102" => "BANCO DE VENEZUELA",
            "156" => "100% BANCO",
            "172" => "BANCAMIGA BANCO MICROFINANCIERO C A",
            "114" => "BANCARIBE",
            "171" => "BANCO ACTIVO",
            "166" => "BANCO AGRICOLA DE VENEZUELA",
            "175" => "BANCO BICENTENARIO DEL PUEBLO",
            "128" => "BANCO CARONI",
            "163" => "BANCO DEL TESORO",
            "115" => "BANCO EXTERIOR",
            "151" => "BANCO FONDO COMUN",
            "173" => "BANCO INTERNACIONAL DE DESARROLLO",
            "105" => "BANCO MERCANTIL",
            "191" => "BANCO NACIONAL DE CREDITO",
            "138" => "BANCO PLAZA",
            "137" => "BANCO SOFITASA",
            "104" => "BANCO VENEZOLANO DE CREDITO",
            "168" => "BANCRECER",
            "134" => "BANESCO",
            "177" => "BANFANB",
            "146" => "BANGENTE",
            "174" => "BANPLUS",
            "108" => "BBVA PROVINCIAL",
            "157" => "DELSUR BANCO UNIVERSAL",
            "169" => "MI BANCO",
            "178" => "N58 BANCO DIGITAL BANCO MICROFINANCIERO S A"
        ];
        return $banco[$codigo];
    }
}
