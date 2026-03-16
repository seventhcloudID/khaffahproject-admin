<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
        <div class="flex items-center gap-4">
          <button
            @click="goBack()"
            class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
          >
            <i class="fas fa-arrow-left"></i>
          </button>
          <h1 class="text-xl font-medium">
            {{ isEditMode ? 'Edit Paket Umrah' : 'Tambah Paket Umrah' }}
          </h1>
        </div>
        <!-- Sticky-style action bar: selalu terlihat untuk akses cepat Simpan/Batal -->
        <div class="flex items-center gap-2">
          <Button type="button" @click="goBack()" outlined color="secondary" size="sm"> Batal </Button>
          <Button type="button" @click="submitForm()" color="success" :loading="isLoadingSubmit" size="sm"> Simpan </Button>
        </div>
      </div>

      <!-- Quick jump: navigasi ke section -->
      <div
        v-if="!isLoadingData"
        class="mb-6 p-3 bg-gray-50 rounded-lg border border-gray-200 flex flex-wrap gap-2 items-center"
      >
        <span class="text-sm font-medium text-gray-600 mr-1">Langsung ke:</span>
        <template v-for="(s, key) in sectionList" :key="key">
          <button
            type="button"
            @click="scrollToSection(key)"
            class="px-2.5 py-1 text-xs rounded-md transition-colors"
            :class="openSections[key] ? 'bg-blue-100 text-blue-800 font-medium' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'"
          >
            {{ s.short }}
          </button>
        </template>
        <button
          type="button"
          @click="toggleAllSections(true)"
          class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
        >
          Buka semua
        </button>
        <button
          type="button"
          @click="toggleAllSections(false)"
          class="px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
        >
          Tutup semua
        </button>
        <span class="text-xs text-gray-400 ml-auto">Klik judul section untuk buka/tutup. Simpan & Batal ada di kanan atas.</span>
      </div>

      <div v-if="isLoadingData" class="flex justify-center items-center py-8">
        <div class="text-gray-600">Loading...</div>
      </div>

      <form v-else @submit.prevent="submitForm()" class="space-y-4">
        <!-- 1. Info Dasar -->
        <div id="section-info" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.info = !openSections.info"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.info }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-info-circle text-blue-600"></i>
              <strong>1. Info Dasar</strong>
            </span>
            <span class="text-sm text-gray-500 ml-2">Nama, deskripsi, musim, lokasi, durasi, kuota</span>
          </button>
          <div v-show="openSections.info" class="p-4 space-y-4 border-t border-gray-200">
          <Field label="Nama Paket" id="nama_paket" required>
            <input
              v-model="formData.nama_paket"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan nama paket"
            />
          </Field>

          <Field label="Deskripsi" id="deskripsi">
            <textarea
              v-model="formData.deskripsi"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan deskripsi paket"
              rows="3"
            ></textarea>
          </Field>

          <div class="grid grid-cols-2 gap-4">
            <Field label="Musim" id="musim" required>
              <AutoComplete
                v-model="formData.musim"
                :suggestions="d_Musim"
                @complete="fetchMusim($event)"
                :optionLabel="'label'"
                :dropdown="true"
              :minLength="0"
                :appendTo="'body'"
                :field="'label'"
                placeholder="Pilih musim..."
                class="w-full"
              />
            </Field>

            <Field label="Bintang (Rating)" id="bintang" required>
              <input
                v-model.number="formData.bintang"
                type="number"
                min="0"
                max="5"
                step="0.1"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Rating bintang"
              />
            </Field>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <Field label="Lokasi Keberangkatan" id="lokasi_keberangkatan_id" required>
              <AutoComplete
                v-model="formData.lokasi_keberangkatan"
                :suggestions="d_Kota"
                @complete="fetchKota($event)"
                :optionLabel="'label'"
                :dropdown="true"
              :minLength="0"
                :appendTo="'body'"
                :field="'label'"
                placeholder="Pilih lokasi keberangkatan..."
                class="w-full"
              />
            </Field>

            <Field label="Lokasi Tujuan" id="lokasi_tujuan_id" required>
              <AutoComplete
                v-model="formData.lokasi_tujuan"
                :suggestions="d_Kota"
                @complete="fetchKota($event)"
                :optionLabel="'label'"
                :dropdown="true"
              :minLength="0"
                :appendTo="'body'"
                :field="'label'"
                placeholder="Pilih lokasi tujuan..."
                class="w-full"
              />
            </Field>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <Field label="Durasi Total (Hari)" id="durasi_total" required>
              <input
                v-model.number="formData.durasi_total"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Durasi total"
              />
            </Field>

            <Field label="Sisa kuota (restock)" id="jumlah_pax" required>
              <input
                v-model.number="formData.jumlah_pax"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                :placeholder="isEditMode ? 'Isi berapa slot yang ingin tersedia' : 'Kuota awal (slot tersedia)'"
              />
              <p v-if="isEditMode && (jumlahPaxTerpakai != null || jumlahPaxSisa != null)" class="text-sm text-gray-600 mt-2">
                Saat ini terpakai <strong>{{ jumlahPaxTerpakai ?? 0 }}</strong> jamaah.
              </p>
              <p class="text-xs text-gray-500 mt-1">
                Isi berapa slot yang ingin tersedia (restock). Sistem hitung kapasitas otomatis. Contoh: isi 50 → kuota tersedia 50 slot.
              </p>
            </Field>
          </div>
          </div>
        </div>

        <!-- 2. Foto Paket Section -->
        <div id="section-foto" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.foto = !openSections.foto"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.foto }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-images text-green-600"></i>
              <strong>2. Foto Paket</strong>
            </span>
            <span class="text-sm text-gray-500 ml-2">Maks. 5 foto</span>
          </button>
          <div v-show="openSections.foto" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Foto Paket (Max 5)</h3>
            <Button
              v-if="formData.foto_paket.length < 5"
              type="button"
              color="success"
              @click="addFoto()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Foto
            </Button>
          </div>

          <div v-if="formData.foto_paket.length === 0" class="text-gray-500 text-center py-4">
            Belum ada foto. Klik "Tambah Foto" untuk menambahkan.
          </div>

          <div
            v-for="(foto, idx) in formData.foto_paket"
            :key="idx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <h4 class="font-medium">Foto {{ idx + 1 }}</h4>
              <button
                type="button"
                @click="removeFoto(idx)"
                class="text-red-600 hover:text-red-800"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Field label="Upload Foto" :id="`foto_file_${idx}`" required>
                    <input
                      type="file"
                      accept="image/jpeg,image/jpg,image/png"
                      @change="onFotoChange($event, idx)"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </Field>
                  <p class="text-xs text-gray-500 mt-1">JPG atau PNG, maks. 2 MB</p>
                  <p v-if="foto.file" class="text-xs text-green-600 mt-1">✓ {{ foto.file.name }}</p>
                </div>

                <div>
                  <Field label="Urutan (1-5)" :id="`urutan_${idx}`" required>
                    <input
                      v-model.number="foto.urutan"
                      type="number"
                      min="1"
                      max="5"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="1-5"
                    />
                  </Field>
                </div>
              </div>

              <div v-if="foto.preview" class="flex justify-center">
                <img
                  :src="foto.preview"
                  alt="Preview"
                  class="max-w-xs max-h-40 rounded border border-gray-300"
                />
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- 3. Destinasi & Hotel Section -->
        <div id="section-destinasi" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.destinasi = !openSections.destinasi"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.destinasi }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-map-marked-alt text-amber-600"></i>
              <strong>3. Destinasi & Hotel</strong>
            </span>
          </button>
          <div v-show="openSections.destinasi" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Destinasi & Hotel</h3>
            <Button
              type="button"
              color="success"
              @click="addDestinasiHotel()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Destinasi
            </Button>
          </div>

          <div v-if="formData.destinasi_hotel.length === 0" class="text-gray-500 text-center py-4">
            Belum ada destinasi. Klik "Tambah Destinasi" untuk menambahkan.
          </div>

          <div
            v-for="(item, idx) in formData.destinasi_hotel"
            :key="idx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <h4 class="font-medium">Destinasi {{ idx + 1 }}</h4>
              <button
                type="button"
                @click="removeDestinasiHotel(idx)"
                class="text-red-600 hover:text-red-800"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <Field label="Nama Kota" :id="`destinasi_kota_${idx}`" required>
                  <AutoComplete
                    v-model="item.nama_kota"
                    :suggestions="d_Kota"
                    @complete="fetchKota($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="1"
                    :appendTo="'body'"
                    :field="'label'"
                    placeholder="Pilih kota..."
                    class="w-full"
                  />
                </Field>

                <Field label="Durasi (Hari)" :id="`destinasi_durasi_${idx}`" required>
                  <input
                    v-model.number="item.durasi"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Durasi hari"
                  />
                </Field>
              </div>

              <div class="grid grid-cols-3 gap-4">
                <Field label="Hotel" :id="`destinasi_hotel_${idx}`" required>
                  <AutoComplete
                    v-model="item.nama_hotel"
                    :suggestions="d_Hotel"
                    @complete="fetchHotel($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="1"
                    :appendTo="'body'"
                    :field="'label'"
                    placeholder="Pilih hotel..."
                    class="w-full"
                  />
                </Field>

                <Field label="Bintang Hotel" :id="`destinasi_bintang_${idx}`">
                  <input
                    v-model.number="item.bintang"
                    type="number"
                    disabled
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                    placeholder="Auto-filled"
                  />
                </Field>

                <div></div>
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- 4. Maskapai Section -->
        <div id="section-maskapai" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.maskapai = !openSections.maskapai"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.maskapai }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-plane text-sky-600"></i>
              <strong>4. Maskapai</strong>
            </span>
          </button>
          <div v-show="openSections.maskapai" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Maskapai</h3>
            <Button
              type="button"
              color="success"
              @click="addMaskapai()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Maskapai
            </Button>
          </div>

          <div v-if="formData.maskapai.length === 0" class="text-gray-500 text-center py-4">
            Belum ada maskapai. Klik "Tambah Maskapai" untuk menambahkan.
          </div>

          <div
            v-for="(maskapai, idx) in formData.maskapai"
            :key="idx"
            class="mb-4 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center gap-4">
              <div class="flex-1">
                <Field label="Maskapai" :id="`maskapai_${idx}`" required>
                  <AutoComplete
                    v-model="maskapai.nama_maskapai"
                    :suggestions="d_Maskapai"
                    @complete="fetchMaskapai($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="1"
                    :appendTo="'body'"
                    :field="'label'"
                    placeholder="Pilih maskapai..."
                    class="w-full"
                  />
                </Field>
              </div>
              <button
                type="button"
                @click="removeMaskapai(idx)"
                class="text-red-600 hover:text-red-800 mt-8"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          </div>
        </div>

        <!-- 5. Fasilitas Tambahan Section -->
        <div id="section-fasilitas" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.fasilitas = !openSections.fasilitas"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.fasilitas }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-concierge-bell text-teal-600"></i>
              <strong>5. Fasilitas Tambahan</strong>
            </span>
          </button>
          <div v-show="openSections.fasilitas" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Fasilitas Tambahan</h3>
            <Button
              type="button"
              color="success"
              @click="addFasilitasJenis()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Kategori
            </Button>
          </div>

          <div
            v-if="formData.fasilitas_tambahan.length === 0"
            class="text-gray-500 text-center py-4"
          >
            Belum ada fasilitas.
          </div>

          <div
            v-for="(jenis, jenisIdx) in formData.fasilitas_tambahan"
            :key="jenisIdx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex-1">
                <Field label="Kategori Fasilitas" :id="`fas_jenis_${jenisIdx}`" required>
                  <AutoComplete
                    v-model="jenis.nama_jenis"
                    :suggestions="d_FasilitasJenis"
                    @complete="fetchFasilitasJenis($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="1"
                    :appendTo="'body'"
                    :field="'label'"
                    placeholder="Pilih kategori..."
                    class="w-full pr-6 mt-2"
                  />
                </Field>
              </div>
              <button
                type="button"
                @click="removeFasilitasJenis(jenisIdx)"
                class="text-red-600 hover:text-red-800 mt-8"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="(item, itemIdx) in jenis.list"
                :key="itemIdx"
                class="flex items-center gap-2 p-3 bg-white rounded border border-gray-200"
              >
                <AutoComplete
                  :modelValue="item.label"
                  :suggestions="d_FasilitasItem"
                  @complete="fetchFasilitasItem($event)"
                  @update:modelValue="handleFasilitasItemSelect($event, jenisIdx, itemIdx)"
                  :optionLabel="'label'"
                  :dropdown="true"
                  :minLength="1"
                  :appendTo="'body'"
                  :field="'label'"
                  placeholder="Pilih item..."
                  class="flex-1"
                />
                <button
                  type="button"
                  @click="removeFasilitasItem(jenisIdx, itemIdx)"
                  class="text-red-600 hover:text-red-800"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <Button
                type="button"
                color="info"
                @click="addFasilitasItem(jenisIdx)"
                outlined
                icon="fas fa-plus"
                size="sm"
                class="p-2"
              >
                Tambah Item
              </Button>
            </div>
          </div>
          </div>
        </div>

        <!-- 6. Perlengkapan Section -->
        <div id="section-perlengkapan" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.perlengkapan = !openSections.perlengkapan"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.perlengkapan }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-suitcase text-purple-600"></i>
              <strong>6. Perlengkapan</strong>
            </span>
          </button>
          <div v-show="openSections.perlengkapan" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Perlengkapan</h3>
            <Button
              type="button"
              color="success"
              @click="addPerlengkapanJenis()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Kategori
            </Button>
          </div>

          <div v-if="formData.perlengkapan.length === 0" class="text-gray-500 text-center py-4">
            Belum ada perlengkapan.
          </div>

          <div
            v-for="(jenis, jenisIdx) in formData.perlengkapan"
            :key="jenisIdx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex-1">
                <Field label="Kategori Perlengkapan" :id="`perl_jenis_${jenisIdx}`" required>
                  <AutoComplete
                    v-model="jenis.nama_jenis"
                    :suggestions="d_PerlengkapanJenis"
                    @complete="fetchPerlengkapanJenis($event)"
                    :optionLabel="'label'"
                    :dropdown="true"
                    :minLength="1"
                    :appendTo="'body'"
                    :field="'label'"
                    placeholder="Pilih kategori..."
                    class="w-full pr-6 mt-2"
                  />
                </Field>
              </div>
              <button
                type="button"
                @click="removePerlengkapanJenis(jenisIdx)"
                class="text-red-600 hover:text-red-800 mt-8"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="(item, itemIdx) in jenis.list"
                :key="itemIdx"
                class="flex items-center gap-2 p-3 bg-white rounded border border-gray-200"
              >
                <AutoComplete
                  :modelValue="item.label"
                  :suggestions="d_PerlengkapanItem"
                  @complete="fetchPerlengkapanItem($event)"
                  @update:modelValue="handlePerlengkapanItemSelect($event, jenisIdx, itemIdx)"
                  :optionLabel="'label'"
                  :dropdown="true"
                  :minLength="1"
                  :appendTo="'body'"
                  :field="'label'"
                  placeholder="Pilih item..."
                  class="flex-1"
                />
                <button
                  type="button"
                  @click="removePerlengkapanItem(jenisIdx, itemIdx)"
                  class="text-red-600 hover:text-red-800"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>

              <Button
                type="button"
                color="info"
                @click="addPerlengkapanItem(jenisIdx)"
                outlined
                icon="fas fa-plus"
                size="sm"
                class="p-2"
              >
                Tambah Item
              </Button>
            </div>
          </div>
          </div>
        </div>

        <!-- 7. Keberangkatan Section -->
        <div id="section-keberangkatan" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.keberangkatan = !openSections.keberangkatan"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.keberangkatan }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-calendar-alt text-orange-600"></i>
              <strong>7. Jadwal Keberangkatan</strong>
            </span>
          </button>
          <div v-show="openSections.keberangkatan" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-medium">Jadwal Keberangkatan</h3>
            <Button
              type="button"
              color="success"
              @click="addKeberangkatan()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Jadwal
            </Button>
          </div>

          <div v-if="formData.keberangkatan.length === 0" class="text-gray-500 text-center py-4">
            Belum ada jadwal keberangkatan.
          </div>

          <div
            v-for="(jadwal, idx) in formData.keberangkatan"
            :key="idx"
            class="mb-6 p-4 bg-gray-50 rounded-md"
          >
            <div class="flex items-center justify-between mb-4">
              <h4 class="font-medium">Jadwal {{ idx + 1 }}</h4>
              <button
                type="button"
                @click="removeKeberangkatan(idx)"
                class="text-red-600 hover:text-red-800"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>

            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <Field label="Tanggal Berangkat" :id="`tgl_berangkat_${idx}`" required>
                  <input
                    v-model="jadwal.tanggal_berangkat"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </Field>

                <Field label="Jam Berangkat" :id="`jam_berangkat_${idx}`" required>
                  <input
                    v-model="jadwal.jam_berangkat"
                    type="time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </Field>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <Field label="Tanggal Pulang" :id="`tgl_pulang_${idx}`" required>
                  <input
                    v-model="jadwal.tanggal_pulang"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </Field>

                <Field label="Jam Pulang" :id="`jam_pulang_${idx}`" required>
                  <input
                    v-model="jadwal.jam_pulang"
                    type="time"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </Field>
              </div>

              <div class="flex items-center gap-2">
                <input
                  v-model="jadwal.is_active"
                  type="checkbox"
                  :id="`active_${idx}`"
                  class="rounded"
                />
                <label :for="`active_${idx}`" class="text-sm">Aktif</label>
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- 8. Tipe Paket Umrah Section -->
        <div id="section-tipe" class="border border-gray-200 rounded-lg overflow-hidden bg-white">
          <button
            type="button"
            class="section-header w-full flex items-center gap-3 px-4 py-3 text-left bg-gray-50 hover:bg-gray-100 transition-colors"
            @click="openSections.tipe = !openSections.tipe"
          >
            <i class="fas fa-chevron-right transition-transform text-gray-500" :class="{ 'rotate-90': openSections.tipe }"></i>
            <span class="flex items-center gap-2">
              <i class="fas fa-bed text-indigo-600"></i>
              <strong>8. Tipe Paket (Tipe Kamar)</strong>
            </span>
            <span class="text-sm text-gray-500 ml-2">Harga & kapasitas per tipe kamar</span>
          </button>
          <div v-show="openSections.tipe" class="p-4 border-t border-gray-200">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-medium">Tipe Paket (Tipe Kamar)</h3>
            <Button
              type="button"
              color="success"
              @click="addTipePaket()"
              outlined
              icon="fas fa-plus"
              size="sm"
              class="p-2"
            >
              Tambah Tipe
            </Button>
          </div>

          <div v-if="formData.tipe_paket_umrah.length === 0" class="text-gray-500 text-center py-4 text-sm">
            Belum ada tipe paket. Klik "Tambah Tipe" untuk menambah.
          </div>

          <div v-else class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-100 text-left text-gray-700 font-medium">
                  <th class="px-3 py-2 rounded-tl-lg w-[35%]">Tipe kamar</th>
                  <th class="px-3 py-2 w-[25%]">Harga per pax (Rp)</th>
                  <th class="px-3 py-2 w-[15%]">Kapasitas</th>
                  <th class="px-3 py-2 w-[12%] text-center">Aktif</th>
                  <th class="px-3 py-2 rounded-tr-lg w-[13%] text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(tipe, idx) in formData.tipe_paket_umrah"
                  :key="idx"
                  class="border-t border-gray-200 hover:bg-gray-50/50"
                >
                  <td class="px-3 py-2 align-middle">
                    <AutoComplete
                      v-model="tipe.tipe_kamar"
                      :suggestions="d_TipeKamar"
                      @complete="fetchTipeKamar($event)"
                      :optionLabel="'label'"
                      :dropdown="true"
                      :minLength="1"
                      :appendTo="'body'"
                      :field="'label'"
                      placeholder="Pilih tipe kamar..."
                      class="w-full"
                    />
                  </td>
                  <td class="px-3 py-2 align-middle">
                    <input
                      v-model.number="tipe.harga_per_pax"
                      type="number"
                      min="0"
                      :id="`harga_pax_${idx}`"
                      class="w-full px-2 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="0"
                    />
                  </td>
                  <td class="px-3 py-2 align-middle">
                    <input
                      v-model.number="tipe.kapasitas_total"
                      type="number"
                      min="0"
                      :id="`kapasitas_${idx}`"
                      class="w-full px-2 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="0"
                    />
                  </td>
                  <td class="px-3 py-2 align-middle text-center">
                    <input
                      v-model="tipe.is_active"
                      type="checkbox"
                      :id="`tipe_active_${idx}`"
                      class="rounded"
                    />
                  </td>
                  <td class="px-3 py-2 align-middle text-center">
                    <button
                      type="button"
                      @click="removeTipePaket(idx)"
                      class="text-red-600 hover:text-red-800 p-1"
                      title="Hapus baris"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>

        <!-- Action Buttons (duplikat di bawah untuk yang scroll sampai bawah) -->
        <div class="flex gap-4 pt-6 border-t mt-6">
          <Button type="button" @click="goBack()" outlined color="secondary"> Batal </Button>
          <Button type="submit" color="success" :loading="isLoadingSubmit"> Simpan </Button>
        </div>
      </form>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
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
const jumlahPaxTerpakai = ref<number | null>(null)
const jumlahPaxSisa = ref<number | null>(null)

// UX: section accordion & quick jump
const sectionList: Record<string, { short: string }> = {
  info: { short: '1. Info' },
  foto: { short: '2. Foto' },
  destinasi: { short: '3. Destinasi' },
  maskapai: { short: '4. Maskapai' },
  fasilitas: { short: '5. Fasilitas' },
  perlengkapan: { short: '6. Perlengkapan' },
  keberangkatan: { short: '7. Jadwal' },
  tipe: { short: '8. Tipe Kamar' },
}
const openSections = ref<Record<string, boolean>>({
  info: true,
  foto: true,
  destinasi: false,
  maskapai: false,
  fasilitas: false,
  perlengkapan: false,
  keberangkatan: false,
  tipe: false,
})
function scrollToSection(key: string) {
  openSections.value[key] = true
  setTimeout(() => {
    document.getElementById('section-' + key)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }, 100)
}
function toggleAllSections(open: boolean) {
  openSections.value = { ...openSections.value, ...Object.fromEntries(Object.keys(sectionList).map((k) => [k, open])) }
}

// Dropdown data
const d_Musim = ref([])
const d_LokasiKeberangkatan = ref([])
const d_LokasiTujuan = ref([])
const d_Kota = ref([])
const d_Hotel = ref([])
const d_Maskapai = ref([])
const d_TipeKamar = ref([])
const d_FasilitasJenis = ref([])
const d_FasilitasItem = ref([])
const d_PerlengkapanJenis = ref([])
const d_PerlengkapanItem = ref([])

const formData = ref({
  id: '',
  nama_paket: '',
  deskripsi: '',
  musim: { value: null, label: '' },
  bintang: 5,
  lokasi_keberangkatan: { value: null, label: '' },
  lokasi_tujuan: { value: null, label: '' },
  durasi_total: 0,
  jumlah_pax: 0,
  harga_termurah: 0,
  harga_termahal: 0,
  foto_paket: [] as Array<{
    id?: number
    file?: File
    preview?: string
    url_foto?: string
    urutan: number
    isChanged?: boolean
  }>,
  destinasi_hotel: [] as Array<{
    nama_kota: { value: null; label: string }
    durasi: number
    nama_hotel: { value: null; label: string }
    bintang: number
  }>,
  maskapai: [] as Array<{
    nama_maskapai: { value: null; label: string }
    kode_iata: string
    negara_asal: string
    logo_url: string
  }>,
  fasilitas_tambahan: [] as Array<{
    nama_jenis: { value: null; label: string }
    icon?: { id: number; nama: string; url: string }
    list: Array<{ value: null; label: string }>
  }>,
  perlengkapan: [] as Array<{
    nama_jenis: { value: null; label: string }
    icon?: { id: number; nama: string; url: string }
    list: Array<{ value: null; label: string }>
  }>,
  keberangkatan: [] as Array<{
    id?: number
    paket_umrah_id?: number
    tanggal_berangkat: string
    jam_berangkat: string
    tanggal_pulang: string
    jam_pulang: string
    is_active: boolean
  }>,
  tipe_paket_umrah: [] as Array<{
    id?: number
    paket_umrah_id?: number
    tipe_kamar: { value: null; label: string }
    is_active: boolean
    harga_per_pax: number
    kapasitas_total: number
  }>,
})

const goBack = () => {
  router.back()
}

const getImageUrl = (path: string) => {
  if (!path || typeof path !== 'string') return ''
  // Jika backend sudah mengembalikan URL lengkap, pakai langsung
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  // Base URL gambar HARUS dari server backend (tempat Laravel serve /storage), jangan pakai VITE_APP_URL (bisa frontend :5173)
  let baseUrl =
    import.meta.env.VITE_STORAGE_URL ||
    (typeof import.meta.env.VITE_API_BASE_URL === 'string'
      ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')
      : '') ||
    'http://localhost:8000'
  baseUrl = baseUrl.replace(/\/$/, '')
  if (typeof window !== 'undefined' && window.location.hostname === 'localhost' && baseUrl.includes('127.0.0.1')) {
    baseUrl = baseUrl.replace('127.0.0.1', 'localhost')
  }
  // Normalisasi path: backslash (Windows) jadi slash, dan pastikan ada prefix /storage/
  const normalizedPath = path.replace(/\\/g, '/').trim()
  let fullPath = normalizedPath
  if (!fullPath.startsWith('storage/') && !fullPath.startsWith('/storage/')) {
    fullPath = `storage/${fullPath}`
  }
  const cleanPath = fullPath.startsWith('/') ? fullPath : `/${fullPath}`
  return `${baseUrl}${cleanPath}`
}

const fetchData = async () => {
  if (!paketId.value) return

  try {
    isLoadingData.value = true
    const response = await api.get(`/sistem-admin/paket-umrah/get-paket-umrah/${paketId.value}`)
    // useApi sudah me-return response.data.
    // Endpoint ini mereturn { status, message, data: {...} }.
    // Jadi kita ambil payload sebenarnya dari properti .data jika ada.
    const wrapped = response ?? {}
    const data = (wrapped && typeof wrapped === 'object' && 'data' in wrapped
      ? (wrapped as { data: any }).data
      : wrapped) as any

    const toBool = (v: unknown) => v === true || v === 1 || v === '1'

    jumlahPaxTerpakai.value = data.jumlah_pax_terpakai != null ? Number(data.jumlah_pax_terpakai) : null
    jumlahPaxSisa.value = data.jumlah_pax_sisa != null ? Number(data.jumlah_pax_sisa) : null
    // Field = sisa kuota (restock). Edit: tampilkan sisa saat ini. Create: tampilkan kapasitas awal.
    const jumlahPaxForField = data.jumlah_pax_sisa != null ? Number(data.jumlah_pax_sisa) : Number(data.jumlah_pax)
    formData.value = {
      id: data.id,
      nama_paket: data.nama_paket,
      deskripsi: data.deskripsi,
      musim: { value: data.musim_id, label: data.musim || '' },
      bintang: parseFloat(data.bintang),
      lokasi_keberangkatan: {
        value: data.lokasi_keberangkatan_id,
        label: data.lokasi_keberangkatan || '',
      },
      lokasi_tujuan: { value: data.lokasi_tujuan_id, label: data.lokasi_tujuan || '' },
      durasi_total: data.durasi_total,
      jumlah_pax: jumlahPaxForField,
      harga_termurah: parseFloat(data.harga_termurah),
      harga_termahal: parseFloat(data.harga_termahal),
      foto_paket: (data.foto_paket || []).map((foto: any) => ({
        id: foto.id,
        file: undefined,
        url_foto: foto.url_foto,
        preview: foto.url_foto_display || getImageUrl(foto.url_foto),
        urutan: foto.urutan,
        isChanged: false,
      })),
      destinasi_hotel:
        (data.destinasi || []).map((dest: any, idx: number) => ({
          nama_kota: {
            value: dest.kota_id ?? dest.id_kota ?? null,
            label: dest.nama_kota || '',
          },
          durasi: dest.durasi,
          nama_hotel: {
            value: data.hotel?.[idx]?.id_hotel ?? null,
            label: data.hotel?.[idx]?.nama_hotel || '',
          },
          bintang: parseFloat(data.hotel?.[idx]?.bintang ?? '0') || 0,
        })),
      maskapai: (data.maskapai || []).map((item: any) => ({
        nama_maskapai: { value: item.value_maskapai || item.id_maskapai, label: item.nama_maskapai },
        kode_iata: item.kode_iata,
        negara_asal: item.negara_asal,
        logo_url: item.logo_url,
      })),
      fasilitas_tambahan: (data.fasilitas_tambahan || []).map((item: any) => ({
        nama_jenis: { value: item.jenis_id, label: item.nama_jenis },
        icon: item.icon,
        list: (item.list || []).map((subitem: any) => ({
          value: subitem.value || subitem.id,
          label: subitem.nama,
        })),
      })),
      perlengkapan: (data.perlengkapan || []).map((item: any) => ({
        nama_jenis: { value: item.jenis_id, label: item.nama_jenis },
        icon: item.icon,
        list: (item.list || []).map((subitem: any) => ({
          value: subitem.value || subitem.id,
          label: subitem.nama,
        })),
      })),
      keberangkatan: (data.keberangkatan || []).map((item: any) => ({
        ...item,
        is_active: toBool(item.is_active),
      })),
      tipe_paket_umrah:
        (data.tipe_paket_umrah || []).map((tipe: any) => ({
          id: tipe.paket_umrah_tipe_id ?? tipe.id,
          paket_umrah_id: tipe.paket_umrah_id,
          tipe_kamar: {
            value: tipe.tipe_kamar_id ?? tipe.paket_umrah_tipe_id,
            label: tipe.tipe_kamar || '',
          },
          is_active: typeof tipe.is_active === 'boolean' ? tipe.is_active : toBool(tipe.is_active),
          harga_per_pax: tipe.harga_per_pax,
          kapasitas_total: tipe.kapasitas_total,
        })) || [],
    }
  } catch (error) {
    console.error('Error fetching data:', error)
    alert('Gagal memuat data paket umrah')
    router.back()
  } finally {
    isLoadingData.value = false
    if (isEditMode.value) toggleAllSections(true)
  }
}

// Foto Paket Methods
const addFoto = () => {
  if (formData.value.foto_paket.length < 5) {
    formData.value.foto_paket.push({
      file: undefined,
      preview: undefined,
      url_foto: '',
      urutan: formData.value.foto_paket.length + 1,
      isChanged: false,
    })
  }
}

const removeFoto = (index: number) => {
  formData.value.foto_paket.splice(index, 1)
}

const FOTO_MAX_SIZE_MB = 2
const FOTO_ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png']

const onFotoChange = (event: Event, idx: number) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (!file) return

  if (!FOTO_ALLOWED_TYPES.includes(file.type)) {
    alert('Format foto harus JPG atau PNG.')
    input.value = ''
    return
  }
  if (file.size > FOTO_MAX_SIZE_MB * 1024 * 1024) {
    alert(`Ukuran foto maksimal ${FOTO_MAX_SIZE_MB} MB.`)
    input.value = ''
    return
  }

  formData.value.foto_paket[idx].file = file
  formData.value.foto_paket[idx].isChanged = true

  const reader = new FileReader()
  reader.onload = (e) => {
    formData.value.foto_paket[idx].preview = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

// Destinasi & Hotel Methods
const addDestinasiHotel = () => {
  formData.value.destinasi_hotel.push({
    nama_kota: { value: null, label: '' },
    durasi: 0,
    nama_hotel: { value: null, label: '' },
    bintang: 0,
  })
}

const removeDestinasiHotel = (index: number) => {
  formData.value.destinasi_hotel.splice(index, 1)
}

// Maskapai Methods
const addMaskapai = () => {
  formData.value.maskapai.push({
    nama_maskapai: { value: null, label: '' },
    kode_iata: '',
    negara_asal: '',
    logo_url: '',
  })
}

const removeMaskapai = (index: number) => {
  formData.value.maskapai.splice(index, 1)
}

// Fasilitas Methods
const addFasilitasJenis = () => {
  formData.value.fasilitas_tambahan.push({
    nama_jenis: { value: null, label: '' },
    list: [],
  })
}

const removeFasilitasJenis = (index: number) => {
  formData.value.fasilitas_tambahan.splice(index, 1)
}

const addFasilitasItem = (jenisIdx: number) => {
  formData.value.fasilitas_tambahan[jenisIdx].list.push({
    value: null,
    label: '',
  })
}

const removeFasilitasItem = (jenisIdx: number, itemIdx: number) => {
  formData.value.fasilitas_tambahan[jenisIdx].list.splice(itemIdx, 1)
}

const handleFasilitasItemSelect = (selected: any, jenisIdx: number, itemIdx: number) => {
  if (selected && typeof selected === 'object' && 'value' in selected) {
    formData.value.fasilitas_tambahan[jenisIdx].list[itemIdx].value = selected.value
    formData.value.fasilitas_tambahan[jenisIdx].list[itemIdx].label = selected.label
  } else if (typeof selected === 'string') {
    formData.value.fasilitas_tambahan[jenisIdx].list[itemIdx].label = selected
  }
}

// Perlengkapan Methods
const addPerlengkapanJenis = () => {
  formData.value.perlengkapan.push({
    nama_jenis: { value: null, label: '' },
    list: [],
  })
}

const removePerlengkapanJenis = (index: number) => {
  formData.value.perlengkapan.splice(index, 1)
}

const addPerlengkapanItem = (jenisIdx: number) => {
  formData.value.perlengkapan[jenisIdx].list.push({
    value: null,
    label: '',
  })
}

const removePerlengkapanItem = (jenisIdx: number, itemIdx: number) => {
  formData.value.perlengkapan[jenisIdx].list.splice(itemIdx, 1)
}

const handlePerlengkapanItemSelect = (selected: any, jenisIdx: number, itemIdx: number) => {
  if (selected && typeof selected === 'object' && 'value' in selected) {
    formData.value.perlengkapan[jenisIdx].list[itemIdx].value = selected.value
    formData.value.perlengkapan[jenisIdx].list[itemIdx].label = selected.label
  } else if (typeof selected === 'string') {
    formData.value.perlengkapan[jenisIdx].list[itemIdx].label = selected
  }
}

// Keberangkatan Methods
const addKeberangkatan = () => {
  formData.value.keberangkatan.push({
    tanggal_berangkat: '',
    jam_berangkat: '',
    tanggal_pulang: '',
    jam_pulang: '',
    is_active: true,
  })
}

const removeKeberangkatan = (index: number) => {
  formData.value.keberangkatan.splice(index, 1)
}

// Tipe Paket Methods
const addTipePaket = () => {
  formData.value.tipe_paket_umrah.push({
    tipe_kamar: { value: null, label: '' },
    is_active: true,
    harga_per_pax: 0,
    kapasitas_total: 0,
  })
}

const removeTipePaket = (index: number) => {
  formData.value.tipe_paket_umrah.splice(index, 1)
}

const submitForm = async () => {
  try {
    isLoadingSubmit.value = true

    // Validasi: slot foto yang ada harus punya file atau url_foto (foto lama)
    const invalidFotoSlots = formData.value.foto_paket.filter((f) => !f.file && !f.url_foto)
    if (invalidFotoSlots.length > 0) {
      alert(
        `Ada ${invalidFotoSlots.length} slot foto yang belum memilih file. Pilih file atau hapus slot tersebut, lalu simpan lagi.`,
      )
      isLoadingSubmit.value = false
      return
    }

    const payload: Record<string, unknown> = {
      nama_paket: formData.value.nama_paket,
      deskripsi: formData.value.deskripsi,
      musim: {
        value: formData.value.musim.value,
        label: formData.value.musim.label,
      },
      bintang: Number(formData.value.bintang),
      lokasi_keberangkatan: {
        value: formData.value.lokasi_keberangkatan.value,
        label: formData.value.lokasi_keberangkatan.label,
      },
      lokasi_tujuan: {
        value: formData.value.lokasi_tujuan.value,
        label: formData.value.lokasi_tujuan.label,
      },
      durasi_total: Number(formData.value.durasi_total),
      // Backend terima kapasitas total. Edit: kapasitas = terpakai + sisa (restock). Create: nilai = kapasitas awal.
      jumlah_pax: isEditMode.value
        ? (jumlahPaxTerpakai.value ?? 0) + Number(formData.value.jumlah_pax)
        : Number(formData.value.jumlah_pax),
      destinasi_hotel: formData.value.destinasi_hotel.map((item) => ({
        nama_kota: {
          value: item.nama_kota.value,
          label: item.nama_kota.label,
        },
        durasi: item.durasi,
        nama_hotel: {
          value: item.nama_hotel.value,
          label: item.nama_hotel.label,
        },
        bintang: item.bintang,
      })),
      maskapai: formData.value.maskapai.map((item) => ({
        nama_maskapai: {
          value: item.nama_maskapai.value,
          label: item.nama_maskapai.label,
        },
        kode_iata: item.kode_iata,
        negara_asal: item.negara_asal,
        logo_url: item.logo_url,
      })),
      fasilitas_tambahan: formData.value.fasilitas_tambahan.map((item) => ({
        nama_jenis: {
          value: item.nama_jenis.value,
          label: item.nama_jenis.label,
        },
        list: item.list.map((subitem) => ({
          value: subitem.value,
          label: subitem.label,
        })),
      })),
      perlengkapan: formData.value.perlengkapan.map((item) => ({
        nama_jenis: {
          value: item.nama_jenis.value,
          label: item.nama_jenis.label,
        },
        list: item.list.map((subitem) => ({
          value: subitem.value,
          label: subitem.label,
        })),
      })),
      keberangkatan: formData.value.keberangkatan.map((item) => ({
        ...(item.id != null && String(item.id) !== '' && { id: item.id }),
        tanggal_berangkat: item.tanggal_berangkat,
        jam_berangkat: item.jam_berangkat,
        tanggal_pulang: item.tanggal_pulang,
        jam_pulang: item.jam_pulang,
        is_active: item.is_active === true || (item as unknown as { is_active?: number }).is_active === 1,
      })),
      tipe_paket_umrah: formData.value.tipe_paket_umrah.map((item) => ({
        tipe_kamar: {
          value: item.tipe_kamar.value,
          label: item.tipe_kamar.label,
        },
        is_active: item.is_active === true || (item as unknown as { is_active?: number }).is_active === 1,
        harga_per_pax: item.harga_per_pax,
        kapasitas_total: item.kapasitas_total,
      })),
    }

    // Supaya backend tahu foto mana yang dihapus: kirim daftar foto yang masih ada (urutan + url_foto)
    if (isEditMode.value) {
      payload.foto_paket = formData.value.foto_paket.map((f) => ({
        urutan: f.urutan,
        url_foto: f.url_foto ?? null,
      }))
    }

    let paketId = formData.value.id

    if (isEditMode.value) {
      await api.put(`/sistem-admin/paket-umrah/update-paket-umrah/${paketId}`, payload)
    } else {
      const res = await api.post('/sistem-admin/paket-umrah/create-paket-umrah', payload) as unknown as (Record<string, unknown> & { id?: number; data?: { id?: number } })
      const numId = res?.id ?? (res?.data as { id?: number } | undefined)?.id
      paketId = numId != null ? String(numId) : undefined
      if (paketId == null) {
        throw new Error('ID paket tidak dikembalikan dari server setelah create.')
      }
    }

    const fotoUploadCount = await submitUpdateFoto(Number(paketId) || 0)

    if (fotoUploadCount > 0) {
      alert('Data dan foto berhasil disimpan.')
    } else {
      alert('Data berhasil disimpan.')
    }
    router.back()
  } catch (err: any) {
    console.error(err)
    const data = err?.response?.data

    let msg =
      data?.message ||
      err?.message ||
      'Gagal menyimpan data'

    if (data?.errors && typeof data.errors === 'object') {
      const parts = Object.entries(data.errors).map(([field, messages]) => {
        const text = Array.isArray(messages) ? messages.join(', ') : String(messages)
        return `${field}: ${text}`
      })
      if (parts.length) {
        msg = parts.join('\n')
      }
    }

    alert(msg)
  } finally {
    isLoadingSubmit.value = false
  }
}

const submitUpdateFoto = async (paketId: number): Promise<number> => {
  // Upload semua slot yang punya file (tidak hanya isChanged) agar foto tidak "hilang"
  const photosToUpload = formData.value.foto_paket.filter((f) => f.file)

  if (!photosToUpload.length) return 0

  let uploadedCount = 0
  const errors: string[] = []

  for (let i = 0; i < photosToUpload.length; i++) {
    const foto = photosToUpload[i]
    const file = foto.file
    if (!file || !(file instanceof File)) {
      errors.push(`Foto ${i + 1}: File tidak valid`)
      continue
    }

    const fd = new FormData()
    fd.append('paket_umrah_id', String(paketId))
    fd.append('urutan', String(foto.urutan))
    fd.append('file', file, file.name || 'foto.jpg')

    if (foto.id) {
      fd.append('foto_id', String(foto.id))
    }

    try {
      // Jangan set Content-Type; biarkan axios/browser set multipart/form-data + boundary
      const res: any = await api.post('/sistem-admin/paket-umrah/upsert-foto', fd, {
        headers: { Accept: 'application/json' },
        maxContentLength: Infinity,
        maxBodyLength: Infinity,
        transformRequest: [(data, headers) => {
          // Pastikan Content-Type tidak di-set agar boundary ter-generate
          if (data instanceof FormData && headers) {
            const h = headers as Record<string, unknown>
            delete h['Content-Type']
          }
          return data
        }],
      })
      if (res && res.success !== false) {
        uploadedCount += 1
      } else {
        errors.push(`Foto ${i + 1}: ${res?.message || 'Upload gagal'}`)
      }
    } catch (e: any) {
      const data = e?.response?.data
      let msg = data?.message || e?.message || 'Upload gagal'
      if (data?.errors && typeof data.errors === 'object') {
        const parts = Object.entries(data.errors).map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(', ') : v}`)
        if (parts.length) msg = parts.join('; ')
      }
      if (data?.content_type && import.meta.env.DEV) msg += ` [Content-Type: ${data.content_type}]`
      if (import.meta.env.DEV) console.warn('Upload foto gagal:', e?.response?.status, data, e)
      errors.push(`Foto ${i + 1}: ${msg}`)
    }
  }

  if (errors.length > 0) {
    throw new Error(errors.join('\n'))
  }

  return uploadedCount
}

// Fetch methods for dropdowns
const fetchMusim = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/musim_m?select=id,nama_musim&param_search=nama_musim&query=${filter.query}&limit=10`,
    )
    d_Musim.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_musim || item.label,
    }))
  } catch (error) {
    console.error('Error fetching musim:', error)
    d_Musim.value = []
  }
}

const fetchKota = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/kota_m?select=id,nama_kota&param_search=nama_kota&query=${filter.query}&limit=10`,
    )
    d_Kota.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_kota || item.label,
    }))
  } catch (error) {
    console.error('Error fetching kota:', error)
    d_Kota.value = []
  }
}

const fetchHotel = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/hotel_m?select=id,nama_hotel,bintang&param_search=nama_hotel&query=${filter.query}&limit=10`,
    )
    d_Hotel.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_hotel || item.label,
      bintang: item.bintang,
    }))
  } catch (error) {
    console.error('Error fetching hotel:', error)
    d_Hotel.value = []
  }
}

const fetchMaskapai = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/maskapai_m?select=id,nama_maskapai&param_search=nama_maskapai&query=${filter.query}&limit=10`,
    )
    d_Maskapai.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_maskapai || item.label,
    }))
  } catch (error) {
    console.error('Error fetching maskapai:', error)
    d_Maskapai.value = []
  }
}

const fetchTipeKamar = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/tipe_kamar_m?select=id,tipe_kamar&param_search=tipe_kamar&query=${filter.query}&limit=10`,
    )
    d_TipeKamar.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.tipe_kamar || item.label,
    }))
  } catch (error) {
    console.error('Error fetching tipe kamar:', error)
    d_TipeKamar.value = []
  }
}

const fetchFasilitasJenis = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/jenis_fasilitas_m?select=id,nama_jenis&param_search=nama_jenis&query=${filter.query}&limit=10`,
    )
    d_FasilitasJenis.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_jenis || item.label,
    }))
  } catch (error) {
    console.error('Error fetching fasilitas jenis:', error)
    d_FasilitasJenis.value = []
  }
}

const fetchFasilitasItem = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/fasilitas_m?select=id,nama_fasilitas&param_search=nama_fasilitas&query=${filter.query}&limit=10`,
    )
    d_FasilitasItem.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_fasilitas || item.label,
    }))
  } catch (error) {
    console.error('Error fetching fasilitas item:', error)
    d_FasilitasItem.value = []
  }
}

const fetchPerlengkapanJenis = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/jenis_perlengkapan_m?select=id,nama_jenis&param_search=nama_jenis&query=${filter.query}&limit=10`,
    )
    d_PerlengkapanJenis.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_jenis || item.label,
    }))
  } catch (error) {
    console.error('Error fetching perlengkapan jenis:', error)
    d_PerlengkapanJenis.value = []
  }
}

const fetchPerlengkapanItem = async (filter: any) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/perlengkapan_m?select=id,nama_perlengkapan&param_search=nama_perlengkapan&query=${filter.query}&limit=10`,
    )
    d_PerlengkapanItem.value = response.data.map((item: any) => ({
      value: item.value,
      label: item.nama_perlengkapan || item.label,
    }))
  } catch (error) {
    console.error('Error fetching perlengkapan item:', error)
    d_PerlengkapanItem.value = []
  }
}

onMounted(() => {
  if (isEditMode.value) {
    fetchData()
  }

  // Watch hotel selection and update bintang
  watch(
    () => formData.value.destinasi_hotel,
    () => {
      formData.value.destinasi_hotel.forEach((item) => {
        const selectedHotel = d_Hotel.value.find((h: any) => h.value === item.nama_hotel.value)
        if (selectedHotel?.bintang) {
          item.bintang = parseFloat(selectedHotel.bintang)
        }
      })
    },
    { deep: true },
  )
})
</script>
