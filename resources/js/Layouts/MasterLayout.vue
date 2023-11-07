<template>
    <Head v-if="title" :title="title" />
    <div class="bg-white h-full flex flex-col">
        <NavBar />
        <div class="h-full flex flex-1">
            <SideMenu />
            <main class="h-full flex flex-1">
                <slot></slot>
            </main>
        </div>
    </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";
import SideMenu from "@/Components/SideMenu.vue";

defineProps({
    title: {
        type: String,
        default: null,
    },
});
</script>
