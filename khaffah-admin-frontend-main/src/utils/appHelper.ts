// src/helpers/appHelper.ts

class AppHelper {
  /**
   * Parse date so that date-only strings (YYYY-MM-DD) tidak bergeser karena timezone.
   * new Date("2025-03-08") = UTC midnight → di timezone UTC-5 jadi 07/03/2025.
   * Dengan parse sebagai tanggal lokal, "2025-03-08" selalu tampil 08/03/2025.
   */
  parseDateSafe(date: any): Date | null {
    if (date == null) return null
    if (date instanceof Date) return isNaN(date.getTime()) ? null : date
    const s = typeof date === 'string' ? date.trim() : String(date)
    const dateOnlyMatch = s.match(/^(\d{4})-(\d{2})-(\d{2})$/)
    if (dateOnlyMatch) {
      const y = Number(dateOnlyMatch[1])
      const m = Number(dateOnlyMatch[2])
      const d = Number(dateOnlyMatch[3])
      const local = new Date(y, m - 1, d)
      return isNaN(local.getTime()) ? null : local
    }
    const parsed = new Date(date)
    return isNaN(parsed.getTime()) ? null : parsed
  }

  formatDate(date: any, format: string = 'YYYY-MM-DD HH:mm:ss') {
    const d = this.parseDateSafe(date)
    if (!d) return ''

    const map: Record<string, string> = {
      YYYY: d.getFullYear().toString(),
      MM: String(d.getMonth() + 1).padStart(2, '0'),
      DD: String(d.getDate()).padStart(2, '0'),
      HH: String(d.getHours()).padStart(2, '0'),
      mm: String(d.getMinutes()).padStart(2, '0'),
      ss: String(d.getSeconds()).padStart(2, '0'),
    }

    let result = format
    Object.keys(map).forEach((k) => {
      const repl = map[k]
      if (repl != null) result = result.replace(k, repl)
    })

    return result
  }

  parseDateRange(dateRange: any) {
    // Jika null
    if (!dateRange) return { dateStart: null, dateEnd: null }

    // Jika array of Date
    if (Array.isArray(dateRange)) {
      return {
        dateStart: dateRange[0] ? new Date(dateRange[0]) : null,
        dateEnd: dateRange[1] ? new Date(dateRange[1]) : null,
      }
    }

    // Jika string dipisah koma
    if (typeof dateRange === 'string') {
      const parts = dateRange.split(',')
      return {
        dateStart: parts[0] ? new Date(parts[0].trim()) : null,
        dateEnd: parts[1] ? new Date(parts[1].trim()) : null,
      }
    }

    // Fallback
    return { dateStart: null, dateEnd: null }
  }

  formatRupiah(value: any, includeSymbol: boolean = true) {
    if (value === null || value === undefined || value === '') {
      return includeSymbol ? 'Rp. 0' : '0'
    }

    // Remove non-numeric characters except minus and dot
    const cleaned = String(value).replace(/[^0-9-.,]/g, '')

    // Try to parse as number. Replace comma with dot if it's used as decimal separator
    const normalized = cleaned.replace(/,/g, '.')
    const num = Number(normalized)

    if (isNaN(num)) return includeSymbol ? 'Rp. 0' : '0'

    // Format without fraction digits (common for IDR)
    const formatted = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(num)

    return includeSymbol ? `Rp. ${formatted}` : formatted
  }
}

const H = new AppHelper()
export default H
