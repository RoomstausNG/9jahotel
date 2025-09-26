<template>
  <div
    class="mobile-app-layout max-w-lg border-2 w-screen mx-auto lg: h-screen relative overflow-hidden flex flex-col"
  >
    <main
      class="flex-1 bg-cover relative overflow-y-auto bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')]"
    >
      <slot />
    </main>
  </div>
</template>

<script setup>
const route = useRoute();
const router = useRouter();

// Menu state
const isMenuOpen = ref(false);

// Navigation methods (keeping for potential future use)
const _showBackButton = computed(() => {
  const backButtonRoutes = ["/hotel/", "/booking/", "/profile/edit"];
  return backButtonRoutes.some((routePath) => route.path.startsWith(routePath));
});

const _toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const _closeMenu = () => {
  isMenuOpen.value = false;
};

const _handleBack = () => {
  router.back();
};

const _handleLogout = () => {
  _closeMenu();
  router.push("/auth/login");
};
</script>

<style scoped>
/* Mobile App Layout */
.mobile-app-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #f8fafc;
}

/* Status Bar Safe Area */
.status-bar-safe-area {
  height: env(safe-area-inset-top);
  background: linear-gradient(
    135deg,
    var(--color-primary-900),
    var(--color-primary-600)
  );
}

/* App Header */
.app-header {
  background: linear-gradient(
    135deg,
    var(--color-primary-600),
    var(--color-primary-700)
  );
  color: white;
  position: sticky;
  top: 0;
  z-index: 40;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  height: 60px;
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 80px;
}

.header-right {
  justify-content: flex-end;
}

.header-center {
  flex: 1;
  display: flex;
  justify-content: center;
}

.app-title {
  display: flex;
  align-items: center;
  font-size: 1.25rem;
  font-weight: 600;
  color: white;
}

/* Main Content */
.main-content {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 80px; /* Space for bottom nav */
}

.content-wrapper {
  min-height: 100%;
}

/* Bottom Navigation */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #e2e8f0;
  z-index: 50;
  padding-bottom: env(safe-area-inset-bottom);
}

.nav-items {
  display: flex;
  justify-content: space-around;
  padding: 0.5rem 0;
}

/* Bottom Safe Area */
.bottom-safe-area {
  height: env(safe-area-inset-bottom);
  background: white;
}
</style>
