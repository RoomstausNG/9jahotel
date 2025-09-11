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
        <h2 class="text-2xl font-bold">Enter Verification Code</h2>
        <p class="text-sm">
          We've sent a code to
          <span class="font-semibold">{{ maskedEmailOrPhone }}</span>
        </p>
      </div>
      <div
        class="bg-black/60 rounded-xl p-4 max-w-md mx-auto w-full flex flex-col items-center"
      >
        <div class="flex gap-2 justify-center mb-4">
          <input
            v-for="(digit, idx) in otpLength"
            :key="idx"
            v-model="otp[idx]"
            maxlength="1"
            class="w-10 h-12 text-center rounded bg-white/10 border border-white/30 text-xl font-mono"
            @input="onOtpInput(idx, $event)"
          >
        </div>
        <div class="flex flex-col items-center mb-4">
          <span class="text-xs text-white/80">{{ timerDisplay }}</span>
          <UButton
            variant="link"
            class="text-primary-400 p-0 mt-1"
            :disabled="timer > 0"
            @click="resendCode"
            >Resend Code</UButton
          >
        </div>
        <UButton
          class="w-full bg-primary"
          size="lg"
          :loading="isVerifying"
          @click="verifyOtp"
          >Verify Now <Icon name="i-heroicons-fingerprint" class="ml-2"
        /></UButton>
      </div>
      <div class="flex flex-col items-center mt-4 space-y-1">
        <span class="text-xs"
          >Wrong number?
          <NuxtLink
            to="/auth/forget-password"
            class="text-primary-400 underline"
            >Change contact</NuxtLink
          ></span
        >
        <span class="text-xs"
          >Need help?
          <NuxtLink to="/support" class="text-primary-400 underline"
            >Get support</NuxtLink
          ></span
        >
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

const router = useRouter();
const route = useRoute();

const otpLength = 6;
const otp = ref(Array(otpLength).fill(""));
const timer = ref(300); // 5 minutes
const isVerifying = ref(false);
const maskedEmailOrPhone = computed(
  () => route.query.contact || "your contact"
);

const timerDisplay = computed(() => {
  const min = Math.floor(timer.value / 60)
    .toString()
    .padStart(2, "0");
  const sec = (timer.value % 60).toString().padStart(2, "0");
  return `${min}:${sec}`;
});

let interval: any = null;

onMounted(() => {
  interval = setInterval(() => {
    if (timer.value > 0) timer.value--;
  }, 1000);
});

watch(timer, (val) => {
  if (val === 0 && interval) clearInterval(interval);
});

function onOtpInput(idx: number, event: Event) {
  const input = event.target as HTMLInputElement;
  if (input.value.length > 1) {
    otp.value[idx] = input.value.slice(-1);
  }
  if (input.value && idx < otpLength - 1) {
    const next = document.querySelectorAll("input")[
      idx + 1
    ] as HTMLInputElement;
    next?.focus();
  }
}

function resendCode() {
  timer.value = 5000; // Reset timer to 5 minutes
  if (interval) clearInterval(interval);
  interval = setInterval(() => {
    if (timer.value > 0) timer.value--;
  }, 1000);
  // TODO: Call resend API
}

async function verifyOtp() {
  isVerifying.value = true;
  // TODO: Call verify API with otp.value.join('')
  setTimeout(() => {
    isVerifying.value = false;
    // router.push('/reset-password-success');
  }, 1500);
}
</script>
