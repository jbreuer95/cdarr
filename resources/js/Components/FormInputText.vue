<template>
    <div class="flex align-middle">
        <label
            v-if="label"
            for="helper-text"
            class="block mr-5 text-sm font-bold mt-1.5 w-64 text-right"
            :class="labelClasses"
            >{{ label }}</label
        >
        <div class="flex flex-col flex-1 mb-5">
            <input
                id="helper-text"
                :type="type"
                class="border text-sm rounded-lg block w-full px-4 py-1.5"
                :class="inputClasses"
                :placeholder="placeholder"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <p v-if="error" class="mt-2 text-sm text-red-600">
                {{ error }}
            </p>
            <p
                v-if="helper || $slots.helper"
                id="helper-text-explanation"
                class="mt-2 text-sm text-gray-500"
            >
                <slot name="helper">
                    {{ helper }}
                </slot>
            </p>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: {
        type: String,
        default: null,
    },
    helper: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "text",
    },
    placeholder: {
        type: String,
        default: "",
    },
    modelValue: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});
defineEmits(["update:modelValue"]);

const labelClasses = computed(() => {
    return {
        "text-neutral-600": !props.error,
        "text-red-700": props.error,
    };
});

const inputClasses = computed(() => {
    return {
        "border-gray-300 text-neutral-600 placeholder-neutral-400 focus:ring-green-400 focus:border-green-400":
            !props.error,
        "border-red-500 text-red-900 placeholder-red-700 focus:ring-red-500 focus:border-red-500":
            props.error,
    };
});
</script>
