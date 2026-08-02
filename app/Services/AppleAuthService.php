<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AppleAuthService
{
    private const ISSUER = 'https://appleid.apple.com';

    /**
     * Validate an identity token created by Apple on the iOS device.
     *
     * @return array{sub: string, email: string|null, email_verified: bool}
     */
    public function verifyIdentityToken(string $identityToken): array
    {
        try {
            $claims = $this->decode($identityToken, $this->publicKeys());
        } catch (Throwable) {
            Cache::forget('apple-auth-public-keys');
            $claims = $this->decode($identityToken, $this->publicKeys());
        }

        $audience = config('services.apple.client_id');
        $tokenAudience = is_array($claims->aud ?? null) ? $claims->aud : [$claims->aud ?? null];

        if (($claims->iss ?? null) !== self::ISSUER || ! in_array($audience, $tokenAudience, true)) {
            throw new RuntimeException('Credencial Apple emitida para outro aplicativo.');
        }

        $subject = trim((string) ($claims->sub ?? ''));
        if ($subject === '') {
            throw new RuntimeException('Credencial Apple sem identificador de usuário.');
        }

        return [
            'sub' => $subject,
            'email' => isset($claims->email) ? strtolower(trim((string) $claims->email)) : null,
            'email_verified' => filter_var($claims->email_verified ?? false, FILTER_VALIDATE_BOOL),
        ];
    }

    private function decode(string $identityToken, array $keySet): object
    {
        return JWT::decode($identityToken, JWK::parseKeySet($keySet, 'RS256'));
    }

    private function publicKeys(): array
    {
        return Cache::remember('apple-auth-public-keys', now()->addHours(12), function (): array {
            $keys = Http::acceptJson()
                ->timeout(5)
                ->retry(2, 150)
                ->get(self::ISSUER.'/auth/keys')
                ->throw()
                ->json();

            if (! is_array($keys) || empty($keys['keys'])) {
                throw new RuntimeException('Não foi possível carregar as chaves públicas da Apple.');
            }

            return $keys;
        });
    }
}
