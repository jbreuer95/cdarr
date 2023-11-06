<template>
    <a
        :href="route(location)"
        class="w-full px-8 py-3 flex items-center border-l-[3px]"
        :class="classes"
    >
        <div class="flex flex-1 items-center ml-[-3px]">
            <FontAwesomeIcon :icon="icon" class="w-4 h-4 mr-3" />
            <span>{{ title }}</span>
        </div>
    </a>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
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
    icon: {
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
