<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jamaah;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Concerns\StoresDocuments;
use Illuminate\Support\Facades\DB;
use App\Models\TipeDokumen;
use App\Models\User;

class UsersController extends Controller
{
    public function getUserAktif(Request $request)
    {
        $tglAwal  = $request->input('tglAwal');
        $tglAkhir = $request->input('tglAkhir');

        $query = User::with(['roles' => function ($q) {
            $q->where('guard_name', 'api');
        }])
            ->where('is_active', true)
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'superadmin');
            });

        if ($tglAwal && $tglAkhir) {
            $query->whereBetween('created_at', [
                $tglAwal . ' 00:00:00',
                $tglAkhir . ' 23:59:59'
            ]);
        }

        $items = $query->get();

        $data = $items->map(function (User $user) {
            $roleNames = $user->roles->pluck('name')->values()->all();
            return array_merge($user->toArray(), [
                'roles' => $roleNames,
                'role'  => $roleNames[0] ?? null,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar user aktif',
            'data' => $data,
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        // Jangan izinkan edit superadmin dari sini
        if ($user->hasRole('superadmin')) {
            return response()->json([
                'status' => false,
                'message' => 'User ini tidak dapat diedit.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'no_handphone' => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'tgl_lahir' => 'nullable|date',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:6',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan user lain.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'no_handphone' => $request->no_handphone,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tgl_lahir' => $request->tgl_lahir,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Data user berhasil diperbarui.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Daftar akun admin (hanya user dengan role superadmin) beserta subrole.
     * Untuk halaman Monitoring Operasional - manajemen role: CS, Akuntan, Owner.
     */
    public function getListAdmin(Request $request)
    {
        $query = User::with(['roles' => fn ($q) => $q->where('guard_name', 'api'), 'subroles'])
            ->where('is_active', true)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'superadmin')->where('guard_name', 'api');
            });

        $items = $query->orderBy('nama_lengkap')->get();

        $data = $items->map(function (User $user) {
            $subroles = $user->subroles->pluck('nama_role')->values()->all();
            return array_merge($user->toArray(), [
                'subroles' => $subroles,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Daftar akun admin',
            'data' => $data,
        ]);
    }

    /**
     * Update subrole untuk akun admin (CS = support, Akuntan = akutansi, Owner = admin).
     */
    public function updateSubrole(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        if (! $user->hasRole('superadmin', 'api')) {
            return response()->json([
                'status' => false,
                'message' => 'Hanya akun admin yang dapat diubah subrole-nya.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'subrole' => 'required|string|in:support,akutansi,admin,manajemen',
        ], [
            'subrole.required' => 'Subrole wajib dipilih.',
            'subrole.in'      => 'Subrole tidak valid. Pilih: support (CS), akutansi (Akuntan), admin (Owner), manajemen.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $subroleName = $request->subrole;
        $subrole = DB::table('subrole_m')->where('nama_role', $subroleName)->first();
        if (! $subrole) {
            return response()->json([
                'status' => false,
                'message' => 'Subrole tidak ditemukan di database.',
            ], 422);
        }

        $user->subroles()->sync([$subrole->id]);

        return response()->json([
            'status' => true,
            'message' => 'Role admin berhasil diperbarui.',
            'data' => $user->fresh(['subroles']),
        ]);
    }
}
