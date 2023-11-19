<template>
    <div
        v-if="!success && !loading"
        class="w-16 h-16 flex flex-col items-center justify-center text-center pt-1"
        :class="classes"
    >
        <FontAwesomeIcon v-if="icon" :icon="icon" class="w-5 h-5" />
        <div class="flex flex-col justify-center h-6 mt-1">
            <p class="text-xs leading-3 text-white">{{ computedTitle }}</p>
        </div>
    </div>
    <div
        v-else-if="loading"
        class="w-16 h-16 flex flex-col items-center justify-center text-white"
    >
        <FontAwesomeIcon icon="gear" class="w-5 h-5" spin />
    </div>
    <div
        v-else-if="success"
        class="w-16 h-16 flex flex-col items-center justify-center text-green-400"
    >
        <FontAwesomeIcon icon="check" class="w-5 h-5" />
    </div>
</template>

<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { computed } from "vue";

const props = defineProps({
    active: {
        type: Boolean,
        default: true,
    },
    icon: {
        type: String,
        default: null,
    },
    title: {
        type: String,
        required: true,
    },
    inActiveTitle: {
        type: String,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    success: {
        type: Boolean,
        default: false,
    },
});

const computedTitle = computed(() => {
    return props.inActiveTitle
        ? props.active
            ? props.title
            : props.inActiveTitle
        : props.title;
});

const classes = computed(() => {
    return {
        "text-white cursor-pointer hover:text-green-400": props.active,
        "text-gray-300": !props.active,
    };
});
</script>
