<template>
    <a
        :href="route(location)"
        class="w-full px-8 py-3 flex items-center"
        :class="classes"
    >
        <span class="mr-3 w-4 h-4">
            <slot name="icon" class="w-4 h-4 fill-current"></slot>
        </span>
        <span>{{ title }}</span>
    </a>
</template>

<script setup>
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

const classes = computed(() => {
    if (route().current() === props.location) {
        return "bg-gray-700 text-green-600 border-l-[3px] border-green-600";
    }

    return "text-white hover:text-green-600";
});
</script>
