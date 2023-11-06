<template>
    <a
        href="javascript:alert('todo');"
        class="py-5 px-2 text-red-600 hover:text-red-700"
    >
        <HeartIcon class="w-4 h-4 fill-current" />
    </a>
</template>

<script setup>
import { HeartIcon } from "@heroicons/vue/24/solid";
</script>
