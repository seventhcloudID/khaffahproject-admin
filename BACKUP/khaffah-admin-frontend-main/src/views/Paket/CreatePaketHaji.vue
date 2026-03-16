<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center gap-4 mb-6">
        <button
          @click="goBack()"
          class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
        >
          <i class="fas fa-arrow-left"></i>
        </button>
        <h1 class="text-xl font-medium">
          {{ isEditMode ? 'Edit Paket Haji' : 'Tambah Paket Haji' }}
        </h1>
      </div>

      <div v-if="isLoadingData" class="flex justify-center items-center py-8">
        <div class="text-gray-600">Loading...</div>
      </div>

      <form v-else @submit.prevent="submitForm()" class="space-y-6">
        <!-- Nama Paket -->
        <Field label="Nama Paket" id="nama_paket" required>
          <input
            v-model="formData.nama_paket"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan nama paket"
          />
        </Field>

        <!-- Biaya Per Pax -->
        <Field label="Biaya Per Pax (Rp)" id="biaya_per_pax" required>
          <input
            v-model="formData.biaya_per_pax"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan biaya per pax"
          />
        </Field>

        <!-- Deskripsi Akomodasi -->
        <Field label="Deskripsi Akomodasi" id="deskripsi_akomodasi">
          <textarea
            v-model="formData.deskripsi_akomodasi"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan deskripsi akomodasi"
            rows="3"
          ></textarea>
        </Field>

        <!-- Akomodasi Section -->
        <div class="border-t pt-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium">Daftar Hotel</h3>
            <Button
              type="button"
              color="success"
              @click="addAkomodasi()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-1"
            >
              Tambah Hotel
            </Button>
          </div>

          <div v-if="formData.akomodasi.length === 0" class="text-gray-500 text-center py-4">
            Belum ada hotel. Klik "Tambah Hotel" untuk menambahkan.
          </div>

          <div
            v-for="(hotel, idx) in formData.akomodasi"
            :key="idx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <h4 class="font-medium">Hotel {{ idx + 1 }}</h4>
              <button
                type="button"
                @click="removeAkomodasi(idx)"
                class="text-red-600 hover:text-red-800"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="columns">
              <div class="column is-3">
                <Field label="Kota" id="kota" required>
                  <AutoComplete
                    v-model="hotel.kota"
                    :suggestions="d_Kota"
                    @complete="fetchKota($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="3"
                    :appendTo="'body'"
                    :loadingIcon="'pi pi-spinner'"
                    :field="'label'"
                    placeholder="Nama Kota ..."
                    class="w-full"
                  />
                </Field>
              </div>
              <div class="column is-5">
                <Field label="Nama Hotel" id="hotel" required>
                  <AutoComplete
                    v-model="hotel.hotel"
                    :suggestions="d_Hotel"
                    @complete="fetchHotel($event)"
                    @item-select="onHotelSelect($event, idx)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="3"
                    :appendTo="'body'"
                    :loadingIcon="'pi pi-spinner'"
                    :field="'label'"
                    placeholder="Nama Hotel ..."
                    class="w-full"
                  />
                </Field>
              </div>
              <Field label="Rating Hotel (0-5)" id="rating_hotel" required>
                <input
                  v-model.number="hotel.rating_hotel"
                  type="number"
                  min="0"
                  disabled
                  max="5"
                  step="0.1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Masukkan rating hotel"
                />
              </Field>
            </div>
          </div>
        </div>

        <!-- Waktu Tunggu Section -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium mb-4">Waktu Tunggu</h3>

          <div class="grid grid-cols-2 gap-4">
            <Field label="Waktu Tunggu Minimal (Tahun)" id="waktu_tunggu_min" required>
              <input
                v-model.number="formData.waktu_tunggu.min"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Masukkan waktu tunggu minimal"
              />
            </Field>

            <Field label="Waktu Tunggu Maksimal (Tahun)" id="waktu_tunggu_max" required>
              <input
                v-model.number="formData.waktu_tunggu.max"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Masukkan waktu tunggu maksimal"
              />
            </Field>
          </div>

          <Field label="Deskripsi Waktu Tunggu" id="waktu_tunggu_deskripsi">
            <textarea
              v-model="formData.waktu_tunggu.deskripsi"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan deskripsi waktu tunggu"
              rows="3"
            ></textarea>
          </Field>
        </div>

        <!-- Fasilitas Tambahan Section -->
        <div class="border-t pt-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium">Fasilitas Tambahan</h3>
            <Button
              type="button"
              color="success"
              @click="addFasilitas()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-1"
            >
              Tambah Fasilitas
            </Button>
          </div>

          <div
            v-if="formData.fasilitas_tambahan.length === 0"
            class="text-gray-500 text-center py-4"
          >
            Belum ada fasilitas. Klik "Tambah Fasilitas" untuk menambahkan.
          </div>

          <div
            v-for="(fasilitas, idx) in formData.fasilitas_tambahan"
            :key="idx"
            class="mb-4 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center gap-4">
              <Field required class="mt-5">
                <AutoComplete
                  v-model="fasilitas.nama_fasilitas"
                  :suggestions="d_Fasilitas"
                  @complete="fetchFasilitas($event)"
                  @item-select="onFasilitasSelect($event, idx)"
                  :optionLabel="'label'"
                  :dropdown="true"
                  :minLength="3"
                  :appendTo="'body'"
                  :loadingIcon="'pi pi-spinner'"
                  :field="'label'"
                  placeholder="Nama Fasilitas ..."
                  class="w-full"
                />
              </Field>
              <button
                type="button"
                @click="removeFasilitas(idx)"
                class="text-red-600 hover:text-red-800"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Deskripsi Fasilitas -->
        <Field label="Deskripsi Fasilitas" id="deskripsi_fasilitas">
          <textarea
            v-model="formData.deskripsi_fasilitas"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan deskripsi fasilitas"
            rows="3"
          ></textarea>
        </Field>

        <!-- Action Buttons -->
        <div class="flex gap-4 pt-6 border-t">
          <Button type="button" @click="goBack()" outlined color="secondary"> Batal </Button>
          <Button type="submit" color="success" :loading="isLoadingSubmit"> Simpan </Button>
        </div>
      </form>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useApi } from '@/api/useApi'
import Card from '@/components/base/card/Card.vue'
import Field from '@/components/base/form/Field.vue'
import Button from '@/components/base/button/Button.vue'
import AutoComplete from 'primevue/autocomplete'

const api = useApi()
const router = useRouter()
const route = useRoute()
const isLoadingData = ref(false)
const isLoadingSubmit = ref(false)
const paketId = computed(() => route.params.id as string | undefined)
const isEditMode = computed(() => !!paketId.value)

const d_Hotel = ref([])
const d_Kota = ref([])
const d_Fasilitas = ref([])

const formData = ref({
  id: '',
  nama_paket: '',
  biaya_per_pax: '',
  deskripsi_akomodasi: '',
  akomodasi: [] as Array<{
    kota: string
    nama_hotel: string
    hotel?: string
    rating_hotel: number
  }>,
  waktu_tunggu: {
    min: '',
    max: '',
    deskripsi: '',
  },
  fasilitas_tambahan: [] as Array<{
    nama_fasilitas: string
  }>,
  deskripsi_fasilitas: '',
})

const goBack = () => {
  router.back()
}

const fetchData = async () => {
  if (!paketId.value) return

  try {
    isLoadingData.value = true
    const response = await api.get(`/sistem-admin/paket-haji/get-paket-haji/${paketId.value}`)
    const data = response.data

    formData.value = {
      id: data.id,
      nama_paket: data.nama_paket,
      biaya_per_pax: data.biaya_per_pax,
      deskripsi_akomodasi: data.deskripsi_akomodasi || '',
      akomodasi: data.akomodasi || [],
      waktu_tunggu: {
        min: data.waktu_tunggu?.min || '',
        max: data.waktu_tunggu?.max || '',
        deskripsi: data.waktu_tunggu?.deskripsi || '',
      },
      fasilitas_tambahan: data.fasilitas_tambahan || [],
      deskripsi_fasilitas: data.deskripsi_fasilitas || '',
    }
  } catch (error) {
    console.error('Error fetching data:', error)
    alert('Gagal memuat data paket haji')
    router.back()
  } finally {
    isLoadingData.value = false
  }
}

const addAkomodasi = () => {
  formData.value.akomodasi.push({
    kota: '',
    nama_hotel: '',
    rating_hotel: 0,
  })
}

const removeAkomodasi = (index: number) => {
  formData.value.akomodasi.splice(index, 1)
}

const addFasilitas = () => {
  formData.value.fasilitas_tambahan.push({
    nama_fasilitas: '',
  })
}

const removeFasilitas = (index: number) => {
  formData.value.fasilitas_tambahan.splice(index, 1)
}

const submitForm = async () => {
  try {
    // Validasi
    if (!formData.value.nama_paket.trim()) {
      alert('Nama paket tidak boleh kosong')
      return
    }

    if (!formData.value.biaya_per_pax) {
      alert('Biaya per pax tidak boleh kosong')
      return
    }

    if (formData.value.akomodasi.length === 0) {
      alert('Minimal harus ada satu hotel')
      return
    }

    if (!formData.value.waktu_tunggu.min || !formData.value.waktu_tunggu.max) {
      alert('Waktu tunggu minimal dan maksimal harus diisi')
      return
    }

    isLoadingSubmit.value = true

    const payload = {
      nama_paket: formData.value.nama_paket,
      biaya_per_pax: Number(formData.value.biaya_per_pax),
      deskripsi_akomodasi: formData.value.deskripsi_akomodasi,
      akomodasi: formData.value.akomodasi,
      waktu_tunggu: {
        min: Number(formData.value.waktu_tunggu.min),
        max: Number(formData.value.waktu_tunggu.max),
        deskripsi: formData.value.waktu_tunggu.deskripsi,
      },
      fasilitas_tambahan: formData.value.fasilitas_tambahan,
      deskripsi_fasilitas: formData.value.deskripsi_fasilitas,
    }

    if (isEditMode.value) {
      await api.put(`/sistem-admin/paket-haji/update-paket-haji/${formData.value.id}`, payload)
      alert('Paket haji berhasil diperbarui')
    } else {
      await api.post('/sistem-admin/paket-haji/create-paket-haji', payload)
      alert('Paket haji berhasil ditambahkan')
    }

    router.back()
  } catch (error) {
    console.error('Error submitting form:', error)
    alert('Gagal menyimpan data')
  } finally {
    isLoadingSubmit.value = false
  }
}

const fetchHotel = async (filter: any) => {
  try {
    const response = await useApi().get(
      `/sistem-admin/utility/dropdown/hotel_m?select=id,nama_hotel,bintang&param_search=nama_hotel&query=${filter.query}&limit=10`,
    )
    d_Hotel.value = response.data
  } catch (error) {
    console.error('Error fetching hotel:', error)
    d_Hotel.value = []
  }
}

const fetchFasilitas = async (filter: any) => {
  try {
    const response = await useApi().get(
      `/sistem-admin/utility/dropdown/fasilitas_m?select=id,nama_fasilitas&param_search=nama_fasilitas&query=${filter.query}&limit=10`,
    )
    d_Fasilitas.value = response.data
  } catch (error) {
    console.error('Error fetching fasilitas:', error)
    d_Fasilitas.value = []
  }
}

const onHotelSelect = (event: any, idx: number) => {
  const selectedHotel = event.value
  if (selectedHotel && selectedHotel.bintang) {
    formData.value.akomodasi[idx].rating_hotel = selectedHotel.bintang
  }
}

const onFasilitasSelect = (event: any, idx: number) => {
  const v = event?.value
  if (v && formData.value.fasilitas_tambahan[idx]) {
    formData.value.fasilitas_tambahan[idx].nama_fasilitas = v.label ?? v.nama_fasilitas ?? ''
  }
}

const fetchKota = async (filter: any) => {
  try {
    const response = await useApi().get(
      `/sistem-admin/utility/dropdown/kota_m?select=id,nama_kota&param_search=nama_kota&query=${filter.query}&limit=10`,
    )
    d_Kota.value = response.data
  } catch (error) {
    console.error('Error fetching kota:', error)
    d_Kota.value = []
  }
}

onMounted(() => {
  if (isEditMode.value) {
    fetchData()
  }
})
</script>
