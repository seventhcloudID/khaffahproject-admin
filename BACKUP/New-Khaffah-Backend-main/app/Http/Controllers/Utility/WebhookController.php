<?php

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PembayaranTransaksi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function handleMoota(Request $request)
    {
        Log::info('📥 Incoming Webhook Moota', [
            'headers' => $request->headers->all(),
            'body' => $request->getContent(),
        ]);

        // === Ambil secret dari .env / config ===
        $secret = config('services.moota.secret') ?? env('MOOTA_WEBHOOK_SECRET');

        if (!$secret) {
            Log::error('❌ MOOTA_WEBHOOK_SECRET tidak diset di .env');
            return response()->json(['success' => false, 'message' => 'Server missing secret'], 500);
        }

        // === Ambil header untuk identifikasi ===
        $incomingSignature = $request->header('Signature');
        $incomingToken = $request->header('X-Webhook-Token')
            ?? $request->header('X-Moota-Secret')
            ?? $request->input('token');

        $payloadRaw = $request->getContent();

        // === Verifikasi preferensi baru: Signature ===
        if ($incomingSignature) {
            $computed = hash_hmac('sha256', $payloadRaw, $secret);

            if (!hash_equals($computed, $incomingSignature)) {
                Log::warning('❌ Signature tidak valid', [
                    'incoming' => $incomingSignature,
                    'expected' => $computed,
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }

            Log::info('✅ Signature valid dari Moota');
        } else {
            // fallback: pakai token lama
            if (!$incomingToken || $incomingToken !== $secret) {
                Log::warning('❌ Webhook Moota ditolak: token tidak valid', [
                    'incoming' => $incomingToken,
                    'expected' => $secret,
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid secret token'], 403);
            }

            Log::info('✅ Token lama valid (sandbox mode)');
        }

        // === Parsing payload ===
        $payload = json_decode($payloadRaw, true) ?? $request->all();

        if (isset($payload[0]) && is_array($payload[0])) {
            $data = $payload[0];
        } else {
            $data = $payload;
        }

        // === Ambil data utama ===
        // Use data_get with multiple fallbacks because Moota payloads vary between accounts
        $amountRaw = data_get($data, 'amount') ?? data_get($data, 'amount_mutasi') ?? null;
        $reference = data_get($data, 'mutation_id') ?? data_get($data, 'token') ?? null;

        // bank: try several likely fields in order of usefulness
        // prefer bank_type (e.g. 'bankTransferSandbox') over label which sometimes contains "bank.label..."
        $bank = data_get($data, 'bank.bank_type')
            ?? data_get($data, 'bank.atas_nama')
            ?? data_get($data, 'bank.label')
            ?? data_get($data, 'account.label')
            ?? data_get($data, 'account.username')
            ?? data_get($data, 'bank_name')
            ?? null;

        $description = data_get($data, 'description') ?? data_get($data, 'note') ?? null;

        // sender name: try direct fields, payment_detail contact, contacts (some payloads use 'contacts'), and bank 'atas_nama'
        $namaPengirim = data_get($data, 'account_name')
            ?? data_get($data, 'sender_name')
            ?? data_get($data, 'payment_detail.contact.name')
            ?? data_get($data, 'contacts.name')
            ?? data_get($data, 'bank.atas_nama')
            ?? null;

        // bank pengirim (human readable) - similar fallbacks, prefer bank_type when label contains placeholder
        $bankPengirim = data_get($data, 'bank_name')
            ?? data_get($data, 'bank.bank_type')
            ?? data_get($data, 'bank.label')
            ?? data_get($data, 'account.label')
            ?? null;

        if ($amountRaw === null) {
            Log::warning('❌ Webhook Moota tanpa nominal', $data);
            return response()->json(['success' => false, 'message' => 'Missing amount'], 400);
        }

        // Normalisasi nominal
        $amount = number_format((float) str_replace([',', ' '], ['', ''], $amountRaw), 2, '.', '');

        try {
            // --- [1] Cek idempotensi ---
            if ($reference) {
                $existing = PembayaranTransaksi::where('moota_reference', $reference)->first();
                if ($existing) {
                    Log::info('♻️ Webhook Moota sudah pernah diproses', [
                        'reference' => $reference,
                        'status' => $existing->status,
                        'id' => $existing->id,
                    ]);
                    return response()->json(['success' => true, 'message' => 'Already processed']);
                }
            }

            // --- [2] Cari pembayaran berdasarkan nominal ---
            // Normalize raw amount to several shapes and try tolerant matching because DB might store integer or decimal
            $amountInt = is_numeric($amountRaw) ? (int) $amountRaw : (int) round((float) str_replace([',', ' '], ['', ''], $amountRaw));
            $amountNormalized = number_format((float) str_replace([',', ' '], ['', ''], $amountRaw), 2, '.', '');

            $pembayaran = PembayaranTransaksi::whereIn('status', ['pending', 'matched'])
                ->where(function ($q) use ($amountInt, $amountNormalized) {
                    $q->where('nominal_transfer', $amountInt)
                        ->orWhere('nominal_transfer', $amountNormalized)
                        // try numeric comparison coercing string to number (works on MySQL)
                        ->orWhereRaw('(nominal_transfer+0) = ?', [$amountInt]);
                })
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$pembayaran) {
                Log::info('🔍 Tidak ada pembayaran cocok dengan nominal', ['amount' => $amount]);
                return response()->json(['success' => false, 'message' => 'Tidak ditemukan pembayaran cocok'], 404);
            }

            // --- [3] Update status ---
            // Log values we will write to DB to help debug missing fields
            Log::debug('Webhook Moota - resolved fields before update', [
                'pembayaran_id' => $pembayaran->id ?? null,
                'amount_raw' => $amountRaw,
                'amount_int' => $amountInt ?? null,
                'amount_normalized' => $amountNormalized ?? null,
                'bank_pengirim' => $bankPengirim,
                'nama_pengirim' => $namaPengirim,
                'reference' => $reference,
                'payload_sample' => array_slice((array) $data, 0, 10),
            ]);

            DB::transaction(function () use ($pembayaran, $amount, $bankPengirim, $namaPengirim, $reference) {
                $pembayaran->update([
                    'amount_mutasi' => $amount,
                    'tanggal_transfer' => now(),
                    'bank_pengirim' => $bankPengirim,
                    'nama_pengirim' => $namaPengirim,
                    'moota_reference' => $reference,
                    'status' => 'verified',
                    'verified_by' => 'moota_webhook',
                    'verified_at' => now(),
                ]);
            });

            Log::info('✅ Pembayaran diverifikasi otomatis', [
                'id' => $pembayaran->id,
                'reference' => $reference,
                'amount' => $amount,
            ]);


            // --- [4] Cek status pelunasan transaksi ---
            try {
                $transaksiId = $pembayaran->transaksi_id;

                $totalTerbayar = DB::table('pembayaran_transaksi_m')
                    ->where('transaksi_id', $transaksiId)
                    ->where('status', 'verified')
                    ->sum('nominal_asli');

                $transaksi = DB::table('transaksi_m')
                    ->select('id', 'total_biaya', 'status_transaksi_id')
                    ->where('id', $transaksiId)
                    ->first();

                if ($transaksi && $totalTerbayar >= $transaksi->total_biaya) {
                    DB::table('transaksi_m')
                        ->where('id', $transaksiId)
                        ->update([
                            'status_transaksi_id' => 4,
                            'updated_at' => now(),
                        ]);

                    Log::info('💰 Transaksi dinyatakan lunas & status diperbarui', [
                        'transaksi_id' => $transaksiId,
                        'total_terbayar' => $totalTerbayar,
                        'total_biaya' => $transaksi->total_biaya,
                    ]);
                } else {
                    Log::info('💵 Pembayaran masuk, tapi transaksi belum lunas', [
                        'transaksi_id' => $transaksiId,
                        'total_terbayar' => $totalTerbayar,
                        'total_biaya' => $transaksi->total_biaya ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('🔥 Gagal cek status pelunasan transaksi: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'pembayaran_id' => $pembayaran->id,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Pembayaran diverifikasi & dicek pelunasan']);
        } catch (\Throwable $e) {
            Log::error('🔥 Error webhook Moota: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $payload,
            ]);
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }
}
