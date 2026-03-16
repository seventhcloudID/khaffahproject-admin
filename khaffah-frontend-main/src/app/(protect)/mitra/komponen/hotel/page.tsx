"use client";

import { useEffect, useState, useMemo } from "react";
import { useRouter } from "next/navigation";
import type { Hotel } from "@/app/(protect)/mitra/pemesanan/isi-paket-kostumisasi/data/hotelData";

const HotelKomponenPage = () => {
  const router = useRouter();
  const [hotels, setHotels] = useState<Hotel[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchName, setSearchName] = useState("");
  const [filterCity, setFilterCity] = useState<string>("all");
  const [filterStars, setFilterStars] = useState<string>("all");

  const hotelsList = useMemo(() => hotels, [hotels]);

  const uniqueCities = useMemo(() => {
    const set = new Set<string>();
    hotelsList.forEach((h) => {
      if (h.city) set.add(h.city);
    });
    return Array.from(set).sort();
  }, [hotelsList]);

  const filteredHotels = useMemo(() => {
    return hotelsList.filter((h) => {
      const byName =
        !searchName ||
        h.name.toLowerCase().includes(searchName.toLowerCase().trim());
      const byCity =
        filterCity === "all" || !filterCity ? true : h.city === filterCity;
      const byStars =
        filterStars === "all" || !filterStars
          ? true
          : h.stars >= Number(filterStars);
      return byName && byCity && byStars;
    });
  }, [hotelsList, searchName, filterCity, filterStars]);

  useEffect(() => {
    let cancelled = false;

    async function loadHotels() {
      setLoading(true);
      setError(null);
      try {
        const base = (process.env.NEXT_PUBLIC_API ?? "http://127.0.0.1:8000").replace(
          /\/+$/,
          ""
        );
        const res = await fetch(`${base}/api/la-umrah/hotels`, {
          method: "GET",
          headers: { "Content-Type": "application/json" },
          cache: "no-store",
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = (await res.json()) as {
          status?: boolean;
          data?: Array<{
            id: string;
            name: string;
            city?: string;
            stars?: number;
            price?: number;
            distance?: string;
            images?: string[];
            description?: string;
          }>;
        };
        const list = Array.isArray(json?.data) ? json.data : [];
        const mapped: Hotel[] = list.map((h) => ({
          id: String(h.id),
          name: h.name ?? "-",
          city: h.city ?? "",
          stars: Number(h.stars ?? 0),
          price: Number(h.price ?? 0),
          distance: h.distance ?? "",
          images: Array.isArray(h.images) ? h.images : [],
          description: h.description ?? "",
          mapUrl: "",
          locationBreadcrumb: "",
          facilities: [],
          nearby: [],
          policies: {
            checkin: "",
            checkout: "",
            others: [],
          },
        }));
        if (!cancelled) {
          setHotels(mapped);
        }
      } catch (e: any) {
        if (!cancelled) {
          setError(e?.message ?? "Gagal mengambil data hotel");
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    loadHotels();

    return () => {
      cancelled = true;
    };
  }, []);

  const handleClickHotel = (hotel: Hotel) => {
    router.push(`/mitra/komponen/hotel/${encodeURIComponent(hotel.id)}`);
  };

  return (
    <div className="space-y-4">
      <div className="mb-2">
        <p className="text-13 font-medium text-black">Daftar Hotel</p>
        <p className="text-12 text-black/60">
          Pilih hotel yang tersedia untuk dipesan sebagai komponen terpisah.
        </p>
      </div>

      {loading && (
        <p className="text-12 text-black/60">Memuat data hotel dari server...</p>
      )}

      {error && (
        <p className="text-12 text-red-500">{error}</p>
      )}

      {/* Filter bar */}
      <div className="flex flex-col gap-3 md:flex-row md:items-end md:gap-4">
        <div className="flex-1">
          <label className="block text-11 text-black/60 mb-1">
            Nama Hotel
          </label>
          <input
            type="text"
            className="w-full rounded-md border border-slate-200 px-3 py-2 text-12 focus:outline-none focus:ring-2 focus:ring-khaffah-primary"
            placeholder="Cari nama hotel..."
            value={searchName}
            onChange={(e) => setSearchName(e.target.value)}
          />
        </div>
        <div className="w-full md:w-40">
          <label className="block text-11 text-black/60 mb-1">Lokasi</label>
          <select
            className="w-full rounded-md border border-slate-200 px-3 py-2 text-12 focus:outline-none focus:ring-2 focus:ring-khaffah-primary"
            value={filterCity}
            onChange={(e) => setFilterCity(e.target.value)}
          >
            <option value="all">Semua Lokasi</option>
            {uniqueCities.map((city) => (
              <option key={city} value={city}>
                {city}
              </option>
            ))}
          </select>
        </div>
        <div className="w-full md:w-40">
          <label className="block text-11 text-black/60 mb-1">
            Minimal Rating
          </label>
          <select
            className="w-full rounded-md border border-slate-200 px-3 py-2 text-12 focus:outline-none focus:ring-2 focus:ring-khaffah-primary"
            value={filterStars}
            onChange={(e) => setFilterStars(e.target.value)}
          >
            <option value="all">Semua Rating</option>
            <option value="5">5 Bintang</option>
            <option value="4">4 Bintang ke atas</option>
            <option value="3">3 Bintang ke atas</option>
          </select>
        </div>
      </div>

      {/* Table list */}
      <div className="w-full overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table className="min-w-full text-left text-12">
          <thead className="bg-slate-50 text-11 text-black/60">
            <tr>
              <th className="px-3 py-2 font-medium">Nama Hotel</th>
              <th className="px-3 py-2 font-medium">Lokasi</th>
              <th className="px-3 py-2 font-medium">Rating</th>
              <th className="px-3 py-2 font-medium">Jarak ke Masjid</th>
              <th className="px-3 py-2 font-medium text-right">
                Harga mulai (IDR)
              </th>
              <th className="px-3 py-2 font-medium text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {filteredHotels.length === 0 && (
              <tr>
                <td
                  className="px-3 py-4 text-center text-12 text-black/60"
                  colSpan={6}
                >
                  Tidak ada hotel yang cocok dengan filter.
                </td>
              </tr>
            )}
            {filteredHotels.map((hotel) => (
              <tr
                key={hotel.id}
                className="border-t border-slate-100 hover:bg-slate-50 cursor-pointer"
                onClick={() => handleClickHotel(hotel)}
              >
                <td className="px-3 py-2 text-12 text-black">
                  {hotel.name}
                </td>
                <td className="px-3 py-2 text-12 text-black/80">
                  {hotel.city || "-"}
                </td>
                <td className="px-3 py-2 text-12 text-black/80">
                  {hotel.stars ? `${hotel.stars} bintang` : "-"}
                </td>
                <td className="px-3 py-2 text-12 text-black/80">
                  {hotel.distance || "-"}
                </td>
                <td className="px-3 py-2 text-12 text-right text-khaffah-primary font-semibold">
                  {hotel.price
                    ? hotel.price.toLocaleString("id-ID")
                    : "0"}
                </td>
                <td className="px-3 py-2 text-center">
                  <button
                    type="button"
                    className="inline-flex items-center justify-center rounded-full border border-khaffah-primary px-3 py-1 text-11 text-khaffah-primary hover:bg-khaffah-primary hover:text-white transition-colors"
                  >
                    Lihat &amp; Pesan
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default HotelKomponenPage;


