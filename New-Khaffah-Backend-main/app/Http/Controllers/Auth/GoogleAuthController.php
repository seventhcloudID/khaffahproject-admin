<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/auth/google",
     *     tags={"Auth Google"},
     *     summary="Redirect ke Google OAuth",
     *     description="Mengarahkan user ke halaman login Google untuk autentikasi",
     *     @OA\Response(
     *         response=302,
     *         description="Redirect ke halaman login Google"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error saat redirect"
     *     )
     * )
     */
    public function redirectToGoogle()
    {
        $redirectUrl = config('services.google.redirect');
        if (empty($redirectUrl)) {
            $redirectUrl = rtrim(config('app.url'), '/') . '/api/auth/google/callback';
        }
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->stateless()
            ->redirect();
    }

    /**
     * @OA\Get(
     *     path="/api/auth/google/callback",
     *     tags={"Auth Google"},
     *     summary="Handle callback dari Google OAuth",
     *     description="Memproses callback dari Google setelah user login, membuat atau memperbarui user, dan mengembalikan token authentication",
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         required=true,
     *         description="Authorization code dari Google",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="state",
     *         in="query",
     *         required=false,
     *         description="State parameter untuk keamanan CSRF",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect ke frontend dengan token dan data user",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="Bearer abc123..."),
     *             @OA\Property(property="nama", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="foto", type="string", format="uri", example="https://lh3.googleusercontent.com/...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token Google tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token Google tidak valid"),
     *             @OA\Property(property="error", type="string", example="Invalid authorization code")
     *         )
     *     )
     * )
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'nama_lengkap'  => $googleUser->getName(),
                    'email'         => $googleUser->getEmail(),
                    'tgl_lahir'     => Carbon::now()->subYears(20)->toDateString(),
                    'password'      => Hash::make(Str::random(16)),
                    'foto_profile'  => $googleUser->getAvatar(),
                ]);

                $user->assignRole('user');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return redirect(env('FRONTEND_URL') . '/auth/callback?' . http_build_query([
                'token' => $token,
                'nama'  => urlencode($user->nama_lengkap),
                'email' => $user->email,
                'foto'  => $user->foto_profile,
            ]));
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token Google tidak valid',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
