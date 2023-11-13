<template>
    <MasterLayout title="Movies">
        <PageToolbar>
            <PageToolBarItem
                v-if="setup"
                icon="rotate"
                title="Sync Movies"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="!setup"
                icon="gear"
                title="Setup Radarr"
            ></PageToolBarItem>
        </PageToolbar>
        <div v-if="setup" class="flex flex-1 flex-col p-4">
            <table>
                <tr>
                    <th>Movie title</th>
                    <th>Year</th>
                    <th>Studio</th>
                    <th>Quality</th>
                    <th>Status</th>
                </tr>
            </table>
        </div>
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";

defineProps({
    setup: {
        type: Boolean,
        default:  false
    }
})
</script>
