<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private GoogleAuthService $googleAuthService
    ) {}

    /**
     * Register a new user.
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ], 201);
    }

    /**
     * Login user with email and password.
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'google_id' => $user->google_id,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ],
        ]);
    }

    /**
     * Redirect to Google OAuth provider.
     * GET /api/auth/google/redirect
     * Opens Google login page directly in the browser.
     * Dynamically builds the redirect_uri based on the incoming request's host,
     * so it works from both localhost and LAN IP.
     */
    public function redirectToGoogle(Request $request)
    {
        // Usa a redirect URI fixa do .env (deve ser http://localhost:8000/...)
        // IPs privados (192.168.x.x) são rejeitados pelo Google OAuth.
        $callbackUrl = config('services.google.redirect');

        Log::info('[GoogleAuth] Redirect URI for Google: ' . $callbackUrl);

        $url = $this->googleAuthService->getAuthUrlWithRedirect($callbackUrl);
        return redirect()->away($url);
    }

    /**
     * Handle Google OAuth callback.
     * GET /api/auth/google/callback
     * Google redirects here after login. We exchange the code,
     * create/find user, generate JWT, then redirect to the mobile app via deep link.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if (!$request->has('code')) {
                return redirect()->away('bibleversemobile://auth?error=no_code');
            }

            // Deve usar a mesma redirect URI fixa enviada ao Google no redirectToGoogle
            $callbackUrl = config('services.google.redirect');

            Log::info('[GoogleAuth] Callback redirect URI: ' . $callbackUrl);

            // Get user info from Google using the dynamic redirect URI
            $googleUser = $this->googleAuthService->getUserFromCodeWithRedirect(
                $request->code,
                $callbackUrl
            );

            // Find or create user
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['google_id'],
                    'avatar' => $googleUser['avatar'],
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => $googleUser['email_verified'] ? now() : null,
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser['google_id'],
                    'avatar' => $googleUser['avatar'],
                    'email_verified_at' => $user->email_verified_at ?? ($googleUser['email_verified'] ? now() : null),
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            // Redirect to mobile app via deep link with token only
            // The app will fetch user data using the token (simpler, more reliable)
            $deepLink = "bibleversemobile://auth?token={$token}";
            
            Log::info('[GoogleAuth] Redirecting to app', ['email' => $user->email]);

            return redirect()->away($deepLink);
        } catch (\Exception $e) {
            Log::error('Google callback failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $error = urlencode($e->getMessage());
            return redirect()->away("bibleversemobile://auth?error={$error}");
        }
    }

    /**
     * Login with Google ID Token (for mobile/SPA apps).
     * POST /api/auth/google/token
     */
    public function loginWithGoogleToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Verify Google ID token
            $googleUser = $this->googleAuthService->verifyIdToken($request->id_token);

            // Find or create user
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['google_id'],
                    'avatar' => $googleUser['avatar'],
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => $googleUser['email_verified'] ? now() : null,
                ]);
            } else {
                // Update existing user with Google info if not already set
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['google_id'],
                        'avatar' => $googleUser['avatar'],
                        'email_verified_at' => $user->email_verified_at ?? ($googleUser['email_verified'] ? now() : null),
                    ]);
                }
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with Google',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'google_id' => $user->google_id,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Google',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Login with Google user data from mobile app (Expo).
     * The mobile app obtains an access_token from Google via implicit flow,
     * fetches user info, and sends it here. The backend verifies the google_id
     * and creates/finds the user, then returns a JWT.
     * POST /api/auth/google/mobile-login
     */
    public function loginWithGoogleMobile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required|string',
                'google_id' => 'required|string',
                'avatar' => 'nullable|string',
                'email_verified' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Find or create user
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'google_id' => $request->google_id,
                    'avatar' => $request->avatar,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => $request->email_verified ? now() : null,
                ]);
            } else {
                $user->update([
                    'google_id' => $request->google_id,
                    'avatar' => $request->avatar,
                    'email_verified_at' => $user->email_verified_at ?? ($request->email_verified ? now() : null),
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            Log::info('[GoogleAuth] Mobile login successful', ['email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with Google',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'google_id' => $user->google_id,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Google mobile login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Google',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Login with Google Authorization Code (for mobile apps using expo-auth-session).
     * Exchanges auth code for tokens, then extracts user info.
     * POST /api/auth/google/code
     */
    public function loginWithGoogleCode(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'redirect_uri' => 'required|string',
                'code_verifier' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Exchange code for user info
            $googleUser = $this->googleAuthService->getUserFromCodeWithRedirect(
                $request->code,
                $request->redirect_uri,
                $request->code_verifier
            );

            // Find or create user
            $user = User::where('email', $googleUser['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['google_id'],
                    'avatar' => $googleUser['avatar'],
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => $googleUser['email_verified'] ? now() : null,
                ]);
            } else {
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser['google_id'],
                        'avatar' => $googleUser['avatar'],
                        'email_verified_at' => $user->email_verified_at ?? ($googleUser['email_verified'] ? now() : null),
                    ]);
                } else {
                    // Update avatar in case it changed
                    $user->update(['avatar' => $googleUser['avatar']]);
                }
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with Google',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'google_id' => $user->google_id,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Google code auth failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Google',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get authenticated user.
     * GET /api/auth/user
     */
    public function user(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'google_id' => $user->google_id,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                ],
            ],
        ]);
    }

    /**
     * Refresh JWT token.
     * POST /api/auth/refresh
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = auth('api')->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Logout user and invalidate token.
     * POST /api/auth/logout
     */
    public function logout(): JsonResponse
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not logout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
