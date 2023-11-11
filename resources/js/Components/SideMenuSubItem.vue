<template>
    <Link
        :href="route(location)"
        class="w-full px-8 py-3 pl-16 flex items-center border-l-[3px] box-border border-green-500"
        :class="linkClasses"
    >
        {{ title }}
    </Link>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    location: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
});

const active = computed(() => {
    return route().current(props.location);
});

const linkClasses = computed(() => {
    return {
        "text-green-500": active.value,
        "text-white hover:text-green-500": !active.value,
    };
});
</script>
