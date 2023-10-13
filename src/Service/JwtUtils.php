<?php

namespace App\Service;

use Exception;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class JwtUtils
{
    public function parse(string $token): UnencryptedToken
    {
        $jwtParser = new Parser(new JoseEncoder());

        $parsedToken = $jwtParser->parse($token);
        if (!$parsedToken instanceof UnencryptedToken) {
            throw new Exception('token is encrypted');
        }

        return $parsedToken;
    }
}
