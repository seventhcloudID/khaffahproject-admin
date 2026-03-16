"use client";

import { use, useEffect, useMemo, useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import { formatRupiah, formatNumberId } from "@/lib/utils";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { useMe } from "@/query/auth";
import { useGetJamaahList } from "@/query/jamaah";
import { apiInstance } from "@/lib/axios";
import { toast } from "sonner";
import {
  Plane,
  CalendarClock,
  Calendar,
  Users,
  Banknote,
  Receipt,
  User,
  Phone,
  Send,
  MapPin,
  Clock,
  Tag,
  MapPinned,
} from "lucide-react";

export type MaskapaiItem = {
  id: string;
  name: string;
  logo?: string;
  description?: string;
  kode_iata?: string | null;
  negara_asal?: string | null;
  jam_keberangkatan?: string | null;
  jam_sampai?: string | null;
  kelas_penerbangan?: string | null;
  harga_per_orang: number | null;
};

function toDatetimeLocalValue(d: Date) {
  const tzOffsetMs = d.getTimezoneOffset() * 60_000;
  return new Date(d.getTime() - tzOffsetMs).toISOString().slice(0, 16);
}

const TiketKomponenPage = () => {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { data: meData } = useMe();
  const user =
    (meData as { data?: { nama_lengkap?: string; no_handphone?: string } })?.data ??
    meData;
  const defaultName = (user as { nama_lengkap?: string })?.nama_lengkap ?? "";
  const defaultPhone = (user as { no_handphone?: string })?.no_handphone ?? "";

  const [maskapaiList, setMaskapaiList] = useState<MaskapaiItem[]>([]);
  const [maskapaiLoading, setMaskapaiLoading] = useState(true);
  const [selectedMaskapaiId, setSelectedMaskapaiId] = useState("");
  const [waktuPemesanan, setWaktuPemesanan] = useState("");
  const [arahPenerbangan, setArahPenerbangan] = useState<"one_way" | "round_trip">("round_trip");
  const [bandaraAsalList, setBandaraAsalList] = useState<{ value: string; label: string }[]>([]);
  const [bandaraTujuanList, setBandaraTujuanList] = useState<{ value: string; label: string }[]>([]);
  const [bandaraAsal, setBandaraAsal] = useState("");
  const [bandaraTujuan, setBandaraTujuan] = useState("");
  const [tanggalKeberangkatan, setTanggalKeberangkatan] = useState("");
  const [tanggalKepulangan, setTanggalKepulangan] = useState("");
  const [jumlahPenumpang, setJumlahPenumpang] = useState<number>(1);
  const [hargaPerPax, setHargaPerPax] = useState<number>(0);
  const [clientName, setClientName] = useState("");
  const [clientPhone, setClientPhone] = useState("");
  const [selectedJamaahIds, setSelectedJamaahIds] = useState<string[]>([]);
  const [jamaahSearch, setJamaahSearch] = useState("");
  const [jamaahDisplayLimit, setJamaahDisplayLimit] = useState(15);
  const [submitting, setSubmitting] = useState(false);

  const apiBase = (process.env.NEXT_PUBLIC_API ?? "").replace(/\/+$/, "");
  // Satu entri per id untuk dropdown (hindari double di UI)
  const uniqueMaskapaiList = useMemo(() => {
    const seen = new Set<string>();
    return maskapaiList.filter((m) => {
      const id = String(m.id);
      if (seen.has(id)) return false;
      seen.add(id);
      return true;
    });
  }, [maskapaiList]);
  const selectedMaskapai = useMemo(
    () => uniqueMaskapaiList.find((m) => String(m.id) === String(selectedMaskapaiId)) ?? null,
    [uniqueMaskapaiList, selectedMaskapaiId]
  );

  const { data: jamaahData } = useGetJamaahList();
  const jamaahList = Array.isArray(jamaahData?.data) ? jamaahData.data : [];
  const filteredJamaahList = useMemo(() => {
    const q = jamaahSearch.trim().toLowerCase();
    if (!q) return jamaahList;
    return jamaahList.filter((j) => {
      const nama = ((j as { nama_lengkap?: string }).nama_lengkap ?? "").toLowerCase();
      return nama.includes(q);
    });
  }, [jamaahList, jamaahSearch]);
  const jamaahListToShow = useMemo(
    () => filteredJamaahList.slice(0, jamaahDisplayLimit),
    [filteredJamaahList, jamaahDisplayLimit]
  );
  const hasMoreJamaah = filteredJamaahList.length > jamaahDisplayLimit;
  const LOAD_MORE_JAMAAH = 15;

  const toggleJamaah = (jamaahId: string) => {
    setSelectedJamaahIds((prev) =>
      prev.includes(jamaahId)
        ? prev.filter((id) => id !== jamaahId)
        : [...prev, jamaahId]
    );
  };

  useEffect(() => {
    setWaktuPemesanan(toDatetimeLocalValue(new Date()));
  }, []);

  useEffect(() => {
    const fromUrl = searchParams.get("maskapai");
    if (fromUrl && maskapaiList.some((m) => m.id === fromUrl)) {
      setSelectedMaskapaiId(fromUrl);
    }
  }, [searchParams, maskapaiList]);

  useEffect(() => {
    let cancelled = false;
    async function load() {
      setMaskapaiLoading(true);
      try {
        const res = await fetch(`${apiBase}/api/master-maskapai/list`, { cache: "no-store" });
        if (!res.ok) throw new Error("HTTP " + res.status);
        const json = (await res.json()) as { data?: MaskapaiItem[] };
        const raw = Array.isArray(json?.data) ? json.data : [];
        // Pastikan satu entri per id (API/database kadang return double)
        const byId = new Map<string, MaskapaiItem>();
        raw.forEach((m) => {
          const id = String(m.id);
          if (!byId.has(id)) byId.set(id, m);
        });
        const list = Array.from(byId.values());
        if (!cancelled) {
          setMaskapaiList(list);
          const fromUrl = searchParams.get("maskapai");
          if (fromUrl && byId.has(String(fromUrl))) {
            setSelectedMaskapaiId(fromUrl);
            const m = byId.get(String(fromUrl));
            if (m?.harga_per_orang != null && m.harga_per_orang > 0) {
              setHargaPerPax(m.harga_per_orang);
            }
          }
        }
      } catch {
        if (!cancelled) setMaskapaiList([]);
      } finally {
        if (!cancelled) setMaskapaiLoading(false);
      }
    }
    if (apiBase) load();
    return () => { cancelled = true; };
  }, [apiBase]);

  useEffect(() => {
    let cancelled = false;
    const opts = { value: "", label: "" };
    type BandaraOpt = typeof opts;
    Promise.all([
      apiInstance.get<{ data?: BandaraOpt[] }>("/api/utility/bandara"),
      apiInstance.get<{ data?: BandaraOpt[] }>("/api/utility/bandara-tujuan"),
    ])
      .then(([r1, r2]) => {
        if (cancelled) return;
        const raw1 = (r1 as { data?: { data?: BandaraOpt[] } }).data?.data;
        const raw2 = (r2 as { data?: { data?: BandaraOpt[] } }).data?.data;
        setBandaraAsalList(Array.isArray(raw1) ? raw1 : []);
        setBandaraTujuanList(Array.isArray(raw2) ? raw2 : []);
      })
      .catch(() => {
        if (!cancelled) {
          setBandaraAsalList([]);
          setBandaraTujuanList([]);
        }
      });
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    if (selectedMaskapai?.harga_per_orang != null && selectedMaskapai.harga_per_orang > 0 && hargaPerPax === 0) {
      setHargaPerPax(selectedMaskapai.harga_per_orang);
    }
  }, [selectedMaskapai]);

  useEffect(() => {
    if (defaultName && !clientName) setClientName(defaultName);
    if (defaultPhone && !clientPhone) setClientPhone(defaultPhone);
  }, [defaultName, defaultPhone]);

  const totalHarga = useMemo(
    () => (jumlahPenumpang > 0 && hargaPerPax > 0 ? jumlahPenumpang * hargaPerPax : 0),
    [jumlahPenumpang, hargaPerPax]
  );

  const handlePesan = async () => {
    if (!selectedMaskapai) {
      toast.error("Pilih maskapai terlebih dahulu.");
      return;
    }
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
      const clientsPayload = selectedJamaahIds.map((jid) => {
        const j = jamaahList.find((x) => String(x.id) === String(jid));
        const row = j as { nama_lengkap?: string; nik?: string; no_paspor?: string; tanggal_lahir?: string } | undefined;
        return {
          fullName: row?.nama_lengkap ?? "Jemaah",
          id: jid,
          nik: row?.nik ?? undefined,
          no_paspor: row?.no_paspor ?? undefined,
          tanggal_lahir: row?.tanggal_lahir ?? undefined,
        };
      });

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
        clients: clientsPayload,
        tipePaket: "la_custom",
        kategoriPaket: "Komponen Tiket Pesawat",
        dibuat_sebagai_mitra: true,
        totalBiayaTiketPesawat: totalHarga,
        data_tiket_pesawat: {
          maskapai_id: selectedMaskapai.id,
          maskapai_nama: selectedMaskapai.name,
          arah_penerbangan: arahPenerbangan,
          bandara_asal: bandaraAsal || null,
          bandara_tujuan: bandaraTujuan || null,
          bandara_asal_label: bandaraAsal ? bandaraAsalList.find((b) => b.value === bandaraAsal)?.label ?? null : null,
          bandara_tujuan_label: bandaraTujuan ? bandaraTujuanList.find((b) => b.value === bandaraTujuan)?.label ?? null : null,
          waktu_pemesanan: waktuPemesanan,
          tanggal_keberangkatan: tanggalKeberangkatan || null,
          tanggal_kepulangan: arahPenerbangan === "round_trip" ? (tanggalKepulangan || null) : null,
          harga_per_pax: hargaPerPax,
          jumlah_penumpang: jumlahPenumpang,
          total: totalHarga,
        },
      };

      const res = await apiInstance.post<{
        data?: { transaksi?: { id: number }; kode_transaksi?: string };
      }>("/api/request-products", payload);
      const transaksiId = res.data?.data?.transaksi?.id;
      toast.success("Permintaan tiket pesawat berhasil dikirim.");
      if (transaksiId) {
        router.push(`/mitra/pesanan/${transaksiId}/pay`);
      } else {
        router.push("/mitra/pesanan");
      }
    } catch (err: unknown) {
      const msg =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ??
        "Gagal mengirim permintaan.";
      toast.error(msg);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="mb-2">
        <p className="text-13 font-medium text-black">Pemesanan Tiket Pesawat</p>
        <p className="text-12 text-black/60">
          Isi form dan pilih maskapai. Data bandara dari master keberangkatan &amp; kepulangan.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <Card className="rounded-2xl border border-border bg-card shadow-sm">
            <CardHeader className="pb-4">
              <div>
                <CardTitle className="text-base font-semibold text-foreground mb-2">
                  Pilih Maskapai
                </CardTitle>
                <Select
                  value={selectedMaskapaiId}
                  onValueChange={(v) => {
                    setSelectedMaskapaiId(v);
                    const m = maskapaiList.find((x) => x.id === v);
                    if (m?.harga_per_orang != null && m.harga_per_orang > 0) {
                      setHargaPerPax(m.harga_per_orang);
                    }
                  }}
                >
                  <SelectTrigger className="w-full h-11 rounded-lg border-border bg-muted/30">
                    <SelectValue placeholder={maskapaiLoading ? "Memuat maskapai..." : "Pilih maskapai"} />
                  </SelectTrigger>
                  <SelectContent>
                    {uniqueMaskapaiList.map((m) => (
                      <SelectItem key={String(m.id)} value={String(m.id)}>
                        <span className="font-medium">{m.name}</span>
                        {m.kode_iata && (
                          <span className="text-muted-foreground ml-2">({m.kode_iata})</span>
                        )}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </CardHeader>
            {selectedMaskapai && (
              <div className="px-6 pb-4">
                <div className="rounded-xl border border-border bg-emerald-50/50 overflow-hidden">
                  <div className="bg-emerald-100 border-b border-emerald-200 px-4 py-2">
                    <span className="text-11 font-semibold text-emerald-800">
                      Maskapai terpilih
                    </span>
                  </div>
                  <div className="p-4">
                    <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                      <div className="flex-1 min-w-0 space-y-2">
                        <div className="flex items-center gap-2 flex-wrap">
                          <Plane className="h-5 w-5 text-emerald-600 shrink-0" />
                          <h3 className="text-15 font-bold text-foreground">{selectedMaskapai.name}</h3>
                          {selectedMaskapai.kode_iata && (
                            <span className="rounded-md bg-emerald-100 px-2 py-0.5 text-11 font-medium text-emerald-800">
                              {selectedMaskapai.kode_iata}
                            </span>
                          )}
                        </div>
                        {selectedMaskapai.description && (
                          <p className="text-12 text-muted-foreground">{selectedMaskapai.description}</p>
                        )}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 text-12 text-muted-foreground">
                          {selectedMaskapai.negara_asal != null && selectedMaskapai.negara_asal !== "" && (
                            <span className="flex items-center gap-1.5">
                              <MapPin className="h-3.5 w-3.5 shrink-0" />
                              {selectedMaskapai.negara_asal}
                            </span>
                          )}
                          {selectedMaskapai.kelas_penerbangan != null && selectedMaskapai.kelas_penerbangan !== "" && (
                            <span className="flex items-center gap-1.5">
                              <Tag className="h-3.5 w-3.5 shrink-0" />
                              {selectedMaskapai.kelas_penerbangan}
                            </span>
                          )}
                          {selectedMaskapai.jam_keberangkatan != null && selectedMaskapai.jam_keberangkatan !== "" && (
                            <span className="flex items-center gap-1.5">
                              <Clock className="h-3.5 w-3.5 shrink-0" />
                              Berangkat: {selectedMaskapai.jam_keberangkatan}
                            </span>
                          )}
                          {selectedMaskapai.jam_sampai != null && selectedMaskapai.jam_sampai !== "" && (
                            <span className="flex items-center gap-1.5">
                              <Clock className="h-3.5 w-3.5 shrink-0" />
                              Sampai: {selectedMaskapai.jam_sampai}
                            </span>
                          )}
                        </div>
                      </div>
                      <div className="shrink-0 border-t sm:border-t-0 sm:border-l border-emerald-200 pt-3 sm:pt-0 sm:pl-4 flex flex-col items-start sm:items-end gap-1">
                        {selectedMaskapai.harga_per_orang != null && selectedMaskapai.harga_per_orang > 0 ? (
                          <>
                            <p className="text-lg font-bold text-khaffah-primary tabular-nums">
                              Rp {formatRupiah(selectedMaskapai.harga_per_orang)}
                            </p>
                            <p className="text-11 text-muted-foreground">/ pax</p>
                          </>
                        ) : (
                          <p className="text-12 text-muted-foreground font-medium">Hubungi untuk harga</p>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}
            <CardContent className="space-y-5">
              <div className="space-y-2">
                <Label className="text-sm font-medium text-foreground">Arah Penerbangan</Label>
                <RadioGroup
                  value={arahPenerbangan}
                  onValueChange={(v) => {
                    setArahPenerbangan(v as "one_way" | "round_trip");
                    if (v === "one_way") setTanggalKepulangan("");
                  }}
                  className="flex flex-wrap gap-4"
                >
                  <label className="flex items-center gap-2 cursor-pointer rounded-lg border border-border px-4 py-3 hover:bg-muted/30 has-[:checked]:border-khaffah-primary has-[:checked]:bg-khaffah-primary/5">
                    <RadioGroupItem value="one_way" id="arah-one-way" className="text-khaffah-primary" />
                    <span className="text-sm font-medium">Satu Arah</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer rounded-lg border border-border px-4 py-3 hover:bg-muted/30 has-[:checked]:border-khaffah-primary has-[:checked]:bg-khaffah-primary/5">
                    <RadioGroupItem value="round_trip" id="arah-pulang-pergi" className="text-khaffah-primary" />
                    <span className="text-sm font-medium">Pulang Pergi</span>
                  </label>
                </RadioGroup>
              </div>

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

              <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <MapPinned className="h-4 w-4 text-muted-foreground" />
                    Bandara Asal (Keberangkatan)
                  </Label>
                  <Select value={bandaraAsal} onValueChange={setBandaraAsal}>
                    <SelectTrigger className="w-full h-10 rounded-lg border-border bg-muted/30">
                      <SelectValue placeholder="Pilih bandara asal" />
                    </SelectTrigger>
                    <SelectContent>
                      {bandaraAsalList.map((b) => (
                        <SelectItem key={b.value} value={b.value}>
                          {b.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <MapPinned className="h-4 w-4 text-muted-foreground" />
                    Bandara Tujuan
                  </Label>
                  <Select value={bandaraTujuan} onValueChange={setBandaraTujuan}>
                    <SelectTrigger className="w-full h-10 rounded-lg border-border bg-muted/30">
                      <SelectValue placeholder="Pilih bandara tujuan" />
                    </SelectTrigger>
                    <SelectContent>
                      {bandaraTujuanList.map((b) => (
                        <SelectItem key={b.value} value={b.value}>
                          {b.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div className={`grid gap-5 sm:grid-cols-2 ${arahPenerbangan === "one_way" ? "sm:grid-cols-1" : ""}`}>
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    Tanggal Keberangkatan
                  </Label>
                  <Input
                    type="date"
                    value={tanggalKeberangkatan}
                    onChange={(e) => setTanggalKeberangkatan(e.target.value)}
                    className="h-10 rounded-lg border-border bg-muted/30"
                  />
                </div>
                {arahPenerbangan === "round_trip" && (
                  <div className="space-y-2">
                    <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                      <Calendar className="h-4 w-4 text-muted-foreground" />
                      Tanggal Kepulangan
                    </Label>
                    <Input
                      type="date"
                      value={tanggalKepulangan}
                      onChange={(e) => setTanggalKepulangan(e.target.value)}
                      min={tanggalKeberangkatan || undefined}
                      className="h-10 rounded-lg border-border bg-muted/30"
                    />
                  </div>
                )}
              </div>

              <div className="grid gap-5 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    Jumlah Penumpang
                  </Label>
                  <Input
                    type="number"
                    min={1}
                    value={jumlahPenumpang}
                    onChange={(e) => {
                      const v = e.target.value;
                      if (v === "") setJumlahPenumpang(1);
                      else setJumlahPenumpang(Math.max(1, parseInt(v, 10) || 1));
                    }}
                    className="h-10 rounded-lg border-border bg-muted/30 tabular-nums"
                  />
                </div>
                <div className="space-y-2">
                  <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Banknote className="h-4 w-4 text-muted-foreground" />
                    Harga / Pax (IDR)
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
                    placeholder="Contoh: 5.000.000"
                    className="h-10 rounded-lg border-border bg-muted/30 tabular-nums"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label className="flex items-center gap-2 text-sm font-medium text-foreground">
                  <Users className="h-4 w-4 text-muted-foreground" />
                  Data Jemaah <span className="text-muted-foreground font-normal">(opsional)</span>
                </Label>
                <p className="text-xs text-muted-foreground">
                  Pilih jemaah yang ikut jika sudah terdaftar di menu Jamaah.
                </p>
                {jamaahList.length > 5 && (
                  <Input
                    type="text"
                    placeholder="Cari nama jemaah..."
                    className="h-9 rounded-lg border-border bg-muted/30 text-sm"
                    value={jamaahSearch}
                    onChange={(e) => {
                      setJamaahSearch(e.target.value);
                      setJamaahDisplayLimit(15);
                    }}
                  />
                )}
                <div className="rounded-lg border border-border bg-muted/20 p-2 max-h-40 overflow-y-auto space-y-1">
                  {jamaahList.length === 0 ? (
                    <p className="text-xs text-amber-600 py-2">Belum ada data jemaah. Tambah di menu Jamaah.</p>
                  ) : filteredJamaahList.length === 0 ? (
                    <p className="text-xs text-muted-foreground py-2">Tidak ada hasil untuk &quot;{jamaahSearch}&quot;</p>
                  ) : (
                    <>
                      {jamaahListToShow.map((j) => {
                        const jid = String(j.id);
                        const checked = selectedJamaahIds.includes(jid);
                        const nama = (j as { nama_lengkap?: string }).nama_lengkap ?? `Jemaah ${j.id}`;
                        return (
                          <label
                            key={j.id}
                            className={`flex items-center gap-2.5 cursor-pointer rounded-lg px-2.5 py-2 transition-colors ${checked ? "bg-khaffah-primary/10 text-khaffah-primary" : "hover:bg-muted"}`}
                          >
                            <input
                              type="checkbox"
                              checked={checked}
                              onChange={() => toggleJamaah(jid)}
                              className="rounded border-input text-khaffah-primary focus:ring-khaffah-primary size-4"
                            />
                            <span className="text-sm font-medium">{nama}</span>
                          </label>
                        );
                      })}
                      {hasMoreJamaah && (
                        <button
                          type="button"
                          onClick={() =>
                            setJamaahDisplayLimit((p) =>
                              Math.min(p + LOAD_MORE_JAMAAH, filteredJamaahList.length)
                            )
                          }
                          className="w-full py-2 text-xs text-khaffah-primary font-medium hover:bg-khaffah-primary/5 rounded-lg transition-colors"
                        >
                          Tampilkan lebih banyak ({filteredJamaahList.length - jamaahDisplayLimit} lagi)
                        </button>
                      )}
                    </>
                  )}
                </div>
                {selectedJamaahIds.length > 0 && (
                  <p className="text-xs text-khaffah-primary font-medium flex items-center gap-1">
                    <span className="inline-flex items-center justify-center size-5 rounded-full bg-khaffah-primary/20 text-khaffah-primary">
                      {selectedJamaahIds.length}
                    </span>
                    jemaah dipilih
                  </p>
                )}
              </div>

              <Button
                type="button"
                onClick={handlePesan}
                disabled={submitting || !selectedMaskapai || totalHarga <= 0}
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
        </div>

        <div className="lg:col-span-1">
          <Card className="sticky top-4 rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
            <div className="bg-gradient-to-r from-khaffah-primary/10 to-emerald-50 px-5 py-4 border-b border-border">
              <h3 className="flex items-center gap-2 text-base font-semibold text-foreground">
                <Receipt className="h-5 w-5 text-khaffah-primary shrink-0" />
                Ringkasan Pemesanan
              </h3>
              <p className="text-xs text-muted-foreground mt-0.5">
                Cek kembali sebelum mengirim permintaan
              </p>
            </div>
            <CardContent className="p-5 space-y-5">
              {!selectedMaskapai ? (
                <div className="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-center">
                  <p className="text-sm text-amber-800">
                    Pilih maskapai dan isi form untuk melihat ringkasan
                  </p>
                </div>
              ) : (
                <>
                  <div className="space-y-3">
                    <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                      Detail penerbangan
                    </p>
                    <ul className="space-y-2.5">
                      <li className="flex justify-between gap-3 text-sm">
                        <span className="text-muted-foreground shrink-0">Maskapai</span>
                        <span className="font-medium text-foreground text-right">
                          {selectedMaskapai.name}
                          {selectedMaskapai.kode_iata && (
                            <span className="text-muted-foreground font-normal ml-1">
                              ({selectedMaskapai.kode_iata})
                            </span>
                          )}
                        </span>
                      </li>
                      <li className="flex justify-between gap-3 text-sm">
                        <span className="text-muted-foreground shrink-0">Arah</span>
                        <span className="font-medium text-foreground">
                          {arahPenerbangan === "one_way" ? "Satu Arah" : "Pulang Pergi"}
                        </span>
                      </li>
                      {bandaraAsal && (
                        <li className="flex justify-between gap-3 text-sm items-start">
                          <span className="text-muted-foreground shrink-0">Asal</span>
                          <span className="font-medium text-foreground text-right text-xs leading-tight max-w-[65%]">
                            {bandaraAsalList.find((b) => b.value === bandaraAsal)?.label ?? bandaraAsal}
                          </span>
                        </li>
                      )}
                      {bandaraTujuan && (
                        <li className="flex justify-between gap-3 text-sm items-start">
                          <span className="text-muted-foreground shrink-0">Tujuan</span>
                          <span className="font-medium text-foreground text-right text-xs leading-tight max-w-[65%]">
                            {bandaraTujuanList.find((b) => b.value === bandaraTujuan)?.label ?? bandaraTujuan}
                          </span>
                        </li>
                      )}
                      {(tanggalKeberangkatan || tanggalKepulangan) && (
                        <li className="flex justify-between gap-3 text-sm">
                          <span className="text-muted-foreground shrink-0">Tanggal</span>
                          <span className="font-medium text-foreground text-right text-xs">
                            {tanggalKeberangkatan && (
                              <span>Berangkat: {tanggalKeberangkatan}</span>
                            )}
                            {arahPenerbangan === "round_trip" && tanggalKepulangan && (
                              <span className="block mt-0.5">Pulang: {tanggalKepulangan}</span>
                            )}
                          </span>
                        </li>
                      )}
                    </ul>
                  </div>

                  <div className="border-t border-border pt-4 space-y-3">
                    <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                      Rincian biaya
                    </p>
                    <div className="flex justify-between text-sm">
                      <span className="text-muted-foreground">
                        {jumlahPenumpang} penumpang × {formatRupiah(hargaPerPax)}
                      </span>
                    </div>
                    <div className="rounded-xl bg-khaffah-primary/10 border border-khaffah-primary/20 px-4 py-3 flex items-center justify-between gap-3">
                      <span className="text-sm font-semibold text-foreground">Total</span>
                      <span className="text-xl font-bold text-khaffah-primary tabular-nums">
                        {formatRupiah(totalHarga)}
                      </span>
                    </div>
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default TiketKomponenPage;
