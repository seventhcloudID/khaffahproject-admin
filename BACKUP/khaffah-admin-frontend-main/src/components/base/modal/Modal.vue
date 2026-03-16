<script setup lang="ts">
import { ref, watchEffect, onUnmounted, onMounted } from 'vue'

export type ModalSize = 'small' | 'medium' | 'large' | 'big' | 'verybig'
export type ModalActionAlign = 'center' | 'right'

export interface ModalProps {
  title?: string
  open?: boolean
  size?: ModalSize
  actions?: ModalActionAlign
  rounded?: boolean
  noscroll?: boolean
  noclose?: boolean
  tabs?: boolean
  closeOnEsc?: boolean
  closeOnOutside?: boolean
  hideFooter?: boolean
  cancelLabel?: string
  actionLabel?: string
  showAction?: boolean
}

const emit = defineEmits<{ 
  (e: 'close'): void
  (e: 'action'): void 
}>()

const props = withDefaults(defineProps<ModalProps>(), {
  closeOnEsc: true,
  closeOnOutside: true,
  cancelLabel: 'Close',
  actionLabel: 'Simpan',
  showAction: false,
})

const wasOpen = ref(false)

const close = () => {
  if (!props.noclose) emit('close')
}

const handleAction = () => {
  emit('action')
}

watchEffect(() => {
  if (props.noscroll && props.open) {
    document.documentElement.classList.add('overflow-hidden')
    wasOpen.value = true
  } else if (wasOpen.value && props.noscroll && !props.open) {
    document.documentElement.classList.remove('overflow-hidden')
    wasOpen.value = false
  }
})

onUnmounted(() => {
  document.documentElement.classList.remove('overflow-hidden')
})

const handleEsc = (e: KeyboardEvent) => {
  if (props.open && props.closeOnEsc && e.key === 'Escape') close()
}

onMounted(() => {
  window.addEventListener('keydown', handleEsc)
})
onUnmounted(() => {
  window.removeEventListener('keydown', handleEsc)
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 flex items-center justify-center z-[200]"
    >
      <!-- Background -->
      <div
        class="absolute inset-0 bg-black/60"
        @click="props.closeOnOutside && close()"
      ></div>

      <!-- Modal -->
      <div
        class="relative bg-white shadow-lg max-h-[90vh] overflow-hidden animate-modal-fade px-3"
        :class="[
          rounded ? 'rounded-xl' : 'rounded-md',
          {
            'max-w-[420px] w-full': size === 'small',
            'max-w-[640px] w-full': size === 'medium',
            'max-w-[72rem] w-full': size === 'large',
            'max-w-[840px] w-full': size === 'big',
            'max-w-[95vw] w-[95vw]': size === 'verybig',
          },
        ]"
      >
        <!-- Header -->
        <header
          v-if="$slots.header || title"
          class="flex items-center border-b border-gray-200 px-5 py-4"
        >
          <slot name="header">
            <h3 class="font-semibold text-gray-900 text-base">{{ title }}</h3>
            <button
              v-if="!noclose"
              class="ml-auto text-gray-500 hover:text-red-500 transition text-xl font-bold"
              @click="close"
            >
              ✕
            </button>
          </slot>
        </header>

        <!-- Body -->
        <div
          class="px-5 py-4 overflow-y-auto"
          :class="tabs ? 'pt-0' : ''"
            style="max-height: calc(90vh - 140px);"
        >
          <slot name="content"></slot>
        </div>

        <!-- Footer -->
        <footer
          v-if="!hideFooter"
          class="flex items-center border-t border-gray-200 px-5 py-4"
          :class="{
            'justify-center': actions === 'center',
            'justify-end': actions === 'right',
          }"
        >
          <div class="flex items-center gap-3">
            <!-- Tombol Tambahan -->
            <slot name="footer-left"></slot>

            <!-- Cancel Button -->
            <slot name="cancel" :close="close">
              <button
                v-if="!noclose"
                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition text-gray-700"
                @click="close"
              >
                {{ cancelLabel }}
              </button>
            </slot>

            <!-- Action Button -->
            <slot name="action" :close="close">
              <button
                v-if="showAction"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition"
                @click="handleAction"
              >
                {{ actionLabel }}
              </button>
            </slot>
          </div>
        </footer>
      </div>
    </div>
  </Teleport>
</template>

<style>
/* Animation */
@keyframes modal-fade {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-modal-fade {
  animation: modal-fade 0.25s ease-out;
}
</style>