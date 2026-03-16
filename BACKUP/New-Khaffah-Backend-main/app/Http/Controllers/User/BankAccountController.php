<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserBankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    /**
     * Daftar rekening bank milik user yang login.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $accounts = UserBankAccount::where('akun_id', $user->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Simpan rekening bank baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:30|regex:/^[0-9]+$/',
            'account_holder_name' => 'required|string|max:150',
        ], [
            'account_number.regex' => 'Nomor rekening hanya boleh berisi angka.',
        ]);

        $user = Auth::user();

        $account = UserBankAccount::create([
            'akun_id' => $user->id,
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'],
            'account_holder_name' => $validated['account_holder_name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rekening berhasil ditambahkan.',
            'data' => $account,
        ], 201);
    }

    /**
     * Hapus rekening (soft: set is_active = false).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $account = UserBankAccount::where('akun_id', $user->id)->find($id);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tidak ditemukan.',
            ], 404);
        }

        $account->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Rekening berhasil dihapus.',
        ]);
    }
}
