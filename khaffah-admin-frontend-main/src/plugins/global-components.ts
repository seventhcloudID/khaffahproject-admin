import type { App } from "vue";

const modules = import.meta.glob("@/components/base/*/*.vue", { eager: true });

export default {
  install(app: App) {
    for (const path in modules) {
      const mod: any = modules[path];
      const component = mod.default;

      // nama file tanpa extension
      const name = path.split("/").pop()!.replace(".vue", "");

      app.component(name, component);
    }
  },
};
