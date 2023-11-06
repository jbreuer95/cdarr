<template>
    <div class="w-52 bg-gray-500 h-full">
        <SideMenuItemVue location="queue" title="Queue">
            <template #icon="props">
                <RectangleStackIcon :class="props.class" />
            </template>
        </SideMenuItemVue>
        <SideMenuItemVue location="history" title="History">
            <template #icon="props">
                <ClockIcon :class="props.class" />
            </template>
        </SideMenuItemVue>
        <SideMenuItemVue location="series" title="Series">
            <template #icon="props">
                <TvIcon :class="props.class" />
        </SideMenuItemVue>
        <SideMenuItemVue location="movies" title="Movies">
            <template #icon="props">
                <FilmIcon :class="props.class" />
            </template>
        </SideMenuItemVue>
    </div>
</template>

<script setup>
import {
    RectangleStackIcon,
    ClockIcon,
    FilmIcon,
    TvIcon,
} from "@heroicons/vue/24/outline";
import SideMenuItemVue from "./SideMenuItem.vue";
</script>
