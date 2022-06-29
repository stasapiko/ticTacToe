<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenService
{
    /**
     * @return string
     */
    public static function getToken(): string
    {
        $payload = [
            'name' => env('APP_NAME'),
            'createdAt' => time(),
        ];

        return JWT::encode($payload, env('APP_KEY'), 'HS256');
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function validateToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }
}
