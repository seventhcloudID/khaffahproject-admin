"use client";

import { use, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";
import { format, addDays } from "date-fns";
import { Card, CardContent } from "@/components/ui/card";
import {
  Utensils,
  Coffee,
  ShoppingBag,
  Landmark,
  Dumbbell,
  Wifi,
  Fan,
  Building,
  Users,
  Calendar,
  Moon,
  BadgeDollarSign,
} from "lucide-react";
import { useMe } from "@/query/auth";
import { useGetJamaahList } from "@/query/jamaah";
import { apiInstance } from "@/lib/axios";
import { toast } from "sonner";

type HotelPhoto = {
  id: number;
  url_foto: string;
  url_foto_display?: string;
  urutan?: number;
};

type HotelRoom = {
  id: number;
  tipe_kamar: string | null;
  kapasitas: number;
  harga_per_malam: number | null;
};

type HotelFacility = {
  id: number;
  nama: string;
  icon: string | null;
};

type HotelDetail = {
  id: string;
  name: string;
  city: string;
  stars: number;
  distance: string;
  address: string;
  description: string;
  price: number | null;
  photos: HotelPhoto[];
  rooms: HotelRoom[];
  facilities?: HotelFacility[];
};

type PageProps = {
  params: Promise<{ id: string }>;
};

/** Format angka ke Rupiah yang konsisten & mudah dibaca (UX-friendly) */
function formatRupiah(n: number): string {
  return new Intl.NumberFormat("id-ID", {
    style: "decimal",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(n);
}

const HotelKomponenDetailPage = (props: PageProps) => {
  const { id } = use(props.params);
  const router = useRouter();
  const [hotel, setHotel] = useState<HotelDetail | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Form pesan hotel
  const [selectedJamaahIds, setSelectedJamaahIds] = useState<string[]>([]);
  const [jamaahSearch, setJamaahSearch] = useState("");
  const [jamaahDisplayLimit, setJamaahDisplayLimit] = useState(15);
  const [checkinDate, setCheckinDate] = useState("");
  const [jumlahMalam, setJumlahMalam] = useState(1);
  /** Jumlah kamar per tipe: roomId -> number */
  const [roomQuantities, setRoomQuantities] = useState<Record<string, number>>({});
  const [submitting, setSubmitting] = useState(false);

  const { data: meData } = useMe();
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
  const LOAD_MORE_SIZE = 15;
  const user = (meData as { data?: { nama_lengkap?: string; no_handphone?: string } })?.data ?? meData;
  const clientName = (user as { nama_lengkap?: string })?.nama_lengkap ?? "";
  const clientPhone = (user as { no_handphone?: string })?.no_handphone ?? "";

  useEffect(() => {
    let cancelled = false;

    async function loadHotel() {
      setLoading(true);
      setError(null);
      try {
        const base = (process.env.NEXT_PUBLIC_API ?? "http://127.0.0.1:8000").replace(
          /\/+$/,
          ""
        );
        const res = await fetch(`${base}/api/la-umrah/hotels/${id}`, {
          method: "GET",
          headers: { "Content-Type": "application/json" },
          cache: "no-store",
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = (await res.json()) as {
          status?: boolean;
          data?: HotelDetail;
        };
        if (!cancelled) {
          if (json?.data) {
            setHotel(json.data);
          } else {
            setError("Hotel tidak ditemukan");
          }
        }
      } catch (e: any) {
        if (!cancelled) {
          setError(e?.message ?? "Gagal mengambil detail hotel");
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    loadHotel();

    return () => {
      cancelled = true;
    };
  }, [id]);

  const setRoomQty = (roomId: string, qty: number) => {
    setRoomQuantities((prev) => ({
      ...prev,
      [roomId]: Math.max(0, qty),
    }));
  };

  const roomDetailsForOrder = useMemo(() => {
    if (!hotel?.rooms) return [];
    return hotel.rooms
      .filter((r) => (roomQuantities[String(r.id)] ?? 0) > 0)
      .map((r) => ({
        roomTypeName: r.tipe_kamar ?? "Kamar",
        qty: roomQuantities[String(r.id)] ?? 0,
        hargaPerMalam: r.harga_per_malam ?? 0,
      }));
  }, [hotel?.rooms, roomQuantities]);

  const totalEstimasi = useMemo(() => {
    return roomDetailsForOrder.reduce(
      (sum, rd) => sum + rd.hargaPerMalam * rd.qty * jumlahMalam,
      0
    );
  }, [roomDetailsForOrder, jumlahMalam]);

  const toggleJamaah = (jamaahId: string) => {
    setSelectedJamaahIds((prev) =>
      prev.includes(jamaahId)
        ? prev.filter((id) => id !== jamaahId)
        : [...prev, jamaahId]
    );
  };

  const handlePesanHotel = async () => {
    if (!hotel) return;
    if (!checkinDate) {
      toast.error("Pilih tanggal check-in.");
      return;
    }
    if (jumlahMalam < 1) {
      toast.error("Jumlah malam minimal 1.");
      return;
    }
    if (roomDetailsForOrder.length === 0) {
      toast.error("Pilih jumlah kamar minimal satu tipe.");
      return;
    }

    const clientsPayload = selectedJamaahIds.map((jid) => {
      const j = jamaahList.find((x) => String(x.id) === String(jid));
      return {
        fullName: (j as { nama_lengkap?: string })?.nama_lengkap ?? "Jemaah",
        id: jid,
      };
    });

    setSubmitting(true);
    try {
      const departureDate = format(new Date(checkinDate), "yyyy-MM-dd");
      const returnDate = format(
        addDays(new Date(checkinDate), jumlahMalam),
        "yyyy-MM-dd"
      );

      const payload = {
        client: {
          fullName: clientName || "Pemesan",
          phoneNumber: clientPhone || "",
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
        departureDate,
        returnDate,
        hotelMekkah: hotel.name,
        hotelRoomsDetail: roomDetailsForOrder,
        tipePaket: "la_custom",
        kategoriPaket: "Komponen Hotel",
        dibuat_sebagai_mitra: true,
      };

      const res = await apiInstance.post<{
        data?: { transaksi?: { id: number }; kode_transaksi?: string };
      }>("/api/request-products", payload);
      const transaksiId = res.data?.data?.transaksi?.id;
      toast.success("Permintaan pemesanan hotel berhasil dikirim.");
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

  const mainPhoto =
    hotel?.photos?.[0]?.url_foto_display || hotel?.photos?.[0]?.url_foto || "";

  const otherPhotos = hotel?.photos?.slice(1) ?? [];

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between gap-2">
        <button
          type="button"
          className="text-12 text-khaffah-primary underline"
          onClick={() => router.back()}
        >
          &larr; Kembali ke daftar hotel
        </button>
        <p className="text-12 font-medium text-black/60">
          Detail Hotel &amp; Pilihan Kamar
        </p>
      </div>

      {loading && (
        <p className="text-12 text-black/60">Memuat data hotel dari server...</p>
      )}

      {error && (
        <p className="text-12 text-red-500">
          {error}
        </p>
      )}

      {!loading && !error && !hotel && (
        <p className="text-12 text-black/60">
          Hotel tidak ditemukan. Silakan kembali ke daftar.
        </p>
      )}

      {hotel && (
        <div className="space-y-4">
          <Card className="shadow-sm">
            <CardContent className="p-4 space-y-4">
              {/* Grid foto */}
              <div className="grid grid-cols-3 gap-3">
                <div className="col-span-3 md:col-span-2">
                  {mainPhoto && (
                    <div className="w-full overflow-hidden rounded-lg border border-slate-200">
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img
                        src={mainPhoto}
                        alt={hotel.name}
                        className="w-full h-48 md:h-56 object-cover"
                      />
                    </div>
                  )}
                </div>
                <div className="hidden md:flex md:flex-col md:gap-2">
                  {otherPhotos.slice(0, 3).map((p) => (
                    <div
                      key={p.id}
                      className="w-full h-16 rounded-md overflow-hidden border border-slate-200"
                    >
                      {/* eslint-disable-next-line @next/next/no-img-element */}
                      <img
                        src={p.url_foto_display || p.url_foto}
                        alt="Foto hotel"
                        className="w-full h-full object-cover"
                      />
                    </div>
                  ))}
                </div>
              </div>

              {/* Info utama */}
              <div className="space-y-2">
                <div className="flex flex-wrap items-center gap-2 justify-between">
                  <p className="text-15 font-semibold text-black">
                    {hotel.name}
                  </p>
                  <div className="flex items-center gap-2">
                    <span className="rounded-full bg-emerald-50 px-3 py-1 text-11 text-emerald-700 border border-emerald-100">
                      Hotel
                    </span>
                    {hotel.stars ? (
                      <span className="text-11 text-amber-500 font-medium">
                        {Array.from({ length: hotel.stars })
                          .map(() => "★")
                          .join(" ")}
                      </span>
                    ) : null}
                  </div>
                </div>

                <div className="flex flex-wrap gap-2 text-11 text-khaffah-primary">
                  {hotel.address && (
                    <span className="underline decoration-dotted">
                      {hotel.address}
                    </span>
                  )}
                  {hotel.distance && (
                    <span className="text-black/60">
                      • Jarak ke Masjidil Haram: {hotel.distance}
                    </span>
                  )}
                </div>

                {hotel.description && (
                  <p className="text-12 text-black/70 whitespace-pre-line">
                    {hotel.description}
                  </p>
                )}
              </div>

              {/* Fasilitas umum (dari API / master fasilitas_hotel_m + hotel_fasilitas_t) */}
              <div className="space-y-2">
                <p className="text-12 font-semibold text-black">
                  Fasilitas Umum
                </p>
                <div className="flex flex-wrap gap-2">
                  {(hotel.facilities && hotel.facilities.length > 0
                    ? hotel.facilities.map((f) => ({ label: f.nama, iconKey: (f.icon || "").toLowerCase() }))
                    : [
                        { label: "Restoran", iconKey: "restoran" },
                        { label: "Lounge", iconKey: "lounge" },
                        { label: "Pusat Perbelanjaan", iconKey: "belanja" },
                        { label: "Mushola", iconKey: "mushola" },
                        { label: "Gym", iconKey: "gym" },
                        { label: "Wi-Fi", iconKey: "wifi" },
                        { label: "Ruangan Ber-AC", iconKey: "ac" },
                        { label: "Lift", iconKey: "lift" },
                      ]
                  ).map((item) => {
                    const Icon =
                      item.iconKey.includes("restoran") || item.iconKey.includes("makan")
                        ? Utensils
                        : item.iconKey.includes("lounge")
                          ? Coffee
                          : item.iconKey.includes("belanja") || item.iconKey.includes("perbelanjaan")
                            ? ShoppingBag
                            : item.iconKey.includes("mushola") || item.iconKey.includes("masjid")
                              ? Landmark
                              : item.iconKey.includes("gym")
                                ? Dumbbell
                                : item.iconKey.includes("wifi")
                                  ? Wifi
                                  : item.iconKey.includes("ac") || item.iconKey.includes("ber-ac")
                                    ? Fan
                                    : item.iconKey.includes("lift")
                                      ? Building
                                      : Building;
                    return (
                      <div
                        key={item.label}
                        className="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1"
                      >
                        <Icon className="h-3 w-3 text-khaffah-primary shrink-0" />
                        <span className="text-11 text-black/70">
                          {item.label}
                        </span>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Harga — di atas data pemesanan */}
              <div className="space-y-4">
                <div className="rounded-xl border-2 border-khaffah-primary/20 bg-gradient-to-br from-khaffah-primary/5 to-emerald-50/50 p-4 md:p-5 flex items-start gap-3">
                  <div className="rounded-lg bg-khaffah-primary/10 p-2 shrink-0">
                    <BadgeDollarSign className="h-5 w-5 text-khaffah-primary" />
                  </div>
                  <div>
                    <p className="text-11 uppercase tracking-wider text-black/50 font-medium mb-0.5">Harga mulai per malam</p>
                    <div className="flex flex-wrap items-baseline gap-2">
                      {hotel.price ? (
                        <>
                          <span className="text-2xl md:text-3xl font-bold text-khaffah-primary tracking-tight tabular-nums">Rp {formatRupiah(hotel.price)}</span>
                          <span className="text-12 text-black/60 font-medium">/ malam</span>
                        </>
                      ) : (
                        <span className="text-15 font-semibold text-black/70">Hubungi kami untuk harga</span>
                      )}
                    </div>
                    <p className="text-11 text-black/50 mt-1.5">Pilih jumlah kamar per tipe di tabel, isi tanggal &amp; durasi. Data jemaah (opsional), lalu klik Pesan Hotel.</p>
                  </div>
                </div>

                {hotel.rooms && hotel.rooms.length > 0 && (
                  <div className="space-y-3">
                    <p className="text-12 font-semibold text-black">Pilihan kamar & harga</p>
                    <div className="w-full overflow-x-auto rounded-xl border border-slate-200/80 bg-white shadow-sm">
                      <table className="min-w-full text-left text-12">
                        <thead>
                          <tr className="border-b border-slate-200 bg-slate-50/80">
                            <th className="px-4 py-3 font-semibold text-black/80 rounded-tl-xl">Tipe Kamar</th>
                            <th className="px-4 py-3 font-semibold text-black/80">Kapasitas</th>
                            <th className="px-4 py-3 font-semibold text-black/80 text-right tabular-nums">Harga / malam</th>
                            <th className="px-4 py-3 font-semibold text-black/80 text-center">Jumlah kamar</th>
                            <th className="px-4 py-3 font-semibold text-black/80 text-right rounded-tr-xl tabular-nums">Subtotal</th>
                          </tr>
                        </thead>
                        <tbody>
                          {hotel.rooms.map((room, idx) => {
                            const qty = roomQuantities[String(room.id)] ?? 0;
                            const harga = room.harga_per_malam ?? 0;
                            const subtotal = qty * harga * jumlahMalam;
                            return (
                              <tr key={room.id} className={`border-b border-slate-100 last:border-0 ${idx % 2 === 0 ? "bg-white" : "bg-slate-50/30"} hover:bg-khaffah-primary/5 transition-colors`}>
                                <td className="px-4 py-3 text-black font-medium">{room.tipe_kamar || "Kamar"}</td>
                                <td className="px-4 py-3 text-black/70">{room.kapasitas} orang</td>
                                <td className="px-4 py-3 text-right tabular-nums">
                                  {room.harga_per_malam != null ? <span className="font-semibold text-khaffah-primary">Rp {formatRupiah(room.harga_per_malam)}</span> : <span className="text-black/50">—</span>}
                                </td>
                                <td className="px-4 py-2 text-center">
                                  <input type="number" min={0} max={50} className="w-14 rounded border border-slate-200 px-2 py-1.5 text-center text-12 tabular-nums focus:outline-none focus:ring-2 focus:ring-khaffah-primary" value={qty} onChange={(e) => setRoomQty(String(room.id), parseInt(e.target.value, 10) || 0)} />
                                </td>
                                <td className="px-4 py-3 text-right font-medium text-black tabular-nums">
                                  {qty > 0 ? `Rp ${formatRupiah(subtotal)}` : "—"}
                                </td>
                              </tr>
                            );
                          })}
                        </tbody>
                      </table>
                    </div>
                    {totalEstimasi > 0 && (
                      <div className="rounded-xl bg-slate-900 text-white p-4 md:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div className="flex items-center gap-3">
                          <div className="rounded-lg bg-white/10 p-2">
                            <BadgeDollarSign className="h-5 w-5 text-white" />
                          </div>
                          <div>
                            <p className="text-12 font-medium text-white/90">Estimasi total</p>
                            <p className="text-10 text-white/70">Untuk {jumlahMalam} malam × kamar yang dipilih</p>
                          </div>
                        </div>
                        <p className="text-xl md:text-2xl font-bold tracking-tight tabular-nums">Rp {formatRupiah(totalEstimasi)}</p>
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Data pemesanan — di bawah harga */}
              <div className="space-y-4">
                <p className="text-13 font-semibold text-black flex items-center gap-2">
                  <Users className="h-4 w-4 text-khaffah-primary" />
                  Data pemesanan
                </p>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
                  {/* Siapa yang menginap */}
                  <div className="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h4 className="text-12 font-semibold text-black mb-2 flex items-center gap-2">
                      <Users className="h-3.5 w-3.5 text-khaffah-primary" />
                      Siapa yang menginap? <span className="text-black/50 font-normal">(opsional)</span>
                    </h4>
                    {jamaahList.length > 5 && (
                      <input
                        type="text"
                        placeholder="Cari nama jemaah..."
                        className="w-full rounded-lg border border-slate-200 px-3 py-2 text-12 mb-2 focus:outline-none focus:ring-2 focus:ring-khaffah-primary focus:border-transparent"
                        value={jamaahSearch}
                        onChange={(e) => {
                          setJamaahSearch(e.target.value);
                          setJamaahDisplayLimit(15);
                        }}
                      />
                    )}
                    <div className="rounded-lg border border-slate-100 bg-slate-50/50 p-2 max-h-40 overflow-y-auto space-y-1">
                      {jamaahList.length === 0 ? (
                        <p className="text-11 text-amber-600 py-2">Belum ada data jemaah. Tambah di menu Jamaah.</p>
                      ) : filteredJamaahList.length === 0 ? (
                        <p className="text-11 text-black/50 py-2">Tidak ada hasil untuk &quot;{jamaahSearch}&quot;</p>
                      ) : (
                        <>
                          {jamaahListToShow.map((j) => {
                            const jid = String(j.id);
                            const checked = selectedJamaahIds.includes(jid);
                            const nama = (j as { nama_lengkap?: string }).nama_lengkap ?? `Jemaah ${j.id}`;
                            return (
                              <label key={j.id} className={`flex items-center gap-2.5 cursor-pointer rounded-lg px-2.5 py-2 transition-colors ${checked ? "bg-khaffah-primary/10 text-khaffah-primary" : "hover:bg-slate-100"}`}>
                                <input type="checkbox" checked={checked} onChange={() => toggleJamaah(jid)} className="rounded border-slate-300 text-khaffah-primary focus:ring-khaffah-primary size-4" />
                                <span className="text-12 font-medium">{nama}</span>
                              </label>
                            );
                          })}
                          {hasMoreJamaah && (
                            <button type="button" onClick={() => setJamaahDisplayLimit((p) => Math.min(p + LOAD_MORE_SIZE, filteredJamaahList.length))} className="w-full py-2 text-11 text-khaffah-primary font-medium hover:bg-khaffah-primary/5 rounded-lg transition-colors">
                              Tampilkan lebih banyak ({filteredJamaahList.length - jamaahDisplayLimit} lagi)
                            </button>
                          )}
                        </>
                      )}
                    </div>
                    {selectedJamaahIds.length > 0 && (
                      <p className="text-11 text-khaffah-primary font-medium mt-2 flex items-center gap-1">
                        <span className="inline-flex items-center justify-center size-5 rounded-full bg-khaffah-primary/20 text-khaffah-primary">{selectedJamaahIds.length}</span>
                        jemaah dipilih
                      </p>
                    )}
                  </div>

                  {/* Tanggal & durasi */}
                  <div className="space-y-4">
                    <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                      <h4 className="text-12 font-semibold text-black mb-2 flex items-center gap-2">
                        <Calendar className="h-3.5 w-3.5 text-khaffah-primary" />
                        Tanggal check-in
                      </h4>
                      <input
                        type="date"
                        className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-12 focus:outline-none focus:ring-2 focus:ring-khaffah-primary focus:border-transparent"
                        value={checkinDate}
                        onChange={(e) => setCheckinDate(e.target.value)}
                        min={format(new Date(), "yyyy-MM-dd")}
                      />
                    </div>
                    <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                      <h4 className="text-12 font-semibold text-black mb-2 flex items-center gap-2">
                        <Moon className="h-3.5 w-3.5 text-khaffah-primary" />
                        Jumlah malam
                      </h4>
                      <input
                        type="number"
                        min={1}
                        max={365}
                        className="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-12 focus:outline-none focus:ring-2 focus:ring-khaffah-primary focus:border-transparent tabular-nums"
                        value={jumlahMalam}
                        onChange={(e) => setJumlahMalam(Math.max(1, parseInt(e.target.value, 10) || 1))}
                      />
                      <p className="text-10 text-black/50 mt-1">Min. 1 malam</p>
                    </div>
                  </div>
                </div>

                <button type="button" className="w-full py-2.5 rounded-md bg-khaffah-primary text-white text-13 font-semibold hover:bg-khaffah-primary/90 transition-colors disabled:opacity-50" onClick={handlePesanHotel} disabled={submitting}>
                  {submitting ? "Mengirim..." : "Pesan Hotel"}
                </button>
              </div>
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
};

export default HotelKomponenDetailPage;

