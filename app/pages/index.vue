<template>
  <div
    class="h-full w-full flex flex-col items-center justify-around bg-gradient-to-br from-primary-950 via-primary-800 to-primary-600 px-4 py-6 overflow-y-auto"
  >
    <!-- Logo -->
    <div class="w-full flex justify-center items-center">
      <NuxtImg
        src="https://res.cloudinary.com/dcm83lp3b/image/upload/e_upscale,e_enhance,e_improve,q_200,f_png,dpr_3.0/v1752508424/logo_transparent_bdqtya.png"
        quality="200"
        alt="9jahotel Logo"
        class="w-1/2 max-w-[200px] object-center object-cover h-[50px] block"
      />
    </div>
    <div
      class="w-full min-h-[50%] max-h-[60%] bg-white/10 border border-white/30 rounded-xl backdrop-blur-md p-0 overflow-hidden flex flex-col"
    >
      <!-- Top Image -->
      <div class="relative w-full h-[60%] max-h-[250px]">
        <NuxtImg
          src="https://res.cloudinary.com/dcm83lp3b/image/upload/w_750,h_400,c_fill,f_webp,q_85/v1752508514/slide_image_upd9ht.webp"
          alt="9jahotel Slide"
          class="object-cover w-full h-full"
        />
      </div>
      <!-- Slide Texts -->
      <div class="flex flex-col items-center px-4 py-6 h-[40%] justify-between">
        <div
          class="relative w-full min-h-[60px] flex flex-col items-center justify-center"
        >
          <Transition :name="transitionName" mode="out-in">
            <div :key="currentIndex" class="w-full">
              <h2
                class="text-white text-lg font-bold mb-1 text-center capitalize"
              >
                {{ slides[currentIndex].title }}
              </h2>
              <p class="text-white/90 text-sm mb-2 text-center">
                {{ slides[currentIndex].subtitle }}
              </p>
            </div>
          </Transition>
        </div>

        <div class="flex justify-center items-center gap-2">
          <button
            v-for="(slide, i) in slides"
            :key="i"
            class="w-4 h-2 rounded-full transition-all duration-300 focus:outline-none border border-white/40"
            :class="
              i === currentIndex ? 'bg-primary-600 w-12 shadow ' : 'bg-white/30'
            "
            aria-label="Go to slide"
            @click="goToSlide(i)"
          />
        </div>
      </div>
    </div>
    <div class="space-y-3 w-full">
      <UButton
        to="/home"
        class="capitalize block shadow-xl text-center"
        size="xl"
        >get Started</UButton
      >
      <ULink
        v-if="!user"
        to="/auth/login"
        class="text-white border rounded-lg font-medium text-center py-2 border-white w-full block capitalize"
        >Login</ULink
      >
    </div>
  </div>
</template>

<script setup>
const userStore = useUserStore();
const user = computed(() => userStore.user);

const slides = [
  {
    title: "FREE HOTEL NIGHT",
    subtitle: "Earn points nightly for free stays or cash!",
  },
  {
    title: "Same Room, Lower Prices!",
    subtitle: "Get Instant-Pay Discount when you pay online",
  },
  // {
  //   title: "BEST PRICES GUARANTEED",
  //   subtitle: "Unbeatable deals across Nigeria",
  // },
  {
    title: "Refundable Cancellation",
    subtitle: "Flexible cancellation options in case your plan changes",
  },
];
const currentIndex = ref(0);
const transitionName = ref("slide-fade");
let intervalId = null;

const nextSlide = () => {
  transitionName.value = "slide-fade";
  currentIndex.value = (currentIndex.value + 1) % slides.length;
};

const goToSlide = (i) => {
  if (i === currentIndex.value) return;
  transitionName.value = i > currentIndex.value ? "slide-fade" : "slide-fade";
  currentIndex.value = i;
  resetInterval();
};

function resetInterval() {
  if (intervalId) clearInterval(intervalId);
  intervalId = setInterval(nextSlide, 3000);
}

onMounted(() => {
  intervalId = setInterval(nextSlide, 3000);
});
onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<style scoped>
.bg-gradient-animated {
  background: linear-gradient(
    135deg,
    var(--color-primary-900),
    var(--color-elephant-600)
  );
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
}
@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(30px) scale(0.96);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-30px) scale(0.96);
}
</style>
