<template>
  <div class="h-full overflow-y-auto p-4 space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-gray-900">
              Total Users
            </h3>
            <UIcon name="i-heroicons-users" class="h-5 w-5 text-gray-500" />
          </div>
        </template>
        <p class="text-3xl font-bold">1,234</p>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-gray-900">
              Revenue
            </h3>
            <UIcon
              name="i-heroicons-currency-dollar"
              class="h-5 w-5 text-gray-500"
            />
          </div>
        </template>
        <p class="text-3xl font-bold">$12,345</p>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-gray-900">
              Active Projects
            </h3>
            <UIcon name="i-heroicons-briefcase" class="h-5 w-5 text-gray-500" />
          </div>
        </template>
        <p class="text-3xl font-bold">42</p>
      </UCard>

      <UCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold leading-6 text-gray-900">
              Tasks Completed
            </h3>
            <UIcon
              name="i-heroicons-check-circle"
              class="h-5 w-5 text-gray-500"
            />
          </div>
        </template>
        <p class="text-3xl font-bold">87%</p>
      </UCard>
    </div>

    <div class="grid grid-cols-1 gap-4">
      <UCard>
        <template #header>
          <h3 class="text-base font-semibold leading-6 text-gray-900">
            Recent Activity
          </h3>
        </template>
        <div class="space-y-4">
          <div
            v-for="activity in activities"
            :key="activity.id"
            class="flex items-start"
          >
            <div class="flex-shrink-0">
              <UIcon :name="activity.icon" class="h-5 w-5 text-gray-500" />
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">
                {{ activity.title }}
              </p>
              <p class="text-sm text-gray-500">{{ activity.description }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ activity.time }}</p>
            </div>
          </div>
        </div>
      </UCard>

      <UCard>
        <template #header>
          <h3 class="text-base font-semibold leading-6 text-gray-900">
            Quick Actions
          </h3>
        </template>
        <div class="grid grid-cols-2 gap-4">
          <UButton
            v-for="action in quickActions"
            :key="action.name"
            :icon="action.icon"
            variant="outline"
            class="w-full"
          >
            {{ action.name }}
          </UButton>
        </div>
      </UCard>
    </div>
  </div>
</template>

<script setup lang="ts">
const activities = [
  {
    id: 1,
    title: "New user registered",
    description: "John Doe created an account",
    time: "5 minutes ago",
    icon: "i-heroicons-user-plus",
  },
  {
    id: 2,
    title: "Project updated",
    description: "Marketing campaign v2 was updated",
    time: "1 hour ago",
    icon: "i-heroicons-pencil-square",
  },
  {
    id: 3,
    title: "Payment received",
    description: "$1,250 payment from Acme Inc",
    time: "2 hours ago",
    icon: "i-heroicons-currency-dollar",
  },
];

const quickActions = [
  { name: "Add User", icon: "i-heroicons-user-plus" },
  { name: "Create Project", icon: "i-heroicons-plus-circle" },
  { name: "Generate Report", icon: "i-heroicons-document-chart-bar" },
  { name: "Settings", icon: "i-heroicons-cog" },
];
</script>
