<template>
    <MasterLayout title="Movies">
        <PageToolbar>
            <PageToolBarItem
                v-if="page.props.setup"
                icon="rotate"
                title="Sync Movies"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="!page.props.setup"
                icon="gear"
                title="Setup Radarr"
            ></PageToolBarItem>
        </PageToolbar>
        <div v-if="page.props.setup" class="flex flex-1 flex-col p-4">

        </div>
    </MasterLayout>
</template>

<script setup>
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
</script>
