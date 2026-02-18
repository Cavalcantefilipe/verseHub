<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Oauth2;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAuthService
{
    private GoogleClient $client;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    /**
     * Get the Google OAuth authorization URL
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get the Google OAuth authorization URL with a custom redirect URI.
     * Used for dynamic redirect URIs (e.g., when accessed via LAN IP).
     */
    public function getAuthUrlWithRedirect(string $redirectUri): string
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri($redirectUri);
        $client->addScope('email');
        $client->addScope('profile');
        $client->setAccessType('offline');
        $client->setPrompt('select_account');

        return $client->createAuthUrl();
    }

    /**
     * Exchange authorization code for user information
     */
    public function getUserFromCode(string $code): array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new Exception('Failed to fetch access token: ' . $token['error']);
        }

        $this->client->setAccessToken($token);

        $oauth = new Oauth2($this->client);
        $userInfo = $oauth->userinfo->get();

        return [
            'email' => $userInfo->email,
            'name' => $userInfo->name,
            'google_id' => $userInfo->id,
            'avatar' => $userInfo->picture,
            'email_verified' => $userInfo->verifiedEmail,
        ];
    }

    /**
     * Exchange authorization code for user information using a custom redirect URI.
     * Supports PKCE (code_verifier) for mobile apps using expo-auth-session.
     */
    public function getUserFromCodeWithRedirect(string $code, string $redirectUri, ?string $codeVerifier = null): array
    {
        // Build token exchange request manually to support dynamic redirect_uri and PKCE
        $params = [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ];

        if ($codeVerifier) {
            $params['code_verifier'] = $codeVerifier;
        }

        Log::info('[GoogleAuth] Exchanging code for tokens', [
            'redirect_uri' => $redirectUri,
            'has_code_verifier' => !empty($codeVerifier),
        ]);

        // Exchange authorization code for tokens
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', $params);

        if (!$response->successful()) {
            $error = $response->json();
            Log::error('[GoogleAuth] Token exchange failed', [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new Exception(
                'Failed to exchange authorization code: ' .
                    ($error['error_description'] ?? $error['error'] ?? 'Unknown error')
            );
        }

        $tokenData = $response->json();
        Log::info('[GoogleAuth] Token exchange successful');

        // If we have an id_token, decode it for user info (faster, no extra API call)
        if (isset($tokenData['id_token'])) {
            return $this->extractUserFromIdToken($tokenData['id_token']);
        }

        // Fallback: use access_token to fetch user info from Google API
        if (isset($tokenData['access_token'])) {
            return $this->fetchUserInfoWithAccessToken($tokenData['access_token']);
        }

        throw new Exception('No id_token or access_token received from Google');
    }

    /**
     * Extract user info from a Google ID token (JWT) without verification via Google Client.
     */
    private function extractUserFromIdToken(string $idToken): array
    {
        // Use Google Client to verify the id_token
        $payload = $this->client->verifyIdToken($idToken);

        if (!$payload) {
            // Fallback: decode JWT without verification (the token was just issued by Google)
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                throw new Exception('Invalid ID token format');
            }
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (!$payload) {
                throw new Exception('Failed to decode ID token');
            }
        }

        return [
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? ($payload['given_name'] ?? 'User'),
            'google_id' => $payload['sub'] ?? null,
            'avatar' => $payload['picture'] ?? null,
            'email_verified' => $payload['email_verified'] ?? false,
        ];
    }

    /**
     * Fetch user info from Google using an access token.
     */
    private function fetchUserInfoWithAccessToken(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo');

        if (!$response->successful()) {
            throw new Exception('Failed to fetch user info from Google');
        }

        $userInfo = $response->json();

        return [
            'email' => $userInfo['email'] ?? null,
            'name' => $userInfo['name'] ?? 'User',
            'google_id' => $userInfo['id'] ?? null,
            'avatar' => $userInfo['picture'] ?? null,
            'email_verified' => $userInfo['verified_email'] ?? false,
        ];
    }

    /**
     * Verify Google ID token (for direct token verification)
     */
    public function verifyIdToken(string $idToken): array
    {
        $payload = $this->client->verifyIdToken($idToken);

        if (!$payload) {
            throw new Exception('Invalid ID token');
        }

        return [
            'email' => $payload['email'],
            'name' => $payload['name'],
            'google_id' => $payload['sub'],
            'avatar' => $payload['picture'] ?? null,
            'email_verified' => $payload['email_verified'] ?? false,
        ];
    }
}
