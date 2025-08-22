export default defineAppConfig({
  ui: {
    colors: {
      primary: "zuccini",
      secondary: "tael",
      tertiary: "elephant",
      neutral: "slate",
    },
    gray: "slate",
    strategy: "merge",
  },
  colorMode: {
    preference: "light", // Force light mode
    fallback: "light",
    hid: "nuxt-color-mode-script",
    globalName: "__NUXT_COLOR_MODE__",
    componentName: "ColorScheme",
    storageKey: "nuxt-color-mode",
  },
});
