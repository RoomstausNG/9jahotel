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
        <div class="space-y-1">
          <h2 class="text-2xl font-bold">Reset Password</h2>
          <p class="text-sm">
            Enter your email address to gain access to your account
          </p>
        </div>
      </div>
      <div class="space-y-6">
        <UForm
          :schema="schema"
          :state="state"
          class="space-y-4 w-form bg-white/50 p-4 rounded-lg border border-white/30"
          @submit="onSubmit"
        >
          <UFormField name="email" label="Email Address">
            <UInput
              v-model="state.email"
              type="email"
              placeholder="Email address"
              variant="subtle"
              class="w-full border-white"
              :color="validationErrors.email ? 'red' : undefined"
              @input="clearValidationError('email')"
            />
            <p v-if="validationErrors.email" class="text-red-600 text-xs mt-1">
              {{ validationErrors.email }}
            </p>
          </UFormField>
          <UButton
            type="submit"
            :loading="isLoading"
            block
            size="lg"
            class="bg-primary cursor-pointer"
          >
            {{ isLoading ? "Sending..." : "Send OTP" }}
          </UButton>

          <div
            class="mt-6 space-y-2 text-left text-white/90 text-xs max-w-xs mx-auto"
          >
            <div class="flex items-center gap-2">
              <Icon name="i-heroicons-envelope" class="text-base" />
              <span
                >We'll send a password reset link to your email address</span
              >
            </div>
            <div class="flex items-center gap-2">
              <Icon name="i-heroicons-shield-check" class="text-base" />
              <span>Check your spam folder if you don't see the email</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="i-heroicons-queue-list" class="text-base" />
              <span>This may take 1-2 minutes to arrive</span>
            </div>
          </div>
        </UForm>
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
  </div>
</template>

<script setup lang="ts">
import * as z from "zod";
import type { FormSubmitEvent } from "@nuxt/ui";

definePageMeta({ middleware: ["guest"] });

const schema = z.object({
  email: z.string().email("Invalid email"),
});

type Schema = z.output<typeof schema>;

const state = reactive<Partial<Schema>>({
  email: undefined,
});

const validationErrors = reactive<Record<string, string>>({
  email: "",
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

async function onSubmit(event: FormSubmitEvent<Schema>) {
  isLoading.value = true;

  // Clear previous validation errors
  validationErrors.email = "";

  try {
    const response = await $fetch<{
      success: boolean;
      message: string;
    }>("/send-otp", {
      baseURL: config.public.apiBase,
      method: "POST",
      body: {
        email: event.data.email,
      },
    });

    if (response.success) {
      // Save email to localStorage only
      if (event.data.email) {
        localStorage.setItem("reset_email", event.data.email);
      }
      toast.add({
        title: "Success",
        description:
          response.message || "Password reset link sent successfully.",
        color: "success",
      });
      // Redirect to check-email page (no query param)
      await navigateTo({ path: "/auth/check-email" });
    }
  } catch (error: any) {
    if (error.statusCode === 422 && error.data?.errors) {
      const errors = error.data.errors;
      if (errors.email) {
        validationErrors.email = Array.isArray(errors.email)
          ? errors.email[0]
          : errors.email;
      }
      toast.add({
        title: "Input validation Error",
        description: "Please check the form for errors.",
        color: "error",
      });
    } else {
      toast.add({
        title: "Error",
        description:
          error.data?.message || "Failed to send reset link. Please try again.",
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
  title: "Reset Password - 9jahotel",
  meta: [
    {
      name: "description",
      content: "Reset your 9jahotel account password.",
    },
  ],
});
</script>
