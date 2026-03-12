<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <Button
            icon="fas fa-arrow-left"
            text
            @click="kembali()"
            class="text-gray-600 hover:text-gray-900"
          />
          <h1 class="text-2xl font-semibold">Verifikasi Pendaftaran Mitra</h1>
        </div>
        <div v-if="bisaSetujuiAtauTolak" class="flex gap-2">
          <Button color="danger" @click="tolakPendaftaran()" icon="fas fa-times-circle" outlined>
            Tolak
          </Button>
          <Button color="success" @click="setujuiPendaftaran()" icon="fas fa-check-circle">
            Setujui Pendaftaran
          </Button>
        </div>
        <div v-else-if="mitraData && (mitraData.status_kode === 'disetujui' || mitraData.status_kode === 'ditolak')" class="text-sm text-gray-500">
          Status final ({{ mitraData.status_kode === 'disetujui' ? 'Disetujui' : 'Ditolak' }}) — tidak dapat diubah.
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-8">
        <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
        <p class="mt-2 text-gray-500">Memuat data mitra...</p>
      </div>

      <!-- Content -->
      <div v-else-if="mitraData" class="space-y-6">
        <!-- Informasi Pribadi -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-user text-blue-600"></i>
            Informasi Pribadi
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-gray-500">Nama Lengkap</label>
              <p class="font-medium">{{ mitraData.nama_lengkap || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">NIK</label>
              <p class="font-medium">{{ mitraData.nik || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Alamat -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-map-marker-alt text-blue-600"></i>
            Alamat Domisili
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-gray-500">Provinsi</label>
              <p class="font-medium">{{ mitraData.nama_provinsi || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Kota/Kabupaten</label>
              <p class="font-medium">{{ mitraData.nama_kota || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Kecamatan</label>
              <p class="font-medium">{{ mitraData.nama_kecamatan || '-' }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="text-sm text-gray-500">Alamat Lengkap</label>
              <p class="font-medium">{{ mitraData.alamat_lengkap || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- Informasi Usaha -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-briefcase text-blue-600"></i>
            Informasi Usaha
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-gray-500">Nomor Ijin Usaha</label>
              <p class="font-medium">{{ mitraData.nomor_ijin_usaha || '-' }}</p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Masa Berlaku Ijin</label>
              <p class="font-medium">
                {{
                  mitraData.masa_berlaku_ijin_usaha
                    ? H.formatDate(mitraData.masa_berlaku_ijin_usaha, 'DD/MM/YYYY')
                    : '-'
                }}
              </p>
            </div>
            <div>
              <label class="text-sm text-gray-500">Level Mitra (potongan harga)</label>
              <p class="font-medium">
                {{ mitraData.level_nama ? `${mitraData.level_nama} (${mitraData.level_persen_potongan}%)` : 'Belum diatur' }}
              </p>
            </div>
            <div v-if="mitraData.status_kode === 'disetujui'" class="md:col-span-2 flex flex-wrap items-center gap-3">
              <label class="text-sm text-gray-500 w-full">Ubah level:</label>
              <select
                v-model="editLevelId"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#007b6f] max-w-xs"
              >
                <option :value="null">-- Pilih level --</option>
                <option
                  v-for="opt in levelOptions"
                  :key="opt.id"
                  :value="opt.id"
                >
                  {{ opt.nama_level }} ({{ opt.persen_potongan }}%)
                </option>
              </select>
              <button
                type="button"
                @click="updateLevelMitra()"
                :disabled="savingLevel"
                class="px-4 py-2 bg-[#007b6f] text-white rounded-md hover:bg-[#00665c] disabled:opacity-50 text-sm"
              >
                {{ savingLevel ? 'Menyimpan...' : 'Update Level' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Dokumen Pendukung -->
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i class="fas fa-file-alt text-blue-600"></i>
            Dokumen Pendukung
          </h3>
          <p class="text-sm text-gray-600 mb-4">
            Klik tombol "Preview" untuk melihat dokumen dan berikan status verifikasi
          </p>

          <div class="space-y-4">
            <!-- Dokumen KTP -->
            <div class="border rounded-lg p-4 bg-white">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <i class="fas fa-id-card text-2xl text-blue-600"></i>
                  <div>
                    <p class="font-semibold">Foto KTP</p>
                    <p class="text-xs text-gray-500">Kartu Tanda Penduduk</p>
                  </div>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-700': dokumenStatus.ktp === 'disetujui',
                    'bg-red-100 text-red-700': dokumenStatus.ktp === 'ditolak',
                    'bg-yellow-100 text-yellow-700': dokumenStatus.ktp === 'sedang_direview',
                    'bg-gray-100 text-gray-700': !dokumen.ktp,
                  }"
                >
                  {{
                    dokumen.ktp
                      ? dokumen.ktp.label_status || getStatusLabel(dokumenStatus.ktp)
                      : 'Belum Diupload'
                  }}
                </span>
              </div>
              <div class="flex gap-2">
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ktp"
                  size="small"
                  color="primary"
                  icon="fas fa-eye"
                  @click="lihatDokumen(dokumen.ktp.id, 'KTP')"
                >
                  Preview
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ktp && dokumenStatus.ktp !== 'disetujui'"
                  size="small"
                  color="success"
                  outlined
                  icon="fas fa-check"
                  @click="setDokumenStatus(dokumen.ktp.id, 'disetujui')"
                >
                  Setujui
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ktp && dokumenStatus.ktp !== 'ditolak'"
                  size="small"
                  color="danger"
                  outlined
                  icon="fas fa-times"
                  @click="setDokumenStatus(dokumen.ktp.id, 'ditolak')"
                >
                  Tolak
                </Button>
              </div>
              <div v-if="dokumenStatus.ktp === 'ditolak'" class="mt-3">
                <textarea
                  v-model="dokumenCatatan.ktp"
                  rows="2"
                  class="w-full border border-red-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                  placeholder="Catatan penolakan KTP..."
                ></textarea>
              </div>
              <div v-if="!dokumen.ktp" class="mt-2 text-sm text-gray-500 italic">
                <i class="fas fa-info-circle"></i> Dokumen belum diupload oleh mitra
              </div>
            </div>

            <!-- Dokumen PPIU -->
            <div class="border rounded-lg p-4 bg-white">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <i class="fas fa-file-contract text-2xl text-blue-600"></i>
                  <div>
                    <p class="font-semibold">PPIU</p>
                    <p class="text-xs text-gray-500">Penyelenggara Perjalanan Ibadah Umrah</p>
                  </div>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-700': dokumenStatus.ppiu === 'disetujui',
                    'bg-red-100 text-red-700': dokumenStatus.ppiu === 'ditolak',
                    'bg-yellow-100 text-yellow-700': dokumenStatus.ppiu === 'sedang_direview',
                    'bg-gray-100 text-gray-700': !dokumen.ppiu,
                  }"
                >
                  {{
                    dokumen.ppiu
                      ? dokumen.ppiu.label_status || getStatusLabel(dokumenStatus.ppiu)
                      : 'Belum Diupload'
                  }}
                </span>
              </div>
              <div class="flex gap-2">
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ppiu"
                  size="small"
                  color="primary"
                  icon="fas fa-eye"
                  @click="lihatDokumen(dokumen.ppiu.id, 'PPIU')"
                >
                  Preview
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ppiu && dokumenStatus.ppiu !== 'disetujui'"
                  size="small"
                  color="success"
                  outlined
                  icon="fas fa-check"
                  @click="setDokumenStatus(dokumen.ppiu.id, 'disetujui')"
                >
                  Setujui
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.ppiu && dokumenStatus.ppiu !== 'ditolak'"
                  size="small"
                  color="danger"
                  outlined
                  icon="fas fa-times"
                  @click="setDokumenStatus(dokumen.ppiu.id, 'ditolak')"
                >
                  Tolak
                </Button>
              </div>
              <div v-if="dokumenStatus.ppiu === 'ditolak'" class="mt-3">
                <textarea
                  v-model="dokumenCatatan.ppiu"
                  rows="2"
                  class="w-full border border-red-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                  placeholder="Catatan penolakan PPIU..."
                ></textarea>
              </div>
              <div v-if="!dokumen.ppiu" class="mt-2 text-sm text-gray-500 italic">
                <i class="fas fa-info-circle"></i> Dokumen belum diupload oleh mitra
              </div>
            </div>

            <!-- Dokumen PIHK -->
            <div class="border rounded-lg p-4 bg-white">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                  <i class="fas fa-certificate text-2xl text-blue-600"></i>
                  <div>
                    <p class="font-semibold">PIHK</p>
                    <p class="text-xs text-gray-500">Penyelenggara Ibadah Haji Khusus</p>
                  </div>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-700': dokumenStatus.pihk === 'disetujui',
                    'bg-red-100 text-red-700': dokumenStatus.pihk === 'ditolak',
                    'bg-yellow-100 text-yellow-700': dokumenStatus.pihk === 'sedang_direview',
                    'bg-gray-100 text-gray-700': !dokumen.pihk,
                  }"
                >
                  {{
                    dokumen.pihk
                      ? dokumen.pihk.label_status || getStatusLabel(dokumenStatus.pihk)
                      : 'Belum Diupload'
                  }}
                </span>
              </div>
              <div class="flex gap-2">
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.pihk"
                  size="small"
                  color="primary"
                  icon="fas fa-eye"
                  @click="lihatDokumen(dokumen.pihk.id, 'PIHK')"
                >
                  Preview
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.pihk && dokumenStatus.pihk !== 'disetujui'"
                  size="small"
                  color="success"
                  outlined
                  icon="fas fa-check"
                  @click="setDokumenStatus(dokumen.pihk.id, 'disetujui')"
                >
                  Setujui
                </Button>
                <Button
                  class="py-1 px-1.5"
                  v-if="dokumen.pihk && dokumenStatus.pihk !== 'ditolak'"
                  size="small"
                  color="danger"
                  outlined
                  icon="fas fa-times"
                  @click="setDokumenStatus(dokumen.pihk.id, 'ditolak')"
                >
                  Tolak
                </Button>
              </div>
              <div v-if="dokumenStatus.pihk === 'ditolak'" class="mt-3">
                <textarea
                  v-model="dokumenCatatan.pihk"
                  rows="2"
                  class="w-full border border-red-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                  placeholder="Catatan penolakan PIHK..."
                ></textarea>
              </div>
              <div v-if="!dokumen.pihk" class="mt-2 text-sm text-gray-500 italic">
                <i class="fas fa-info-circle"></i> Dokumen belum diupload oleh mitra
              </div>
            </div>
          </div>

          <!-- Ringkasan Status Dokumen -->
          <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
              <i class="fas fa-info-circle text-blue-600 mt-1"></i>
              <div class="flex-1">
                <p class="font-medium text-blue-900 mb-2">Status Verifikasi Dokumen</p>
                <div class="text-sm text-blue-800 space-y-1">
                  <p>
                    • KTP:
                    <span class="font-medium">{{
                      dokumen.ktp
                        ? dokumen.ktp.label_status || getStatusLabel(dokumenStatus.ktp)
                        : 'Belum Diupload'
                    }}</span>
                  </p>
                  <p>
                    • PPIU:
                    <span class="font-medium">{{
                      dokumen.ppiu
                        ? dokumen.ppiu.label_status || getStatusLabel(dokumenStatus.ppiu)
                        : 'Belum Diupload'
                    }}</span>
                  </p>
                  <p>
                    • PIHK:
                    <span class="font-medium">{{
                      dokumen.pihk
                        ? dokumen.pihk.label_status || getStatusLabel(dokumenStatus.pihk)
                        : 'Belum Diupload'
                    }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="text-center py-8">
        <i class="fas fa-exclamation-triangle text-4xl text-red-400"></i>
        <p class="mt-2 text-gray-500">Data mitra tidak ditemukan</p>
        <Button @click="kembali()" class="mt-4" outlined> Kembali </Button>
      </div>
    </Card>
  </div>

  <!-- Modal Konfirmasi Setujui -->
  <Modal
    :open="ModalKonfirmasiSetuju"
    size="small"
    rounded
    :closeOnEsc="true"
    :closeOnOutside="false"
    actions="right"
    cancelLabel="Batal"
    actionLabel="Ya, Setujui"
    :showAction="true"
    @close="ModalKonfirmasiSetuju = false"
    @action="konfirmasiSetuju()"
  >
    <template #header>
      <div class="w-full flex items-center">
        <h3 class="font-semibold">Konfirmasi Persetujuan</h3>
        <button
          class="ml-auto text-gray-500 hover:text-red-500"
          @click="ModalKonfirmasiSetuju = false"
        >
          ✕
        </button>
      </div>
    </template>

    <template #content>
      <div class="py-4">
        <div class="text-center mb-4">
          <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
          <p class="text-lg font-medium mb-2">Setujui Pendaftaran Mitra?</p>
          <p class="text-gray-600 mb-4">
            Anda akan menyetujui pendaftaran <strong>{{ mitraData?.nama_lengkap }}</strong>
          </p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Level Mitra (potongan harga paket umrah)</label>
          <select
            v-model="setujuiLevelId"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#007b6f]"
          >
            <option :value="null">-- Pilih level (opsional) --</option>
            <option
              v-for="opt in levelOptions"
              :key="opt.id"
              :value="opt.id"
            >
              {{ opt.nama_level }} (potongan {{ opt.persen_potongan }}%)
            </option>
          </select>
          <p class="text-xs text-gray-500 mt-1">Mitra akan melihat harga paket umrah setelah potongan di halaman Buat Pesanan.</p>
        </div>

        <!-- Validasi Dokumen -->
        <div
          v-if="!allDocumentsApproved()"
          class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"
        >
          <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
            <div>
              <p class="font-medium text-red-900 mb-2">Peringatan!</p>
              <p class="text-sm text-red-800">
                Tidak semua dokumen telah disetujui. Pastikan semua dokumen telah diverifikasi
                dengan benar.
              </p>
              <div class="mt-2 text-sm text-red-700">
                <p v-if="dokumenStatus.ktp !== 'disetujui'">• KTP belum disetujui</p>
                <p v-if="dokumenStatus.ppiu !== 'disetujui'">• PPIU belum disetujui</p>
                <p v-if="dokumenStatus.pihk !== 'disetujui'">• PIHK belum disetujui</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </Modal>

  <!-- Modal Konfirmasi Tolak -->
  <Modal
    :open="ModalKonfirmasiTolak"
    size="small"
    rounded
    :closeOnEsc="true"
    :closeOnOutside="false"
    actions="right"
    cancelLabel="Batal"
    actionLabel="Ya, Tolak"
    :showAction="true"
    @close="ModalKonfirmasiTolak = false"
    @action="konfirmasiTolak()"
  >
    <template #header>
      <div class="w-full flex items-center">
        <h3 class="font-semibold">Konfirmasi Penolakan</h3>
        <button
          class="ml-auto text-gray-500 hover:text-red-500"
          @click="ModalKonfirmasiTolak = false"
        >
          ✕
        </button>
      </div>
    </template>

    <template #content>
      <div class="py-4">
        <div class="text-center mb-4">
          <i class="fas fa-times-circle text-6xl text-red-500 mb-4"></i>
          <p class="text-lg font-medium mb-2">Tolak Pendaftaran Mitra?</p>
          <p class="text-gray-600 mb-4">
            Anda akan menolak pendaftaran <strong>{{ mitraData?.nama_lengkap }}</strong>
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2">Alasan Penolakan *</label>
          <textarea
            v-model="alasanPenolakan"
            rows="4"
            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500"
            placeholder="Masukkan alasan penolakan..."
          ></textarea>

          <!-- Info Dokumen yang Ditolak -->
          <div
            v-if="hasRejectedDocuments()"
            class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg"
          >
            <p class="text-sm font-medium text-red-900 mb-2">Dokumen yang ditolak:</p>
            <div class="text-sm text-red-800 space-y-1">
              <div v-if="dokumenStatus.ktp === 'ditolak'">
                <p class="font-medium">• KTP</p>
                <p v-if="dokumenCatatan.ktp" class="ml-4 text-xs">{{ dokumenCatatan.ktp }}</p>
              </div>
              <div v-if="dokumenStatus.ppiu === 'ditolak'">
                <p class="font-medium">• PPIU</p>
                <p v-if="dokumenCatatan.ppiu" class="ml-4 text-xs">{{ dokumenCatatan.ppiu }}</p>
              </div>
              <div v-if="dokumenStatus.pihk === 'ditolak'">
                <p class="font-medium">• PIHK</p>
                <p v-if="dokumenCatatan.pihk" class="ml-4 text-xs">{{ dokumenCatatan.pihk }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </Modal>

  <!-- Modal Preview Dokumen -->
  <Modal
    :open="ModalPreviewDokumen"
    size="verybig"
    rounded
    :closeOnEsc="true"
    :closeOnOutside="true"
    @close="ModalPreviewDokumen = false"
  >
    <template #header>
      <div class="w-full flex items-center">
        <h3 class="font-semibold">Preview Dokumen - {{ currentDokumenName }}</h3>
        <button
          class="ml-auto text-gray-500 hover:text-red-500"
          @click="ModalPreviewDokumen = false"
        >
          ✕
        </button>
      </div>
    </template>

    <template #content>
      <div class="py-4">
        <div v-if="currentDokumenUrl" class="text-center">
          <!-- Preview untuk Gambar -->
          <img
            v-if="currentDokumenType === 'image'"
            :src="currentDokumenUrl"
            :alt="currentDokumenName"
            class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
            style="max-height: 70vh"
            @error="handleImageError"
          />

          <!-- Preview untuk PDF -->
          <iframe
            v-else-if="currentDokumenType === 'pdf'"
            :src="currentDokumenUrl"
            class="w-full rounded-lg shadow-lg"
            style="height: 70vh; border: none"
          ></iframe>

          <!-- Fallback untuk tipe file lainnya -->
          <div v-else class="text-center py-8">
            <i class="fas fa-file text-6xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 mb-4">Format dokumen tidak bisa ditampilkan dalam preview</p>
          </div>

          <div class="mt-4">
            <a
              :href="currentDokumenUrl"
              target="_blank"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              <i class="fas fa-external-link-alt"></i>
              Buka di Tab Baru
            </a>
          </div>
        </div>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/api/useApi'
import H from '@/utils/appHelper'

const route = useRoute()
const router = useRouter()
const api = useApi()

const loading = ref(true)
const mitraData = ref(null)
const dokumen = ref<Record<string, any>>({})
const catatanVerifikasi = ref('')
const alasanPenolakan = ref('')

const dokumenStatus = ref({
  ktp: 'pending',
  ppiu: 'pending',
  pihk: 'pending',
})

const dokumenCatatan = ref({
  ktp: '',
  ppiu: '',
  pihk: '',
})

const ModalKonfirmasiSetuju = ref(false)
const ModalKonfirmasiTolak = ref(false)
const ModalPreviewDokumen = ref(false)
const currentDokumenUrl = ref('')
const currentDokumenName = ref('')
const currentDokumenType = ref('') // image, pdf, atau file type lainnya

const levelOptions = ref<{ id: number; nama_level: string; persen_potongan: number }[]>([])
const setujuiLevelId = ref<number | null>(null)
const editLevelId = ref<number | null>(null)
const savingLevel = ref(false)

/** Tombol Setujui/Tolak hanya tampil jika status belum final (bukan disetujui/ditolak) */
const bisaSetujuiAtauTolak = computed(() => {
  const kode = mitraData.value?.status_kode
  return kode && kode !== 'disetujui' && kode !== 'ditolak'
})

const fetchLevelOptions = async () => {
  try {
    const res = await api.get('/sistem-admin/master-level-mitra/list')
    const list = res.data?.data ?? res.data ?? []
    levelOptions.value = Array.isArray(list) ? list : []
  } catch {
    levelOptions.value = []
  }
}

const updateLevelMitra = async () => {
  try {
    savingLevel.value = true
    await api.put('/sistem-admin/mitra/update-level-mitra', {
      id_mitra: route.params.id,
      mitra_level_id: editLevelId.value,
    })
    await fetchDetailMitra()
  } catch (e) {
    console.error('Error update level mitra:', e)
    alert('Gagal mengubah level mitra')
  } finally {
    savingLevel.value = false
  }
}

const kembali = () => {
  router.back()
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: 'Menunggu Review',
    disetujui: 'Disetujui',
    ditolak: 'Ditolak',
  }
  return labels[status] || status
}

const setDokumenStatus = async (dokumenId: number, status: string) => {
  try {
    const payload = {
      id_dokumen: dokumenId,
      status: status,
    }
    const res = await api.post('/sistem-admin/dokumen/set-status-dokumen', payload) as unknown as Record<string, unknown>
    if (res && (res.status === true || res.status === 'success' || res.status === 1)) {
      fetchDetailMitra()
    }
  } catch (err: unknown) {
    console.error('Error update status:', err)
    const e = err as { response?: { data?: { message?: string } }; message?: string }
    const msg = e?.response?.data?.message ?? e?.message ?? 'Terjadi kesalahan saat update status.'
    alert(msg)
  }
}

const lihatDokumen = async (dokumenId: number, name: string) => {
  try {
    currentDokumenName.value = name
    currentDokumenUrl.value = ''
    currentDokumenType.value = ''
    ModalPreviewDokumen.value = true

    console.log('Fetching dokumen ID:', dokumenId)

    // Fetch dengan responseType blob
    const res = await api.get(`/sistem-admin/dokumen/${dokumenId}/preview`, {
      responseType: 'blob',
    })

    console.log('Response:', res)
    console.log('Response data:', res.data)
    console.log('Is Blob?', res.data instanceof Blob)
    console.log('Blob size:', res.data?.size)
    console.log('Content-Type:', res.headers?.['content-type'])

    // Karena interceptor sudah diperbaiki, res sekarang adalah full response
    if (res.data instanceof Blob && res.data.size > 0) {
      const mimeType = res.headers['content-type'] || res.data.type || 'image/jpeg'
      const blob = new Blob([res.data], { type: mimeType })
      currentDokumenUrl.value = URL.createObjectURL(blob)

      // Tentukan tipe dokumen berdasarkan MIME type
      if (mimeType.includes('pdf')) {
        currentDokumenType.value = 'pdf'
      } else if (mimeType.includes('image')) {
        currentDokumenType.value = 'image'
      } else {
        currentDokumenType.value = 'file'
      }

      console.log('✅ Dokumen berhasil dimuat')
      console.log('   - URL:', currentDokumenUrl.value)
      console.log('   - Size:', blob.size, 'bytes')
      console.log('   - Type:', blob.type)
      console.log('   - Document Type:', currentDokumenType.value)
    } else {
      console.error('❌ Response tidak valid')
      alert('Format dokumen tidak valid atau file kosong')
      ModalPreviewDokumen.value = false
    }
  } catch (err: unknown) {
    console.error('❌ Error:', err)
    const e = err as { response?: { status?: number } }
    let errorMsg = 'Gagal memuat dokumen'
    if (e.response?.status === 403) errorMsg = 'Tidak memiliki akses'
    else if (e.response?.status === 404) errorMsg = 'Dokumen tidak ditemukan'

    alert(errorMsg)
    ModalPreviewDokumen.value = false
  }
}

const handleImageError = (event: Event) => {
  console.error('Error loading image:', event)
  alert('Gagal menampilkan gambar')
}

const allDocumentsApproved = () => {
  return (
    dokumenStatus.value.ktp === 'disetujui' &&
    dokumenStatus.value.ppiu === 'disetujui' &&
    dokumenStatus.value.pihk === 'disetujui'
  )
}

const hasRejectedDocuments = () => {
  return (
    dokumenStatus.value.ktp === 'ditolak' ||
    dokumenStatus.value.ppiu === 'ditolak' ||
    dokumenStatus.value.pihk === 'ditolak'
  )
}

const fetchDetailMitra = async () => {
  try {
    loading.value = true
    const id_mitra = route.params.id

    console.log(route.params)

    const response = await api.get(`/sistem-admin/mitra/get-detail-mitra?id_mitra=${id_mitra}`)

    if (response && response.data) {
      const raw = response.data
      const data = raw.data ?? raw
      mitraData.value = data
      editLevelId.value = data.mitra_level_id ?? null
      dokumen.value = data.dokumen || {}
      dokumenStatus.value = {
        ktp: dokumen.value.ktp?.status ?? 'pending',
        ppiu: dokumen.value.ppiu?.status ?? 'pending',
        pihk: dokumen.value.pihk?.status ?? 'pending',
      }
    } else {
      alert('Data mitra tidak ditemukan')
    }
  } catch (error) {
    console.error('Error fetching detail mitra:', error)
    alert('Gagal memuat data mitra')
  } finally {
    loading.value = false
  }
}

const setujuiPendaftaran = () => {
  ModalKonfirmasiSetuju.value = true
}

const tolakPendaftaran = () => {
  ModalKonfirmasiTolak.value = true
}

const konfirmasiSetuju = async () => {
  try {
    const payload: Record<string, any> = {
      id_mitra: route.params.id,
    }
    if (setujuiLevelId.value != null) {
      payload.mitra_level_id = setujuiLevelId.value
    }

    const res = await api.post('/sistem-admin/mitra/setujui-mitra', payload)

    const body = res as unknown as (Record<string, unknown> & { data?: { status?: boolean; message?: string }; message?: string })
    if (body && (body.data?.status === true || body.status === true || body.status === 'success' || body.status === 1)) {
      alert('Pendaftaran mitra berhasil disetujui. Role mitra sudah diaktifkan.')
      ModalKonfirmasiSetuju.value = false
      router.push('/Pendaftaran/Pendaftaran-mitra')
    } else {
      const msg = body?.data?.message ?? body?.message ?? 'Gagal menyetujui pendaftaran'
      alert(msg)
    }
  } catch (err: unknown) {
    console.error('Error setujui mitra:', err)
    const e = err as { response?: { data?: { message?: string } }; message?: string }
    const msg = e?.response?.data?.message ?? e?.message ?? 'Terjadi kesalahan saat menyetujui pendaftaran'
    alert(msg)
  }
}

const konfirmasiTolak = async () => {
  if (!alasanPenolakan.value.trim()) {
    alert('Alasan penolakan harus diisi')
    return
  }

  try {
    const payload = {
      id_mitra: route.params.id,
      alasan_penolakan: alasanPenolakan.value,
    }

    const res = await api.post('/sistem-admin/mitra/tolak-mitra', payload) as unknown as Record<string, unknown>
    if (res && (res.status === true || res.status === 'success' || res.status === 1)) {
      ModalKonfirmasiTolak.value = false
      router.back()
    }
  } catch (err: unknown) {
    console.error('Error tolak mitra:', err)
  }
}

onMounted(() => {
  fetchLevelOptions()
  fetchDetailMitra()
})
</script>

<style scoped></style>
