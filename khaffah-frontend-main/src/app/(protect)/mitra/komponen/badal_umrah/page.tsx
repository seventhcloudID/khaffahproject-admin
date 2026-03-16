"use client";

import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { formatRupiah, formatNumberId } from "@/lib/utils";
import { useMe } from "@/query/auth";
import { apiInstance } from "@/lib/axios";
import { toast } from "sonner";
import {
  CalendarClock,
  HandHeart,
  Users,
  Banknote,
  Receipt,
  User,
  Phone,
  Send,
} from "lucide-react";

function toDatetimeLocalValue(d: Date) {
  const tzOffsetMs = d.getTimezoneOffset() * 60_000;
  return new Date(d.getTime() - tzOffsetMs).toISOString().slice(0, 16);
}

export type LayananBadalUmrahItem = {
  id: number;
  nama_layanan: string;
  slug: string;
  deskripsi: string | null;
  harga: number;
  urutan: number;
};

const DESKRIPSI_BADAL_UMRAH = `Hadiah Ibadah Terindah untuk Orang Tercinta

Ada orang tua, pasangan, atau keluarga tercinta yang sangat ingin umrah, namun tak lagi mampu berangkat karena wafat, sakit permanen, atau usia lanjut?

Kini Anda tetap bisa menghadiahkan pahala Umrah untuk mereka, dengan cara yang sah secara syariat, amanah, dan penuh tanggung jawab.

Apa Itu Badal Umrah?

Badal Umrah adalah pelaksanaan ibadah Umrah yang dilakukan atas nama orang lain yang:

    Telah wafat
    Mengalami sakit permanen
    Tidak memungkinkan secara fisik untuk berangkat ke Tanah Suci

Ibadah ini diperbolehkan dalam Islam, dan telah dipraktikkan sejak zaman Rasulullah ﷺ, sebagai bentuk bakti, cinta, dan amal jariyah.

Mengapa Badal Umrah Ini Begitu Bernilai?

Karena ini bukan sekadar perjalanan, melainkan:

    Doa yang terus mengalir
    Bukti bakti seorang anak
    Bentuk cinta yang menembus jarak dan waktu
    Amal yang pahalanya terus hidup

    “Ketika seseorang wafat, terputus amalnya kecuali tiga perkara…”
    (HR. Muslim)

Badal Umrah adalah salah satu jalan untuk menghadirkan pahala itu kembali.

Dilaksanakan Oleh Pelaksana Amanah & Berpengalaman

Badal Umrah dilakukan oleh:

    Muslim yang sudah menunaikan Umrah untuk dirinya sendiri
    Memahami manasik dengan benar
    Berniat khusus atas nama orang yang dibadalkan
    Melaksanakan seluruh rukun dan wajib Umrah secara sempurna

Setiap prosesi dijalankan dengan penuh kekhusyukan, bukan terburu-buru, bukan sekadar formalitas.

Bukti & Transparansi

Kami memahami: ini ibadah, bukan hal sepele.

Karena itu Anda akan mendapatkan:

    📷 Dokumentasi foto saat niat Badal Umrah
    🕋 Dokumentasi di Masjidil Haram
    📜 Laporan pelaksanaan ibadah
    🕊️ Doa khusus atas nama yang dibadalkan

Semua dilakukan dengan adab dan rasa tanggung jawab kepada Allah.

Cocok Untuk:

    Orang tua tercinta yang telah wafat
    Ayah/Ibu yang sakit menahun
    Kakek/Nenek yang tak mampu lagi berjalan
    Hadiah ibadah untuk keluarga tersayang
    Menunaikan nazar yang belum sempat dilakukan

Bukan Tentang Harga, Tapi Tentang Amanah

Badal Umrah bukan soal murah atau mahal.
Ini soal siapa yang Anda percayai membawa nama orang yang Anda cintai ke hadapan Allah.

Kami menjaga amanah ini seperti amanah keluarga sendiri.

Niat Anda Mulia, Jangan Ditunda

Jika Allah telah menggerakkan hati Anda hari ini, bisa jadi ini adalah panggilan kebaikan.`;

export default function BadalUmrahKomponenPage() {
  const router = useRouter();
  const { data: meData } = useMe();
  const user =
    (meData as { data?: { nama_lengkap?: string; no_handphone?: string } })?.data ??
    meData;
  const defaultName = (user as { nama_lengkap?: string })?.nama_lengkap ?? "";
  const defaultPhone = (user as { no_handphone?: string })?.no_handphone ?? "";

  const [layananList, setLayananList] = useState<LayananBadalUmrahItem[]>([]);
  const [layananSlug, setLayananSlug] = useState<string>("");
  const [waktuPemesanan, setWaktuPemesanan] = useState("");
  const [hargaPerPax, setHargaPerPax] = useState<number>(0);
  const [jumlahJamaah, setJumlahJamaah] = useState<number>(1);
  const [clientName, setClientName] = useState("");
  const [clientPhone, setClientPhone] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    setWaktuPemesanan(toDatetimeLocalValue(new Date()));
  }, []);

  useEffect(() => {
    if (defaultName && !clientName) setClientName(defaultName);
    if (defaultPhone && !clientPhone) setClientPhone(defaultPhone);
  }, [defaultName, defaultPhone]);

  const apiBase = (process.env.NEXT_PUBLIC_API ?? "").replace(/\/+$/, "");

  useEffect(() => {
    let cancelled = false;
    async function load() {
      try {
        const res = await fetch(`${apiBase}/api/master-badal-umrah/list`, {
          cache: "no-store",
        });
        if (!res.ok || cancelled) return;
        const json = (await res.json()) as {
          data?: LayananBadalUmrahItem[];
        };
        const data = json?.data ?? [];
        if (!Array.isArray(data) || data.length === 0) return;
        setLayananList(data);
        if (!layananSlug) {
          const first = data[0];
          setLayananSlug(first.slug);
          if (first.harga > 0) setHargaPerPax(first.harga);
        }
      } catch {
        // ignore
      }
    }
    if (!apiBase) return;
    load();
    return () => {
      cancelled = true;
    };
  }, [apiBase]);

  const selectedLayanan = useMemo(
    () => layananList.find((l) => l.slug === layananSlug) ?? null,
    [layananList, layananSlug]
  );

  useEffect(() => {
    if (selectedLayanan?.harga != null && selectedLayanan.harga > 0) {
      setHargaPerPax((prev) => (prev === 0 ? selectedLayanan.harga : prev));
    }
  }, [selectedLayanan?.slug, selectedLayanan?.harga]);

  const setLayananBadalUmrahAndHarga = (slug: string) => {
    setLayananSlug(slug);
    const item = layananList.find((l) => l.slug === slug);
    if (item?.harga != null && item.harga > 0) setHargaPerPax(item.harga);
  };

  const totalHarga = useMemo(() => {
    const j = Number.isFinite(jumlahJamaah) ? jumlahJamaah : 0;
    const h = Number.isFinite(hargaPerPax) ? hargaPerPax : 0;
    return Math.max(0, j) * Math.max(0, h);
  }, [jumlahJamaah, hargaPerPax]);

  const handleKirimPermintaan = async () => {
    if (!clientName.trim()) {
      toast.error("Nama pemesan wajib diisi.");
      return;
    }
    if (!clientPhone.trim()) {
      toast.error("No. WhatsApp wajib diisi.");
      return;
    }
    if (totalHarga <= 0) {
      toast.error("Total harga harus lebih dari 0.");
      return;
    }
    if (!waktuPemesanan) {
      toast.error("Waktu pemesanan belum terisi.");
      return;
    }

    setSubmitting(true);
    try {
      const payload = {
        client: {
          fullName: clientName.trim(),
          phoneNumber: clientPhone.trim(),
          state: "",
          stateName: "",
          city: "",
          cityName: "",
          suburb: "",
          suburbName: "",
          address: "",
          email: "",
          nik: "",
          gender: "male" as const,
        },
        clients: [],
        tipePaket: "la_custom",
        kategoriPaket: "Komponen Badal Umrah",
        dibuat_sebagai_mitra: true,
        totalBiayaBadalUmrah: totalHarga,
        data_badal_umrah: {
          layanan_nama: selectedLayanan?.nama_layanan ?? "",
          layanan_slug: layananSlug,
          waktu_pemesanan: waktuPemesanan,
          harga_per_pax: hargaPerPax,
          jumlah_jamaah: jumlahJamaah,
          total: totalHarga,
        },
      };

      const res = await apiInstance.post<{
        data?: { transaksi?: { id: number }; kode_transaksi?: string };
      }>("/api/request-products", payload);
      const transaksiId = res.data?.data?.transaksi?.id;
      toast.success("Permintaan Badal Umrah berhasil dikirim.");
      if (transaksiId) {
        router.push(`/mitra/pesanan/${transaksiId}/pay`);
      } else {
        router.push("/mitra/pesanan");
      }
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data
          ?.message ?? "Gagal mengirim permintaan.";
      toast.error(msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-3">
        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-khaffah-primary/10">
          <HandHeart className="h-6 w-6 text-khaffah-primary" />
        </div>
        <div>
          <h1 className="text-xl font-semibold text-foreground">Badal Umrah</h1>
          <p className="text-sm text-muted-foreground">
            Isi data pemesanan Badal Umrah dan lihat ringkasan biayanya.
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Form */}
        <div className="lg:col-span-2 space-y-6">
          <Card className="rounded-2xl border border-border bg-card shadow-sm">
            <CardHeader className="pb-4">
              <CardTitle className="text-base font-semibold text-foreground">
                Detail Pemesanan
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5">
              {/* Data Pemesan */}
              <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <User className="h-4 w-4 text-muted-foreground" />
                    Nama Pemesan <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    type="text"
                    value={clientName}
                    onChange={(e) => setClientName(e.target.value)}
                    placeholder="Nama lengkap pemesan"
                    className="h-10 rounded-lg border-border bg-muted/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Phone className="h-4 w-4 text-muted-foreground" />
                    No. WhatsApp <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    type="text"
                    value={clientPhone}
                    onChange={(e) => setClientPhone(e.target.value)}
                    placeholder="08xxxxxxxxxx"
                    className="h-10 rounded-lg border-border bg-muted/30"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                  <CalendarClock className="h-4 w-4 text-muted-foreground" />
                  Waktu Pemesanan (otomatis)
                </Label>
                <Input
                  type="datetime-local"
                  value={waktuPemesanan}
                  readOnly
                  className="h-10 rounded-lg border-border bg-muted/30"
                />
              </div>

              {/* Layanan Badal Umrah */}
              <div className="space-y-2">
                <Label className="text-sm font-medium text-foreground">
                  Layanan Badal Umrah
                </Label>
                <div className="grid gap-2">
                  {layananList.length === 0 && (
                    <p className="text-sm text-muted-foreground py-2">
                      Memuat layanan Badal Umrah...
                    </p>
                  )}
                  {layananList.map((item) => (
                    <button
                      key={item.id}
                      type="button"
                      onClick={() => setLayananBadalUmrahAndHarga(item.slug)}
                      className={`flex items-start gap-3 rounded-xl border-2 p-4 text-left transition-all ${
                        layananSlug === item.slug
                          ? "border-khaffah-primary bg-khaffah-primary/5"
                          : "border-border bg-muted/20 hover:border-khaffah-primary/50 hover:bg-muted/40"
                      }`}
                    >
                      <span
                        className={`mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2 ${
                          layananSlug === item.slug
                            ? "border-khaffah-primary bg-khaffah-primary"
                            : "border-muted-foreground"
                        }`}
                      >
                        {layananSlug === item.slug && (
                          <span className="h-1.5 w-1.5 rounded-full bg-white" />
                        )}
                      </span>
                      <div>
                        <p className="font-medium text-foreground">
                          {item.nama_layanan}
                        </p>
                        {item.deskripsi && (
                          <p className="text-xs text-muted-foreground mt-0.5">
                            {item.deskripsi}
                          </p>
                        )}
                      </div>
                    </button>
                  ))}
                </div>
              </div>

              <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Banknote className="h-4 w-4 text-muted-foreground" />
                    Harga Layanan / Pax (IDR)
                  </Label>
                  <Input
                    type="text"
                    inputMode="numeric"
                    value={formatNumberId(hargaPerPax)}
                    onChange={(e) => {
                      const digits = e.target.value.replace(/\D/g, "");
                      const num =
                        digits === "" ? 0 : Math.max(0, parseInt(digits, 10) || 0);
                      setHargaPerPax(num);
                    }}
                    placeholder="Contoh: 5.000.000"
                    className="h-10 rounded-lg border-border bg-muted/30 tabular-nums"
                  />
                </div>

                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    Jumlah Jamaah
                  </Label>
                  <Input
                    type="number"
                    min={1}
                    value={jumlahJamaah}
                    onChange={(e) => {
                      const v = e.target.value;
                      if (v === "") setJumlahJamaah(1);
                      else setJumlahJamaah(Math.max(1, parseInt(v, 10) || 1));
                    }}
                    className="h-10 rounded-lg border-border bg-muted/30 tabular-nums"
                  />
                </div>
              </div>

              <Button
                type="button"
                onClick={handleKirimPermintaan}
                disabled={submitting || totalHarga <= 0}
                className="w-full rounded-xl bg-khaffah-primary hover:bg-khaffah-primary/90 text-white font-medium h-11"
              >
                {submitting ? (
                  "Mengirim..."
                ) : (
                  <>
                    <Send className="h-4 w-4 mr-2 inline" />
                    Kirim Permintaan Pemesanan
                  </>
                )}
              </Button>
            </CardContent>
          </Card>

          <Card className="rounded-2xl border border-border bg-card shadow-sm">
            <CardHeader className="pb-3">
              <CardTitle className="text-base font-semibold text-foreground">
                Deskripsi Layanan
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4 text-sm leading-6 text-foreground/90">
                <div className="space-y-2">
                  <p className="text-base font-semibold text-foreground">
                    Hadiah Ibadah Terindah untuk Orang Tercinta
                  </p>
                  <p>
                    Ada orang tua, pasangan, atau keluarga tercinta yang sangat ingin umrah,
                    namun tak lagi mampu berangkat karena wafat, sakit permanen, atau usia
                    lanjut?
                  </p>
                  <p>
                    Kini Anda tetap bisa menghadiahkan pahala Umrah untuk mereka, dengan cara
                    yang sah secara syariat, amanah, dan penuh tanggung jawab.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">Apa Itu Badal Umrah?</p>
                  <p>
                    Badal Umrah adalah pelaksanaan ibadah Umrah yang dilakukan atas nama orang
                    lain yang:
                  </p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>Telah wafat</li>
                    <li>Mengalami sakit permanen</li>
                    <li>Tidak memungkinkan secara fisik untuk berangkat ke Tanah Suci</li>
                  </ul>
                  <p>
                    Ibadah ini diperbolehkan dalam Islam, dan telah dipraktikkan sejak zaman
                    Rasulullah ﷺ, sebagai bentuk bakti, cinta, dan amal jariyah.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">
                    Mengapa Badal Umrah Ini Begitu Bernilai?
                  </p>
                  <p>Karena ini bukan sekadar perjalanan, melainkan:</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>Doa yang terus mengalir</li>
                    <li>Bukti bakti seorang anak</li>
                    <li>Bentuk cinta yang menembus jarak dan waktu</li>
                    <li>Amal yang pahalanya terus hidup</li>
                  </ul>
                  <div className="rounded-xl border border-border bg-muted/20 p-4">
                    <p className="text-foreground/90 italic">
                      “Ketika seseorang wafat, terputus amalnya kecuali tiga perkara…”
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">(HR. Muslim)</p>
                  </div>
                  <p>
                    Badal Umrah adalah salah satu jalan untuk menghadirkan pahala itu kembali.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">
                    Dilaksanakan Oleh Pelaksana Amanah &amp; Berpengalaman
                  </p>
                  <p>Badal Umrah dilakukan oleh:</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>Muslim yang sudah menunaikan Umrah untuk dirinya sendiri</li>
                    <li>Memahami manasik dengan benar</li>
                    <li>Berniat khusus atas nama orang yang dibadalkan</li>
                    <li>Melaksanakan seluruh rukun dan wajib Umrah secara sempurna</li>
                  </ul>
                  <p>
                    Setiap prosesi dijalankan dengan penuh kekhusyukan, bukan terburu-buru,
                    bukan sekadar formalitas.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">Bukti &amp; Transparansi</p>
                  <p>Kami memahami: ini ibadah, bukan hal sepele.</p>
                  <p>Karena itu Anda akan mendapatkan:</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>Dokumentasi foto saat niat Badal Umrah</li>
                    <li>Dokumentasi di Masjidil Haram</li>
                    <li>Laporan pelaksanaan ibadah</li>
                    <li>Doa khusus atas nama yang dibadalkan</li>
                  </ul>
                  <p>
                    Semua dilakukan dengan adab dan rasa tanggung jawab kepada Allah.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">Cocok Untuk:</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li>Orang tua tercinta yang telah wafat</li>
                    <li>Ayah/Ibu yang sakit menahun</li>
                    <li>Kakek/Nenek yang tak mampu lagi berjalan</li>
                    <li>Hadiah ibadah untuk keluarga tersayang</li>
                    <li>Menunaikan nazar yang belum sempat dilakukan</li>
                  </ul>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">
                    Bukan Tentang Harga, Tapi Tentang Amanah
                  </p>
                  <p>
                    Badal Umrah bukan soal murah atau mahal. Ini soal siapa yang Anda percayai
                    membawa nama orang yang Anda cintai ke hadapan Allah.
                  </p>
                  <p>
                    Kami menjaga amanah ini seperti amanah keluarga sendiri.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">
                    Niat Anda Mulia, Jangan Ditunda
                  </p>
                  <p>
                    Jika Allah telah menggerakkan hati Anda hari ini, bisa jadi ini adalah
                    panggilan kebaikan.
                  </p>
                </div>

                {/* keep constant referenced (avoid unused export changes) */}
                <span className="hidden">{DESKRIPSI_BADAL_UMRAH}</span>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sidebar */}
        <div className="lg:col-span-1">
          <Card className="sticky top-4 rounded-2xl border border-border bg-card shadow-sm">
            <CardHeader className="pb-2">
              <CardTitle className="flex items-center gap-2 text-base font-semibold text-foreground">
                <Receipt className="h-4 w-4 text-khaffah-primary" />
                Ringkasan
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="rounded-xl bg-muted/30 p-4 space-y-2">
                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>Layanan</span>
                  <span className="text-foreground font-medium">
                    {selectedLayanan?.nama_layanan ?? "—"}
                  </span>
                </div>
                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>Harga / pax</span>
                  <span className="text-foreground font-medium tabular-nums">
                    {formatRupiah(hargaPerPax)}
                  </span>
                </div>
                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>Jumlah jamaah</span>
                  <span className="text-foreground font-medium tabular-nums">
                    {jumlahJamaah}
                  </span>
                </div>
              </div>

              <div className="border-t border-border pt-4">
                <p className="text-xs text-muted-foreground mb-1">Total Harga</p>
                <p className="text-2xl font-bold text-khaffah-primary tabular-nums">
                  {formatRupiah(totalHarga)}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

