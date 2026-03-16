"use client";

import { useState, useMemo, useEffect } from "react";
import { useRouter } from "next/navigation";
import { formatRupiah, formatNumberId } from "@/lib/utils";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useMe } from "@/query/auth";
import { apiInstance } from "@/lib/axios";
import { toast } from "sonner";
import {
  FileCheck2,
  CalendarClock,
  Plane,
  Users,
  Banknote,
  Receipt,
  User,
  Phone,
  Send,
} from "lucide-react";

export type LayananVisaItem = {
  id: number;
  nama_layanan: string;
  slug: string;
  deskripsi: string | null;
  harga: number;
  urutan: number;
};

const VisaKomponenPage = () => {
  const router = useRouter();
  const { data: meData } = useMe();
  const user = (meData as { data?: { nama_lengkap?: string; no_handphone?: string } })?.data ?? meData;
  const defaultName = (user as { nama_lengkap?: string })?.nama_lengkap ?? "";
  const defaultPhone = (user as { no_handphone?: string })?.no_handphone ?? "";

  const [layananList, setLayananList] = useState<LayananVisaItem[]>([]);
  const [layananSlug, setLayananSlug] = useState<string>("");
  const [waktuPemesanan, setWaktuPemesanan] = useState("");
  const [tanggalKeberangkatan, setTanggalKeberangkatan] = useState("");
  const [jumlahVisa, setJumlahVisa] = useState<number>(1);
  const [hargaPerPax, setHargaPerPax] = useState<number>(0);
  const [clientName, setClientName] = useState("");
  const [clientPhone, setClientPhone] = useState("");
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (defaultName && !clientName) setClientName(defaultName);
    if (defaultPhone && !clientPhone) setClientPhone(defaultPhone);
  }, [defaultName, defaultPhone]);

  const apiBase = (process.env.NEXT_PUBLIC_API ?? "").replace(/\/+$/, "");

  useEffect(() => {
    let cancelled = false;
    async function load() {
      try {
        const res = await fetch(`${apiBase}/api/master-visa/list`, {
          cache: "no-store",
        });
        if (!res.ok || cancelled) return;
        const json = (await res.json()) as {
          data?: LayananVisaItem[];
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

  const setLayananVisaAndHarga = (slug: string) => {
    setLayananSlug(slug);
    const item = layananList.find((l) => l.slug === slug);
    if (item?.harga != null && item.harga > 0) setHargaPerPax(item.harga);
  };

  const totalHarga = useMemo(
    () => (jumlahVisa > 0 ? jumlahVisa * hargaPerPax : 0),
    [jumlahVisa, hargaPerPax]
  );

  const handlePesan = async () => {
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
        kategoriPaket: "Komponen Visa",
        dibuat_sebagai_mitra: true,
        totalBiayaVisa: totalHarga,
        data_visa: {
          layanan_nama: selectedLayanan?.nama_layanan ?? "",
          layanan_slug: layananSlug,
          jumlah_visa: jumlahVisa,
          harga_per_pax: hargaPerPax,
          total: totalHarga,
          tanggal_keberangkatan: tanggalKeberangkatan || null,
          waktu_pemesanan: waktuPemesanan || null,
        },
      };
      const res = await apiInstance.post<{
        data?: { transaksi?: { id: number }; kode_transaksi?: string };
      }>("/api/request-products", payload);
      const transaksiId = res.data?.data?.transaksi?.id;
      toast.success("Permintaan pemesanan visa berhasil dikirim.");
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
          <FileCheck2 className="h-6 w-6 text-khaffah-primary" />
        </div>
        <div>
          <h1 className="text-xl font-semibold text-foreground">
            Komponen Visa
          </h1>
          <p className="text-sm text-muted-foreground">
            Isi data pemesanan visa. Pilih layanan dan lengkapi detail perjalanan.
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {/* Form utama */}
        <div className="lg:col-span-2">
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

              {/* Waktu Pemesanan */}
              <div className="space-y-2">
                <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                  <CalendarClock className="h-4 w-4 text-muted-foreground" />
                  Waktu Pemesanan
                </Label>
                <Input
                  type="datetime-local"
                  value={waktuPemesanan}
                  onChange={(e) => setWaktuPemesanan(e.target.value)}
                  className="h-10 rounded-lg border-border bg-muted/30"
                />
              </div>

              {/* Layanan Visa */}
              <div className="space-y-2">
                <Label className="text-sm font-medium text-foreground">
                  Layanan Visa
                </Label>
                <div className="grid gap-2">
                  {layananList.length === 0 && (
                    <p className="text-sm text-muted-foreground py-2">
                      Memuat layanan visa...
                    </p>
                  )}
                  {layananList.map((item) => (
                    <button
                      key={item.id}
                      type="button"
                      onClick={() => setLayananVisaAndHarga(item.slug)}
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

              {/* Tanggal Keberangkatan + Jumlah & Harga dalam grid */}
              <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Plane className="h-4 w-4 text-muted-foreground" />
                    Tanggal Keberangkatan
                  </Label>
                  <Input
                    type="date"
                    value={tanggalKeberangkatan}
                    onChange={(e) =>
                      setTanggalKeberangkatan(e.target.value)
                    }
                    className="h-10 rounded-lg border-border bg-muted/30"
                  />
                </div>
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    Jumlah Visa
                  </Label>
                  <Input
                    type="number"
                    min={1}
                    value={jumlahVisa}
                    onChange={(e) => {
                      const v = e.target.value;
                      if (v === "") setJumlahVisa(1);
                      else
                        setJumlahVisa(
                          Math.max(1, parseInt(v, 10) || 1)
                        );
                    }}
                    className="h-10 rounded-lg border-border bg-muted/30"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                  <Banknote className="h-4 w-4 text-muted-foreground" />
                  Harga Visa / Pax (IDR)
                </Label>
                <Input
                  type="text"
                  inputMode="numeric"
                  value={formatNumberId(hargaPerPax)}
                  onChange={(e) => {
                    const digits = e.target.value.replace(/\D/g, "");
                    const num = digits === "" ? 0 : Math.max(0, parseInt(digits, 10) || 0);
                    setHargaPerPax(num);
                  }}
                  placeholder="Contoh: 2.000.000"
                  className="h-10 rounded-lg border-border bg-muted/30 tabular-nums"
                />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sidebar: Ringkasan Total */}
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
                  <span>Jumlah</span>
                  <span className="text-foreground font-medium">
                    {jumlahVisa} visa
                  </span>
                </div>
                <div className="flex justify-between text-sm text-muted-foreground">
                  <span>Harga / pax</span>
                  <span className="text-foreground font-medium tabular-nums">
                    {formatRupiah(hargaPerPax)}
                  </span>
                </div>
              </div>
              <div className="border-t border-border pt-4">
                <p className="text-xs text-muted-foreground mb-1">
                  Total Harga
                </p>
                <p className="text-2xl font-bold text-khaffah-primary tabular-nums">
                  {formatRupiah(totalHarga)}
                </p>
                <Button
                  type="button"
                  onClick={handlePesan}
                  disabled={submitting || totalHarga <= 0 || layananList.length === 0}
                  className="mt-4 w-full rounded-xl bg-khaffah-primary hover:bg-khaffah-primary/90 text-white font-medium h-11"
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
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default VisaKomponenPage;
