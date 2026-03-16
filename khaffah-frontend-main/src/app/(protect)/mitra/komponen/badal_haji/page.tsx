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

export type LayananBadalHajiItem = {
  id: number;
  nama_layanan: string;
  slug: string;
  deskripsi: string | null;
  harga: number;
  urutan: number;
};

export default function BadalHajiKomponenPage() {
  const router = useRouter();
  const { data: meData } = useMe();
  const user =
    (meData as { data?: { nama_lengkap?: string; no_handphone?: string } })?.data ??
    meData;
  const defaultName = (user as { nama_lengkap?: string })?.nama_lengkap ?? "";
  const defaultPhone = (user as { no_handphone?: string })?.no_handphone ?? "";

  const [layananList, setLayananList] = useState<LayananBadalHajiItem[]>([]);
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
        const res = await fetch(`${apiBase}/api/master-badal-haji/list`, {
          cache: "no-store",
        });
        if (!res.ok || cancelled) return;
        const json = (await res.json()) as {
          data?: LayananBadalHajiItem[];
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

  const setLayananBadalHajiAndHarga = (slug: string) => {
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
    if (!waktuPemesanan) {
      toast.error("Waktu pemesanan belum terisi.");
      return;
    }
    // Badal Haji boleh total 0 (hubungi untuk harga)
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
        kategoriPaket: "Komponen Badal Haji",
        dibuat_sebagai_mitra: true,
        totalBiayaBadalHaji: totalHarga,
        data_badal_haji: {
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
      toast.success("Permintaan Badal Haji berhasil dikirim.");
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
      <div className="flex items-center gap-3">
        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-khaffah-primary/10">
          <HandHeart className="h-6 w-6 text-khaffah-primary" />
        </div>
        <div>
          <h1 className="text-xl font-semibold text-foreground">Badal Haji</h1>
          <p className="text-sm text-muted-foreground">
            Pelaksanaan ibadah haji atas nama orang lain yang tidak mampu melaksanakannya sendiri (sesuai syariat).
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-6">
          <Card className="rounded-2xl border border-border bg-card shadow-sm">
            <CardHeader className="pb-4">
              <CardTitle className="text-base font-semibold text-foreground">
                Detail Pemesanan
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5">
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

              <div className="space-y-2">
                <Label className="text-sm font-medium text-foreground">
                  Layanan Badal Haji
                </Label>
                <div className="grid gap-2">
                  {layananList.length === 0 && (
                    <p className="text-sm text-muted-foreground py-2">
                      Memuat layanan Badal Haji...
                    </p>
                  )}
                  {layananList.map((item) => (
                    <button
                      key={item.id}
                      type="button"
                      onClick={() => setLayananBadalHajiAndHarga(item.slug)}
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
                    placeholder="Contoh: 5.000.000 atau 0 (hubungi kami)"
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
                disabled={submitting}
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
                Tentang Badal Haji
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4 text-sm leading-6 text-foreground/90">
                <p>
                  <strong>Badal haji</strong> adalah pelaksanaan ibadah haji yang dilakukan oleh seseorang untuk menggantikan atau mewakili orang lain yang secara syariat tidak mampu melaksanakannya sendiri.
                </p>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">1. Dasar Hukum dan Ketentuan</p>
                  <p>
                    Mayoritas ulama (mazhab Hanafi, Syafi&apos;i, dan Hanbali) menyepakati bahwa badal haji adalah boleh dan sah. Hal ini didasarkan pada hadis Rasulullah SAW yang mengizinkan seorang wanita menghajikan ayahnya yang sudah tua renta serta menghajikan ibunya yang telah wafat.
                  </p>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">2. Siapa yang Boleh Dibadalkan?</p>
                  <p>Badal haji diperuntukkan bagi muslim yang memenuhi kriteria berikut:</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li><strong>Telah Wafat:</strong> Meninggal dunia sebelum sempat menunaikan haji atau meninggal saat proses haji berlangsung sebelum wukuf.</li>
                    <li><strong>Sakit Permanen:</strong> Mengalami sakit berat yang tidak ada harapan sembuh menurut medis sehingga tidak memungkinkan melakukan perjalanan.</li>
                    <li><strong>Uzur Jasmani/Usia Lanjut:</strong> Kondisi fisik yang sangat lemah karena usia sehingga tidak mampu duduk tegak di atas kendaraan.</li>
                    <li><strong>Gangguan Jiwa:</strong> Mengalami gangguan jiwa berat atau permanen.</li>
                  </ul>
                </div>

                <div className="space-y-2">
                  <p className="font-semibold text-foreground">3. Syarat bagi Orang yang Membadalkan</p>
                  <ul className="list-disc pl-5 space-y-1">
                    <li><strong>Sudah Berhaji:</strong> Orang yang menghajikan orang lain wajib sudah pernah menunaikan ibadah haji untuk dirinya sendiri sebelumnya.</li>
                    <li><strong>Satu Orang untuk Satu Nama:</strong> Satu petugas badal haji hanya boleh mewakili satu orang dalam satu musim haji.</li>
                    <li><strong>Niat:</strong> Saat memulai ihram, petugas harus berniat secara khusus atas nama orang yang dibadalkan.</li>
                  </ul>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

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
                    {hargaPerPax > 0 ? formatRupiah(hargaPerPax) : "Hubungi kami"}
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
                  {totalHarga > 0 ? formatRupiah(totalHarga) : "Hubungi kami"}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
