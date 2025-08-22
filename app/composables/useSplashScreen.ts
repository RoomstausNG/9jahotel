import { ref, readonly } from 'vue'

export const useSplashScreen = () => {
  const showSplash = ref(true)
  const isAppReady = ref(false)

  const hideSplash = () => {
    showSplash.value = false
    isAppReady.value = true
  }

  const resetSplash = () => {
    showSplash.value = true
    isAppReady.value = false
  }

  // Auto-hide splash screen after a maximum time (failsafe)
  const autoHideSplash = (maxDuration: number = 5000) => {
    setTimeout(() => {
      if (showSplash.value) {
        hideSplash()
      }
    }, maxDuration)
  }

  return {
    showSplash: readonly(showSplash),
    isAppReady: readonly(isAppReady),
    hideSplash,
    resetSplash,
    autoHideSplash
  }
}
