<template>
  <div
    class="h-full overflow-y-auto bg-cover flex flex-col justify-center bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')]"
  >
    <div
      class="space-y-6 backdrop-brightness-50 flex flex-col justify-center p-6 mx-auto h-full w-full text-white"
    >
      <div class="text-center space-y-2">
        <NuxtImg
          size="xl"
          src="https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508285/9jahotel_qddu0r.png"
          class="block mx-auto h-auto w-20 rounded-full"
        />
        <h2 class="text-2xl font-bold">Reset Your Password</h2>
        <p class="text-sm -mt-2">
          Create a strong, unique password for your account
        </p>
      </div>
      <div
        class="bg-white/90 rounded-xl p-6 max-w-md mx-auto w-full flex flex-col items-center mt-4 text-gray-900"
      >
        <UForm
          :schema="schema"
          :state="state"
          class="w-full space-y-4"
          @submit="onSubmit"
        >
          <UFormField label="New Password" name="password">
            <UInput
              v-model="state.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter new password"
              class="w-full"
              @input="clearValidationError('password')"
            >
              <template #trailing>
                <UButton
                  color="neutral"
                  variant="link"
                  size="sm"
                  :icon="
                    showPassword ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'
                  "
                  aria-label="Toggle password visibility"
                  @click="showPassword = !showPassword"
                />
              </template>
            </UInput>
            <template #error>
              <span
                v-if="validationErrors.password"
                class="text-red-500 text-xs"
                >{{ validationErrors.password }}</span
              >
            </template>
          </UFormField>
          <div class="flex items-center justify-between text-xs mt-1 mb-2">
            <span>Password Strength</span>
            <span :class="strengthColor">{{ passwordStrength.label }}</span>
          </div>
          <div class="w-full h-1 bg-gray-200 rounded">
            <div
              :class="['h-1 rounded', strengthBarColor]"
              :style="{ width: passwordStrength.percent + '%' }"
            />
          </div>
          <UFormField label="Confirm New Password" name="confirmPassword">
            <UInput
              v-model="state.confirmPassword"
              :type="showConfirm ? 'text' : 'password'"
              placeholder="Confirm your password"
              class="w-full"
              @input="clearValidationError('confirmPassword')"
            >
              <template #trailing>
                <UButton
                  color="neutral"
                  variant="link"
                  size="sm"
                  :icon="
                    showConfirm ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'
                  "
                  aria-label="Toggle password visibility"
                  @click="showConfirm = !showConfirm"
                />
              </template>
            </UInput>
            <template #error>
              <span
                v-if="validationErrors.confirmPassword"
                class="text-red-500 text-xs"
                >{{ validationErrors.confirmPassword }}</span
              >
            </template>
          </UFormField>
          <div
            class="bg-white/80 border border-gray-200 rounded-lg p-4 mt-2 w-full"
          >
            <div class="font-semibold mb-2 text-sm">Password Requirements</div>
            <ul class="space-y-1 text-xs">
              <li :class="reqClass(reqs.length)">
                <Icon name="i-heroicons-check-circle" class="mr-1" /> At least 8
                characters
              </li>
              <li :class="reqClass(reqs.upper)">
                <Icon name="i-heroicons-check-circle" class="mr-1" /> Contains
                uppercase letter
              </li>
              <li :class="reqClass(reqs.lower)">
                <Icon name="i-heroicons-check-circle" class="mr-1" /> Contains
                lowercase letter
              </li>
              <li :class="reqClass(reqs.number)">
                <Icon name="i-heroicons-check-circle" class="mr-1" /> Contains
                number
              </li>
              <li :class="reqClass(reqs.special)">
                <Icon name="i-heroicons-check-circle" class="mr-1" /> Contains
                special character
              </li>
            </ul>
          </div>
          <UButton
            type="submit"
            class="w-full block mt-4 bg-primary"
            size="lg"
            :loading="isLoading"
            >Reset Password</UButton
          >
        </UForm>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import * as z from "zod";

const router = useRouter();
const isLoading = ref(false);
const showPassword = ref(false);
const showConfirm = ref(false);
const state = ref({ password: "", confirmPassword: "" });
const validationErrors = ref({ password: "", confirmPassword: "" });
const email = ref("");

const schema = z
  .object({
    password: z
      .string()
      .min(8, "At least 8 characters")
      .regex(/[A-Z]/, "Must contain uppercase letter")
      .regex(/[a-z]/, "Must contain lowercase letter")
      .regex(/[0-9]/, "Must contain number")
      .regex(/[^A-Za-z0-9]/, "Must contain special character"),
    confirmPassword: z.string(),
  })
  .refine((data) => data.password === data.confirmPassword, {
    message: "Passwords don't match",
    path: ["confirmPassword"],
  });

const reqs = computed(() => ({
  length: state.value.password.length >= 8,
  upper: /[A-Z]/.test(state.value.password),
  lower: /[a-z]/.test(state.value.password),
  number: /[0-9]/.test(state.value.password),
  special: /[^A-Za-z0-9]/.test(state.value.password),
}));

const passwordStrength = computed(() => {
  const score = Object.values(reqs.value).filter(Boolean).length;
  if (score <= 2) return { label: "Weak", percent: 33 };
  if (score === 3 || score === 4) return { label: "Medium", percent: 66 };
  return { label: "Strong", percent: 100 };
});
const strengthColor = computed(() => {
  if (passwordStrength.value.label === "Weak") return "text-red-500";
  if (passwordStrength.value.label === "Medium") return "text-yellow-500";
  return "text-green-600";
});
const strengthBarColor = computed(() => {
  if (passwordStrength.value.label === "Weak") return "bg-red-400";
  if (passwordStrength.value.label === "Medium") return "bg-yellow-400";
  return "bg-green-500";
});

function reqClass(ok: boolean) {
  return ok
    ? "text-green-600 flex items-center"
    : "text-gray-400 flex items-center";
}

function clearValidationError(field: "password" | "confirmPassword") {
  validationErrors.value[field] = "";
}

onMounted(async () => {
  // Get email from localStorage only
  email.value = localStorage.getItem("reset_email") || "";
});

async function onSubmit() {
  isLoading.value = true;
  validationErrors.value.password = "";
  validationErrors.value.confirmPassword = "";
  try {
    const parsed = schema.safeParse(state.value);
    if (!parsed.success) {
      parsed.error.errors.forEach((err) => {
        if (err.path[0] in validationErrors.value) {
          validationErrors.value[err.path[0]] = err.message;
        }
      });
      isLoading.value = false;
      return;
    }

    try {
      const response = await $fetch<{
        success: boolean;
        message: string;
      }>("/reset-password", {
        baseURL: useRuntimeConfig().public.apiBase,
        method: "PATCH",
        body: {
          email: email.value,
          password: state.value.password,
          confirmPassword: state.value.confirmPassword,
        },
      });

      if (response.success) {
        useToast().add({
          title: "Success",
          description: response.message || "Password reset successfully.",
          color: "success",
        });
        // Remove email from storage
        localStorage.removeItem("reset_email");
        // Redirect to login
        await router.push("/auth/login");
      }
    } catch (error: any) {
      useToast().add({
        title: "Error",
        description: error.data?.message || "Reset failed",
        color: "error",
      });
    }
  } finally {
    isLoading.value = false;
  }
}
</script>
