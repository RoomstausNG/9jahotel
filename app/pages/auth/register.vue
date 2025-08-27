<template>
  <div
    class="h-full overflow-y-auto bg-cover flex flex-col bg-[url('https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508514/slide_image_upd9ht.webp')]"
  >
    <div
      class="flex backdrop-brightness-50 flex-col space-y-6 p-6 px-8 mx-auto h-full w-full text-white overflow-y-auto"
    >
      <div class="text-center space-y-6">
        <NuxtImg
          size="xl"
          src="https://res.cloudinary.com/dcm83lp3b/image/upload/v1752508285/9jahotel_qddu0r.png"
          class="block mx-auto h-auto w-20 rounded-full"
        />
        <div class="">
          <h2 class="text-xl font-bold">Create an Account</h2>
          <p class="">Join us and start your journey</p>
        </div>
      </div>
      <div class="space-y-4">
        <UForm
          :schema="schema"
          :state="state"
          class="space-y-4 w-form bg-white/30 backdrop-blur-sm p-4 text-white rounded-lg border border-white/30"
          @submit="onSubmit"
        >
          <UFormField
            label="First Name"
            name="firstname"
            :error="validationErrors.firstname"
          >
            <UInput
              v-model="state.firstname"
              type="text"
              placeholder="First Name"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('firstname')"
            />
          </UFormField>
          <UFormField
            label="Last Name"
            name="lastname"
            :error="validationErrors.lastname"
          >
            <UInput
              v-model="state.lastname"
              type="text"
              placeholder="Last Name"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('lastname')"
            />
          </UFormField>
          <UFormField
            label="Email"
            name="email"
            :error="validationErrors.email"
          >
            <UInput
              v-model="state.email"
              type="email"
              placeholder="Email address"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('email')"
            />
          </UFormField>
          <UFormField
            label="Phone Number"
            name="phone"
            :error="validationErrors.phone"
          >
            <UInput
              v-model="state.phone"
              type="text"
              placeholder="Phone Number"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('phone')"
            />
          </UFormField>
          <UFormField
            label="Password"
            name="password"
            :error="validationErrors.password"
          >
            <UInput
              v-model="state.password"
              :type="passwordType ? 'text' : 'password'"
              placeholder="Password"
              variant="subtle"
              class="w-full border-white text-white"
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
          <UFormField
            label="Confirm Password"
            name="password_confirmation"
            :error="validationErrors.password_confirmation"
          >
            <UInput
              v-model="state.password_confirmation"
              :type="passwordConfirmationType ? 'text' : 'password'"
              placeholder="Confirm Password"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('password_confirmation')"
            >
              <template #trailing>
                <UButton
                  color="neutral"
                  variant="link"
                  size="sm"
                  :icon="
                    passwordConfirmationType
                      ? 'i-heroicons-eye-slash'
                      : 'i-heroicons-eye'
                  "
                  aria-label="Toggle password confirmation visibility"
                  @click="togglePasswordConfirmationVisibility"
                /> </template
            ></UInput>
          </UFormField>
          <UFormField
            label="Referrer Code (Optional)"
            name="referral_code"
            :error="validationErrors.referral_code"
          >
            <UInput
              v-model="state.referral_code"
              type="text"
              placeholder="Referrer Code"
              variant="subtle"
              class="w-full border-white text-white"
              @input="clearValidationError('referral_code')"
            />
          </UFormField>
          <UFormField name="terms" :error="validationErrors.terms">
            <UCheckbox
              v-model="state.terms"
              name="terms"
              @change="clearValidationError('terms')"
            >
              <template #label>
                <span class="text-xs text-white"
                  >I agree to the
                  <NuxtLink to="/terms" class="text-primary-400 underline"
                    >Terms of Service</NuxtLink
                  >
                  and
                  <NuxtLink to="/privacy" class="text-primary-400 underline"
                    >Privacy Policy</NuxtLink
                  ></span
                >
              </template>
            </UCheckbox>
          </UFormField>

          <UButton
            type="submit"
            :loading="isLoading"
            block
            size="lg"
            class="bg-primary cursor-pointer"
          >
            {{ isLoading ? "Creating Account..." : "Sign Up" }}
          </UButton>
        </UForm>

        <div class="text-center text-sm">
          <span class="">Already have an account?</span>
          <NuxtLink
            to="/auth/login"
            class="ml-1 text-primary-600 hover:text-primary-500 font-medium"
          >
            Log In
          </NuxtLink>
        </div>
        <div class="text-center text-sm">
          <div>
            <NuxtLink
              to="/home"
              class="text-white hover:text-primary-500 font-medium underline"
            >
              Continue without login
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import * as z from "zod";
import type { FormSubmitEvent } from "@nuxt/ui";

definePageMeta({ middleware: ["guest"] });

// Define a type for the user object returned by the API
interface ApiUser {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  referralCode?: string;
  parentId?: number | null;
  avatar?: string | null;
  address?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  token_version?: number;
}

const passwordType = ref(false);
const passwordConfirmationType = ref(false);

function togglePasswordVisibility() {
  passwordType.value = !passwordType.value;
}

function togglePasswordConfirmationVisibility() {
  passwordConfirmationType.value = !passwordConfirmationType.value;
}

const schema = z
  .object({
    firstname: z.string().min(2, "First name is required"),
    lastname: z.string().min(2, "Last name is required"),
    email: z.string().email("Invalid email"),
    phone: z.string().min(10, "Please enter a valid phone number"),
    password: z.string().min(8, "Must be at least 8 characters"),
    password_confirmation: z.string().min(8, "Passwords must match"),
    referral_code: z.string().optional(),
    terms: z.boolean().refine((val) => val === true, {
      message: "You must accept the terms and conditions",
    }),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Passwords don't match",
    path: ["password_confirmation"], // path of error
  });

type Schema = z.output<typeof schema>;

const state = reactive<Partial<Schema>>({
  firstname: undefined,
  lastname: undefined,
  email: undefined,
  phone: undefined,
  password: undefined,
  password_confirmation: undefined,
  referral_code: undefined,
  terms: false,
});

const validationErrors = reactive<Record<string, string>>({
  firstname: "",
  lastname: "",
  email: "",
  phone: "",
  password: "",
  password_confirmation: "",
  referral_code: "",
  terms: "",
});

const clearValidationError = (field: keyof Schema) => {
  if (validationErrors[field]) {
    validationErrors[field] = "";
  }
};

const isLoading = ref(false);
const toast = useToast();
const config = useRuntimeConfig();

async function onSubmit(event: FormSubmitEvent<Schema>) {
  isLoading.value = true;
  Object.keys(validationErrors).forEach(
    (key) => (validationErrors[key as keyof typeof validationErrors] = "")
  );

  try {
    const { password_confirmation, terms, referral_code, ...restOfData } =
      event.data;

    const requestBody = {
      ...restOfData,
      referral_code: referral_code,
    };

    const response = await $fetch<{
      success: boolean;
      message: string;
      data?: ApiUser;
    }>("/register", {
      baseURL: config.public.apiBase,
      method: "POST",
      body: requestBody,
    });

    if (response.success && response.data) {
      const user = response.data;

      toast.add({
        title: `Congrats ${user.firstName}!`,
        description: response.message || "Registration successful",
        color: "success",
      });
      await navigateTo("/auth/login");
    } else {
      toast.add({
        title: "Registration Failed",
        description: response.message || "An unknown error occurred.",
        color: "error",
      });
    }
  } catch (error: any) {
    if (error.statusCode === 422 && error.data?.errors) {
      const errors = error.data.errors;
      for (const key in errors) {
        if (Object.prototype.hasOwnProperty.call(validationErrors, key)) {
          validationErrors[key as keyof typeof validationErrors] =
            Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        }
      }
      toast.add({
        title: "Validation Error",
        description: "Please check the form for errors.",
        color: "error",
      });
    } else {
      toast.add({
        title: "Error",
        description:
          error.data?.message || "Registration failed. Please try again.",
        color: "error",
      });
    }
  } finally {
    isLoading.value = false;
  }
}

useHead({
  title: "Register - 9jahotel",
  meta: [
    {
      name: "description",
      content: "Create a new account at 9jahotel.",
    },
  ],
});
</script>
