<?php
function createHash($data)
{
    $validation = hash('sha256', utf8_encode($data));
    return $validation;
}

function proSession($val, $Masterkey)
{
    $wk = decrypt($val, $Masterkey);
    $wk = json_decode($wk, true);
    $wk = $wk['WorkingKey'];
    $_SESSION['WorkingKey'] = $wk;
}

function gPost($gurl, $jsonSolicitud)
{
    $ch = curl_init($gurl);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonSolicitud);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function refere()
{
    //20220831090831
    $fecha = date('Y-m-d h:i:s', time());
    $fecha = strval($fecha);
    $fecha = str_replace("-", "", $fecha);
    $fecha = str_replace(":", "", $fecha);
    $fecha = str_replace(" ", "", $fecha);
    $result = $fecha;
    return $result;
}

function encrypt($data, $Masterkey)
{
    $method = 'aes-256-cbc';
    $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . chr(0x64) . chr(0x65) . chr(0x76);

    $pbkdf2 = hash_pbkdf2('SHA1', $Masterkey, $sSalt, 1000, 48, true);
    $key = substr($pbkdf2, 0, 32);
    $iv =  substr($pbkdf2, 32, strlen($pbkdf2));


    $string =  mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
    $encrypted = base64_encode(openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv));
    return $encrypted; //tools
}

function decrypt($data, $Masterkey)
{
    $method = 'aes-256-cbc';
    $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . chr(0x64) . chr(0x65) . chr(0x76);

    $pbkdf2 = hash_pbkdf2('SHA1', $Masterkey, $sSalt, 1000, 48, true);
    $key = substr($pbkdf2, 0, 32);
    $iv =  substr($pbkdf2, 32, strlen($pbkdf2));

    $string = openssl_decrypt(base64_decode($data), $method, $key, OPENSSL_RAW_DATA, $iv);
    $decrypted = mb_convert_encoding($string, 'UTF-8', 'UTF-16LE');

    return $decrypted;
}
