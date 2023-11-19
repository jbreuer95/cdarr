<template>
    <h2 class="text-xl border-b border-solid border-neural-200 mb-5">
        {{ title }}
    </h2>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>
