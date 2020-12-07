#!/usr/bin/php
<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2019-11-14 22:16:25
 *
 * @file kovey/main/generate.php
 *
 */
if (!extension_loaded('openssl')) {
    echo 'extension openssl is not found.' . PHP_EOL;
    exit;
}

$opt = getopt('s:t:p:');

if (empty($opt['t'])) {
    showHelp();
    exit;
}

$size = intval($opt['s'] ?? 43);

$path = $opt['p'] ?? __DIR__;

if (!is_dir($path)) {
    mkdir($path, 0777, true);
}

if ($opt['t'] == 'rsa') {
    rsa($size, $path);
    exit;
}

if ($opt['t'] == 'aes') {
    secret($size);
    exit;
}

showHelp();

function showHelp()
{
    echo 'Usage: php generate.php <-t rsa | aes>' . PHP_EOL .
        'Options:' . PHP_EOL .
        '   -p rsa created in directory, default is current directory' . PHP_EOL .
        '   -s secret length, default is 43' . PHP_EOL;
    exit;
}

function secret($size)
{
    $chars = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n',
        'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    );

    $result = '';
    $total = count($chars) - 1;


    for ($i = 0; $i < $size; $i ++) {
        $result .= $chars[random_int(0, $total)];
    }

    echo "aes secret: $result" . PHP_EOL;
}

function rsa($size, $path)
{
    if ($size < 384) {
        echo 'private key length is too short; it needs to be at least 384 bits, not ' . $size . PHP_EOL;
        return;
    }

    $configs = array(
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => $size
    );
    $res = openssl_pkey_new($configs);
    if ($res === false) {
        echo sprintf('rsa keys created failure, error: %s', openssl_error_string()) . PHP_EOL;
        return;
    }

    openssl_pkey_export($res, $priKey);
    $pubKey = openssl_pkey_get_details($res);

    file_put_contents($path . '/private.pem', $priKey);
    file_put_contents($path . '/public.pem', $pubKey['key']);

    echo "rsa keys created in $path" . PHP_EOL;
}
