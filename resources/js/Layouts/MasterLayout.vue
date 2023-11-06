<template>
    <Head v-if="title" :title="title" />
    <div class="bg-white h-full flex flex-col">
        <NavBar />
        <main class="flex">
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";

defineProps({
    title: {
        type: String,
        default: null,
    },
});
</script>
