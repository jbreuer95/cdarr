<template>
    <div
        class="p-5 text-white hover:text-gray-300 cursor-pointer sm:hidden"
        @click.stop="menu.toggle()"
    >
        <FontAwesomeIcon icon="bars" class="w-4 h-4" />
    </div>
</template>

<script setup>
import { useMenuStore } from "@/store";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const menu = useMenuStore();
</script>
