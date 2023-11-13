<template>
    <div
        ref="menuRef"
        class="w-52 bg-gray-500 h-full fixed sm:static"
        :class="menuClasses"
    >
        <SideMenuItem location="home" title="Queue" icon="gear" />
        <SideMenuItem
            location="history"
            title="History"
            icon="clock-rotate-left"
        />
        <SideMenuItem location="movies" title="Movies" icon="film" />
        <SideMenuItem location="series" title="Series" icon="tv" />
        <SideMenuItem location="settings" title="Settings" icon="sliders">
            <SideMenuSubItem location="settings.general" title="General" />
            <SideMenuSubItem location="settings.video" title="Video" />
            <SideMenuSubItem location="settings.audio" title="Audio" />
            <SideMenuSubItem location="settings.subtitles" title="Subtitles" />
            <SideMenuSubItem location="settings.radarr.index" title="Radarr" />
            <SideMenuSubItem location="settings.sonarr" title="Sonarr" />
        </SideMenuItem>
        <SideMenuItem location="system" title="System" icon="laptop">
            <SideMenuSubItem location="system.status" title="Status" />
            <SideMenuSubItem location="system.tasks" title="Tasks" />
            <SideMenuSubItem location="system.backup" title="Backup" />
            <SideMenuSubItem location="system.updates" title="Updates" />
            <SideMenuSubItem location="system.events" title="Events" />
            <SideMenuSubItem location="system.logs" title="Logs" />
        </SideMenuItem>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import SideMenuItem from "@/Components/SideMenuItem.vue";
import SideMenuSubItem from "@/Components/SideMenuSubItem.vue";
import { useMenuStore } from "@/store";
import { useDetectOutsideClick } from "@/composables";

const menuRef = ref(null);
const menu = useMenuStore();

const menuClasses = computed(() => {
    return {
        "hidden sm:block": !menu.isOpen,
        block: menu.isOpen,
    };
});

useDetectOutsideClick(menuRef, () => {
    menu.close();
});
</script>
