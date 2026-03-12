import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Login from '@/views/auth/Login.vue'
import Dashboard from '@/views/Dashboard.vue'
import Handling from '@/views/Handling.vue'
import VerifikasiMitra from '@/views/Pendaftaran/Verifikasi-Mitra.vue'
import CreatePaketHaji from '@/views/Paket/CreatePaketHaji.vue'
import CreatePaketUmrah from '@/views/Paket/CreatePaketUmrah.vue'
import SettingTentangKami from '@/views/sistem-admin/Setting-Tentang-Kami.vue'
import SettingSyaratKetentuan from '@/views/sistem-admin/Setting-Syarat-Ketentuan.vue'
import SettingKebijakanPrivasi from '@/views/sistem-admin/Setting-Kebijakan-Privasi.vue'
import SettingFaq from '@/views/sistem-admin/Setting-FAQ.vue'
import SettingPengaturanAplikasi from '@/views/sistem-admin/Setting-Pengaturan-Aplikasi.vue'
import ManajemenBanner from '@/views/sistem-admin/Manajemen-Banner.vue'
import ManajemenBannerForm from '@/views/sistem-admin/Manajemen-Banner-Form.vue'
import UserManagement from '@/views/sistem-admin/User-Management.vue'
import DataMitra from '@/views/sistem-admin/Data-Mitra.vue'
import PendaftaranMitra from '@/views/Pendaftaran/Pendaftaran-mitra.vue'
import MonitoringOperasional from '@/views/Manajemen/Monitoring-Operasional.vue'
import MasterHotelForm from '@/views/Paket/Master-Hotel-Form.vue'
import MasterMaskapaiForm from '@/views/Paket/Master-Maskapai-Form.vue'
import MasterKeberangkatanForm from '@/views/Paket/Master-Keberangkatan-Form.vue'
import MasterLevelMitra from '@/views/Paket/Master-Level-Mitra.vue'
import MasterLevelMitraForm from '@/views/Paket/Master-Level-Mitra-Form.vue'
import PaketRequest from '@/views/paket-request.vue'
import DetailTransaksi from '@/views/Daftar-Transaksi/Detail-Transaksi.vue'
import PemesananPaketUmrah from '@/views/Daftar-Transaksi/Pemesanan-Paket-Umrah.vue'
import PendaftaranHaji from '@/views/Daftar-Transaksi/Pendaftaran-Haji.vue'
import PeminatEdutrip from '@/views/Daftar-Transaksi/Peminat-Edutrip.vue'
import PermintaanCustom from '@/views/Daftar-Transaksi/Permintaan-Custom.vue'
import DaftarRefund from '@/views/Daftar-Transaksi/Daftar-Refund.vue'
import { useApi } from '@/api/useApi'
import { useUserSession } from '@/stores/userSession'
import { useModulesStore } from '@/stores/modules'

let dynamicRoutesInitialized = false

// helper to add routes dynamically from backend modules
export async function initDynamicRoutes(modulesFromSidebar?: any[]) {
  const viewModules: Record<string, () => Promise<any>> = import.meta.glob('../views/**/*.vue')

  let modules = modulesFromSidebar

  // If modules are not provided, try to fetch them from the API
  if (!Array.isArray(modules)) {
    try {
      const api = useApi()
      const userSession = useUserSession()

      const res = await api.post('/sistem-admin/get-modul-dinamis', {
        subroles: userSession.user?.subroles ?? [],
      }) as { data?: unknown[] }

      modules = Array.isArray(res?.data) ? res.data : []

      // populate modules store so UI (sidebar) can read them
      try {
        const modulesStore = useModulesStore()
        modulesStore.setModules(modules)
      } catch (e) {
        // ignore if pinia not yet active or similar
      }
    } catch (err) {
      console.error('Failed to fetch modules for dynamic routes', err)
      modules = []
    }
  }

  // if modules are provided (from caller) populate the modules store too
  if (Array.isArray(modules) && modules.length > 0) {
    try {
      const modulesStore = useModulesStore()
      modulesStore.setModules(modules)
    } catch (e) {
      // ignore
    }
  }

  if (!Array.isArray(modules)) return

  modules.forEach((m: any) => {
    m.child.forEach((child: any) => {
      const name = `dynamic-${child.sub_modul_id}`

      const urlPath = (child.url || '').replace(/^\//, '')
      const candidates = [`../views/${urlPath}.vue`, `../views/${urlPath}/index.vue`]

      let component = null
      for (const c of candidates) {
        if (viewModules[c]) {
          component = viewModules[c]
          break
        }
      }

      if (!component) {
        component = () => import('@/views/DynamicView.vue')
      }

      if (!router.hasRoute(name)) {
        router.addRoute({
          path: child.url,
          name,
          component,
          meta: {
            requiresAuth: true,
            title: child.nama_sub_modul,
          },
        })
      }
    })
  })
}

export function resetDynamicRoutes() {
  dynamicRoutesInitialized = false

  router.getRoutes().forEach((route) => {
    if (route.name && String(route.name).startsWith('dynamic-')) {
      router.removeRoute(route.name)
    }
  })
}

const routes: RouteRecordRaw[] = [
  { path: '/', redirect: '/dashboard' },
  { path: '/auth/login', name: 'login', component: Login, meta: { layout: 'empty' } },
  { path: '/handling', name: 'handling', component: Handling, meta: { layout: 'empty' } },

  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },

  {
    path: '/paket-request',
    name: 'paket-request',
    component: PaketRequest,
    meta: { requiresAuth: true, title: 'Paket Request' },
  },
  {
    path: '/Daftar-Transaksi/Pemesanan-Paket-Umrah',
    name: 'pemesanan-paket-umrah',
    component: PemesananPaketUmrah,
    meta: { requiresAuth: true, title: 'Pemesanan Paket Umrah' },
  },
  {
    path: '/Daftar-Transaksi/Pendaftaran-Haji',
    name: 'pendaftaran-haji',
    component: PendaftaranHaji,
    meta: { requiresAuth: true, title: 'Pendaftaran Haji' },
  },
  {
    path: '/Daftar-Transaksi/Peminat-Edutrip',
    name: 'peminat-edutrip',
    component: PeminatEdutrip,
    meta: { requiresAuth: true, title: 'Peminat Edutrip' },
  },
  {
    path: '/Daftar-Transaksi/Permintaan-Custom',
    name: 'permintaan-custom',
    component: PermintaanCustom,
    meta: { requiresAuth: true, title: 'Permintaan Custom' },
  },
  {
    path: '/Daftar-Transaksi/Daftar-Refund',
    name: 'daftar-refund',
    component: DaftarRefund,
    meta: { requiresAuth: true, title: 'Daftar Refund' },
  },
  {
    path: '/Daftar-Transaksi/Permintaan-Custom/:id',
    name: 'permintaan-custom-detail',
    component: DetailTransaksi,
    meta: {
      requiresAuth: true,
      title: 'Detail Permintaan Custom',
      backPath: '/Daftar-Transaksi/Permintaan-Custom',
      detailType: 'custom',
    },
  },
  {
    path: '/Daftar-Transaksi/Pemesanan-Paket-Umrah/:id',
    name: 'pemesanan-paket-umrah-detail',
    component: DetailTransaksi,
    meta: {
      requiresAuth: true,
      title: 'Detail Pemesanan Paket Umrah',
      backPath: '/Daftar-Transaksi/Pemesanan-Paket-Umrah',
      detailType: 'umrah',
    },
  },
  {
    path: '/Daftar-Transaksi/Pendaftaran-Haji/:id',
    name: 'pendaftaran-haji-detail',
    component: DetailTransaksi,
    meta: {
      requiresAuth: true,
      title: 'Detail Pendaftaran Haji',
      backPath: '/Daftar-Transaksi/Pendaftaran-Haji',
      detailType: 'haji',
    },
  },
  {
    path: '/Daftar-Transaksi/Peminat-Edutrip/:id',
    name: 'peminat-edutrip-detail',
    component: DetailTransaksi,
    meta: {
      requiresAuth: true,
      title: 'Detail Peminat Edutrip',
      backPath: '/Daftar-Transaksi/Peminat-Edutrip',
      detailType: 'edutrip',
    },
  },

  {
    path: '/verifikasi-mitra/:id',
    name: 'verifikasi-mitra',
    component: VerifikasiMitra,
    meta: { requiresAuth: true },
  },

  {
    path: '/paket/haji/form/:id?',
    name: 'form-paket-haji',
    component: CreatePaketHaji,
    meta: { requiresAuth: true },
  },
  {
    path: '/paket/umrah/form/:id?',
    name: 'form-paket-umrah',
    component: CreatePaketUmrah,
    meta: { requiresAuth: true },
  },
  {
    path: '/Paket/Master-Hotel/form/:id?',
    name: 'master-hotel-form',
    component: MasterHotelForm,
    meta: { requiresAuth: true, title: 'Form Hotel' },
  },
  {
    path: '/Paket/Master-Maskapai/form/:id?',
    name: 'master-maskapai-form',
    component: MasterMaskapaiForm,
    meta: { requiresAuth: true, title: 'Form Maskapai' },
  },
  {
    path: '/Paket/Master-Keberangkatan/form/:id?',
    name: 'master-keberangkatan-form',
    component: MasterKeberangkatanForm,
    meta: { requiresAuth: true, title: 'Form Keberangkatan' },
  },
  {
    path: '/Paket/Master-Level-Mitra',
    name: 'master-level-mitra',
    component: MasterLevelMitra,
    meta: { requiresAuth: true, title: 'Master Level Mitra' },
  },
  {
    path: '/Paket/Master-Level-Mitra/form/:id?',
    name: 'master-level-mitra-form',
    component: MasterLevelMitraForm,
    meta: { requiresAuth: true, title: 'Form Level Mitra' },
  },
  {
    path: '/sistem-admin/Setting-Tentang-Kami',
    name: 'setting-tentang-kami',
    component: SettingTentangKami,
    meta: { requiresAuth: true, title: 'Setting Tentang Kami' },
  },
  {
    path: '/sistem-admin/Setting-Syarat-Ketentuan',
    name: 'setting-syarat-ketentuan',
    component: SettingSyaratKetentuan,
    meta: { requiresAuth: true, title: 'Setting Syarat & Ketentuan' },
  },
  {
    path: '/sistem-admin/Setting-Kebijakan-Privasi',
    name: 'setting-kebijakan-privasi',
    component: SettingKebijakanPrivasi,
    meta: { requiresAuth: true, title: 'Setting Kebijakan Privasi' },
  },
  {
    path: '/sistem-admin/Setting-FAQ',
    name: 'setting-faq',
    component: SettingFaq,
    meta: { requiresAuth: true, title: 'Setting FAQ' },
  },
  {
    path: '/sistem-admin/Setting-Pengaturan-Aplikasi',
    name: 'setting-pengaturan-aplikasi',
    component: SettingPengaturanAplikasi,
    meta: { requiresAuth: true, title: 'Pengaturan Aplikasi' },
  },
  {
    path: '/sistem-admin/Manajemen-Banner',
    name: 'manajemen-banner',
    component: ManajemenBanner,
    meta: { requiresAuth: true, title: 'Manajemen Banner' },
  },
  {
    path: '/sistem-admin/Manajemen-Banner/form/:id?',
    name: 'manajemen-banner-form',
    component: ManajemenBannerForm,
    meta: { requiresAuth: true, title: 'Form Banner' },
  },
  {
    path: '/sistem-admin/User-Management',
    name: 'user-management',
    component: UserManagement,
    meta: { requiresAuth: true, title: 'User Management' },
    alias: '/Sistem-Admin/User-Management',
  },
  {
    path: '/Manajemen/Monitoring-Operasional',
    name: 'monitoring-operasional',
    component: MonitoringOperasional,
    meta: { requiresAuth: true, title: 'Monitoring Operasional' },
  },
  {
    path: '/Mitra/Data-Mitra',
    name: 'mitra-data-mitra',
    component: DataMitra,
    meta: { requiresAuth: true, title: 'Data Mitra' },
  },
  {
    path: '/Mitra/Pendaftaran-Mitra',
    name: 'mitra-pendaftaran-mitra',
    component: PendaftaranMitra,
    meta: { requiresAuth: true, title: 'Pendaftaran Mitra' },
    alias: '/Pendaftaran/Pendaftaran-mitra',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// route guard
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  const userSession = useUserSession()

  // kalau belum login dan butuh auth
  if (to.meta.requiresAuth && !auth.token) {
    return next('/auth/login')
  }

  // kalau udah login tapi belum ambil profile (misalnya reload)
  if (auth.token && !userSession.user) {
    try {
      await auth.getProfile()
    } catch {
      auth.logout()
      return next('/auth/login')
    }
  }

  if (auth.token && userSession.user && !dynamicRoutesInitialized) {
    dynamicRoutesInitialized = true

    try {
      await initDynamicRoutes()
      return next({ ...to, replace: true })
    } catch (err) {
      console.error('Failed to initialize dynamic routes:', err)
    }
  }

  // cek role superadmin
  if (to.meta.role === 'superadmin') {
    const roles = userSession.user?.roles || []
    if (!roles.includes('superadmin')) {
      return next('/handling')
    }
  }

  next()
})

export default router
