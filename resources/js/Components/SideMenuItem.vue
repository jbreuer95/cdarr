<template>
    <Link
        :href="route(location)"
        class="w-full px-8 py-3 flex items-center border-l-[3px]"
        :class="linkClasses"
    >
        <div class="flex flex-1 items-center ml-[-3px]">
            <FontAwesomeIcon :icon="icon" class="w-4 h-4 mr-3" />
            <span>{{ title }}</span>
        </div>
    </Link>
    <slot v-if="active || subActive"></slot>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
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

const mainActive = computed(() => {
    return route().current(props.location);
});

const subActive = computed(() => {
    return route().current(`${props.location}.*`);
});

const active = computed(() => {
    return subActive.value || mainActive.value;
});

const linkClasses = computed(() => {
    return {
        "text-white hover:text-green-500": !mainActive.value,
        "text-green-500": mainActive.value,
        "box-border bg-gray-700 border-green-500": active.value,
        "border-transparent": !active.value,
    };
});
</script>
