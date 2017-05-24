<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 02.05.17
 * Time: 14:37
 */

/**
 * Default config for sandbox API
 */
//TODO: anton 24.05.17 Убрать конфиг по умолчанию в SignMe.php и в phpDoc
return [
    'urlSign' => 'https://sandbox.sign.me:443/signapi/sjson',
    'urlCheck' => 'https://sandbox.sign.me:443/signaturecheck/json',
    'pathToCertificate' => __DIR__ . '/certs/sandboxsignme.crt',
    'returnUrl' => '',
];