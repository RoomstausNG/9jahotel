<template>
  <div class="min-h-screen bg-neutral-50 pb-24 px-3 overflow-y-auto">
    <div class="mx-auto w-full">
      <!-- Profile Header -->
      <div class="bg-white rounded-xl shadow-lg mb-6">
        <div
          class="flex items-center justify-between px-2 py-4 border-b border-neutral-100"
        >
          <div class="flex items-center gap-3">
            <UIcon name="i-lucide-user" class="w-7 h-7 text-primary-600" />
            <h1 class="font-bold text-xl text-neutral-900">My Profile</h1>
          </div>
        </div>

        <!-- Profile Completion -->
        <div v-if="profileCompletionPercentage < 70" class="py-4 px-2">
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium text-neutral-700"
              >Profile {{ profileCompletionPercentage }}% complete</span
            >
            <span class="text-xs font-semibold text-primary-600"
              >{{ profileCompletionPercentage }}%</span
            >
          </div>
          <div class="w-full h-3 bg-neutral-200 rounded-full mb-2">
            <div
              class="h-3 bg-primary-600 rounded-full transition-all duration-300"
              :style="{ width: profileCompletionPercentage + '%' }"
            ></div>
          </div>
          <p class="text-xs text-neutral-500">
            Complete your profile for better service
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Avatar Section -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-lg py-6 px-2 text-center">
            <div class="relative inline-block">
              <UAvatar
                :src="profile.avatar"
                :alt="profile.firstName + ' ' + profile.lastName"
                class="w-24 h-24 rounded-full border-4 border-primary-100 object-cover"
              />
              <UButton
                icon="i-lucide-camera"
                color="primary"
                variant="solid"
                class="absolute bottom-0 right-0 rounded-full p-2 shadow-lg"
                @click="triggerAvatarInput"
              />
            </div>

            <h3 class="mt-4 text-xl font-semibold text-neutral-900">
              {{ profile.firstName }} {{ profile.lastName }}
            </h3>
            <p class="text-sm text-neutral-500 mb-4">{{ profile.email }}</p>

            <div class="space-y-2">
              <UButton
                variant="outline"
                color="primary"
                class="w-full block"
                @click="triggerAvatarInput"
                :loading="avatarUploading"
              >
                <UIcon name="i-lucide-upload" class="mr-2" />
                Update Picture
              </UButton>

              <UButton
                variant="outline"
                color="neutral"
                class="w-full block"
                @click="openPasswordModal"
              >
                <UIcon name="i-lucide-lock" class="mr-2" />
                Change Password
              </UButton>
            </div>

            <input
              ref="avatarInput"
              type="file"
              accept="image/png,image/jpeg,image/jpg"
              class="hidden"
              @change="handleAvatarChange"
            />

            <p class="mt-3 text-xs text-neutral-400 text-center">
              Use a clear, recent photo<br />
              Supported: JPG, PNG (max 5MB)
            </p>
          </div>

          <!-- Referral Code -->
          <div class="bg-white rounded-xl shadow-lg py-6 px-2 mt-3">
            <h3 class="font-semibold text-neutral-900 mb-3">Referral Code</h3>
            <div class="flex items-center gap-2 p-3 bg-neutral-50 rounded-lg">
              <UIcon name="i-lucide-gift" class="w-5 h-5 text-primary-600" />
              <span class="font-mono font-semibold text-lg">{{
                profile.referralCode
              }}</span>
              <UButton
                variant="ghost"
                size="sm"
                icon="i-lucide-copy"
                @click="copyReferralCode"
              />
            </div>
          </div>
        </div>

        <!-- Profile Form -->
        <div class="w-full">
          <UForm
            class="bg-white rounded-xl shadow-lg py-6 px-2 space-y-6"
            :state="form"
            :schema="profileSchema"
            @submit="onProfileSubmit"
          >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField label="First Name *" name="firstName">
                <UInput
                  v-model="form.firstName"
                  placeholder="Enter your first name"
                  class="w-full"
                />
              </UFormField>

              <UFormField label="Last Name *" name="lastName">
                <UInput
                  v-model="form.lastName"
                  placeholder="Enter your last name"
                  class="w-full"
                />
              </UFormField>

              <UFormField label="Email Address *" name="email">
                <UInput
                  v-model="form.email"
                  type="email"
                  placeholder="your@email.com"
                  class="w-full"
                  disabled
                />
                <template #hint>
                  <div class="flex items-center gap-2 mt-1">
                    <UIcon
                      name="i-lucide-check-circle"
                      class="w-4 h-4 text-green-600"
                    />
                    <span class="text-xs text-green-600">Verified</span>
                  </div>
                </template>
              </UFormField>

              <UFormField label="Phone Number *" name="phone">
                <UInput
                  v-model="form.phone"
                  type="tel"
                  placeholder="+234 XXX XXX XXXX"
                  class="w-full"
                />
                <template #hint>
                  <div class="flex items-center gap-2 mt-1">
                    <UIcon
                      name="i-lucide-check-circle"
                      class="w-4 h-4 text-green-600"
                    />
                    <span class="text-xs text-green-600">Verified</span>
                  </div>
                </template>
              </UFormField>

              <UFormField label="Date of Birth" name="dob">
                <UPopover :popper="{ placement: 'bottom-start' }">
                  <UButton
                    color="white"
                    variant="outline"
                    icon="i-lucide-calendar"
                    class="w-full justify-start"
                    :label="dobDisplay"
                  />
                  <template #content="{ close }">
                    <UCalendar
                      v-model="dobCalendarDate"
                      :max-value="maxCalendarDate"
                      @update:model-value="close"
                    />
                  </template>
                </UPopover>
                <template #hint>
                  <span class="text-xs text-neutral-500"
                    >Used for age verification only</span
                  >
                </template>
              </UFormField>

              <UFormField label="Gender" name="gender">
                <USelect
                  v-model="form.gender"
                  :items="genderOptions"
                  placeholder="Select gender"
                  class="w-full"
                />
                <template #hint>
                  <span class="text-xs text-neutral-500">Optional</span>
                </template>
              </UFormField>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField
                class="w-full md:col-span-2"
                label="Street Address *"
                name="address"
              >
                <UTextarea
                  v-model="form.address"
                  placeholder="Enter your full address"
                  rows="3"
                  class="w-full"
                />
              </UFormField>

              <UFormField label="City" name="cityId">
                <USelectMenu
                  v-model="selectedCity"
                  v-model:search-term="searchLocation"
                  :items="cities"
                  :loading="citiesLoading"
                  :placeholder="
                    selectedCity ? selectedCity.name : 'Select your city'
                  "
                  size="xl"
                  label-key="name"
                  value-key="id"
                  icon="i-lucide-map-pin"
                  class="w-full"
                />
              </UFormField>
            </div>

            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <UFormField label="ID Card Type *" name="idType">
                <USelect
                  v-model="form.idType"
                  :options="idTypeOptions"
                  placeholder="Select ID type"
                  class="w-full"
                />
              </UFormField>

              <UFormField label="ID Number *" name="idNumber">
                <UInput
                  v-model="form.idNumber"
                  placeholder="Enter ID number"
                  class="w-full"
                />
              </UFormField>
            </div> -->

            <!-- Action Buttons -->
            <!-- <div
              class="grid grid-cols-2 gap-4 pt-6 border-t border-neutral-200"
            >
              <UButton
                type="submit"
                color="primary"
                class="w-full block"
                :loading="loading"
                :disabled="!hasFormChanges"
              >
                <UIcon name="i-lucide-save" class="mr-2" />
                Save
              </UButton>

              <UButton
                type="button"
                variant="outline"
                color="neutral"
                class="w-full block"
                :disabled="!hasFormChanges"
                @click="resetForm"
              >
                <UIcon name="i-lucide-x" class="mr-2" />
                Cancel
              </UButton>
            </div> -->

            <!-- Display form validation errors -->
            <div v-if="Object.keys(formErrors).length > 0" class="mt-4">
              <div
                v-for="(error, field) in formErrors"
                :key="field"
                class="text-sm text-red-600"
              >
                {{ error }}
              </div>
            </div>
          </UForm>
        </div>
      </div>
    </div>

    <!-- Password Update Modal -->
    <UCard>
      <template #header>
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold">Update Password</h3>
        </div>
      </template>

      <div class="space-y-4">
        <UFormField label="Current Password" name="currentPassword" required>
          <UInput
            v-model="passwordForm.currentPassword"
            type="password"
            placeholder="Enter current password"
            class="w-full"
          />
        </UFormField>

        <UFormField label="New Password" name="newPassword" required>
          <UInput
            v-model="passwordForm.newPassword"
            type="password"
            placeholder="Enter new password"
            class="w-full"
          />
        </UFormField>

        <UFormField
          label="Confirm New Password"
          name="confirmPassword"
          required
        >
          <UInput
            v-model="passwordForm.confirmPassword"
            type="password"
            placeholder="Confirm new password"
            class="w-full"
          />
        </UFormField>
      </div>

      <template #footer>
        <div class="flex gap-3 justify-end">
          <UButton type="button" variant="ghost" @click="closePasswordModal">
            Cancel
          </UButton>
          <UButton
            type="button"
            color="primary"
            :loading="passwordLoading"
            @click="onPasswordSubmit"
          >
            Update Password
          </UButton>
        </div>
      </template>
    </UCard>
  </div>
</template>

<script setup lang="ts">
import { z } from "zod";
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
} from "@internationalized/date";

definePageMeta({
  layout: "auth",
});

const config = useRuntimeConfig();
const apiBaseUrl = config.public.apiBase || "/api";

// Template refs
const avatarInput = ref<HTMLInputElement>();

// Loading states
const loading = ref(false);
const avatarUploading = ref(false);
const citiesLoading = ref(false);
const passwordLoading = ref(false);

// Toast notifications
const toast = useToast();
const passwordModalOpen = ref(false);

// Reactive data
const profile = ref<any>({});
const form = ref({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  dob: "",
  gender: "",
  address: "",
  cityId: null,
  idType: "",
  idNumber: "",
  latitude: null,
  longitude: null,
});

// Store original form values for comparison
const originalForm = ref({
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  dob: "",
  gender: "",
  address: "",
  cityId: null,
  idType: "",
  idNumber: "",
  latitude: null,
  longitude: null,
});

// Password form
const passwordForm = ref({
  currentPassword: "",
  newPassword: "",
  confirmPassword: "",
});

// Date handling
const dobCalendarDate = ref<CalendarDate | null>(null);
const dateFormatter = new DateFormatter("en-US", { dateStyle: "medium" });

// Max date for calendar (today)
const maxCalendarDate = computed(() => {
  const today = new Date();
  return new CalendarDate(
    today.getFullYear(),
    today.getMonth() + 1,
    today.getDate()
  );
});

// Display text for DOB button
const dobDisplay = computed(() => {
  if (dobCalendarDate.value) {
    return dateFormatter.format(
      dobCalendarDate.value.toDate(getLocalTimeZone())
    );
  }
  return "Select your date of birth";
});

// Watch calendar date changes and sync with form
watch(dobCalendarDate, (newDate) => {
  if (newDate) {
    form.value.dob = newDate.toString();
  } else {
    form.value.dob = "";
  }
});

// Form schemas
const profileSchema = z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Invalid email address"),
  phone: z.string().min(1, "Phone number is required"),
  dob: z.string().optional(),
  gender: z.string().optional(),
  address: z.string().min(1, "Address is required"),
  cityId: z.number().optional().nullable(),
  idType: z.string().min(1, "ID type is required"),
  idNumber: z.string().min(1, "ID number is required"),
});

const passwordSchema = z
  .object({
    currentPassword: z.string().min(1, "Current password is required"),
    newPassword: z
      .string()
      .min(6, "New password must be at least 6 characters"),
    confirmPassword: z.string().min(1, "Please confirm your password"),
  })
  .refine((data) => data.newPassword === data.confirmPassword, {
    message: "Passwords don't match",
    path: ["confirmPassword"],
  });

// Options
const genderOptions = [
  { label: "Male", value: "male" },
  { label: "Female", value: "female" },
];

const idTypeOptions = [
  { label: "National ID", value: "National ID" },
  { label: "Passport", value: "Passport" },
  { label: "Driver's License", value: "Driver's License" },
  { label: "Voter ID", value: "Voter ID" },
];

const cities = ref<any[]>([]);
const selectedCity = ref<any>(null);
const searchLocation = ref("");

// Watch searchLocation and fetch cities when it changes
watch(searchLocation, (val) => {
  fetchCities();
});

// Watch selectedCity changes and update form
watch(selectedCity, (newCity) => {
  if (newCity) {
    form.value.cityId = newCity.id;
  } else {
    form.value.cityId = null;
  }
});

// Computed properties
const profileCompletionPercentage = computed(() => {
  const fields = [
    form.value.firstName,
    form.value.lastName,
    form.value.email,
    form.value.phone,
    form.value.address,
    form.value.idType,
    form.value.idNumber,
    profile.value.avatar,
    form.value.dob,
    form.value.gender,
  ];
  const completedFields = fields.filter(
    (field) => field && field.toString().trim() !== ""
  ).length;
  return Math.round((completedFields / fields.length) * 100);
});

const hasFormChanges = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(originalForm.value);
});

// Methods
function clearSearchText() {
  searchLocation.value = "";
}

async function fetchCities() {
  citiesLoading.value = true;
  try {
    const search = searchLocation.value
      ? `?search=${encodeURIComponent(searchLocation.value)}`
      : "";
    const res = await $fetch(`${apiBaseUrl}/allcity${search}`, {
      method: "GET",
    });
    if (res.success && res.data && Array.isArray(res.data.cities)) {
      cities.value = res.data.cities.map((city: any) => ({
        id: city.id,
        name: city.name,
        label: city.name,
        value: city.id,
      }));
    } else {
      cities.value = [];
    }
  } catch (e) {
    cities.value = [];
  } finally {
    citiesLoading.value = false;
  }
}

function triggerAvatarInput() {
  avatarInput.value?.click();
}

async function handleAvatarChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    toast.add({
      title: "Error",
      description: "File size must be less than 5MB",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
    return;
  }

  // Validate file type
  if (!file.type.startsWith("image/")) {
    toast.add({
      title: "Error",
      description: "Please select a valid image file",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
    return;
  }

  await uploadAvatar(file);
}

async function uploadAvatar(file: File) {
  avatarUploading.value = true;

  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No authentication token found.");

    const formData = new FormData();
    formData.append("avatar", file);

    const res = await $fetch(`${apiBaseUrl}/profile/avatar`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${token}`,
      },
      body: formData,
    });

    if (res.success && res.data) {
      profile.value = res.data;
      mapProfileToForm(res.data);
      toast.add({
        title: "Success",
        description: "Profile picture updated successfully!",
        color: "green",
        icon: "i-lucide-check-circle",
      });
    } else {
      throw new Error(res.message || "Failed to update avatar");
    }
  } catch (e: any) {
    toast.add({
      title: "Error",
      description: e.message || "Failed to update avatar",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
  } finally {
    avatarUploading.value = false;
  }
}

function openPasswordModal() {
  passwordModalOpen.value = true;
  passwordForm.value = {
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  };
}

function closePasswordModal() {
  passwordModalOpen.value = false;
  passwordForm.value = {
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  };
}

async function onPasswordSubmit() {
  // Validate form first
  try {
    passwordSchema.parse(passwordForm.value);
  } catch (error: any) {
    toast.add({
      title: "Validation Error",
      description: error.errors[0]?.message || "Please check your inputs",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
    return;
  }

  passwordLoading.value = true;

  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No authentication token found.");

    const res = await $fetch(`${apiBaseUrl}/update-password`, {
      method: "PATCH",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body: {
        currentPassword: passwordForm.value.currentPassword,
        newPassword: passwordForm.value.newPassword,
        confirmPassword: passwordForm.value.confirmPassword,
      },
    });

    if (res.success) {
      toast.add({
        title: "Success",
        description: "Password updated successfully!",
        color: "green",
        icon: "i-lucide-check-circle",
      });
      closePasswordModal();
    } else {
      throw new Error(res.message || "Failed to update password");
    }
  } catch (e: any) {
    toast.add({
      title: "Error",
      description: e.message || "Failed to update password",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
  } finally {
    passwordLoading.value = false;
  }
}

async function copyReferralCode() {
  try {
    await navigator.clipboard.writeText(profile.value.referralCode);
    toast.add({
      title: "Success",
      description: "Referral code copied to clipboard!",
      color: "green",
      icon: "i-lucide-check-circle",
    });
  } catch {
    toast.add({
      title: "Error",
      description: "Failed to copy referral code",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
  }
}

function resetForm() {
  mapProfileToForm(profile.value);
}

function mapProfileToForm(data: any) {
  form.value = {
    firstName: data.firstName || "",
    lastName: data.lastName || "",
    email: data.email || "",
    phone: data.phone || "",
    dob: data.dob || "",
    gender: data.gender || "",
    address: data.address || "",
    cityId: data.cityId || null,
    idType: data.idType || "",
    idNumber: data.idNumber || "",
    latitude: data.latitude || null,
    longitude: data.longitude || null,
  };

  // Set calendar date from form dob
  if (data.dob) {
    try {
      dobCalendarDate.value = CalendarDate.fromString(data.dob);
    } catch {
      dobCalendarDate.value = null;
    }
  } else {
    dobCalendarDate.value = null;
  }

  // Set selectedCity from cityId
  if (data.cityId && cities.value.length > 0) {
    const found = cities.value.find((c) => c.id === data.cityId);
    selectedCity.value = found || null;
  } else {
    selectedCity.value = null;
  }

  // Update original form for comparison
  originalForm.value = { ...form.value };
}

async function fetchProfile() {
  loading.value = true;
  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No authentication token found.");

    const res = await $fetch(`${apiBaseUrl}/profile`, {
      method: "GET",
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (res.success && res.data) {
      profile.value = res.data;
      mapProfileToForm(res.data);
    } else {
      throw new Error(res.message || "Failed to fetch profile");
    }
  } catch (e: any) {
    toast.add({
      title: "Error",
      description: e.message || "Failed to fetch profile",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
  } finally {
    loading.value = false;
  }
}

// Add error state for form validation errors
const formErrors = ref<Record<string, string>>({});

async function onProfileSubmit() {
  loading.value = true;
  formErrors.value = {};
  try {
    const token = localStorage.getItem("token");
    if (!token) throw new Error("No authentication token found.");

    // Build body and remove fields without a value
    const rawBody = {
      firstname: form.value.firstName,
      lastname: form.value.lastName,
      gender: form.value.gender,
      dob: form.value.dob,
      address: form.value.address,
      cityId: selectedCity.value ? selectedCity.value.id : null,
      idType: form.value.idType,
      idNumber: form.value.idNumber,
      latitude: form.value.latitude,
      longitude: form.value.longitude,
    };
    // Remove fields with empty string, null, or undefined
    const body = Object.fromEntries(
      Object.entries(rawBody).filter(
        ([, v]) => v !== null && v !== undefined && v !== ""
      )
    );

    const res = await $fetch(`${apiBaseUrl}/profile/update`, {
      method: "PATCH",
      headers: {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
      },
      body,
      onResponseError({ response }) {
        if (response.status === 422 && response._data?.errors) {
          formErrors.value = response._data.errors;
          toast.add({
            title: "Validation Error",
            description: response._data.message || "Please check your inputs",
            color: "error",
            icon: "i-lucide-alert-circle",
          });
        }
      },
    });

    if (res.success && res.data) {
      profile.value = res.data;
      mapProfileToForm(res.data);
      toast.add({
        title: "Success",
        description: "Profile updated successfully!",
        color: "green",
        icon: "i-lucide-check-circle",
      });
    } else if (res.status === 422 && res.errors) {
      formErrors.value = res.errors;
      toast.add({
        title: "Validation Error",
        description: res.message || "Please check your inputs",
        color: "error",
        icon: "i-lucide-alert-circle",
      });
    } else {
      throw new Error(res.message || "Failed to update profile");
    }
  } catch (e: any) {
    toast.add({
      title: "Error",
      description: e.message || "Failed to update profile",
      color: "red",
      icon: "i-lucide-alert-circle",
    });
  } finally {
    loading.value = false;
  }
}

// Initialize component
onMounted(() => {
  fetchProfile();
  fetchCities();
});
</script>

<style scoped>
/* Custom styles if needed */
.btn-primary {
  background: #008282;
  color: #fff;
  font-weight: 600;
  border-radius: 0.5rem;
  padding: 0.75rem;
  border: none;
  cursor: pointer;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
  font-weight: 500;
  border-radius: 0.5rem;
  padding: 0.75rem;
  border: none;
  cursor: pointer;
}
</style>
