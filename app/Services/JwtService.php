<?php

namespace App\Services;

class JwtService
{
    protected $secret;
    protected $expirationMs;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', '__dev_secret_that_is_at_least_32_chars_long__');
        $this->expirationMs = (int) env('JWT_EXPIRATION_MS', 3600000);
    }

    /**
     * Generate a JWT token for the student.
     */
    public function generate($subject, $email, $fullName)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $now = time();
        $exp = $now + ($this->expirationMs / 1000);

        $payload = json_encode([
            'sub' => $subject,
            'email' => $email,
            'name' => $fullName,
            'iat' => $now,
            'exp' => $exp
        ]);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Parse and validate a JWT token.
     */
    public function parse($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerB64, $payloadB64, $signatureB64) = $parts;

        $sig = hash_hmac('sha256', $headerB64 . "." . $payloadB64, $this->secret, true);
        if ($this->base64UrlEncode($sig) !== $signatureB64) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!$payload || !isset($payload['exp']) || time() >= $payload['exp']) {
            return null;
        }

        return $payload;
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    protected function base64UrlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
