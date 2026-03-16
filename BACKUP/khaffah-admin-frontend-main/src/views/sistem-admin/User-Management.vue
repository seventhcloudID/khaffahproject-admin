<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <h1 class="text-xl font-medium mb-6 flex items-center gap-2">
        <i class="fas fa-users-cog text-[#007b6f]"></i>
        User Management
      </h1>

      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="searchQuery"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Cari nama atau email..."
          />
        </div>
        <Button
          color="success"
          icon="fas fa-search"
          :disabled="loading"
          @click="fetchData"
        >
          Cari
        </Button>
        <Button
          outlined
          icon="fas fa-sync-alt"
          :disabled="loading"
          @click="fetchData"
        >
          Refresh
        </Button>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i>
      </div>

      <DataTable
        v-else
        :value="filteredUsers"
        class="datatable"
        :paginator="true"
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
        paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} data"
        responsiveLayout="scroll"
      >
        <template #empty>
          <div class="text-center py-8 text-gray-500">
            <i class="fas fa-users text-4xl mb-3 opacity-50"></i>
            <p>Tidak ada data user. Hubungkan ke API user management untuk menampilkan data.</p>
          </div>
        </template>
        <Column field="no" header="No" style="width: 60px" />
        <Column field="nama" header="Nama" />
        <Column field="email" header="Email" />
        <Column field="role" header="Role">
          <template #body="{ data }">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
              {{ data.role || '-' }}
            </span>
          </template>
        </Column>
        <Column field="status" header="Status">
          <template #body="{ data }">
            <span
              :class="[
                'inline-flex px-2 py-0.5 rounded text-xs font-medium',
                data.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600',
              ]"
            >
              {{ data.status === 'aktif' ? 'Aktif' : (data.status || '-') }}
            </span>
          </template>
        </Column>
        <Column header="Terakhir login">
          <template #body="{ data }">
            {{ data.last_login ? formatDate(data.last_login) : '-' }}
          </template>
        </Column>
        <Column header="#" :style="{ width: '80px' }">
          <template #body="{ data }">
            <Button
              icon="fas fa-ellipsis-v"
              text
              @click="(e) => toggleMenu(e, data)"
              aria-haspopup="true"
              class="p-button-circle p-button-text w-8 h-8 flex items-center justify-center"
            />
            <Menu ref="menuRef" :model="getMenuItems(data)" :popup="true" appendTo="body">
              <template #item="{ item }">
                <a
                  class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-gray-100"
                  @click="item.command && (item.command as (e?: unknown) => void)(undefined)"
                >
                  <i :class="[item.icon, item.iconColor || 'text-gray-600']"></i>
                  <span>{{ item.label }}</span>
                </a>
              </template>
            </Menu>
          </template>
        </Column>
      </DataTable>
    </Card>

    <!-- Modal Detail User -->
    <Modal
      :open="showDetailModal"
      size="medium"
      rounded
      :closeOnEsc="true"
      :closeOnOutside="true"
      cancelLabel="Tutup"
      :showAction="false"
      :hideFooter="false"
      @close="showDetailModal = false"
    >
      <template #header>
        <span class="flex items-center gap-2">
          <i class="fas fa-user text-[#007b6f]"></i>
          Detail User
        </span>
      </template>
      <template #content>
        <div v-if="selectedUser" class="space-y-4">
          <div class="grid grid-cols-1 gap-3 text-sm">
            <div class="flex border-b border-gray-100 pb-2">
              <span class="w-36 text-gray-500">Nama</span>
              <span class="font-medium">{{ selectedUser.nama }}</span>
            </div>
            <div class="flex border-b border-gray-100 pb-2">
              <span class="w-36 text-gray-500">Email</span>
              <span>{{ selectedUser.email }}</span>
            </div>
            <div class="flex border-b border-gray-100 pb-2">
              <span class="w-36 text-gray-500">Role</span>
              <span>{{ selectedUser.role || '-' }}</span>
            </div>
            <div class="flex border-b border-gray-100 pb-2">
              <span class="w-36 text-gray-500">Status</span>
              <span :class="selectedUser.status === 'aktif' ? 'text-green-600' : 'text-gray-500'">
                {{ selectedUser.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
            <div class="flex border-b border-gray-100 pb-2">
              <span class="w-36 text-gray-500">Terakhir login</span>
              <span>{{ selectedUser.last_login ? formatDate(selectedUser.last_login) : '-' }}</span>
            </div>
          </div>
        </div>
      </template>
    </Modal>

    <!-- Modal Edit User -->
    <Modal
      :open="showEditModal"
      size="medium"
      rounded
      :closeOnEsc="true"
      :closeOnOutside="true"
      cancelLabel="Batal"
      actionLabel="Simpan"
      :showAction="true"
      :hideFooter="false"
      @close="tutupEditModal"
      @action="simpanEditUser"
    >
      <template #header>
        <span class="flex items-center gap-2">
          <i class="fas fa-user-edit text-[#007b6f]"></i>
          Edit User
        </span>
      </template>
      <template #content>
        <form v-if="editForm" @submit.prevent="simpanEditUser" class="space-y-4">
          <p v-if="editPesan" :class="editPesanSukses ? 'text-green-600 text-sm' : 'text-red-600 text-sm'">
            {{ editPesan }}
          </p>
          <Field label="Nama Lengkap" id="edit-nama" required>
            <input
              v-model="editForm.nama_lengkap"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Nama lengkap"
            />
          </Field>
          <Field label="Email" id="edit-email" required>
            <input
              v-model="editForm.email"
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="email@contoh.com"
            />
          </Field>
          <Field label="No. Handphone" id="edit-hp">
            <input
              v-model="editForm.no_handphone"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="08xxxxxxxxxx"
            />
          </Field>
          <Field label="Jenis Kelamin" id="edit-jk">
            <select
              v-model="editForm.jenis_kelamin"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            >
              <option value="">— Pilih —</option>
              <option value="laki-laki">Laki-laki</option>
              <option value="perempuan">Perempuan</option>
            </select>
          </Field>
          <Field label="Tanggal Lahir" id="edit-tgl">
            <input
              v-model="editForm.tgl_lahir"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            />
          </Field>
          <Field label="Status" id="edit-status">
            <select
              v-model="editForm.is_active"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            >
              <option :value="true">Aktif</option>
              <option :value="false">Nonaktif</option>
            </select>
          </Field>
          <Field label="Password baru (kosongkan jika tidak diubah)" id="edit-password">
            <input
              v-model="editForm.password"
              type="password"
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="••••••••"
            />
          </Field>
        </form>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'
import Modal from '@/components/base/modal/Modal.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Menu from 'primevue/menu'
import { useApi } from '@/api/useApi'

const api = useApi()
const loading = ref(false)
const searchQuery = ref('')
const users = ref<any[]>([])
const menuRef = ref()
const selectedUser = ref<any>(null)
const showDetailModal = ref(false)
const showEditModal = ref(false)
const editPesan = ref('')
const editPesanSukses = ref(false)
const savingEdit = ref(false)

const editForm = reactive<{
  id: number | null
  nama_lengkap: string
  email: string
  no_handphone: string
  jenis_kelamin: string
  tgl_lahir: string
  is_active: boolean
  password: string
}>({
  id: null,
  nama_lengkap: '',
  email: '',
  no_handphone: '',
  jenis_kelamin: '',
  tgl_lahir: '',
  is_active: true,
  password: '',
})

function getMenuItems(data: any) {
  return [
    {
      label: 'Detail',
      icon: 'fas fa-eye',
      iconColor: 'text-blue-600',
      command: () => detailUser(data),
    },
    {
      label: 'Edit',
      icon: 'fas fa-edit',
      iconColor: 'text-amber-600',
      command: () => editUser(data),
    },
    {
      label: data?.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan',
      icon: 'fas fa-user-slash',
      iconColor: 'text-red-600',
      command: () => toggleStatus(data),
    },
  ]
}

const filteredUsers = computed(() => {
  let list = users.value
  const q = (searchQuery.value || '').trim().toLowerCase()
  if (q) {
    list = list.filter(
      (u) =>
        (u.nama && u.nama.toLowerCase().includes(q)) ||
        (u.email && u.email.toLowerCase().includes(q)),
    )
  }
  return list.map((u, i) => ({ ...u, no: i + 1 }))
})

function formatDate(val: string) {
  if (!val) return '-'
  try {
    const d = new Date(val)
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch {
    return val
  }
}

function toggleMenu(event: Event, data: any) {
  selectedUser.value = data
  menuRef.value?.toggle(event)
}

function detailUser(user: any) {
  selectedUser.value = user
  showDetailModal.value = true
}

function editUser(user: any) {
  selectedUser.value = user
  editForm.id = user.id ?? null
  editForm.nama_lengkap = user.nama_lengkap ?? user.nama ?? ''
  editForm.email = user.email ?? ''
  editForm.no_handphone = user.no_handphone ?? ''
  editForm.jenis_kelamin = user.jenis_kelamin ?? ''
  editForm.tgl_lahir = user.tgl_lahir ? (typeof user.tgl_lahir === 'string' ? user.tgl_lahir.slice(0, 10) : '') : ''
  editForm.is_active = user.status === 'aktif' || user.is_active === true
  editForm.password = ''
  editPesan.value = ''
  showEditModal.value = true
}

function tutupEditModal() {
  showEditModal.value = false
  editForm.id = null
  editForm.nama_lengkap = ''
  editForm.email = ''
  editForm.no_handphone = ''
  editForm.jenis_kelamin = ''
  editForm.tgl_lahir = ''
  editForm.is_active = true
  editForm.password = ''
  editPesan.value = ''
}

async function simpanEditUser() {
  if (!editForm.id) return
  editPesan.value = ''
  editPesanSukses.value = false
  savingEdit.value = true
  try {
    const payload: Record<string, unknown> = {
      nama_lengkap: editForm.nama_lengkap,
      email: editForm.email,
      no_handphone: editForm.no_handphone || null,
      jenis_kelamin: editForm.jenis_kelamin || null,
      tgl_lahir: editForm.tgl_lahir || null,
      is_active: editForm.is_active,
    }
    if (editForm.password && editForm.password.trim()) {
      payload.password = editForm.password.trim()
    }
    await api.put(`/sistem-admin/user/update/${editForm.id}`, payload)
    editPesan.value = 'Data user berhasil diperbarui.'
    editPesanSukses.value = true
    fetchData()
    setTimeout(() => {
      tutupEditModal()
    }, 1500)
  } catch (e: any) {
    editPesan.value = e?.response?.data?.message || e?.message || 'Gagal menyimpan.'
    editPesanSukses.value = false
  } finally {
    savingEdit.value = false
  }
}

function toggleStatus(user: any) {
  const action = user?.status === 'aktif' ? 'nonaktifkan' : 'aktifkan'
  if (!confirm(`Yakin ingin ${action} user "${user?.nama}"?`)) return
  // TODO: panggil API saat endpoint tersedia, mis. api.put('/sistem-admin/user/toggle-status/' + user.id)
  alert(`Fitur ${action} user akan berjalan setelah API tersedia.`)
  fetchData()
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/sistem-admin/user/get-list-user-aktif').catch(() => ({ data: null }))
    const body = res?.data
    const data = Array.isArray(body?.data) ? body.data : Array.isArray(body) ? body : []
    users.value = data.map((u: any, i: number) => ({
      no: i + 1,
      nama: u.nama_lengkap || u.name || u.nama || '-',
      email: u.email || '-',
      role: typeof u.role === 'string' ? u.role : (u.roles?.[0]?.name ?? u.roles?.[0] ?? u.role ?? '-'),
      status: u.is_active !== false ? 'aktif' : 'nonaktif',
      last_login: u.last_login_at || u.updated_at || null,
      ...u,
    }))
  } catch (err) {
    console.error('Gagal memuat data user:', err)
    users.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
