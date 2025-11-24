import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

window.Echo.channel('zoom-chat')
    .listen('.ZoomMessageSent', (e) => {
        console.log('Message received:', e.message);
        sendToZoom(e.message);
    });
