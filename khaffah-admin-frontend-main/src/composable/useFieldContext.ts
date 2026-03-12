import { inject, provide, reactive } from "vue";

export type FieldContext = {
  id?: string;
  required?: boolean;
};

const FIELD_SYMBOL = Symbol("FieldContext");

export function useProvideFieldContext(ctx: FieldContext) {
  const state = reactive(ctx);
  provide(FIELD_SYMBOL, state);
  return state;
}

export function useFieldContext(defaults: FieldContext = {}) {
  const parent = inject<FieldContext | null>(FIELD_SYMBOL, null);
  return parent ?? reactive(defaults);
}
