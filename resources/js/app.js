import 'alpinejs'
import Echo from "laravel-echo"
window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    cluster: 'local',
    wsHost: window.location.hostname,
    wsPort: window.location.port,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
});
