<script lang="ts">
import { defineComponent, computed, h, type PropType } from 'vue'
import { RouterLink, type RouteLocationRaw } from 'vue-router'

import Placeload from '../loader/Placeload.vue'
import { CssUnitRe } from '@/utils/regex'

export type ButtonSize = 'medium' | 'big' | 'huge' | 'sm'
export type ButtonColor =
  | 'primary'
  | 'info'
  | 'success'
  | 'warning'
  | 'danger'
  | 'purple'
  | 'white'
  | 'dark'
  | 'light'
  | 'primary-grey'
  | 'secondary'

export default defineComponent({
  name: 'Button',

  props: {
    to: [Object, String] as PropType<RouteLocationRaw>,
    href: String,

    icon: String,
    iconCaret: String,

    placeload: {
      type: String,
      validator: (value: string) => {
        if (!value.match(CssUnitRe)) {
          console.warn(`Button: "${value}" adalah unit CSS tidak valid.`)
        }
        return true
      },
    },

    color: String as PropType<ButtonColor>,
    size: String as PropType<ButtonSize>,

    dark: String,

    rounded: Boolean,
    bold: Boolean,
    fullwidth: Boolean,
    light: Boolean,
    raised: Boolean,
    elevated: Boolean,
    outlined: Boolean,
    darkOutlined: Boolean,
    loading: Boolean,
    lower: Boolean,
    disabled: Boolean,
    static: Boolean,
    
    // 🆕 New prop untuk kontrol responsive behavior
    hideTextOnMobile: {
      type: Boolean,
      default: false,
    },
    circle: {
      type: Boolean,
      default: false,
    },
  },

  setup(props, { slots, attrs }) {
    // -----------------------------------------
    // 1️⃣ CLASS: sudah diganti ke Tailwind
    // -----------------------------------------
    const classes = computed(() => {
      const external = (attrs.class ?? []) as string[]

      const base = 'inline-flex items-center justify-center gap-2 transition-all select-none'

      const sizeMap: Record<ButtonSize, string> = {
        sm: 'px-3 py-1.5 text-sm',
        medium: 'px-4 py-2 text-sm',
        big: 'px-5 py-2.5 text-base',
        huge: 'px-6 py-3 text-lg',
      }

      // 🆕 Circle size mapping (width = height, no text gap)
      const circleSizeMap: Record<ButtonSize, string> = {
        sm: 'w-8 h-8 p-0',
        medium: 'w-10 h-10 p-0',     // 40x40px
        big: 'w-12 h-12 p-0',         // 48x48px
        huge: 'w-14 h-14 p-0',        // 56x56px
      }

      // 🆕 Mobile size mapping (icon only mode - smaller padding)
      const mobileSizeMap: Record<ButtonSize, string> = {
        sm: 'px-1.5 py-1.5',
        medium: 'px-2 py-2',
        big: 'px-2.5 py-2.5',
        huge: 'px-3 py-3',
      }

      const effectiveSize: ButtonSize = props.size ?? 'medium'

      // Filled variants (bg + text + hover)
      const filledMap: Record<string, string> = {
        primary: 'bg-blue-600 text-white hover:bg-blue-700',
        info: 'bg-sky-500 text-white hover:bg-sky-600',
        success: 'bg-green-600 text-white hover:bg-green-700',
        warning: 'bg-yellow-500 text-white hover:bg-yellow-600',
        danger: 'bg-red-600 text-white hover:bg-red-700',
        purple: 'bg-purple-600 text-white hover:bg-purple-700',
        white: 'bg-white text-gray-800 border border-gray-300 hover:bg-gray-50',
        dark: 'bg-gray-800 text-white hover:bg-gray-900',
        light: 'bg-gray-100 text-gray-800 hover:bg-gray-200',
        'primary-grey': 'bg-gray-500 text-white hover:bg-gray-600',
        secondary: 'bg-gray-200 text-gray-800 hover:bg-gray-300',
      }

      const outlinedMap: Record<string, string> = {
        primary:
          'bg-transparent border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white',
        info: 'bg-transparent border border-sky-500 text-sky-500 hover:bg-sky-500 hover:text-white',
        success:
          'bg-transparent border border-green-600 text-green-600 hover:bg-green-600 hover:text-white',
        warning:
          'bg-transparent border border-yellow-500 text-yellow-600 hover:bg-yellow-500 hover:text-white',
        danger:
          'bg-transparent border border-red-600 text-red-600 hover:bg-red-600 hover:text-white',
        purple:
          'bg-transparent border border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white',
        white:
          'bg-transparent border border-gray-300 text-gray-800 hover:bg-gray-300 hover:text-white',
        dark: 'bg-transparent border border-gray-800 text-gray-800 hover:bg-gray-800 hover:text-white',
        light:
          'bg-transparent border border-gray-200 text-gray-800 hover:bg-gray-200 hover:text-white',
        'primary-grey':
          'bg-transparent border border-gray-500 text-gray-500 hover:bg-gray-500 hover:text-white',
        secondary:
          'bg-transparent border border-gray-400 text-gray-700 hover:bg-gray-200 hover:text-gray-900',
      }

      // 🆕 Conditional size class based on hideTextOnMobile
      const sizeCls = props.hideTextOnMobile 
        ? `${mobileSizeMap[effectiveSize]} md:${sizeMap[effectiveSize]}` 
        : sizeMap[effectiveSize]

      const effectiveColor = (props.color ?? 'primary') as keyof typeof filledMap
      const variantCls = props.outlined
        ? outlinedMap[effectiveColor] ?? outlinedMap.primary
        : filledMap[effectiveColor] ?? filledMap.primary

      const rounded = props.rounded ? 'rounded-full' : 'rounded-md'
      const bold = props.bold ? 'font-bold' : 'font-medium'
      const full = props.fullwidth ? 'w-full' : ''
      const lower = props.lower ? 'lowercase' : ''
      const elevated = props.elevated ? 'shadow-lg' : props.raised ? 'shadow' : ''
      const loading = props.loading ? 'opacity-70 cursor-not-allowed' : ''
      const disabled = props.disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''

      return [
        ...external,
        base,
        sizeCls,
        variantCls,
        rounded,
        bold,
        full,
        lower,
        elevated,
        loading,
        disabled,
      ]
    })

    // -----------------------------------------
    // 2️⃣ ICON LOGIC (tetap sama)
    // -----------------------------------------
    const isIconify = (name?: string) => name?.includes(':')

    const renderIcon = (name?: string, wrapperClass = 'icon') => {
      if (!name) return null

      return h(
        'span',
        { class: wrapperClass },
        isIconify(name) ? h('i', { class: 'iconify', 'data-icon': name }) : h('i', { class: name }),
      )
    }

    // -----------------------------------------
    // 3️⃣ CHILDREN - 🆕 dengan responsive text
    // -----------------------------------------
    const getChildren = () => {
      const children = []

      // 🆕 Untuk circle button, hanya render icon (no text)
      if (props.circle) {
        const icon = renderIcon(props.icon)
        if (icon) children.push(icon)
        return children
      }

      // Logic normal (ada text)
      const iconLeft = renderIcon(props.icon)
      if (iconLeft) children.push(iconLeft)

      if (props.placeload) {
        children.push(h(Placeload, { width: props.placeload }))
      } else {
        // 🆕 Tambah class hidden md:inline untuk text jika hideTextOnMobile = true
        const textClass = props.hideTextOnMobile ? 'hidden md:inline' : ''
        children.push(h('span', { class: textClass }, slots.default?.()))
      }

      const caret = renderIcon(props.iconCaret, 'caret')
      if (caret) children.push(caret)

      return children
    }

    // -----------------------------------------
    // 4️⃣ RouterLink / <a> / <button>
    // -----------------------------------------
    const commonProps = {
      ...attrs,
      'aria-hidden': props.placeload ? true : undefined,
      class: classes.value,
    }

    return () => {
      if (props.to) {
        return h(RouterLink, { ...commonProps, to: props.to }, { default: getChildren })
      }

      if (props.href) {
        return h('a', { ...commonProps, href: props.href }, getChildren())
      }

      return h(
        'button',
        { ...commonProps, type: 'button', disabled: props.disabled || props.loading },
        getChildren(),
      )
    }
  },
})
</script>
