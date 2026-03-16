<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Jenssegers\Agent\Agent;
use App\Models\LoginLog;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register user baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"nama_lengkap","jenis_kelamin","tgl_lahir","email","no_handphone","password","role"},
     *                 @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *                 @OA\Property(property="jenis_kelamin", type="string", enum={"laki-laki","perempuan"}, example="laki-laki"),
     *                 @OA\Property(property="tgl_lahir", type="string", format="date", example="2000-01-01"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="no_handphone", type="string", example="08123456789"),
     *                 @OA\Property(property="password", type="string", format="password", example="password123"),
     *                 @OA\Property(property="role", type="string", enum={"user","mitra","superadmin"}, example="user"),
     *                 @OA\Property(property="foto_profile", type="string", format="binary", description="Profile picture (jpg, jpeg, png)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    // Register

    public function register(Request $request)
    {
        try {
            $request->validate([
                'nama_lengkap'  => 'required|string|max:255',
                'jenis_kelamin' => 'required|in:laki-laki,perempuan',
                'tgl_lahir'     => 'required|date',
                'email'         => 'required|string|email|unique:users,email',
                'no_handphone'  => 'required|string|unique:users,no_handphone',
                'password'      => 'required|string|min:6',
                'role'          => 'required|string|in:user,mitra,superadmin',
                'foto_profile'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $path = null;
            if ($request->hasFile('foto_profile')) {
                $path = $request->file('foto_profile')->store('profile', 'public');
            }

            $user = User::create([
                'nama_lengkap'  => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tgl_lahir'     => $request->tgl_lahir,
                'email'         => $request->email,
                'no_handphone'  => $request->no_handphone,
                'password'      => Hash::make($request->password),
                'foto_profile'  => $path,
            ]);

            $user->assignRole($request->role);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'role' => $request->role,
                    'token'          => $token,
                    'token_type'     => 'Bearer',
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Login
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login pengguna",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login success", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Login success"),
     *         @OA\Property(property="user", type="object"),
     *         @OA\Property(property="role", type="array", @OA\Items(type="string")),
     *         @OA\Property(property="token", type="string", example="Bearer <token>")
     *     )),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        $user->tokens()->delete();

        $token = $user->createToken(
            $agent->device() ?: 'unknown-device',
            ['*'],
            now()->addHours(8)
        )->plainTextToken;

        LoginLog::create([
            'user_id'      => $user->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->header('User-Agent'),
            'device'       => $agent->device(),
            'platform'     => $agent->platform(),
            'browser'      => $agent->browser(),
            'logged_in_at' => now(),
        ]);

        $firstSubrole = $user->subroles()->orderBy('id', 'asc')->first();

        return response()->json([
            'message'          => 'Login success',
            'id'               => $user->id,
            'nama_lengkap'     => $user->nama_lengkap,
            'jenis_kelamin'    => $user->jenis_kelamin,
            'tgl_lahir'        => $user->tgl_lahir,
            'email'            => $user->email,
            'no_handphone'     => $user->no_handphone,
            'foto_profile'     => $user->foto_profile ? asset('storage/' . $user->foto_profile) : null,
            'roles'            => $user->getRoleNames(),
            'subroles'         => $user->subroles->pluck('nama_role'),
            'dashboard_url'    => $firstSubrole->url_dashboard ?? null,
            'token'            => $token,
            'token-type'       => 'Bearer ' . $token,
        ]);
    }

    // Check Session (profile)
    /**
     * @OA\Get(
     *     path="/api/me",
     *     tags={"Auth"},
     *     summary="Ambil data user yang sedang login (profile)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User profile", @OA\JsonContent(
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="nama_lengkap", type="string", example="John Doe"),
     *         @OA\Property(property="jenis_kelamin", type="string", example="laki-laki"),
     *         @OA\Property(property="tgl_lahir", type="string", format="date", example="2000-01-01"),
     *         @OA\Property(property="email", type="string", example="john@example.com"),
     *         @OA\Property(property="no_handphone", type="string", example="08123456789"),
     *             @OA\Property(
     *                 property="foto_profile",
     *                 type="string",
     *                 format="uri",
     *                 nullable=true,
     *                 example="http://localhost:8000/storage/profile/foo.jpg",
     *                 description="<img src='http://localhost:8000/storage/profile/foo.jpg' width='120' />"
     *             ),
     *         @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *     )),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function me(Request $request)
    {
        $t0 = microtime(true);
        $user = $request->user();
        // Eager load agar tidak N+1 (roles via Spatie, subroles, mitra+level untuk diskon)
        $user->load(['roles', 'subroles', 'mitra.level']);

        $controllerMs = round((microtime(true) - $t0) * 1000);
        \Illuminate\Support\Facades\Log::channel('single')->info('[Laravel me] controller', ['controllerMs' => $controllerMs]);

        $payload = [
            'id'               => $user->id,
            'nama_lengkap'     => $user->nama_lengkap,
            'jenis_kelamin'    => $user->jenis_kelamin,
            'tgl_lahir'        => $user->tgl_lahir,
            'email'            => $user->email,
            'no_handphone'     => $user->no_handphone,
            'foto_profile'     => $user->foto_profile ? asset('storage/' . $user->foto_profile) : null,
            'roles'            => $user->getRoleNames(),
            'subroles'         => $user->subroles->pluck('nama_role'),
        ];

        // Untuk mitra: sertakan persen diskon dari level (paket custom umrah / request product)
        if ($user->hasRole('mitra') && $user->mitra) {
            $level = $user->mitra->level;
            $payload['persen_potongan_mitra'] = $level ? (float) $level->persen_potongan : 0;
            $payload['nama_level_mitra'] = $level ? $level->nama_level : null;
        }

        return response()->json($payload);
    }

    /**
     * Update data profile user (nama, jenis kelamin, tgl lahir, email, no handphone).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'tgl_lahir'     => 'nullable|date',
            'email'         => ['required', 'string', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'no_handphone'  => ['required', 'string', 'max:20', Rule::unique('users', 'no_handphone')->ignore($user->id)],
        ]);

        $user->update([
            'nama_lengkap'   => $validated['nama_lengkap'],
            'jenis_kelamin'  => $validated['jenis_kelamin'],
            'tgl_lahir'      => $validated['tgl_lahir'] ?? null,
            'email'          => $validated['email'],
            'no_handphone'   => $validated['no_handphone'],
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user'    => [
                'id'           => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'jenis_kelamin' => $user->jenis_kelamin,
                'tgl_lahir'    => $user->tgl_lahir,
                'email'        => $user->email,
                'no_handphone' => $user->no_handphone,
                'foto_profile' => $user->foto_profile ? asset('storage/' . $user->foto_profile) : null,
            ],
        ]);
    }

    /**
     * Upload / update foto profile user (max 1 MB, jpeg/png).
     */
    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'foto_profile' => 'required|image|mimes:jpeg,jpg,png|max:1024',
        ], [
            'foto_profile.required' => 'Pilih file foto terlebih dahulu.',
            'foto_profile.image'   => 'File harus berupa gambar (JPEG/PNG).',
            'foto_profile.mimes'   => 'Format hanya .JPEG, .JPG, atau .PNG.',
            'foto_profile.max'     => 'Ukuran maksimal 1 MB.',
        ]);

        $user = $request->user();

        if ($user->foto_profile && Storage::disk('public')->exists($user->foto_profile)) {
            Storage::disk('public')->delete($user->foto_profile);
        }

        $path = $request->file('foto_profile')->store('profile', 'public');
        $user->update(['foto_profile' => $path]);

        return response()->json([
            'message'      => 'Foto profile berhasil diperbarui.',
            'foto_profile' => asset('storage/' . $path),
        ]);
    }

    // Logout
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout user (hapus token saat ini)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logged out successfully", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Logged out successfully")
     *     )),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        LoginLog::where('user_id', $request->user()->id)
            ->whereNull('logged_out_at')
            ->latest('logged_in_at')
            ->first()
            ?->update([
                'logged_out_at' => now(),
            ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
