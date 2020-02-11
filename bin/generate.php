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
$opt = getopt('s:t:p:');

$size = intval($opt['s'] ?? 43);

$path = $opt['p'] ?? __DIR__;

if (!is_dir($path)) {
	mkdir($path, 0777, true);
}

if ($opt['t'] == 'rsa') {
    rsa($size, $path);
    exit;
}

secret($size);

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

    echo "$result\n";
}

function rsa($size, $path)
{
    $configs = array(
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => $size
    );
    $res = openssl_pkey_new($configs);
    if ($res === false) {
        return;
    }

    openssl_pkey_export($res, $priKey);
    $pubKey = openssl_pkey_get_details($res);

    file_put_contents($path . '/private.pem', $priKey);
    file_put_contents($path . '/public.pem', $pubKey['key']);

    echo "success\n";
}
