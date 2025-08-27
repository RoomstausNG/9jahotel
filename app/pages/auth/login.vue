<template>
  <div
    class="h-full overflow-y-auto bg-cover flex flex-col justify-center bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')]"
  >
    <div
      class="backdrop-brightness-50 space-y-6 flex flex-col justify-around p-6 mx-auto h-full w-full text-white"
    >
      <div class="text-center space-y-6">
        <BrandLogo />
        <div class="space-y-0">
          <h2 class="text-xl font-bold">Welcome Back</h2>
          <p class="">Sign in to access your bookings</p>
        </div>
      </div>
      <div class="space-y-6">
        <UForm
          :schema="schema"
          :state="state"
          class="space-y-6 w-form bg-white/30 backdrop-blur-sm px-4 py-6 rounded-lg border border-white/30"
          @submit="onSubmit"
        >
          <UFormField name="email">
            <UInput
              v-model="state.email"
              type="email"
              size="xl"
              placeholder="Email address"
              variant="subtle"
              class="w-full border-white"
              :color="validationErrors.email ? 'error' : undefined"
              @input="clearValidationError('email')"
            />
            <p v-if="validationErrors.email" class="text-red-600 text-xs mt-1">
              {{ validationErrors.email }}
            </p>
          </UFormField>
          <div class="space-y-2">
            <UFormField name="password">
              <UInput
                v-model="state.password"
                size="xl"
                :type="passwordType ? 'text' : 'password'"
                placeholder="Password"
                variant="subtle"
                class="w-full border-white"
                :color="validationErrors.password ? 'error' : undefined"
                @input="clearValidationError('password')"
              >
                <template #trailing>
                  <UButton
                    color="neutral"
                    variant="link"
                    size="sm"
                    :icon="
                      passwordType ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'
                    "
                    aria-label="Toggle password visibility"
                    @click="togglePasswordVisibility"
                  /> </template
              ></UInput>
            </UFormField>
            <p
              v-if="validationErrors.password"
              class="text-red-600 text-xs mt-1"
            >
              {{ validationErrors.password }}
            </p>
            <div class="flex items-center text-xs justify-between">
              <UCheckbox
                v-model="state.remember"
                label="Remember me"
                size="sm"
                color="primary"
                class="text-white placeholder-white"
              />
              <ULink
                to="/auth/forget-password"
                class="text-white text-xs hover:text-primary-500 font-medium"
                >Forget Password</ULink
              >
            </div>
          </div>
          <UButton
            type="submit"
            :loading="isLoading"
            block
            size="lg"
            class="bg-primary cursor-pointer"
          >
            {{ isLoading ? "Logging in..." : "Log In" }}
          </UButton>
        </UForm>
        <!-- <div class="space-y-4">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span
                class="px-3 bg-white/30 text-white backdrop-blur-sm rounded-sm py-0.5"
                >Or continue with</span
              >
            </div>
          </div>
          <div class="flex gap-4 items-center justify-center">
            <UButton
              size="lg"
              block
              class="bg-white/30 text-white cursor-pointer border border-white/70 backdrop-blur-sm"
              @click="loginWithGoogle"
            >
              <UIcon name="devicon:google" class="mr-2" /> Google
            </UButton>
            <UButton
              size="lg"
              class="bg-white/30 text-white cursor-pointer border border-white/70 backdrop-blur-sm"
              block
              @click="loginWithApple"
            >
              <Icon name="i-logos-facebook" class="mr-2 text-white" /> Facebook
            </UButton>
          </div>
          <div class="text-center text-sm">
            <span class="">Don't have an account?</span>
            <NuxtLink
              to="/auth/register"
              class="ml-1 text-primary-600 hover:text-primary-500 font-medium"
            >
              Sign Up
            </NuxtLink>
          </div>
        </div> -->
      </div>
      <ContinueWithoutAccount />
    </div>
  </div>
</template>

<script setup lang="ts">
import * as z from "zod";
import type { FormSubmitEvent } from "@nuxt/ui";
import type { User } from "~/stores/user";

definePageMeta({ middleware: ["guest"] });

const passwordType: Ref<boolean> = ref(false);

function togglePasswordVisibility() {
  passwordType.value = !passwordType.value;
}

const schema = z.object({
  email: z.string().email("Invalid email"),
  password: z.string().min(6, "Must be at least 6 characters"),
  remember: z.boolean().optional(),
});

type Schema = z.output<typeof schema>;

const state = reactive<Partial<Schema>>({
  email: undefined,
  password: undefined,
  remember: true,
});

const validationErrors = reactive<Record<string, string>>({
  email: "",
  password: "",
});

// Clear validation errors when user types
const clearValidationError = (field: string) => {
  if (validationErrors[field]) {
    validationErrors[field] = "";
  }
};

const isLoading = ref(false);

const toast = useToast();
const config = useRuntimeConfig();
const userStore = useUserStore();

async function onSubmit(event: FormSubmitEvent<Schema>) {
  isLoading.value = true;

  // Clear previous validation errors
  validationErrors.email = "";
  validationErrors.password = "";

  try {
    const response = await $fetch<{
      success: boolean;
      message: string;
      data?: { user: Record<string, unknown>; token: string };
    }>("/login", {
      baseURL: config.public.apiBase,
      method: "POST",
      body: {
        email: event.data.email,
        password: event.data.password,
      },
    });

    if (response.success && response.data) {
      // Save user and token to pinia
      userStore.setUser(response.data.user as unknown as User);
      userStore.setToken(response.data.token);
      toast.add({
        title: "Success",
        description: response.message || "Login successful",
        color: "success",
      });
      await navigateTo("/home");
    } else {
      toast.add({
        title: "Login Failed",
        description: response.message || "An unknown error occurred.",
        color: "error",
      });
    }
  } catch (error: any) {
    if (error.statusCode === 422 && error.data?.errors) {
      const errors = error.data.errors;
      if (errors.email) {
        validationErrors.email = Array.isArray(errors.email)
          ? errors.email[0]
          : errors.email;
      }
      if (errors.password) {
        validationErrors.password = Array.isArray(errors.password)
          ? errors.password[0]
          : errors.password;
      }
      toast.add({
        title: "Validation Error",
        description: "Please check the form for errors.",
        color: "error",
      });
    } else {
      toast.add({
        title: "Error",
        description: error.data?.message || "Login failed. Please try again.",
        color: "error",
      });
    }
  } finally {
    isLoading.value = false;
  }
}

const loginWithGoogle = async () => {
  // Implement Google OAuth
  console.log("Login with Google");
};

const loginWithApple = async () => {
  // Implement Apple OAuth
  console.log("Login with Apple");
};

useHead({
  title: "Sign In - 9jahotel",
  meta: [
    {
      name: "description",
      content:
        "Sign in to your 9jahotel account to book hotels and manage your reservations.",
    },
  ],
});
</script>
