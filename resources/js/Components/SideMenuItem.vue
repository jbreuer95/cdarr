<template>
    <a
        :href="route(location)"
        class="w-full px-8 py-3 flex items-center border-l-[3px]"
        :class="classes"
    >
        <div class="flex flex-1 items-center ml-[-3px]">
            <span class="mr-3">
                <slot name="icon" class="w-5 h-5"></slot>
            </span>
            <span class="text-sm">{{ title }}</span>
        </div>
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
        return "box-border bg-gray-700 text-green-600 border-green-600";
    }

    return "text-white hover:text-green-600 border-transparent";
});
</script>
