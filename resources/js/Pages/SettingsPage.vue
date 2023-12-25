<template>
    <MasterLayout title="Settings">
        <div class="flex flex-col p-4">
            <div>
                <a
                    class="text-xl border-b border-solid border-neural-200 w-fit"
                    :href="route('settings.radarr')"
                >
                    Radarr
                </a>
                <p class="mt-2 mb-7">Radarr connection settings</p>
            </div>
            <div>
                <a
                    class="text-xl border-b border-solid border-neural-200 w-fit"
                    :href="route('settings.sonarr')"
                >
                    Sonarr
                </a>
                <p class="mt-2 mb-7">Sonarr connection settings</p>
            </div>
        </div>
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
</script>
