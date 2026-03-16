<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    public const SLUG_TENTANG_KAMI = 'tentang_kami';

    /** Default content for tentang kami (fallback) */
    public static function defaultTentangKami(): array
    {
        return [
            'title' => 'Tentang Kami',
            'subtitle' => 'Kaffah Khadmat Tour',
            'tagline' => 'Biro Perjalanan Haji & Umrah Terpercaya',
            'description' => 'Kami membantu mewujudkan perjalanan umroh impian Anda, tanpa ribet, tanpa pusing. Sudah ratusan klien dari seluruh Indonesia berangkat bersama kami.',
            'description_2' => 'Sebagai biro perjalanan haji dan umrah terpercaya, kami berkomitmen memberikan layanan profesional, aman, dan nyaman bagi jamaah. Dengan pembimbing berpengalaman dan fasilitas terbaik, nikmati pengalaman ibadah yang berkesan.',
            'image_url' => '/assets/about-us.jpg',
            'visi' => 'Menjadi mitra terpercaya bagi keluarga Muslim Indonesia dalam mewujudkan perjalanan ibadah haji dan umrah yang khusyuk, nyaman, dan penuh keberkahan.',
            'misi' => [
                'Memberikan layanan perjalanan ibadah dengan standar kualitas tertinggi.',
                'Mendampingi jamaah dengan pembimbing yang kompeten dan berakhlak.',
                'Menjaga keamanan, kenyamanan, dan kepuasan jamaah sebagai prioritas utama.',
            ],
            'nilai' => [
                ['judul' => 'Amanah', 'deskripsi' => 'Setiap perjalanan kami kelola dengan penuh tanggung jawab dan transparansi.'],
                ['judul' => 'Profesional', 'deskripsi' => 'Tim berpengalaman dan sistem yang teruji untuk layanan terbaik.'],
                ['judul' => 'Nyaman', 'deskripsi' => 'Fasilitas dan pendampingan yang membuat ibadah Anda tenang dan fokus.'],
            ],
            'stat_jamaah' => '500+',
            'stat_label_jamaah' => 'Jamaah Berangkat',
            'stat_tahun' => '5+',
            'stat_label_tahun' => 'Tahun Pengalaman',
        ];
    }

    /**
     * Public API: get content for frontend (no auth).
     */
    public function getTentangKami()
    {
        $row = PageContent::where('slug', self::SLUG_TENTANG_KAMI)->first();
        $content = $row ? $row->content : null;
        if (!$content || !is_array($content)) {
            $content = self::defaultTentangKami();
        } else {
            $content = array_merge(self::defaultTentangKami(), $content);
        }
        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $content,
        ], 200);
    }

    /**
     * Admin: get content for edit form.
     */
    public function getTentangKamiAdmin()
    {
        $row = PageContent::where('slug', self::SLUG_TENTANG_KAMI)->first();
        $content = $row ? $row->content : self::defaultTentangKami();
        if (!is_array($content)) {
            $content = self::defaultTentangKami();
        } else {
            $content = array_merge(self::defaultTentangKami(), $content);
        }
        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $content,
        ], 200);
    }

    /**
     * Admin: update content.
     */
    public function updateTentangKami(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'description_2' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'visi' => 'nullable|string',
            'misi' => 'nullable|array',
            'misi.*' => 'nullable|string',
            'nilai' => 'nullable|array',
            'nilai.*.judul' => 'nullable|string|max:255',
            'nilai.*.deskripsi' => 'nullable|string',
            'stat_jamaah' => 'nullable|string|max:50',
            'stat_label_jamaah' => 'nullable|string|max:100',
            'stat_tahun' => 'nullable|string|max:50',
            'stat_label_tahun' => 'nullable|string|max:100',
        ]);

        $defaults = self::defaultTentangKami();
        $filtered = array_filter($validated, fn($v) => $v !== null && $v !== '');
        $content = array_merge($defaults, $filtered);
        // Pastikan 4 field statistik selalu ada
        $content['stat_jamaah'] = $validated['stat_jamaah'] ?? $defaults['stat_jamaah'];
        $content['stat_label_jamaah'] = $validated['stat_label_jamaah'] ?? $defaults['stat_label_jamaah'];
        $content['stat_tahun'] = $validated['stat_tahun'] ?? $defaults['stat_tahun'];
        $content['stat_label_tahun'] = $validated['stat_label_tahun'] ?? $defaults['stat_label_tahun'];
        $row = PageContent::updateOrCreate(
            ['slug' => self::SLUG_TENTANG_KAMI],
            ['content' => $content]
        );

        return response()->json([
            'status' => true,
            'message' => 'Konten Tentang Kami berhasil disimpan.',
            'data' => $row->content,
        ], 200);
    }

    // --- Syarat dan Ketentuan ---
    public const SLUG_SYARAT_KETENTUAN = 'syarat_ketentuan';

    public static function defaultSyaratKetentuan(): array
    {
        return [
            'title' => 'Syarat dan Ketentuan',
            'content' => "Dengan mengakses dan menggunakan layanan Kaffah Khadmat Tour, Anda setuju untuk mematuhi syarat dan ketentuan berikut.\n\n1. Umum\nLayanan kami diperuntukkan bagi calon jamaah haji dan umrah. Setiap pemesanan tunduk pada ketentuan yang berlaku.\n\n2. Pembayaran\nPembayaran dilakukan sesuai jadwal yang disepakati. Keterlambatan dapat mengakibatkan pembatalan atau penyesuaian.",
        ];
    }

    public function getSyaratKetentuan()
    {
        $row = PageContent::where('slug', self::SLUG_SYARAT_KETENTUAN)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultSyaratKetentuan(), $row->content) : self::defaultSyaratKetentuan();
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function getSyaratKetentuanAdmin()
    {
        $row = PageContent::where('slug', self::SLUG_SYARAT_KETENTUAN)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultSyaratKetentuan(), $row->content) : self::defaultSyaratKetentuan();
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function updateSyaratKetentuan(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);
        $content = array_merge(self::defaultSyaratKetentuan(), array_filter($validated, fn($v) => $v !== null && $v !== ''));
        PageContent::updateOrCreate(['slug' => self::SLUG_SYARAT_KETENTUAN], ['content' => $content]);
        return response()->json(['status' => true, 'message' => 'Syarat dan Ketentuan berhasil disimpan.', 'data' => $content], 200);
    }

    // --- Kebijakan Privasi ---
    public const SLUG_KEBIJAKAN_PRIVASI = 'kebijakan_privasi';

    public static function defaultKebijakanPrivasi(): array
    {
        return [
            'title' => 'Kebijakan Privasi',
            'content' => "Kaffah Khadmat Tour menghormati privasi Anda.\n\n1. Data yang Kami Kumpulkan\nKami mengumpulkan data yang Anda berikan saat mendaftar, memesan paket, atau menghubungi kami, seperti nama, kontak, dan data perjalanan.\n\n2. Penggunaan Data\nData digunakan untuk memproses pemesanan, mengirim informasi perjalanan, dan meningkatkan layanan kami. Kami tidak menjual data pribadi Anda kepada pihak ketiga.\n\n3. Keamanan\nKami menerapkan langkah-langkah keamanan yang wajar untuk melindungi data Anda.",
        ];
    }

    public function getKebijakanPrivasi()
    {
        $row = PageContent::where('slug', self::SLUG_KEBIJAKAN_PRIVASI)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultKebijakanPrivasi(), $row->content) : self::defaultKebijakanPrivasi();
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function getKebijakanPrivasiAdmin()
    {
        $row = PageContent::where('slug', self::SLUG_KEBIJAKAN_PRIVASI)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultKebijakanPrivasi(), $row->content) : self::defaultKebijakanPrivasi();
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function updateKebijakanPrivasi(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);
        $content = array_merge(self::defaultKebijakanPrivasi(), array_filter($validated, fn($v) => $v !== null && $v !== ''));
        PageContent::updateOrCreate(['slug' => self::SLUG_KEBIJAKAN_PRIVASI], ['content' => $content]);
        return response()->json(['status' => true, 'message' => 'Kebijakan Privasi berhasil disimpan.', 'data' => $content], 200);
    }

    // --- FAQ ---
    public const SLUG_FAQ = 'faq';

    public static function defaultFaq(): array
    {
        return [
            'title' => 'Pertanyaan yang Sering Diajukan',
            'subtitle' => 'Temukan jawaban untuk pertanyaan umum seputar layanan haji dan umrah kami.',
            'items' => [
                ['question' => 'Bagaimana cara mendaftar paket umrah?', 'answer' => 'Anda dapat mendaftar melalui website dengan memilih paket yang tersedia, mengisi formulir, dan melakukan pembayaran sesuai tahapan yang kami informasikan.'],
                ['question' => 'Apakah pembayaran bisa dicicil?', 'answer' => 'Ya, kami menyediakan skema pembayaran bertahap. Detail jadwal cicilan akan diberikan setelah Anda memilih paket.'],
                ['question' => 'Dokumen apa saja yang diperlukan?', 'answer' => 'Umumnya paspor yang masih berlaku minimal 6 bulan, foto, dan dokumen pendukung lain sesuai ketentuan yang berlaku. Tim kami akan memandu Anda.'],
            ],
        ];
    }

    public function getFaq()
    {
        $row = PageContent::where('slug', self::SLUG_FAQ)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultFaq(), $row->content) : self::defaultFaq();
        if (empty($content['items']) || !is_array($content['items'])) {
            $content['items'] = self::defaultFaq()['items'];
        }
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function getFaqAdmin()
    {
        $row = PageContent::where('slug', self::SLUG_FAQ)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultFaq(), $row->content) : self::defaultFaq();
        if (empty($content['items']) || !is_array($content['items'])) {
            $content['items'] = self::defaultFaq()['items'];
        }
        return response()->json(['status' => true, 'message' => 'OK', 'data' => $content], 200);
    }

    public function updateFaq(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'items' => 'nullable|array',
            'items.*.question' => 'nullable|string|max:500',
            'items.*.answer' => 'nullable|string',
        ]);
        $defaults = self::defaultFaq();
        $content = array_merge($defaults, array_filter($validated, fn($v) => $v !== null && $v !== ''));
        if (isset($validated['items']) && is_array($validated['items'])) {
            $content['items'] = $validated['items'];
        }
        PageContent::updateOrCreate(['slug' => self::SLUG_FAQ], ['content' => $content]);
        return response()->json(['status' => true, 'message' => 'FAQ berhasil disimpan.', 'data' => $content], 200);
    }

    // --- App Settings (WhatsApp Admin, dll) ---
    public const SLUG_APP_SETTINGS = 'app_settings';

    public static function defaultAppSettings(): array
    {
        return [
            'whatsapp_admin' => '6289677771070',
        ];
    }

    /**
     * Public API: get app settings for frontend (no auth).
     */
    public function getAppSettings()
    {
        $row = PageContent::where('slug', self::SLUG_APP_SETTINGS)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultAppSettings(), $row->content) : self::defaultAppSettings();
        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $content,
        ], 200);
    }

    /**
     * Admin: get app settings for edit form.
     */
    public function getAppSettingsAdmin()
    {
        $row = PageContent::where('slug', self::SLUG_APP_SETTINGS)->first();
        $content = $row && is_array($row->content) ? array_merge(self::defaultAppSettings(), $row->content) : self::defaultAppSettings();
        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $content,
        ], 200);
    }

    /**
     * Admin: update app settings.
     */
    public function updateAppSettings(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_admin' => 'nullable|string|max:20',
        ]);
        $defaults = self::defaultAppSettings();
        $content = array_merge($defaults, array_filter($validated, fn ($v) => $v !== null && $v !== ''));
        // Normalize: hanya angka untuk wa.me (hilangkan +, spasi, strip 0 depan jadi 62)
        if (!empty($content['whatsapp_admin'])) {
            $content['whatsapp_admin'] = preg_replace('/\D/', '', $content['whatsapp_admin']);
            if (str_starts_with($content['whatsapp_admin'], '0')) {
                $content['whatsapp_admin'] = '62' . substr($content['whatsapp_admin'], 1);
            } elseif (!str_starts_with($content['whatsapp_admin'], '62')) {
                $content['whatsapp_admin'] = '62' . $content['whatsapp_admin'];
            }
        }
        PageContent::updateOrCreate(['slug' => self::SLUG_APP_SETTINGS], ['content' => $content]);
        return response()->json([
            'status' => true,
            'message' => 'Pengaturan aplikasi berhasil disimpan.',
            'data' => $content,
        ], 200);
    }
}
