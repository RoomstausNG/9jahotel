export default defineNuxtPlugin(() => {
  // Force light mode
  if (import.meta.client) {
    // Set color mode to light
    const colorMode = useColorMode();
    colorMode.preference = "light";
    colorMode.value = "light";

    // Remove any dark mode classes from document
    document.documentElement.classList.remove("dark");
    document.documentElement.classList.add("light");

    // Set data attribute for consistency
    document.documentElement.setAttribute("data-color-mode", "light");
  }
});
