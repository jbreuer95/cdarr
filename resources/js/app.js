import 'alpinejs'
import Turbolinks from 'turbolinks'
import Echo from "laravel-echo"
window.Pusher = require('pusher-js');

Turbolinks.start()

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'local',
    cluster: 'local',
    wsHost: window.location.hostname,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
});
