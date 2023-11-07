<template>
    <div class="w-52 bg-gray-500 h-full">
        <SideMenuItem location="home" title="Queue" icon="gear" />
        <SideMenuItem
            location="history"
            title="History"
            icon="clock-rotate-left"
        />
        <SideMenuItem location="series" title="Series" icon="tv" />
        <SideMenuItem location="movies" title="Movies" icon="film" />
        <SideMenuItem location="settings" title="Settings" icon="sliders">
            <SideMenuSubItem location="settings.general" title="General" />
            <SideMenuSubItem location="settings.video" title="Video" />
            <SideMenuSubItem location="settings.audio" title="Audio" />
            <SideMenuSubItem location="settings.subtitles" title="Subtitles" />
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
import SideMenuItem from "./SideMenuItem.vue";
import SideMenuSubItem from "./SideMenuSubItem.vue";
</script>
