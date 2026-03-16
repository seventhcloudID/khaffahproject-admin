/** Role dari backend (bisa object atau string) */
export type UserRole = string | { id?: number; name?: string; [key: string]: unknown }

export interface User {
  id: number
  nama_lengkap: string
  jenis_kelamin?: string
  tgl_lahir?: string
  email: string
  no_handphone?: string
  foto_profile?: string | null
  roles?: string[]
  subroles?: string[] | Record<string, unknown>[]
  [key: string]: unknown
}

/** Response body dari POST /login dan GET /me (setelah interceptor, jadi langsung body) */
export interface LoginResponse {
  token: string
  id: number
  nama_lengkap: string
  jenis_kelamin?: string
  tgl_lahir?: string
  email: string
  no_handphone?: string
  foto_profile?: string | null
  roles?: string[] | UserRole[]
  subroles?: string[] | Record<string, unknown>[]
  role?: string
  dashboard_url?: string
  [key: string]: unknown
}
