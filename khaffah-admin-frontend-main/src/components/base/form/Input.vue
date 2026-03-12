<script setup lang="ts">
import { computed, useSlots } from 'vue'
import { useProvideFieldContext } from '@/composable/useFieldContext'

export interface FieldProps {
  id?: string
  label?: string
  addons?: boolean
  textaddon?: boolean
  grouped?: boolean
  multiline?: boolean
  horizontal?: boolean
  required?: boolean
  raw?: boolean
}

const props = withDefaults(defineProps<FieldProps>(), {
  id: undefined,
  label: undefined,
})

const slots = useSlots()
const hasLabel = computed(() => Boolean(slots.label || props.label))

const ctx = useProvideFieldContext({
  id: props.id,
  required: props.required,
})

const classes = computed(() =>
  props.raw
    ? []
    : [
        'field',
        props.addons && 'has-addons',
        props.textaddon && 'has-textarea-addon',
        props.grouped && 'is-grouped',
        props.grouped && props.multiline && 'is-grouped-multiline',
        props.horizontal && 'is-horizontal',
      ],
)
</script>

<template>
  <div :class="classes">
    <template v-if="hasLabel && props.horizontal">
      <div class="field-label is-normal" :class="[props.required && 'is-required']">
        <slot name="label">
          <VLabel :for="props.id">{{ props.label }}</VLabel>
        </slot>
      </div>

      <div class="field-body">
        <slot v-bind="ctx"></slot>
      </div>
    </template>

    <template v-else-if="hasLabel">
      <slot name="label">
        <VLabel :for="props.id">{{ props.label }}</VLabel>
      </slot>

      <slot v-bind="ctx"></slot>
    </template>

    <template v-else>
      <slot v-bind="ctx"></slot>
    </template>
  </div>
</template>
