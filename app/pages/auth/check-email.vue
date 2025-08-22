<template>
  <div
    class="h-full overflow-y-auto bg-cover flex flex-col justify-center bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')]"
  >
    <div
      class="space-y-6 backdrop-brightness-50 flex flex-col justify-center p-6 mx-auto h-full w-full text-white"
    >
      <div class="text-center space-y-6">
        <NuxtImg
          size="xl"
          src="https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508285/9jahotel_qddu0r.png"
          class="block mx-auto h-auto w-20 rounded-full"
        />
        <h2 class="text-2xl font-bold">Check Your Email</h2>
        <p class="text-sm">
          We've sent a password reset link to
          <span class="font-semibold">{{ email }}</span>
        </p>
      </div>
      <div
        class="bg-white/30 rounded-xl p-4 max-w-md mx-auto w-full flex flex-col items-center mt-4"
      >
        <div class="w-full flex flex-col items-center mt-6">
          <UPinInput v-model="otp" type="number" size="xl" otp class="mb-4" />
          <div class="flex flex-col items-center mb-4">
            <span class="text-xs text-white/80">{{ timerDisplay }}</span>
            <UButton
              variant="link"
              class="text-primary-400 p-0 mt-1"
              :disabled="timer > 0 || isResending"
              :loading="isResending"
              @click="resendCode"
              >Resend OTP</UButton
            >
          </div>
          <UButton
            class="w-full text-center block mx-auto bg-primary"
            size="lg"
            :loading="isVerifying"
            @click="verifyOtp"
          >
            Verify Now
          </UButton>
        </div>
      </div>
      <div class="space-y-4">
        <UButton
          to="/auth/login"
          size="lg"
          class="bg-white/30 px-6 max-w-max mx-auto block rounded-full text-white cursor-pointer border border-white/50"
          block
        >
          Back to sign-in
        </UButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const otp = ref<number[]>([]);
const timer = ref(10);
const isVerifying = ref(false);
const isResending = ref(false);
const email = ref("");

const config = useRuntimeConfig();

const timerDisplay = computed(() => {
  const min = Math.floor(timer.value / 60)
    .toString()
    .padStart(2, "0");
  const sec = (timer.value % 60).toString().padStart(2, "0");
  return `${min}:${sec}`;
});

let interval: ReturnType<typeof setInterval> | null = null;

onMounted(async () => {
  // Fetch email from localStorage only
  email.value = localStorage.getItem("reset_email") || "";
  interval = setInterval(() => {
    if (timer.value > 0) timer.value--;
  }, 1000);
});

watch(timer, (val) => {
  if (val === 0 && interval) clearInterval(interval);
});

async function resendCode() {
  isResending.value = true;
  try {
    timer.value = 5000;
    if (interval) clearInterval(interval);
    interval = setInterval(() => {
      if (timer.value > 0) timer.value--;
    }, 1000);
    // Actually call resend API
    if (email.value) {
      try {
        await $fetch("/send-otp", {
          baseURL: config.public.apiBase,
          method: "POST",
          body: { email: email.value },
        });
        useToast().add({
          title: "Code Sent",
          description: "A new code has been sent to your email.",
          color: "success",
        });
      } catch (error: any) {
        useToast().add({
          title: "Error Resending Code",
          description:
            validationErrors(error.data?.errors) ||
            "An unknown error occurred.",
          color: "error",
        });
      }
    }
  } finally {
    isResending.value = false;
  }
}

async function verifyOtp() {
  isVerifying.value = true;
  try {
    const response = await $fetch<{
      success: boolean;
      message: string;
    }>("/verify-otp", {
      baseURL: config.public.apiBase,
      method: "POST",
      body: {
        email: email.value,
        otp: otp.value.join(""),
      },
    });
    if (response.success) {
      useToast().add({
        title: "OTP Verified",
        description: "Your OTP has been verified successfully.",
        color: "success",
      });
      await navigateTo("/auth/reset-password");
    } else {
      useToast().add({
        title: "Invalid OTP",
        description: response.message || "The OTP you entered is incorrect.",
        color: "error",
      });
    }
  } catch (error: any) {
    useToast().add({
      title: "Error",
      description:
        error.data?.message || "An error occurred during verification.",
      color: "error",
    });
  } finally {
    isVerifying.value = false;
  }
}
</script>
