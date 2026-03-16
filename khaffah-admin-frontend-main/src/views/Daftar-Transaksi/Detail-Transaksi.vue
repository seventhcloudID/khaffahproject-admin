<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center justify-between mb-6">
        <router-link
          :to="backPath"
          class="flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium"
        >
          <i class="fas fa-arrow-left"></i>
          Kembali ke Daftar
        </router-link>
        <div v-if="transactionDetail" class="flex items-center gap-2">
          <Button
            label="Update Status Transaksi"
            icon="fas fa-pencil"
            class="p-button-outlined p-button-sm"
            @click="openUpdateStatus"
          />
          <Button
            label="Update Status Pembayaran"
            icon="fas fa-money-bill-wave"
            class="p-button-outlined p-button-sm"
            @click="openUpdateStatusPembayaran"
          />
        </div>
      </div>

      <div v-if="loading" class="text-center py-12 text-gray-500">Memuat detail...</div>
      <div v-else-if="error" class="text-center py-12 text-red-600">{{ error }}</div>
      <div v-else-if="transactionDetail" class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ pageTitle }}</h1>

        <!-- Info utama — card agar mudah dibaca -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
          <div class="px-5 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Pesanan</h2>
          </div>
          <div class="p-5">
            <dl class="space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">No. Pesanan</dt>
                <dd class="text-base font-semibold text-gray-900 font-mono">{{ transactionDetail.kode_transaksi }}</dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Tanggal Pembelian</dt>
                <dd class="text-base text-gray-900">
                  <span v-if="transactionDetail.tgl_pemesanan">
                    {{ H.formatDate(transactionDetail.tgl_pemesanan, 'DD/MM/YYYY') }}
                    <span class="text-gray-500 font-normal">· {{ H.formatDate(transactionDetail.tgl_pemesanan, 'HH:mm') }} WIB</span>
                  </span>
                  <span v-else class="text-gray-500">–</span>
                </dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Pemesan</dt>
                <dd class="text-base font-medium text-gray-900">{{ [transactionDetail.gelar, transactionDetail.nama_lengkap].filter(Boolean).join(' ') || '–' }}</dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">No. WhatsApp</dt>
                <dd class="text-base text-gray-900 flex items-center gap-2">
                  <span>{{ transactionDetail.no_whatsapp || '–' }}</span>
                  <a
                    v-if="transactionDetail.no_whatsapp"
                    :href="`https://wa.me/${String(transactionDetail.no_whatsapp).replace(/^0/, '62').replace(/\D/g, '')}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center w-8 h-8 bg-green-500 hover:bg-green-600 rounded-lg transition-colors text-white"
                    title="Chat via WhatsApp"
                  >
                    <i class="fab fa-whatsapp text-sm"></i>
                  </a>
                </dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Tipe / Nama Paket</dt>
                <dd class="text-base">
                  <span class="font-semibold text-gray-900">{{ transactionDetail.nama_paket || '–' }}</span>
                  <p v-if="tipePaketLabel" class="text-sm text-gray-500 mt-0.5">{{ tipePaketLabel }}</p>
                </dd>
              </div>
              <div v-if="detailType === 'haji' && transactionDetail.alamat_lengkap" class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Alamat Lengkap</dt>
                <dd class="text-base text-gray-900">{{ transactionDetail.alamat_lengkap }}</dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Status Transaksi</dt>
                <dd>
                  <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-amber-100 text-amber-800">
                    {{ transactionDetail.status_nama ?? transactionDetail.nama_status ?? '–' }}
                  </span>
                </dd>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Status Pembayaran</dt>
                <dd>
                  <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-800">
                    {{ transactionDetail.status_pembayaran_nama ?? '–' }}
                  </span>
                </dd>
              </div>
              <div v-if="transactionDetail.deskripsi" class="flex flex-col sm:flex-row sm:items-start gap-1 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500 shrink-0 sm:w-40">Catatan</dt>
                <dd class="text-base text-gray-900">{{ transactionDetail.deskripsi }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <div v-if="transactionDetail.snapshot_produk" class="space-y-6">
          <!-- ========== CUSTOM (Permintaan Custom / LA / Komponen) ========== -->
          <template v-if="detailType === 'custom'">
            <div v-if="hasTanggalProgram" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Tanggal Program</h2>
              </div>
              <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                  <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Check-in</dt>
                    <dd class="text-base font-medium text-gray-900">{{ H.formatDate(tanggalProgram.departureDate, 'DD/MM/YYYY') || '–' }}</dd>
                  </div>
                  <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Durasi</dt>
                    <dd class="text-base font-medium text-gray-900">{{ durasiHari }} malam</dd>
                  </div>
                  <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Check-out</dt>
                    <dd class="text-base font-medium text-gray-900">{{ H.formatDate(tanggalProgram.returnDate, 'DD/MM/YYYY') || '–' }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <div v-if="hasDataHotel" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Data Hotel</h2>
              </div>
              <div class="p-5">
              <div class="space-y-1 text-sm text-gray-700">
                <p v-if="(dh.hotelMekkah ?? dh.hotel_mekkah)">
                  <span class="text-gray-500">Hotel:</span> {{ (dh.hotelMekkah ?? dh.hotel_mekkah) }}
                  <span v-if="(dh.hotelMekkahHarga ?? dh.hotel_mekkah_harga) != null && !roomDetails.length" class="text-gray-600">({{ H.formatRupiah(dh.hotelMekkahHarga ?? dh.hotel_mekkah_harga) }}/malam)</span>
                </p>
                <p v-if="(dh.hotelMadinah ?? dh.hotel_madinah) && !roomDetails.length">
                  <span class="text-gray-500">Hotel Madinah:</span> {{ (dh.hotelMadinah ?? dh.hotel_madinah) }}
                  <span v-if="(dh.hotelMadinahHarga ?? dh.hotel_madinah_harga) != null" class="text-gray-600">({{ H.formatRupiah(dh.hotelMadinahHarga ?? dh.hotel_madinah_harga) }}/malam)</span>
                </p>
                <p v-if="(dh.kuotaKamar ?? dh.kuota_kamar) != null && !roomDetails.length"><span class="text-gray-500">Kuota Kamar:</span> {{ dh.kuotaKamar ?? dh.kuota_kamar }}</p>
              </div>
              <!-- Tabel detail kamar (Transaksi Komponen Hotel) -->
              <div v-if="roomDetails.length > 0" class="mt-3 overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm">
                  <thead class="bg-gray-100">
                    <tr>
                      <th class="px-3 py-2 text-left font-semibold text-gray-700">Tipe Kamar</th>
                      <th class="px-3 py-2 text-center font-semibold text-gray-700">Jumlah</th>
                      <th class="px-3 py-2 text-right font-semibold text-gray-700">Harga / malam</th>
                      <th class="px-3 py-2 text-right font-semibold text-gray-700">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="(rd, idx) in roomDetails" :key="idx" class="bg-white">
                      <td class="px-3 py-2 text-gray-900">{{ rd.roomTypeName ?? rd.room_type_name ?? 'Kamar' }}</td>
                      <td class="px-3 py-2 text-center text-gray-700">{{ rd.qty ?? 0 }}</td>
                      <td class="px-3 py-2 text-right text-gray-700">{{ H.formatRupiah(rd.hargaPerMalam ?? rd.harga_per_malam ?? 0) }}</td>
                      <td class="px-3 py-2 text-right font-medium text-gray-900">{{ H.formatRupiah((Number(rd.qty) || 0) * (Number(rd.hargaPerMalam ?? rd.harga_per_malam) || 0) * durasiHariDisplay) }}</td>
                    </tr>
                  </tbody>
                </table>
                <p class="px-3 py-2 text-xs text-gray-500 bg-gray-50 border-t border-gray-100">Jumlah malam: {{ durasiHariDisplay }} malam</p>
              </div>
              </div>
            </div>
            <div v-if="hasDataKeberangkatan" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Data Keberangkatan</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-3">
                  <div v-if="String((dataKeberangkatan && (dataKeberangkatan.namaMaskapai ?? dataKeberangkatan.nama_maskapai)) || '').trim()">
                    <dt class="text-xs text-gray-500 mb-0.5">Maskapai</dt>
                    <dd class="text-base text-gray-900">{{ (dataKeberangkatan?.namaMaskapai ?? dataKeberangkatan?.nama_maskapai) || '–' }}</dd>
                  </div>
                  <div v-if="String((dataKeberangkatan && (dataKeberangkatan.bandaraKeberangkatan ?? dataKeberangkatan.bandara_keberangkatan)) || '').trim()">
                    <dt class="text-xs text-gray-500 mb-0.5">Bandara Keberangkatan</dt>
                    <dd class="text-base text-gray-900">{{ (dataKeberangkatan?.bandaraKeberangkatan ?? dataKeberangkatan?.bandara_keberangkatan) || '–' }}</dd>
                  </div>
                  <div v-if="String((dataKeberangkatan && (dataKeberangkatan.bandaraKepulangan ?? dataKeberangkatan.bandara_kepulangan)) || '').trim()">
                    <dt class="text-xs text-gray-500 mb-0.5">Bandara Kepulangan</dt>
                    <dd class="text-base text-gray-900">{{ (dataKeberangkatan?.bandaraKepulangan ?? dataKeberangkatan?.bandara_kepulangan) || '–' }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <!-- Komponen Visa -->
            <div v-if="transactionDetail.snapshot_produk?.data_visa && Object.keys(transactionDetail.snapshot_produk.data_visa).length" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Data Visa</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-3 text-sm">
                  <div v-if="transactionDetail.snapshot_produk.data_visa.layanan_nama">
                    <dt class="text-xs text-gray-500 mb-0.5">Layanan</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_visa.layanan_nama }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_visa.jumlah_visa != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Jumlah Visa</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_visa.jumlah_visa }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_visa.harga_per_pax != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Harga / pax</dt>
                    <dd class="text-base text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_visa.harga_per_pax) }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_visa.total != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Total</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_visa.total) }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_visa.tanggal_keberangkatan">
                    <dt class="text-xs text-gray-500 mb-0.5">Tanggal Keberangkatan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_visa.tanggal_keberangkatan, 'DD/MM/YYYY') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_visa.waktu_pemesanan">
                    <dt class="text-xs text-gray-500 mb-0.5">Waktu Pemesanan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_visa.waktu_pemesanan, 'DD/MM/YYYY HH:mm') }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <!-- Komponen Tiket Pesawat -->
            <div v-if="transactionDetail.snapshot_produk?.data_tiket_pesawat && Object.keys(transactionDetail.snapshot_produk.data_tiket_pesawat).length" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Rincian Tiket Pesawat</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-3 text-sm">
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.maskapai_nama">
                    <dt class="text-xs text-gray-500 mb-0.5">Maskapai</dt>
                    <dd class="text-base font-medium text-gray-900">{{ transactionDetail.snapshot_produk.data_tiket_pesawat.maskapai_nama }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.waktu_pemesanan">
                    <dt class="text-xs text-gray-500 mb-0.5">Waktu Pemesanan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_tiket_pesawat.waktu_pemesanan, 'DD/MM/YYYY HH:mm') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.tanggal_keberangkatan">
                    <dt class="text-xs text-gray-500 mb-0.5">Tanggal Keberangkatan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_tiket_pesawat.tanggal_keberangkatan, 'DD/MM/YYYY') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.tanggal_kepulangan">
                    <dt class="text-xs text-gray-500 mb-0.5">Tanggal Kepulangan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_tiket_pesawat.tanggal_kepulangan, 'DD/MM/YYYY') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.jumlah_penumpang != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Jumlah Penumpang</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_tiket_pesawat.jumlah_penumpang }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.harga_per_pax != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Harga / pax</dt>
                    <dd class="text-base text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_tiket_pesawat.harga_per_pax) }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_tiket_pesawat.total != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Total</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_tiket_pesawat.total) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <!-- Komponen Badal Umrah -->
            <div v-if="transactionDetail.snapshot_produk?.data_badal_umrah && Object.keys(transactionDetail.snapshot_produk.data_badal_umrah).length" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Rincian Badal Umrah</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-3 text-sm">
                  <div v-if="transactionDetail.snapshot_produk.data_badal_umrah.layanan_nama">
                    <dt class="text-xs text-gray-500 mb-0.5">Layanan</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_badal_umrah.layanan_nama }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_umrah.waktu_pemesanan">
                    <dt class="text-xs text-gray-500 mb-0.5">Waktu Pemesanan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_badal_umrah.waktu_pemesanan, 'DD/MM/YYYY HH:mm') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_umrah.jumlah_jamaah != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Jumlah Jamaah</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_badal_umrah.jumlah_jamaah }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_umrah.harga_per_pax != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Harga / pax</dt>
                    <dd class="text-base text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_badal_umrah.harga_per_pax) }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_umrah.total != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Total</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_badal_umrah.total) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <!-- Komponen Badal Haji -->
            <div v-if="transactionDetail.snapshot_produk?.data_badal_haji && Object.keys(transactionDetail.snapshot_produk.data_badal_haji).length" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Rincian Badal Haji</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-3 text-sm">
                  <div v-if="transactionDetail.snapshot_produk.data_badal_haji.layanan_nama">
                    <dt class="text-xs text-gray-500 mb-0.5">Layanan</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_badal_haji.layanan_nama }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_haji.waktu_pemesanan">
                    <dt class="text-xs text-gray-500 mb-0.5">Waktu Pemesanan</dt>
                    <dd class="text-base text-gray-900">{{ H.formatDate(transactionDetail.snapshot_produk.data_badal_haji.waktu_pemesanan, 'DD/MM/YYYY HH:mm') }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_haji.jumlah_jamaah != null">
                    <dt class="text-xs text-gray-500 mb-0.5">Jumlah Jamaah</dt>
                    <dd class="text-base text-gray-900">{{ transactionDetail.snapshot_produk.data_badal_haji.jumlah_jamaah }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_haji.harga_per_pax != null && transactionDetail.snapshot_produk.data_badal_haji.harga_per_pax > 0">
                    <dt class="text-xs text-gray-500 mb-0.5">Harga / pax</dt>
                    <dd class="text-base text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_badal_haji.harga_per_pax) }}</dd>
                  </div>
                  <div v-if="transactionDetail.snapshot_produk.data_badal_haji.total != null && transactionDetail.snapshot_produk.data_badal_haji.total > 0">
                    <dt class="text-xs text-gray-500 mb-0.5">Total</dt>
                    <dd class="text-base font-semibold text-gray-900">{{ H.formatRupiah(transactionDetail.snapshot_produk.data_badal_haji.total) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
            <div v-if="layananWajibItems.length > 0" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Layanan Wajib</h2>
              </div>
              <div class="p-5 space-y-3">
                <div v-for="(item, idx) in layananWajibItems" :key="'wajib-' + idx" class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                  <div>
                    <p class="font-medium text-gray-900">{{ item.nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ H.formatRupiah(item.harga) }} {{ item.satuan || '/pax' }} × {{ item.multLabel }}</p>
                  </div>
                  <span class="font-semibold text-gray-900 whitespace-nowrap">{{ H.formatRupiah(item.value) }}</span>
                </div>
                <div v-if="totalLayananWajib > 0" class="flex justify-between font-semibold pt-3 border-t border-gray-200">
                  <span>Subtotal layanan wajib</span>
                  <span>{{ H.formatRupiah(totalLayananWajib) }}</span>
                </div>
              </div>
            </div>
            <div v-if="layananTambahanIds.length > 0" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Layanan Tambahan</h2>
              </div>
              <div class="p-5 space-y-3">
                <div v-for="(item, idx) in layananTambahanItems" :key="idx" class="flex justify-between items-start py-2 border-b border-gray-100 last:border-0">
                  <div>
                    <p class="font-medium text-gray-900">{{ item.nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ H.formatRupiah(item.harga) }} {{ item.satuan || '/pax' }} × {{ item.multLabel }}</p>
                  </div>
                  <span class="font-semibold text-gray-900 whitespace-nowrap">{{ H.formatRupiah(item.value) }}</span>
                </div>
                <div v-if="totalLayananTambahan > 0" class="flex justify-between font-semibold pt-3 border-t border-gray-200">
                  <span>Subtotal layanan tambahan</span>
                  <span>{{ H.formatRupiah(totalLayananTambahan) }}</span>
                </div>
              </div>
            </div>
            <div v-if="rincianBiaya.length > 0 || totalLayananWajib > 0 || totalLayananTambahan > 0" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Rincian Biaya</h2>
              </div>
              <div class="p-5">
                <dl class="space-y-2">
                  <div v-for="(item, i) in rincianBiaya" :key="'hotel-' + i" class="flex justify-between text-base">
                    <dt class="text-gray-700">{{ item.label }}</dt>
                    <dd class="font-medium text-gray-900">{{ H.formatRupiah(item.value) }}</dd>
                  </div>
                  <div v-if="totalLayananWajib > 0" class="flex justify-between text-base">
                    <dt class="text-gray-700">Layanan wajib</dt>
                    <dd class="font-medium text-gray-900">{{ H.formatRupiah(totalLayananWajib) }}</dd>
                  </div>
                  <div v-if="totalLayananTambahan > 0" class="flex justify-between text-base">
                    <dt class="text-gray-700">Layanan tambahan</dt>
                    <dd class="font-medium text-gray-900">{{ H.formatRupiah(totalLayananTambahan) }}</dd>
                  </div>
                </dl>
                <div v-if="totalBiaya > 0" class="flex justify-between items-center pt-4 mt-4 border-t-2 border-gray-200">
                  <span class="text-base font-semibold text-gray-900">Total</span>
                  <span class="text-lg font-bold text-gray-900">{{ H.formatRupiah(totalBiaya) }}</span>
                </div>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.komponen && Object.values(transactionDetail.snapshot_produk.komponen).some((v: any) => v)" class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
              <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Komponen LA</h2>
              </div>
              <div class="p-5">
                <div class="flex flex-wrap gap-2">
                  <span v-for="(val, key) in transactionDetail.snapshot_produk.komponen" :key="String(key)" v-show="val" class="px-3 py-1.5 bg-teal-50 text-teal-800 rounded-lg text-sm font-medium">{{ key }}</span>
                </div>
              </div>
            </div>
          </template>

          <!-- ========== PAKET UMRAH (struktur sama dengan Master-Umrah) ========== -->
          <template v-else-if="detailType === 'umrah'">
            <!-- Data dari API detail paket (get-paket-umrah/{id}) atau fallback snapshot -->
            <div class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Detail Paket Umrah</div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-700">
                <p><strong>Nama Paket:</strong> {{ paketUmrahDisplay.nama_paket || transactionDetail.snapshot_produk?.nama_paket || '-' }}</p>
                <p><strong>Musim:</strong> {{ paketUmrahDisplay.musim ?? '-' }}</p>
                <p><strong>Range Harga:</strong> {{ (paketUmrahDisplay.harga_termurah != null || paketUmrahDisplay.harga_termahal != null) ? (H.formatRupiah(paketUmrahDisplay.harga_termurah) + ' - ' + H.formatRupiah(paketUmrahDisplay.harga_termahal)) : (transactionDetail.snapshot_produk?.harga_per_pax != null ? H.formatRupiah(transactionDetail.snapshot_produk.harga_per_pax) + ' / pax' : '-') }}</p>
                <p><strong>Rating Hotel:</strong> {{ paketUmrahDisplay.bintang ?? '-' }}</p>
                <p><strong>Durasi Total:</strong> {{ (paketUmrahDisplay.durasi_total ?? transactionDetail.snapshot_produk?.durasi_total) ?? '-' }} Hari</p>
                <p><strong>Jadwal Keberangkatan:</strong> {{ jadwalKeberangkatanDisplay }}</p>
                <p v-if="paketUmrahDisplay.lokasi_keberangkatan" class="sm:col-span-2"><strong>Lokasi Keberangkatan:</strong> {{ paketUmrahDisplay.lokasi_keberangkatan }}</p>
                <p v-if="paketUmrahDisplay.lokasi_tujuan" class="sm:col-span-2"><strong>Lokasi Tujuan:</strong> {{ paketUmrahDisplay.lokasi_tujuan }}</p>
                <p v-if="paketUmrahDisplay.maskapai_names" class="sm:col-span-2"><strong>Maskapai:</strong> {{ paketUmrahDisplay.maskapai_names }}</p>
                <p v-if="paketUmrahDisplay.kelas_penerbangan" class="sm:col-span-2"><strong>Kelas Penerbangan:</strong> {{ paketUmrahDisplay.kelas_penerbangan }}</p>
                <p v-if="(transactionDetail.snapshot_produk?.deskripsi || paketUmrahDisplay.deskripsi)" class="sm:col-span-2"><strong>Deskripsi:</strong> {{ paketUmrahDisplay.deskripsi || transactionDetail.snapshot_produk?.deskripsi }}</p>
              </div>
            </div>
            <!-- Destinasi (sesuai Master-Umrah: Kota + Durasi Hari) -->
            <div v-if="(paketUmrahDisplay.destinasi && paketUmrahDisplay.destinasi.length) || (transactionDetail.snapshot_produk?.akomodasi?.length)" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Destinasi</div>
              <div v-if="paketUmrahDisplay.destinasi?.length" class="space-y-2 text-sm">
                <div v-for="(d, i) in paketUmrahDisplay.destinasi" :key="i" class="flex justify-between py-1 border-b border-gray-200 last:border-0">
                  <span class="font-medium text-gray-700">{{ d.nama_kota }}</span>
                  <span class="text-gray-600">{{ d.durasi }} Hari</span>
                </div>
              </div>
              <ul v-else-if="transactionDetail.snapshot_produk.akomodasi?.length" class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(a, i) in transactionDetail.snapshot_produk.akomodasi" :key="i">
                  <strong>{{ a.kota }}:</strong> {{ a.nama_hotel }} (rating: {{ a.rating_hotel }}) — {{ a.jarak_ke_masjid }}
                </li>
              </ul>
            </div>
            <!-- Hotel / Akomodasi (sesuai Master-Umrah) -->
            <div v-if="paketUmrahDisplay.hotel?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Akomodasi (Hotel)</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(h, i) in paketUmrahDisplay.hotel" :key="i">
                  <strong>{{ h.kota || 'Hotel' }}:</strong> {{ h.nama_hotel }} (bintang: {{ h.bintang }}) <span v-if="h.jarak_ke_masjid">— {{ h.jarak_ke_masjid }}</span>
                </li>
              </ul>
            </div>
            <div v-else-if="transactionDetail.snapshot_produk?.akomodasi?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Akomodasi</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(a, i) in transactionDetail.snapshot_produk.akomodasi" :key="i">
                  <strong>{{ a.kota }}:</strong> {{ a.nama_hotel }} (rating: {{ a.rating_hotel }}) — {{ a.jarak_ke_masjid }}
                </li>
              </ul>
            </div>
            <!-- Fasilitas Tambahan (grouped by jenis seperti Master) -->
            <div v-if="paketUmrahDisplay.fasilitas_tambahan?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Fasilitas Tambahan</div>
              <div class="space-y-2 text-sm">
                <div v-for="(g, gi) in paketUmrahDisplay.fasilitas_tambahan" :key="gi">
                  <div class="font-medium text-gray-700">{{ g.nama_jenis }}</div>
                  <ul class="list-disc pl-5 mt-0.5 text-gray-600">
                    <li v-for="(f, fi) in (g.list || [])" :key="fi">{{ f.nama }}</li>
                  </ul>
                </div>
              </div>
            </div>
            <div v-else-if="transactionDetail.snapshot_produk?.fasilitas_tambahan?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Fasilitas Tambahan</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(f, i) in transactionDetail.snapshot_produk.fasilitas_tambahan" :key="i">{{ f.nama_fasilitas }}</li>
              </ul>
            </div>
          </template>

          <!-- ========== PENDAFTARAN HAJI ========== -->
          <template v-else-if="detailType === 'haji'">
            <div class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Snapshot Paket Haji</div>
              <div class="space-y-1 text-sm text-gray-700">
                <p><strong>Nama Paket:</strong> {{ transactionDetail.snapshot_produk.nama_paket || '-' }}</p>
                <p v-if="transactionDetail.snapshot_produk.biaya_per_pax != null"><strong>Biaya per Pax:</strong> {{ H.formatRupiah(transactionDetail.snapshot_produk.biaya_per_pax) }}</p>
                <p v-if="transactionDetail.snapshot_produk.waktu_tunggu_min != null"><strong>Waktu Tunggu:</strong> {{ transactionDetail.snapshot_produk.waktu_tunggu_min }} - {{ transactionDetail.snapshot_produk.waktu_tunggu_max }} tahun</p>
                <p v-if="transactionDetail.snapshot_produk.deskripsi_akomodasi"><strong>Deskripsi Akomodasi:</strong> {{ transactionDetail.snapshot_produk.deskripsi_akomodasi }}</p>
                <p v-if="transactionDetail.snapshot_produk.deskripsi_fasilitas"><strong>Deskripsi Fasilitas:</strong> {{ transactionDetail.snapshot_produk.deskripsi_fasilitas }}</p>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.akomodasi?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Akomodasi</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(a, i) in transactionDetail.snapshot_produk.akomodasi" :key="i">
                  <strong>{{ a.kota }}:</strong> {{ a.nama_hotel }} (rating: {{ a.rating_hotel }}) — {{ a.jarak_ke_masjid }}
                </li>
              </ul>
            </div>
            <div v-if="transactionDetail.snapshot_produk.fasilitas_tambahan?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Fasilitas Tambahan</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(f, i) in transactionDetail.snapshot_produk.fasilitas_tambahan" :key="i">{{ f.nama_fasilitas }}</li>
              </ul>
            </div>
          </template>

          <!-- ========== PEMINAT EDUTRIP ========== -->
          <template v-else-if="detailType === 'edutrip'">
            <div class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Snapshot Edutrip</div>
              <div class="space-y-1 text-sm text-gray-700">
                <p><strong>Nama Paket:</strong> {{ transactionDetail.snapshot_produk.nama_paket || '-' }}</p>
                <p v-if="transactionDetail.snapshot_produk.deskripsi"><strong>Deskripsi:</strong> {{ transactionDetail.snapshot_produk.deskripsi }}</p>
                <p><strong>Jumlah Hari:</strong> {{ transactionDetail.snapshot_produk.jumlah_hari ?? '-' }} Hari</p>
                <p><strong>Jadwal Pertemuan:</strong> {{ transactionDetail.snapshot_produk.tanggal_kunjungan ? H.formatDate(transactionDetail.snapshot_produk.tanggal_kunjungan, 'DD/MM/YYYY') : '-' }} {{ transactionDetail.snapshot_produk.jam_kunjungan ?? '' }}</p>
                <p v-if="transactionDetail.snapshot_produk.deskripsi_fasilitas"><strong>Deskripsi Fasilitas:</strong> {{ transactionDetail.snapshot_produk.deskripsi_fasilitas }}</p>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.akomodasi?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Akomodasi</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(a, i) in transactionDetail.snapshot_produk.akomodasi" :key="i">
                  <strong>{{ a.kota }}:</strong> {{ a.nama_hotel }} (rating: {{ a.rating_hotel }}) — {{ a.jarak_ke_masjid }}
                </li>
              </ul>
            </div>
            <div v-if="transactionDetail.snapshot_produk.fasilitas_tambahan?.length" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Fasilitas Tambahan</div>
              <ul class="list-disc pl-5 text-sm space-y-1">
                <li v-for="(f, i) in transactionDetail.snapshot_produk.fasilitas_tambahan" :key="i">{{ f.nama_fasilitas }}</li>
              </ul>
            </div>
          </template>

          <!-- Informasi Jemaah (untuk semua tipe yang punya jamaah_data) -->
          <div v-if="transactionDetail.jamaah_data && transactionDetail.jamaah_data.length" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-users text-teal-600"></i>
              Informasi Jemaah ({{ transactionDetail.jamaah_data.length }} orang)
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(j, i) in transactionDetail.jamaah_data" :key="i" class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                  <span class="flex items-center justify-center w-8 h-8 rounded-full bg-teal-100 text-teal-700 text-sm font-semibold">{{ i + 1 }}</span>
                  <span class="font-semibold text-gray-900">{{ j.nama || 'Nama belum diisi' }}</span>
                </div>
                <dl class="space-y-2 text-sm">
                  <div class="flex justify-between gap-2"><dt class="text-gray-500 shrink-0">NIK</dt><dd class="text-gray-900 font-medium text-right break-all">{{ j.nik || '–' }}</dd></div>
                  <div class="flex justify-between gap-2"><dt class="text-gray-500 shrink-0">No. Paspor</dt><dd class="text-gray-900 font-medium text-right break-all">{{ j.no_paspor ?? j.noPaspor ?? '–' }}</dd></div>
                </dl>
                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-2">
                  <button v-if="j.dokumen_ktp_id" type="button" @click="openPreviewDoc(j.dokumen_ktp_id, `${j.nama || 'Jemaah'} - KTP`)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200">
                    <i class="fas fa-id-card text-amber-600"></i> Preview KTP
                  </button>
                  <button v-if="j.dokumen_paspor_id" type="button" @click="openPreviewDoc(j.dokumen_paspor_id, `${j.nama || 'Jemaah'} - Paspor`)" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-sky-50 text-sky-800 hover:bg-sky-100 border border-sky-200">
                    <i class="fas fa-passport text-sky-600"></i> Preview Paspor
                  </button>
                  <span v-if="!(j.dokumen_ktp_id || j.dokumen_paspor_id)" class="text-xs text-gray-400">Tidak ada dokumen diunggah</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bukti Pembayaran (untuk semua tipe) -->
          <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <i class="fas fa-receipt text-teal-600"></i>
              Bukti Pembayaran
            </div>
            <div v-if="listPembayaran.length" class="space-y-3">
              <div
                v-for="(p, idx) in listPembayaran"
                :key="p.id || idx"
                class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-gray-200 last:border-0"
              >
                <div class="text-sm text-gray-700">
                  <span class="font-medium">{{ H.formatRupiah(p.nominal_transfer) }}</span>
                  <span v-if="p.bank_tujuan" class="text-gray-500"> Â· {{ p.bank_tujuan }}</span>
                  <span class="ml-2 px-1.5 py-0.5 rounded text-xs" :class="p.status === 'verified' ? 'bg-green-100 text-green-800' : p.status === 'rejected' ? 'bg-red-100 text-red-800' : p.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600'">{{ p.status }}</span>
                  <div class="text-xs text-gray-500 mt-0.5">{{ H.formatDate(p.created_at, 'DD/MM/YYYY HH:mm') }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <button
                    v-if="p.bukti_pembayaran"
                    type="button"
                    @click="openPreviewBukti(p.bukti_pembayaran, `Bukti ${H.formatRupiah(p.nominal_transfer)}`)"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200"
                  >
                    <i class="fas fa-file-image text-teal-600"></i>
                    Preview Bukti
                  </button>
                  <template v-if="p.status === 'pending'">
                    <button
                      type="button"
                      :disabled="verifikasiLoading === p.id"
                      @click="verifikasiBukti(p.id, 'verified')"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-green-50 text-green-800 hover:bg-green-100 border border-green-200 disabled:opacity-50"
                    >
                      <i v-if="verifikasiLoading === p.id" class="fas fa-spinner fa-spin"></i>
                      <i v-else class="fas fa-check"></i>
                      Setujui
                    </button>
                    <button
                      type="button"
                      :disabled="verifikasiLoading === p.id"
                      @click="verifikasiBukti(p.id, 'rejected')"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md bg-red-50 text-red-800 hover:bg-red-100 border border-red-200 disabled:opacity-50"
                    >
                      <i v-if="verifikasiLoading === p.id" class="fas fa-spinner fa-spin"></i>
                      <i v-else class="fas fa-times"></i>
                      Tolak
                    </button>
                  </template>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500 py-2">
              Belum ada bukti pembayaran diunggah untuk pesanan ini. Customer dapat mengunggah bukti dari halaman <strong>Pesanan Saya â†’ Detail Pesanan â†’ Bayar Sekarang</strong>.
            </p>
            <!-- Sisa Pembayaran: tampil hanya setelah ada bukti yang di-acc (verified) -->
            <div v-if="showSisaPembayaran" class="mt-4 pt-4 border-t border-gray-200">
              <div class="text-sm text-gray-600 mb-1">Total sudah diverifikasi</div>
              <div class="font-semibold text-gray-900">{{ H.formatRupiah(totalTerbayarVerified) }}</div>
              <div class="text-sm text-gray-600 mt-2 mb-1">Sisa pembayaran</div>
              <div class="font-semibold" :class="sisaPembayaran <= 0 ? 'text-green-700' : 'text-amber-700'">
                {{ sisaPembayaran <= 0 ? 'Lunas' : H.formatRupiah(sisaPembayaran) }}
              </div>
            </div>
          </div>

        </div>
      </div>
    </Card>

    <!-- Modal Preview Dokumen (KTP / Paspor) -->
    <Modal
      :open="previewDocOpen"
      size="medium"
      rounded
      noscroll
      :closeOnEsc="true"
      :closeOnOutside="true"
      actions="right"
      cancelLabel="Tutup"
      :showAction="false"
      @close="closePreviewDoc"
    >
      <template #header>
        <div class="w-full flex items-center">
          <h3 class="font-semibold">{{ previewDocTitle }}</h3>
          <button class="ml-auto text-gray-500 hover:text-red-500" @click="closePreviewDoc">âœ•</button>
        </div>
      </template>
      <template #content>
        <div class="flex justify-center items-start bg-gray-100 rounded-lg p-2 min-h-[200px]">
          <img
            v-if="previewDocUrl && previewDocIsImage"
            :src="previewDocUrl"
            alt="Preview dokumen"
            class="max-w-full max-h-[70vh] object-contain rounded"
          />
          <iframe
            v-else-if="previewDocUrl && !previewDocIsImage"
            :src="previewDocUrl"
            class="w-full h-[70vh] rounded border-0"
            title="Preview dokumen"
          />
          <p v-else-if="previewDocLoading" class="text-gray-500 py-8">Memuat...</p>
          <p v-else-if="previewDocError" class="text-red-600 py-4">{{ previewDocError }}</p>
        </div>
      </template>
    </Modal>

    <!-- Modal Update Status -->
    <Modal
      :open="ModalUpdateStatus"
      size="small"
      rounded
      noscroll
      :closeOnEsc="true"
      :closeOnOutside="false"
      actions="right"
      cancelLabel="Tutup"
      actionLabel="Simpan"
      :showAction="true"
      @close="ModalUpdateStatus = false"
      @action="handleUpdate"
    >
      <template #header>
        <div class="w-full flex items-center">
          <h3 class="font-semibold">Update Status</h3>
          <button class="ml-auto text-gray-500 hover:text-red-500" @click="ModalUpdateStatus = false">âœ•</button>
        </div>
      </template>
      <template #content>
        <div class="column is-12 text-center">
          <label for="status-transaksi">Status Transaksi</label>
          <AutoComplete
            id="status-transaksi"
            v-model="selectedStatusTransaksi"
            :suggestions="d_StatusTransaksi"
            @complete="fetchStatusTransaksi($event)"
            optionLabel="label"
            :dropdown="true"
            :minLength="0"
            :forceSelection="true"
            appendTo="body"
            field="label"
            placeholder="Pilih Status Transaksi..."
            class="w-full"
          />
        </div>
      </template>
    </Modal>

    <!-- Modal Update Status Pembayaran (Lunas, Belum Bayar, dll) -->
    <Modal
      :open="ModalUpdateStatusPembayaran"
      size="small"
      rounded
      noscroll
      :closeOnEsc="true"
      :closeOnOutside="false"
      actions="right"
      cancelLabel="Tutup"
      actionLabel="Simpan"
      :showAction="true"
      @close="ModalUpdateStatusPembayaran = false"
      @action="handleUpdatePembayaran"
    >
      <template #header>
        <div class="w-full flex items-center">
          <h3 class="font-semibold">Update Status Pembayaran</h3>
          <button class="ml-auto text-gray-500 hover:text-red-500" @click="ModalUpdateStatusPembayaran = false">✕</button>
        </div>
      </template>
      <template #content>
        <div class="column is-12 text-center">
          <label for="status-pembayaran">Status Pembayaran</label>
          <AutoComplete
            id="status-pembayaran"
            v-model="selectedStatusPembayaran"
            :suggestions="d_StatusPembayaran"
            @complete="fetchStatusPembayaran($event)"
            optionLabel="label"
            :dropdown="true"
            :minLength="0"
            appendTo="body"
            field="label"
            placeholder="Pilih (Belum Bayar, Lunas, dll)..."
            class="w-full"
          />
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/api/useApi'
import H from '@/utils/appHelper'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Modal from '@/components/base/modal/Modal.vue'
import AutoComplete from 'primevue/autocomplete'

const route = useRoute()
const router = useRouter()
const api = useApi()

const id = computed(() => route.params.id as string)
/** Tipe detail: custom | umrah | haji | edutrip — isi konten disesuaikan per tipe */
const detailType = computed(() => (route.meta.detailType as string) || 'custom')
const backPath = computed(() => (route.meta.backPath as string) || '/Daftar-Transaksi/Permintaan-Custom')
const pageTitle = computed(() => (route.meta.title as string) || 'Detail Transaksi')
const tipePaketLabel = computed(() => {
  const t = detailType.value
  if (t === 'umrah') return 'Paket Umrah'
  if (t === 'haji') return 'Pendaftaran Haji'
  if (t === 'edutrip') return 'Peminat Edutrip'
  return 'Land Arrangement Custom'
})
/** Untuk tampilan Umrah: jadwal keberangkatan dari snapshot */
const jadwalKeberangkatanDisplay = computed(() => {
  const snap = transactionDetail.value?.snapshot_produk
  if (!snap) return '-'
  if (snap.tanggal_kunjungan) {
    const tgl = H.formatDate(snap.tanggal_kunjungan, 'DD/MM/YYYY')
    return snap.jam_kunjungan ? `${tgl} ${snap.jam_kunjungan}` : tgl
  }
  const kb = snap.keberangkatan
  if (kb?.tanggal_berangkat || kb?.tanggal_pulang) {
    const berangkat = kb.tanggal_berangkat ? H.formatDate(kb.tanggal_berangkat, 'DD/MM/YYYY') + (kb.jam_berangkat ? ` ${kb.jam_berangkat}` : '') : '-'
    const pulang = kb.tanggal_pulang ? H.formatDate(kb.tanggal_pulang, 'DD/MM/YYYY') + (kb.jam_pulang ? ` ${kb.jam_pulang}` : '') : '-'
    return `Berangkat: ${berangkat} — Pulang: ${pulang}`
  }
  return '-'
})
const transactionDetail = ref<any>(null)
const loading = ref(true)
const error = ref('')
/** ID pembayaran yang sedang di-verifikasi (loading) */
const verifikasiLoading = ref<number | null>(null)
const ModalUpdateStatus = ref(false)
const ModalUpdateStatusPembayaran = ref(false)
const selectedStatusTransaksi = ref<any>(null)
const selectedStatusPembayaran = ref<any>(null)
const d_StatusTransaksi = ref<any[]>([])
const d_StatusPembayaran = ref<any[]>([])
const layananItemsMap = ref<Record<number, { nama: string; harga?: number; satuan?: string; jenis?: string }>>({})

// Preview dokumen (KTP / Paspor)
const previewDocOpen = ref(false)
const previewDocTitle = ref('')
const previewDocUrl = ref<string | null>(null)
const previewDocIsImage = ref(true)
const previewDocLoading = ref(false)
const previewDocError = ref('')
let previewDocObjectUrl: string | null = null
/** Daftar layanan wajib dari API (untuk order lama yang snapshot belum punya layanan_wajib) */
const layananWajibList = ref<Array<{ id: number; nama: string; harga: number; satuan?: string }>>([])
/** Detail paket umrah dari API get-paket-umrah/{id} (agar tampilan sama dengan Master-Umrah) */
const paketUmrahDetail = ref<any>(null)

/** Tampilan detail paket umrah: dari API paketUmrahDetail atau fallback snapshot kosong */
const paketUmrahDisplay = computed(() => {
  const raw = paketUmrahDetail.value
  const snap = transactionDetail.value?.snapshot_produk
  if (!raw) {
    return {
      nama_paket: snap?.nama_paket,
      musim: null,
      harga_termurah: snap?.harga_per_pax != null ? Number(snap.harga_per_pax) : null,
      harga_termahal: null,
      bintang: null,
      durasi_total: snap?.durasi_total ?? snap?.jumlah_hari,
      lokasi_keberangkatan: null,
      lokasi_tujuan: null,
      destinasi: null,
      hotel: null,
      maskapai_names: null,
      kelas_penerbangan: null,
      fasilitas_tambahan: null,
      deskripsi: snap?.deskripsi,
    }
  }
  const maskapaiList = Array.isArray(raw.maskapai) ? raw.maskapai : []
  const maskapaiNames = maskapaiList.map((m: any) => m.nama_maskapai).filter(Boolean).join(', ') || null
  const kelas = (raw.keberangkatan && raw.keberangkatan[0]?.kelas_penerbangan) ?? (raw.tipe_paket_umrah && raw.tipe_paket_umrah[0]?.kelas_penerbangan) ?? null
  return {
    nama_paket: raw.nama_paket,
    musim: raw.musim ?? null,
    harga_termurah: raw.harga_termurah != null ? Number(raw.harga_termurah) : null,
    harga_termahal: raw.harga_termahal != null ? Number(raw.harga_termahal) : null,
    bintang: raw.bintang ?? null,
    durasi_total: raw.durasi_total ?? null,
    lokasi_keberangkatan: raw.lokasi_keberangkatan ?? null,
    lokasi_tujuan: raw.lokasi_tujuan ?? null,
    destinasi: Array.isArray(raw.destinasi) ? raw.destinasi.map((d: any) => ({ nama_kota: d.nama_kota, durasi: d.durasi })) : null,
    hotel: Array.isArray(raw.hotel) ? raw.hotel : null,
    maskapai_names: maskapaiNames,
    kelas_penerbangan: kelas ?? null,
    fasilitas_tambahan: Array.isArray(raw.fasilitas_tambahan) ? raw.fasilitas_tambahan : null,
    deskripsi: raw.deskripsi ?? null,
  }
})

const dh = computed(() => transactionDetail.value?.snapshot_produk?.data_hotel ?? {})
/** Data hotel hanya ditampilkan jika ada nama hotel atau detail kamar */
const hasDataHotel = computed(() => {
  const d = dh.value
  if (!d || typeof d !== 'object') return false
  const hasName = Boolean((d as any).hotelMekkah ?? (d as any).hotel_mekkah ?? (d as any).hotelMadinah ?? (d as any).hotel_madinah)
  const rd = (d as any).room_details
  const hasRooms = Array.isArray(rd) && rd.length > 0
  return hasName || hasRooms
})
/** Tanggal program hanya ditampilkan jika ada tanggal check-in/check-out */
const hasTanggalProgram = computed(() => {
  const tp = tanggalProgram.value
  if (!tp || typeof tp !== 'object') return false
  const dep = (tp as any).departureDate ?? (tp as any).departure_date
  const ret = (tp as any).returnDate ?? (tp as any).return_date
  return (dep != null && String(dep).trim() !== '') || (ret != null && String(ret).trim() !== '')
})
/** Data keberangkatan hanya ditampilkan jika ada isi (komponen hotel biasanya kosong) */
const dataKeberangkatan = computed(() => transactionDetail.value?.snapshot_produk?.data_keberangkatan ?? null)
const hasDataKeberangkatan = computed(() => {
  const d = dataKeberangkatan.value
  if (!d || typeof d !== 'object') return false
  const v = (key: string) => (d as Record<string, unknown>)[key]
  const maskapai = (v('namaMaskapai') ?? v('nama_maskapai')) as string
  const bandaraBerangkat = (v('bandaraKeberangkatan') ?? v('bandara_keberangkatan')) as string
  const bandaraPulang = (v('bandaraKepulangan') ?? v('bandara_kepulangan')) as string
  return [maskapai, bandaraBerangkat, bandaraPulang].some((s) => s != null && String(s).trim() !== '')
})
/** Detail kamar per tipe (Komponen Hotel): untuk tabel di Data Hotel & rincian biaya */
const roomDetails = computed(() => {
  const d = dh.value
  const rd = d?.room_details
  return Array.isArray(rd) ? rd : []
})
const jamaahCount = computed(() => Math.max(transactionDetail.value?.jamaah_data?.length ?? 1, 1))
const tanggalProgram = computed(() => transactionDetail.value?.snapshot_produk?.tanggal_program_umrah ?? null)
const listPembayaran = computed(() => {
  const p = transactionDetail.value?.pembayaran
  return Array.isArray(p) ? p : []
})
/** Total nominal pembayaran yang sudah di-acc admin (status verified) */
const totalTerbayarVerified = computed(() => {
  return listPembayaran.value
    .filter((p: any) => p.status === 'verified')
    .reduce((sum: number, p: any) => sum + (Number(p.nominal_transfer) || 0), 0)
})
/** Total biaya pesanan */
const totalBiayaOrder = computed(() => {
  const v = transactionDetail.value?.total_biaya
  return v != null && v !== '' ? Number(String(v).replace(/[^0-9.-]/g, '')) : 0
})
/** Sisa pembayaran (total biaya - yang sudah verified); minimal 0 */
const sisaPembayaran = computed(() => Math.max(0, totalBiayaOrder.value - totalTerbayarVerified.value))
/** Tampilkan blok Sisa Pembayaran hanya bila ada minimal satu bukti yang sudah di-acc */
const showSisaPembayaran = computed(() => totalTerbayarVerified.value > 0)

const durasiHari = computed(() => {
  const tp = tanggalProgram.value
  if (!tp?.departureDate || !tp?.returnDate) return 0
  const dep = new Date(tp.departureDate).getTime()
  const ret = new Date(tp.returnDate).getTime()
  return Math.max(0, Math.round((ret - dep) / (24 * 60 * 60 * 1000))) || 9
})
/** Untuk tampilan tabel kamar & rincian: minimal 1 agar subtotal tidak 0 saat tanggal belum ada */
const durasiHariDisplay = computed(() => Math.max(durasiHari.value, 1))

const rincianBiaya = computed(() => {
  const data = dh.value
  if (!data) return []
  const parse = (v: any) => (v != null && v !== '' ? Number(String(v).replace(/[^0-9.-]/g, '')) : 0)
  const hari = Math.max(durasiHari.value, 1)
  const out: { label: string; value: number }[] = []
  const rd = roomDetails.value
  if (rd.length > 0) {
    const hotelName = (data.hotelMekkah ?? data.hotel_mekkah ?? data.hotelMadinah ?? data.hotel_madinah ?? 'Hotel') as string
    rd.forEach((r: any) => {
      const qty = Math.max(Number(r.qty) || 0, 0)
      const harga = parse(r.hargaPerMalam ?? r.harga_per_malam)
      if (qty > 0 && harga > 0) {
        const label = (r.roomTypeName ?? r.room_type_name) ? `${(r.roomTypeName ?? r.room_type_name)} (${hotelName})` : hotelName
        out.push({ label, value: harga * qty * hari })
      }
    })
  } else {
    const hMekkah = parse(data.hotelMekkahHarga ?? data.hotel_mekkah_harga)
    const hMadinah = parse(data.hotelMadinahHarga ?? data.hotel_madinah_harga)
    const kamar = Math.max(parse(data.kuotaKamar ?? data.kuota_kamar) || 1, 1)
    if (hMekkah > 0) {
      const nama = (data.hotelMekkah ?? data.hotel_mekkah) || 'Hotel Mekkah'
      out.push({ label: nama, value: hMekkah * kamar * hari })
    }
    if (hMadinah > 0) {
      const nama = (data.hotelMadinah ?? data.hotel_madinah) || 'Hotel Madinah'
      out.push({ label: nama, value: hMadinah * kamar * hari })
    }
  }
  return out
})

/** Nilai per item layanan: per-pax/per-orang hanya Ã— jemaah; hanya /hari yang Ã— hari */
function toLayananValue(item: { harga?: number | string; satuan?: string | null }, jamaah: number, hari: number): number {
  const base = item.harga != null && item.harga !== '' ? Number(String(item.harga).replace(/[^0-9.-]/g, '')) : 0
  if (!base || base <= 0) return 0
  const satuan = (item.satuan ?? '').toLowerCase()
  const perPax = /pax|orang/.test(satuan)
  const perHari = /hari/.test(satuan)
  const mult = perPax ? jamaah : perHari ? hari : 1
  return base * mult
}

/** Label multiplier untuk tampilan: "3 orang" atau "9 hari" */
function getMultLabel(satuan: string | undefined, jamaah: number, hari: number): string {
  const s = (satuan ?? '').toLowerCase()
  const perPax = /pax|orang/.test(s)
  const perHari = /hari/.test(s)
  return perPax ? `${jamaah} orang` : perHari ? `${hari} hari` : '1'
}

/** Transaksi komponen (Komponen Hotel, dll): layanan wajib tidak dipakai, hanya komponen saja */
const isTransaksiKomponen = computed(() => {
  const k = transactionDetail.value?.snapshot_produk?.kategori_paket
  return typeof k === 'string' && (k.startsWith('Komponen') || k === 'Komponen Hotel')
})

/** Layanan wajib: dari snapshot (order baru) atau dari list API (order lama). Untuk transaksi Komponen tidak tampil. */
const layananWajibItems = computed(() => {
  const snap = transactionDetail.value?.snapshot_produk
  const jamaah = jamaahCount.value
  const hari = durasiHari.value
  if (isTransaksiKomponen.value) return []
  const fromSnap = Array.isArray(snap?.layanan_wajib) && snap.layanan_wajib.length > 0 ? snap.layanan_wajib : null
  if (fromSnap && fromSnap.length > 0) {
    return fromSnap.map((x: any) => {
      const value = toLayananValue(x, jamaah, hari)
      return {
        id: x.id,
        nama: x.nama ?? '',
        harga: Number(x.harga) || 0,
        satuan: x.satuan ?? '',
        value,
        multLabel: getMultLabel(x.satuan, jamaah, hari),
      }
    })
  }
  return layananWajibList.value.map((x) => {
    const value = toLayananValue(x, jamaah, hari)
    return { ...x, value, multLabel: getMultLabel(x.satuan, jamaah, hari) }
  })
})

const totalLayananWajib = computed(() =>
  layananWajibItems.value.reduce((sum: number, item: { value?: number }) => sum + (item.value ?? 0), 0)
)

const totalBiaya = computed(() => {
  const t = transactionDetail.value?.total_biaya
  const num = t != null && t !== '' ? Number(String(t).replace(/[^0-9.-]/g, '')) : 0
  if (num > 0) return num
  const dariHotel = rincianBiaya.value.reduce((sum, item) => sum + item.value, 0)
  return dariHotel + totalLayananWajib.value + totalLayananTambahan.value
})

const layananTambahanIds = computed(() => {
  const snap = transactionDetail.value?.snapshot_produk
  if (!snap) return []
  const ids = snap.layanan_tambahan_ids ?? snap.layananTambahanIds
  return Array.isArray(ids) ? ids.filter((id: any) => id != null && id !== '') : []
})

const layananTambahanItems = computed(() => {
  const ids = layananTambahanIds.value
  const map = layananItemsMap.value
  const jamaah = jamaahCount.value
  const hari = durasiHari.value
  return ids.map((id: number) => {
    const item = map[id]
    const nama = item?.nama ?? `Layanan #${id}`
    const harga = item?.harga != null ? Number(item.harga) : 0
    const satuan = item?.satuan?.trim() || '/pax'
    const value = toLayananValue({ harga, satuan }, jamaah, hari)
    return { id, nama, harga, satuan, value, multLabel: getMultLabel(satuan, jamaah, hari) }
  })
})

const totalLayananTambahan = computed(() =>
  layananTambahanItems.value.reduce((sum, item) => sum + (item.value ?? 0), 0)
)

const fetchDetail = async () => {
  if (!id.value) return
  loading.value = true
  error.value = ''
  paketUmrahDetail.value = null
  try {
    const res = await api.get(`/account/orders/${id.value}`, { params: { scope: 'admin' } })
    const data = (res as any)?.data
    transactionDetail.value = data ?? null
    if (!transactionDetail.value) {
      error.value = 'Pesanan tidak ditemukan.'
    } else {
      fetchLayananMapOnce()
      if (detailType.value === 'umrah') {
        const paketId = transactionDetail.value?.snapshot_produk?.id
        if (paketId) await fetchPaketUmrahDetail(paketId)
      }
    }
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'Gagal memuat detail pesanan.'
    transactionDetail.value = null
  } finally {
    loading.value = false
  }
}

/** Ambil detail paket umrah dari API (struktur sama dengan Master-Umrah) */
async function fetchPaketUmrahDetail(paketId: number | string) {
  try {
    const res = await api.get(`/sistem-admin/paket-umrah/get-paket-umrah/${paketId}`)
    const data = (res as any)?.data
    paketUmrahDetail.value = data ?? null
  } catch {
    paketUmrahDetail.value = null
  }
}

/** Verifikasi bukti pembayaran: Setujui (verified) atau Tolak (rejected) */
const verifikasiBukti = async (pembayaranId: number, status: 'verified' | 'rejected') => {
  verifikasiLoading.value = pembayaranId
  try {
    await api.post('/sistem-admin/transaksi/verifikasi-bukti-pembayaran', {
      pembayaran_id: pembayaranId,
      status,
    })
    await fetchDetail()
  } catch (e: any) {
    alert(e?.response?.data?.message ?? 'Gagal verifikasi bukti pembayaran.')
  } finally {
    verifikasiLoading.value = null
  }
}

const fetchLayananMapOnce = async () => {
  if (Object.keys(layananItemsMap.value).length > 0) return
  try {
    const res = await api.get('/sistem-admin/layanan-paket-request/get-list')
    const data = (res as any)?.data
    const items = Array.isArray(data?.items) ? data.items : []
    const map: Record<number, { nama: string; harga?: number; satuan?: string; jenis?: string }> = {}
    const wajibList: Array<{ id: number; nama: string; harga: number; satuan?: string }> = []
    items.forEach((x: any) => {
      if (x.id == null) return
      const entry = { nama: x.nama ?? `#${x.id}`, harga: x.harga, satuan: x.satuan, jenis: x.jenis }
      map[Number(x.id)] = entry
      if (x.jenis === 'wajib') {
        wajibList.push({
          id: x.id,
          nama: entry.nama,
          harga: Number(entry.harga) || 0,
          satuan: entry.satuan,
        })
      }
    })
    layananItemsMap.value = map
    layananWajibList.value = wajibList
  } catch {
    layananItemsMap.value = {}
    layananWajibList.value = []
  }
}

const openUpdateStatus = () => {
  selectedStatusTransaksi.value = null
  ModalUpdateStatus.value = true
  fetchStatusTransaksi({ query: '' })
}

const openUpdateStatusPembayaran = () => {
  selectedStatusPembayaran.value = null
  ModalUpdateStatusPembayaran.value = true
  fetchStatusPembayaran({ query: '' })
}

const fetchStatusPembayaran = async (filter: any) => {
  try {
    const q = (filter?.query ?? '') || ''
    const response = await api.get(
      `/sistem-admin/utility/dropdown/status_pembayaran_m?select=id,nama_status&param_search=nama_status&query=${encodeURIComponent(q)}&limit=20`
    )
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    const arr = Array.isArray(raw) ? raw : []
    d_StatusPembayaran.value = arr.map((x: any) => ({
      id: x.value ?? x.id,
      label: x.label ?? x.nama_status ?? `#${x.value ?? x.id}`,
      value: x.value ?? x.id,
    }))
  } catch {
    d_StatusPembayaran.value = []
  }
}

const handleUpdatePembayaran = async () => {
  if (!selectedStatusPembayaran.value || !transactionDetail.value?.id) {
    alert('Pilih status pembayaran (mis. Lunas) terlebih dahulu.')
    return
  }
  const statusId = selectedStatusPembayaran.value?.id ?? selectedStatusPembayaran.value?.value
  if (!statusId) {
    alert('Status pembayaran tidak valid.')
    return
  }
  try {
    const res = await api.post('/sistem-admin/transaksi/update-status-pembayaran', {
      id: transactionDetail.value.id,
      status_pembayaran_id: Number(statusId),
    })
    const body = res as { status?: boolean; message?: string }
    if (body?.status === true) {
      ModalUpdateStatusPembayaran.value = false
      selectedStatusPembayaran.value = null
      await fetchDetail()
      alert('Status pembayaran berhasil diubah.')
    } else {
      alert(body?.message ?? 'Update status pembayaran gagal.')
    }
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal update status pembayaran.')
  }
}

const fetchStatusTransaksi = async (filter: any) => {
  try {
    const q = (filter?.query ?? '') || ''
    const response = await api.get(
      `/sistem-admin/utility/dropdown/status_transaksi_m?select=id,nama_status&param_search=nama_status&query=${encodeURIComponent(q)}&limit=10`
    )
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    d_StatusTransaksi.value = (Array.isArray(raw) ? raw : []).map((x: any) => ({
      id: x.value ?? x.id,
      label: x.nama_status ?? x.label ?? `#${x.value ?? x.id}`,
    }))
  } catch {
    d_StatusTransaksi.value = []
  }
}

const handleUpdate = async () => {
  if (!selectedStatusTransaksi.value || !transactionDetail.value?.id) {
    alert('Pilih status terlebih dahulu.')
    return
  }
  const statusId = selectedStatusTransaksi.value?.id ?? selectedStatusTransaksi.value?.value
  if (!statusId) {
    alert('Status tidak valid.')
    return
  }
  try {
    await api.post('/sistem-admin/transaksi/update-status-transaksi', {
      id: transactionDetail.value.id,
      status_id: statusId,
    })
    ModalUpdateStatus.value = false
    selectedStatusTransaksi.value = null
    fetchDetail()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal update status.')
  }
}

async function openPreviewDoc(dokumenId: number, title: string) {
  previewDocOpen.value = true
  previewDocTitle.value = title
  previewDocUrl.value = null
  previewDocError.value = ''
  previewDocLoading.value = true
  if (previewDocObjectUrl) {
    URL.revokeObjectURL(previewDocObjectUrl)
    previewDocObjectUrl = null
  }
  try {
    const res = await api.get(`/dokumen/${dokumenId}/preview`, { responseType: 'blob' }) as { data: Blob }
    const blob = res?.data
    if (!blob) {
      previewDocError.value = 'Dokumen tidak dapat dimuat.'
      return
    }
    previewDocObjectUrl = URL.createObjectURL(blob)
    previewDocUrl.value = previewDocObjectUrl
    previewDocIsImage.value = (blob.type || '').startsWith('image/')
  } catch (e: any) {
    previewDocError.value = e?.response?.data?.message ?? e?.message ?? 'Gagal memuat dokumen.'
  } finally {
    previewDocLoading.value = false
  }
}

async function openPreviewBukti(path: string, title: string) {
  previewDocOpen.value = true
  previewDocTitle.value = title
  previewDocUrl.value = null
  previewDocError.value = ''
  previewDocLoading.value = true
  if (previewDocObjectUrl) {
    URL.revokeObjectURL(previewDocObjectUrl)
    previewDocObjectUrl = null
  }
  try {
    const res = await api.get(`/sistem-admin/transaksi/preview-file?path=${encodeURIComponent(path)}`, { responseType: 'blob' }) as { data: Blob }
    const blob = res?.data
    if (!blob) {
      previewDocError.value = 'Bukti tidak dapat dimuat.'
      return
    }
    previewDocObjectUrl = URL.createObjectURL(blob)
    previewDocUrl.value = previewDocObjectUrl
    previewDocIsImage.value = (blob.type || '').startsWith('image/')
  } catch (e: any) {
    previewDocError.value = e?.response?.data?.message ?? e?.message ?? 'Gagal memuat bukti.'
  } finally {
    previewDocLoading.value = false
  }
}

function closePreviewDoc() {
  previewDocOpen.value = false
  previewDocTitle.value = ''
  previewDocUrl.value = null
  previewDocError.value = ''
  if (previewDocObjectUrl) {
    URL.revokeObjectURL(previewDocObjectUrl)
    previewDocObjectUrl = null
  }
}

watch(id, () => fetchDetail(), { immediate: false })
onMounted(() => fetchDetail())
</script>
