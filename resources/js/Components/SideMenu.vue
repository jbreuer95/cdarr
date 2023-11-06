<template>
    <div class="w-52 bg-gray-500 h-full">
        <SideMenuItem location="queue" title="Queue" icon="gear" />
        <SideMenuItem
            location="history"
            title="History"
            icon="clock-rotate-left"
        />
        <SideMenuItem location="series" title="Series" icon="tv" />
        <SideMenuItem location="movies" title="Movies" icon="film" />
    </div>
</template>

<script setup>
import SideMenuItem from "./SideMenuItem.vue";
</script>
