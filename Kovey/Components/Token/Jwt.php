<?php
/**
 * @description Json Web Token
 *
 * @package App\Token
 *
 * @author zhayai
 *
 * @time 2020-02-16 10:56:25
 *
 * @file jg-boss-api/application/library/App/Token/Jwt.php
 *
 */
namespace Kovey\Components\Token;

use Kovey\Util\Json;
use Kovey\Rpc\Encryption\Aes;

class Jwt 
{
    const JWT_ALG = 'HS256';

    const JWT_TYPE = 'JWT';

    const JWT_ADMIN = 'JWT_API_ADMIN_KOVEY';

    private $expired;

    private $header;

    private $key;

    private $algConfig = array(
        'HS256'=>'sha256'
    );

    public function __construct($key, $expired = 86400)
    {
        $this->expired = $expired;
        $this->key = $key;
        $this->header = array(
            'alg' => self::JWT_ALG,
            'typ' => self::JWT_TYPE
        );
    }

    public function encode(Array $ext)
    {
        $base64header = $this->base64UrlEncode(Json::encode($this->header));
        $base64payload = $this->base64UrlEncode(Json::encode(array(
            'iss' => self::JWT_ADMIN,
            'iat' => time(),
            'exp' => time() + $this->expired,
            'jti' => uniqid('JWT_API_UNIQID', true) . strval(microtime(true)) . random_int(1000000, 9999999),
            'ext' => $ext
        )));

        return $base64header . '.' . $base64payload . '.' . $this->signature($base64header . '.' . $base64payload, $this->key, $this->header['alg']);
    }


    public function decode(string $token)
    {
        $tokens = explode('.', $token);
        if (count($tokens) != 3) {
            throw new TokenExpiredException('TOKEN_EXPIRED');
        }

        list($base64header, $base64payload, $sign) = $tokens;

        $base64decodeheader = Json::decode($this->base64UrlDecode($base64header));

        if (empty($base64decodeheader['alg'])) {
            throw new TokenExpiredException('TOKEN_EXPIRED');
        }

        if ($this->signature($base64header . '.' . $base64payload, $this->key, $base64decodeheader['alg']) !== $sign) {
            throw new TokenExpiredException('TOKEN_EXPIRED');
        }

        $payload = Json::decode($this->base64UrlDecode($base64payload));

        if (empty($payload['iat'])
            || $payload['iat'] > time()
        ) {
            throw new TokenExpiredException('TOKEN_EXPIRED');
        }

        if (empty($payload['exp'])
            || $payload['exp'] < time()
        ) {
            throw new TokenExpiredException('TOKEN_EXPIRED');
        }

        return $payload['ext'] ?? array();
    }

    private function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(Aes::encrypt($input, $this->key), '+/', '-_'));
    }

    private function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return Aes::decrypt(strtr($input, '-_', '+/'), $this->key);
    }

    private function signature(string $input, string $key, string $alg = 'HS256')
    {
        return $this->base64UrlEncode(hash_hmac($this->algConfig[$alg], $input, $key,true));
    }
}
