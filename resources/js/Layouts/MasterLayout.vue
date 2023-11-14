<template>
    <Head v-if="title" :title="title" />
    <NavBar />
    <SideMenu />
    <main class="w-full overflow-y-auto overflow-x-hidden z-0 flex flex-1 flex-col sm:pl-52 relative h-[calc(100%-theme('height.14'))]">
        <slot></slot>
    </main>
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
