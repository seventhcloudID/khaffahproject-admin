<script setup lang="ts">
import { computed } from 'vue'

defineOptions({
  inheritAttrs: false, // supaya kamu kontrol penuh atas class
})

export type CardRadius = 'regular' | 'smooth' | 'rounded'
export type CardPadding = 'none' | 'sm' | 'md' | 'lg'

export interface CardProps {
  elevated?: boolean
  radius?: CardRadius
  padding?: CardPadding
}

const props = withDefaults(defineProps<CardProps>(), {
  elevated: false,
  radius: 'regular',
  padding: 'md'
})

const cardClasses = computed(() => {
  return [
    // ===== OUTER WRAPPER =====
    // hanya styling kontainer (tidak boleh bersentuhan dengan styling komponen internal)
    'bg-white',
    props.elevated ? 'shadow-lg' : 'shadow-sm',

    props.radius === 'smooth' && 'rounded-xl',
    props.radius === 'rounded' && 'rounded-2xl',
    props.radius === 'regular' && 'rounded-lg'
  ]
})

const paddingClasses = computed(() => {
  switch (props.padding) {
    case 'none': return 'p-0'
    case 'sm':   return 'p-3'
    case 'md':   return 'p-6'
    case 'lg':   return 'p-8'
  }
})
</script>

<template>
  <div :class="[$attrs.class, cardClasses]">
    <div :class="paddingClasses">
      <slot />
    </div>
  </div>
</template>
