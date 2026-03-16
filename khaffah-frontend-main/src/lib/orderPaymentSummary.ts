import type { Order } from "@/types/order";
import type { SnapshotLayananItem } from "@/types/order";
import { parseNumberLoose } from "@/lib/utils";

export interface OrderPaymentSummary {
  totalBiaya: number;
  jamaahCount: number;
  hargaPerPax: number;
  duration: number;
  departureDate: Date;
  returnDate: Date;
  costBreakdown: { label: string; value: number; detail?: string }[];
}

interface FallbackLayanan {
  layanan_wajib: SnapshotLayananItem[];
  layanan_tambahan: SnapshotLayananItem[];
}

/**
 * Menghitung total biaya, jemaah, dan harga per pax sesuai logic di order detail.
 * Dipakai di Rincian Pembayaran (order detail) dan halaman pembayaran (step 1).
 */
export function getOrderPaymentSummary(
  order: Order,
  fallbackLayanan?: FallbackLayanan | null
): OrderPaymentSummary {
  const snap = order.snapshot_produk;
  const isKomponenVisa = snap?.kategori_paket === "Komponen Visa";
  const dataVisa = snap?.data_visa as {
    layanan_nama?: string;
    jumlah_visa?: number;
    harga_per_pax?: number;
    total?: number;
    tanggal_keberangkatan?: string;
  } | undefined;

  const isKomponenBadalUmrah = snap?.kategori_paket === "Komponen Badal Umrah";
  const dataBadalUmrah = snap?.data_badal_umrah as {
    layanan_nama?: string;
    layanan_slug?: string;
    waktu_pemesanan?: string;
    harga_per_pax?: number;
    jumlah_jamaah?: number;
    total?: number;
  } | undefined;

  const isKomponenBadalHaji = snap?.kategori_paket === "Komponen Badal Haji";
  const dataBadalHaji = snap?.data_badal_haji as {
    layanan_nama?: string;
    layanan_slug?: string;
    waktu_pemesanan?: string;
    harga_per_pax?: number;
    jumlah_jamaah?: number;
    total?: number;
  } | undefined;

  const isKomponenTiketPesawat = snap?.kategori_paket === "Komponen Tiket Pesawat";
  const dataTiketPesawat = snap?.data_tiket_pesawat as {
    maskapai_nama?: string;
    maskapai_id?: string;
    waktu_pemesanan?: string;
    tanggal_keberangkatan?: string;
    tanggal_kepulangan?: string;
    harga_per_pax?: number;
    jumlah_penumpang?: number;
    total?: number;
  } | undefined;

  if (isKomponenTiketPesawat && dataTiketPesawat) {
    const totalBiaya = parseNumberLoose(dataTiketPesawat.total ?? order.total_biaya);
    const jumlahPenumpang = Math.max(Number(dataTiketPesawat.jumlah_penumpang) || 1, 1);
    const hargaPerPax = parseNumberLoose(dataTiketPesawat.harga_per_pax) || (totalBiaya > 0 ? totalBiaya / jumlahPenumpang : 0);
    const costBreakdown: { label: string; value: number; detail?: string }[] = [];
    if (totalBiaya > 0) {
      costBreakdown.push({
        label: dataTiketPesawat.maskapai_nama || "Tiket Pesawat",
        value: totalBiaya,
        detail: `${jumlahPenumpang} penumpang × ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(hargaPerPax)} = ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(totalBiaya)}`,
      });
    }
    const depStr = dataTiketPesawat.tanggal_keberangkatan ?? dataTiketPesawat.waktu_pemesanan ?? null;
    const departureDate = depStr ? new Date(depStr) : new Date();
    const retStr = dataTiketPesawat.tanggal_kepulangan ?? null;
    const returnDate = retStr ? new Date(retStr) : new Date(departureDate);
    returnDate.setDate(returnDate.getDate() + 1);
    return {
      totalBiaya,
      jamaahCount: jumlahPenumpang,
      hargaPerPax,
      duration: 0,
      departureDate,
      returnDate,
      costBreakdown,
    };
  }

  if (isKomponenBadalUmrah && dataBadalUmrah) {
    const totalBiaya = parseNumberLoose(dataBadalUmrah.total ?? order.total_biaya);
    const jumlahJamaah = Math.max(Number(dataBadalUmrah.jumlah_jamaah) || 1, 1);
    const hargaPerPax = parseNumberLoose(dataBadalUmrah.harga_per_pax) || (totalBiaya > 0 ? totalBiaya / jumlahJamaah : 0);
    const costBreakdown: { label: string; value: number; detail?: string }[] = [];
    if (totalBiaya > 0) {
      costBreakdown.push({
        label: dataBadalUmrah.layanan_nama || "Badal Umrah",
        value: totalBiaya,
        detail: `${jumlahJamaah} jamaah × ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(hargaPerPax)} = ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(totalBiaya)}`,
      });
    }
    const waktuStr = dataBadalUmrah.waktu_pemesanan ?? null;
    const departureDate = waktuStr ? new Date(waktuStr) : new Date();
    const returnDate = new Date(departureDate);
    returnDate.setDate(returnDate.getDate() + 1);
    return {
      totalBiaya,
      jamaahCount: jumlahJamaah,
      hargaPerPax,
      duration: 0,
      departureDate,
      returnDate,
      costBreakdown,
    };
  }

  if (isKomponenBadalHaji && dataBadalHaji) {
    const totalBiaya = parseNumberLoose(dataBadalHaji.total ?? order.total_biaya);
    const jumlahJamaah = Math.max(Number(dataBadalHaji.jumlah_jamaah) || 1, 1);
    const hargaPerPax = parseNumberLoose(dataBadalHaji.harga_per_pax) || (totalBiaya > 0 ? totalBiaya / jumlahJamaah : 0);
    const costBreakdown: { label: string; value: number; detail?: string }[] = [];
    if (totalBiaya > 0) {
      costBreakdown.push({
        label: dataBadalHaji.layanan_nama || "Badal Haji",
        value: totalBiaya,
        detail: `${jumlahJamaah} jamaah × ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(hargaPerPax)} = ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(totalBiaya)}`,
      });
    }
    const waktuStr = dataBadalHaji.waktu_pemesanan ?? null;
    const departureDate = waktuStr ? new Date(waktuStr) : new Date();
    const returnDate = new Date(departureDate);
    returnDate.setDate(returnDate.getDate() + 1);
    return {
      totalBiaya,
      jamaahCount: jumlahJamaah,
      hargaPerPax,
      duration: 0,
      departureDate,
      returnDate,
      costBreakdown,
    };
  }

  if (isKomponenVisa && dataVisa) {
    const totalBiaya = parseNumberLoose(dataVisa.total ?? order.total_biaya);
    const jumlahVisa = Math.max(Number(dataVisa.jumlah_visa) || 1, 1);
    const hargaPerPax = parseNumberLoose(dataVisa.harga_per_pax) || (totalBiaya > 0 ? totalBiaya / jumlahVisa : 0);
    const costBreakdown: { label: string; value: number; detail?: string }[] = [];
    if (totalBiaya > 0) {
      costBreakdown.push({
        label: dataVisa.layanan_nama || "Visa",
        value: totalBiaya,
        detail: `${jumlahVisa} visa × ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(hargaPerPax)} = ${new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0 }).format(totalBiaya)}`,
      });
    }
    const depStr = dataVisa.tanggal_keberangkatan ?? snap?.tanggal_program_umrah?.departureDate ?? null;
    const departureDate = depStr ? new Date(depStr) : new Date();
    const returnDate = new Date(departureDate);
    returnDate.setDate(returnDate.getDate() + 1);
    return {
      totalBiaya,
      jamaahCount: jumlahVisa,
      hargaPerPax,
      duration: 0,
      departureDate,
      returnDate,
      costBreakdown,
    };
  }

  const jamaahCount = order.jamaah_data?.length || 1;
  const tanggalProgram = snap?.tanggal_program_umrah;
  const depStr = tanggalProgram?.departureDate ?? null;
  const retStr = tanggalProgram?.returnDate ?? null;
  const departureDate = depStr ? new Date(depStr) : new Date("2025-08-21");
  const returnDate = retStr
    ? new Date(retStr)
    : (() => {
        const r = new Date(departureDate);
        r.setDate(departureDate.getDate() + 9);
        return r;
      })();
  const duration =
    Math.round(
      (returnDate.getTime() - departureDate.getTime()) / (24 * 60 * 60 * 1000)
    ) || 9;

  const dataHotel = snap?.data_hotel;
  const costBreakdown: { label: string; value: number; detail?: string }[] = [];
  const hari = Math.max(duration, 1);
  const formatCurrency = (n: number) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(n);

  if (dataHotel) {
    const roomDetails = Array.isArray(dataHotel.room_details)
      ? dataHotel.room_details
      : [];
    if (roomDetails.length > 0) {
      const hotelName = (dataHotel.hotelMekkah ?? dataHotel.hotelMadinah ?? "Hotel") as string;
      roomDetails.forEach((rd: { roomTypeName?: string | null; qty?: number; hargaPerMalam?: number | null }) => {
        const qty = Math.max(Number(rd.qty) || 0, 0);
        const harga = parseNumberLoose(rd.hargaPerMalam);
        if (qty > 0 && harga > 0) {
          const value = harga * qty * hari;
          const label = rd.roomTypeName
            ? `${rd.roomTypeName} (${hotelName})`
            : hotelName;
          costBreakdown.push({
            label,
            value,
            detail: `${qty} kamar × ${hari} malam × ${formatCurrency(harga)} = ${formatCurrency(value)}`,
          });
        }
      });
    } else {
      const hMekkah = parseNumberLoose(dataHotel.hotelMekkahHarga);
      const hMadinah = parseNumberLoose(dataHotel.hotelMadinahHarga);
      const kamar = Math.max(parseNumberLoose(dataHotel.kuotaKamar) || 1, 1);
      if (hMekkah > 0) {
        costBreakdown.push({
          label: dataHotel.hotelMekkah
            ? `Hotel Mekkah (${dataHotel.hotelMekkah})`
            : "Hotel Mekkah",
          value: hMekkah * kamar * hari,
        });
      }
      if (hMadinah > 0) {
        costBreakdown.push({
          label: dataHotel.hotelMadinah
            ? `Hotel Madinah (${dataHotel.hotelMadinah})`
            : "Hotel Madinah",
          value: hMadinah * kamar * hari,
        });
      }
    }
  }

  const layananWajib =
    Array.isArray(snap?.layanan_wajib) && (snap.layanan_wajib?.length ?? 0) > 0
      ? snap.layanan_wajib
      : fallbackLayanan?.layanan_wajib ?? [];
  const layananTambahan =
    Array.isArray(snap?.layanan_tambahan) &&
    (snap.layanan_tambahan?.length ?? 0) > 0
      ? snap.layanan_tambahan
      : fallbackLayanan?.layanan_tambahan ?? [];

  const toLayananValue = (item: {
    harga?: number | string;
    satuan?: string | null;
  }) => {
    const base = parseNumberLoose(item.harga);
    if (!base || base <= 0) return 0;
    const satuan = (item.satuan ?? "").toLowerCase();
    const perPax = /pax|orang/.test(satuan);
    const perHari = /hari/.test(satuan);
    const mult = perPax ? jamaahCount : perHari ? duration : 1;
    return base * mult;
  };
  const getLayananDetail = (
    item: { harga?: number | string; satuan?: string | null },
    value: number
  ) => {
    const base = parseNumberLoose(item.harga);
    if (!base || base <= 0) return undefined;
    const satuan = (item.satuan ?? "").trim() || "/pax";
    const perPax = /pax|orang/.test(satuan.toLowerCase());
    const perHari = /hari/.test(satuan.toLowerCase());
    const multLabel = perPax
      ? `${jamaahCount} orang`
      : perHari
        ? `${duration} hari`
        : "1";
    return `${formatCurrency(base)} ${satuan} × ${multLabel} = ${formatCurrency(value)}`;
  };

  [...layananWajib, ...layananTambahan].forEach((item) => {
    const value = toLayananValue(item);
    if (value > 0 && item.nama) {
      const detail = getLayananDetail(item, value);
      costBreakdown.push({ label: item.nama, value, detail });
    }
  });

  const totalFromApi = parseNumberLoose(order.total_biaya);
  const hargaPerPaxFromSnapshot = parseNumberLoose(snap?.harga_per_pax);
  const totalFromRincian =
    costBreakdown.length > 0
      ? costBreakdown.reduce((sum, item) => sum + item.value, 0)
      : 0;

  const totalBiaya =
    totalFromApi > 0
      ? totalFromApi
      : hargaPerPaxFromSnapshot > 0
        ? hargaPerPaxFromSnapshot * jamaahCount
        : totalFromRincian > 0
          ? totalFromRincian
          : 0;

  const hargaPerPax =
    jamaahCount > 0 ? totalBiaya / jamaahCount : totalBiaya;

  return {
    totalBiaya,
    jamaahCount,
    hargaPerPax,
    duration,
    departureDate,
    returnDate,
    costBreakdown,
  };
}
