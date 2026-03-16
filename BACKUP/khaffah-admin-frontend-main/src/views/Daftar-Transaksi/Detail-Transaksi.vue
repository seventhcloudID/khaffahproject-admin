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
      <div v-else-if="transactionDetail" class="space-y-4">
        <h1 class="text-xl font-semibold mb-4">{{ pageTitle }}</h1>

        <!-- Info utama -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500">No. Pesanan</div>
            <div class="font-medium">{{ transactionDetail.kode_transaksi }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Tanggal Pembelian</div>
            <div class="font-medium">
              {{ transactionDetail.tgl_pemesanan ? H.formatDate(transactionDetail.tgl_pemesanan, 'DD/MM/YYYY') : '-' }}
              {{ transactionDetail.tgl_pemesanan ? H.formatDate(transactionDetail.tgl_pemesanan, 'HH:mm') + ' WIB' : '' }}
            </div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Pemesan</div>
            <div class="font-medium">{{ transactionDetail.gelar }} {{ transactionDetail.nama_lengkap }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">No. Whatsapp</div>
            <div class="font-medium flex items-center gap-2">
              {{ transactionDetail.no_whatsapp }}
              <a
                v-if="transactionDetail.no_whatsapp"
                :href="`https://wa.me/${String(transactionDetail.no_whatsapp).replace(/^0/, '62').replace(/\D/g, '')}`"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center w-7 h-7 bg-green-500 hover:bg-green-600 rounded-full transition-colors"
                title="Chat via WhatsApp"
              >
                <i class="fab fa-whatsapp text-white text-sm"></i>
              </a>
            </div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Tipe / Nama Paket</div>
            <div class="font-medium">{{ transactionDetail.nama_paket }}</div>
            <div class="text-xs text-gray-500">{{ tipePaketLabel }}</div>
          </div>
          <div v-if="detailType === 'haji' && transactionDetail.alamat_lengkap" class="sm:col-span-2">
            <div class="text-sm text-gray-500">Alamat Lengkap</div>
            <div class="font-medium">{{ transactionDetail.alamat_lengkap }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Status</div>
            <div class="font-medium">{{ transactionDetail.status_nama ?? transactionDetail.nama_status }}</div>
          </div>
          <div v-if="transactionDetail.deskripsi" class="sm:col-span-2">
            <div class="text-sm text-gray-500">Catatan</div>
            <div class="font-medium">{{ transactionDetail.deskripsi }}</div>
          </div>
        </div>

        <div v-if="transactionDetail.snapshot_produk" class="pt-4 border-t space-y-4">
          <!-- ========== CUSTOM (Permintaan Custom / LA) ========== -->
          <template v-if="detailType === 'custom'">
            <div v-if="tanggalProgram" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Tanggal Program</div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm text-gray-700">
                <div>
                  <span class="text-gray-500 block text-xs">Tanggal Keberangkatan</span>
                  <span>{{ H.formatDate(tanggalProgram.departureDate, 'DD/MM/YYYY') || '-' }}</span>
                </div>
                <div>
                  <span class="text-gray-500 block text-xs">Durasi</span>
                  <span>{{ durasiHari }} Hari</span>
                </div>
                <div>
                  <span class="text-gray-500 block text-xs">Tanggal Kepulangan</span>
                  <span>{{ H.formatDate(tanggalProgram.returnDate, 'DD/MM/YYYY') || '-' }}</span>
                </div>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.data_hotel" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Data Hotel</div>
              <div class="space-y-1 text-sm text-gray-700">
                <p>
                  <span class="text-gray-500">Hotel Mekkah:</span>
                  {{ (dh.hotelMekkah ?? dh.hotel_mekkah) || '-' }}
                  <span v-if="(dh.hotelMekkahHarga ?? dh.hotel_mekkah_harga) != null" class="text-gray-600">({{ H.formatRupiah(dh.hotelMekkahHarga ?? dh.hotel_mekkah_harga) }})</span>
                </p>
                <p>
                  <span class="text-gray-500">Hotel Madinah:</span>
                  {{ (dh.hotelMadinah ?? dh.hotel_madinah) || '-' }}
                  <span v-if="(dh.hotelMadinahHarga ?? dh.hotel_madinah_harga) != null" class="text-gray-600">({{ H.formatRupiah(dh.hotelMadinahHarga ?? dh.hotel_madinah_harga) }})</span>
                </p>
                <p><span class="text-gray-500">Kuota Kamar:</span> {{ (dh.kuotaKamar ?? dh.kuota_kamar) != null ? (dh.kuotaKamar ?? dh.kuota_kamar) : '-' }}</p>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.data_keberangkatan" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Data Keberangkatan</div>
              <div class="space-y-1 text-sm text-gray-700">
                <p><span class="text-gray-500">Maskapai:</span> {{ (transactionDetail.snapshot_produk.data_keberangkatan.namaMaskapai ?? transactionDetail.snapshot_produk.data_keberangkatan.nama_maskapai) || '-' }}</p>
                <p><span class="text-gray-500">Bandara Keberangkatan:</span> {{ (transactionDetail.snapshot_produk.data_keberangkatan.bandaraKeberangkatan ?? transactionDetail.snapshot_produk.data_keberangkatan.bandara_keberangkatan) || '-' }}</p>
                <p><span class="text-gray-500">Bandara Kepulangan:</span> {{ (transactionDetail.snapshot_produk.data_keberangkatan.bandaraKepulangan ?? transactionDetail.snapshot_produk.data_keberangkatan.bandara_kepulangan) || '-' }}</p>
              </div>
            </div>
            <div v-if="layananWajibItems.length > 0" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Layanan Wajib</div>
              <div class="space-y-2 text-sm text-gray-700">
                <div v-for="(item, idx) in layananWajibItems" :key="'wajib-' + idx" class="py-2 border-b border-gray-200 last:border-0">
                  <div class="flex justify-between items-center">
                    <span>{{ item.nama }}</span>
                    <span class="font-medium text-gray-900 whitespace-nowrap">{{ H.formatRupiah(item.value) }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">{{ H.formatRupiah(item.harga) }} {{ item.satuan || '/pax' }} × {{ item.multLabel }} = {{ H.formatRupiah(item.value) }}</p>
                </div>
                <div v-if="totalLayananWajib > 0" class="flex justify-between font-semibold pt-2 mt-2 border-t border-gray-200">
                  <span>Subtotal layanan wajib</span>
                  <span>{{ H.formatRupiah(totalLayananWajib) }}</span>
                </div>
              </div>
            </div>
            <div v-if="layananTambahanIds.length > 0" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Layanan Tambahan yang Dipilih</div>
              <div class="space-y-2 text-sm text-gray-700">
                <div v-for="(item, idx) in layananTambahanItems" :key="idx" class="py-2 border-b border-gray-200 last:border-0">
                  <div class="flex justify-between items-center">
                    <span>{{ item.nama }}</span>
                    <span class="font-medium text-gray-900 whitespace-nowrap">{{ H.formatRupiah(item.value) }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">{{ H.formatRupiah(item.harga) }} {{ item.satuan || '/pax' }} × {{ item.multLabel }} = {{ H.formatRupiah(item.value) }}</p>
                </div>
                <div v-if="totalLayananTambahan > 0" class="flex justify-between font-semibold pt-2 mt-2 border-t border-gray-200">
                  <span>Subtotal layanan tambahan</span>
                  <span>{{ H.formatRupiah(totalLayananTambahan) }}</span>
                </div>
              </div>
            </div>
            <div v-if="rincianBiaya.length > 0 || totalLayananWajib > 0 || totalLayananTambahan > 0" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-2">Rincian Biaya</div>
              <div class="space-y-1 text-sm">
                <div v-for="(item, i) in rincianBiaya" :key="'hotel-' + i" class="flex justify-between text-gray-700">
                  <span>{{ item.label }}</span>
                  <span>{{ H.formatRupiah(item.value) }}</span>
                </div>
                <div v-if="totalLayananWajib > 0" class="flex justify-between text-gray-700">
                  <span>Layanan wajib</span>
                  <span>{{ H.formatRupiah(totalLayananWajib) }}</span>
                </div>
                <div v-if="totalLayananTambahan > 0" class="flex justify-between text-gray-700">
                  <span>Layanan tambahan</span>
                  <span>{{ H.formatRupiah(totalLayananTambahan) }}</span>
                </div>
                <div v-if="totalBiaya > 0" class="flex justify-between font-semibold pt-2 border-t border-gray-200 mt-2">
                  <span>Total</span>
                  <span>{{ H.formatRupiah(totalBiaya) }}</span>
                </div>
              </div>
            </div>
            <div v-if="transactionDetail.snapshot_produk.komponen && Object.values(transactionDetail.snapshot_produk.komponen).some((v: any) => v)" class="p-3 bg-gray-50 rounded">
              <div class="font-medium mb-1">Komponen LA</div>
              <div class="flex flex-wrap gap-1">
                <span v-for="(val, key) in transactionDetail.snapshot_produk.komponen" :key="String(key)" v-show="val" class="px-2 py-0.5 bg-teal-100 rounded text-xs">{{ key }}</span>
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

const rincianBiaya = computed(() => {
  const data = dh.value
  if (!data) return []
  const parse = (v: any) => (v != null && v !== '' ? Number(String(v).replace(/[^0-9.-]/g, '')) : 0)
  const hMekkah = parse(data.hotelMekkahHarga ?? data.hotel_mekkah_harga)
  const hMadinah = parse(data.hotelMadinahHarga ?? data.hotel_madinah_harga)
  const kamar = Math.max(parse(data.kuotaKamar ?? data.kuota_kamar) || 1, 1)
  const hari = Math.max(durasiHari.value, 1)
  const out: { label: string; value: number }[] = []
  if (hMekkah > 0) {
    const nama = (data.hotelMekkah ?? data.hotel_mekkah) || 'Hotel Mekkah'
    out.push({ label: nama, value: hMekkah * kamar * hari })
  }
  if (hMadinah > 0) {
    const nama = (data.hotelMadinah ?? data.hotel_madinah) || 'Hotel Madinah'
    out.push({ label: nama, value: hMadinah * kamar * hari })
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

/** Layanan wajib: dari snapshot (order baru) atau dari list API (order lama) */
const layananWajibItems = computed(() => {
  const snap = transactionDetail.value?.snapshot_produk
  const jamaah = jamaahCount.value
  const hari = durasiHari.value
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
    await api.post('/sistem-admin/transaksi/update-status-pembayaran', {
      id: transactionDetail.value.id,
      status_pembayaran_id: statusId,
    })
    ModalUpdateStatusPembayaran.value = false
    selectedStatusPembayaran.value = null
    fetchDetail()
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
