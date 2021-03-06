<aside class="fixed h-full bg-gray-700 w-52 mt-15 -ml-52 md:ml-0">
    <x-sidemenu-item route="queue" title="Queue" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>'/>
    <x-sidemenu-item route="history" title="History" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'/>
    <x-sidemenu-item route="series" title="Series" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>'/>
    <x-sidemenu-item route="movies" title="Movies" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>'/>
    <x-sidemenu-item route="settings" title="Settings" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'>
        <x-sidemenu-subitem route="settings.general" title="General" />
        <x-sidemenu-subitem route="settings.sonarr" title="Sonarr" />
        <x-sidemenu-subitem route="settings.radarr" title="Radarr" />
        <x-sidemenu-subitem route="settings.plex" title="Plex" />
        <x-sidemenu-subitem route="settings.emby" title="Emby" />
        <x-sidemenu-subitem route="settings.jellyfin" title="Jellyfin" />
        <x-sidemenu-subitem route="settings.video" title="Video" />
        <x-sidemenu-subitem route="settings.audio" title="Audio" />
        <x-sidemenu-subitem route="settings.subtitles" title="Subtitles" />
        <x-sidemenu-subitem route="settings.scheduler" title="Scheduler" />
        <x-sidemenu-subitem route="settings.notifications" title="Notifications" />
    </x-sidemenu-item>
    <x-sidemenu-item route="system" title="System" icon='<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>'>
        <x-sidemenu-subitem route="system.status" title="Status" />
        <x-sidemenu-subitem route="system.tasks" title="Tasks" />
        <x-sidemenu-subitem route="system.backup" title="Backup" />
        <x-sidemenu-subitem route="system.updates" title="Updates" />
        <x-sidemenu-subitem route="system.events" title="Events" />
        <x-sidemenu-subitem route="system.logs" title="Logs" />
    </x-sidemenu-item>
</aside>
