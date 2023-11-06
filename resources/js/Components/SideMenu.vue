<template>
    <div class="w-52 bg-gray-500 h-full">
        <SideMenuItemVue location="queue" title="Queue">
            <template #icon="props">
                <Bars3Icon :class="props.class" />
            </template>
        </SideMenuItemVue>
        <SideMenuItemVue location="history" title="History">
            <template #icon="props">
                <Bars3Icon :class="props.class" />
            </template>
        </SideMenuItemVue>
    </div>
</template>

<script setup>
import { Bars3Icon } from "@heroicons/vue/24/solid";
import SideMenuItemVue from "./SideMenuItem.vue";
</script>
