<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Zoom Meeting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Zoom SDK CSS -->
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.12.0/css/bootstrap.css" />

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        #zmmtg-root {
            width: 100%;
            height: 100%;
            background: #000;
        }

        /* Hide unnecessary Zoom UI elements */
        #app-signal,
        .waiting-room-container .wr-default-bg,
        .waiting-room-container .wr-content-default,
        .page-footer,
        #participant,
        .more-button__item-box,
        .wr-header {
            display: none !important;
        }

        /* Animated watermark */
        .watermark {
            position: fixed;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 5px 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            z-index: 10000;
            pointer-events: none;
            white-space: nowrap;
        }

        /* Broadcast popup */
        #popup-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10001;
        }

        .broadcast-popup {
            background-color: rgba(255, 0, 0, 0.9);
            /* bright red background */
            color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            font-size: 20px;
            font-weight: bold;
            text-shadow: 1px 1px 3px #000;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.7);
            margin-top: 10px;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: auto;
            /* allow clicking the button */
            text-align: center;
            max-width: 90vw;
            word-wrap: break-word;
            position: relative;
            /* for close button positioning */
        }

        /* Close button */
        .broadcast-popup .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 18px;
            color: #fff;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div id="zmmtg-root"></div>
    <div id="popup-container"></div> <!-- Popup container -->

    <!-- Zoom Web SDK scripts -->
    <script src="https://source.zoom.us/3.12.0/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/lodash.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/zoom-meeting-3.12.0.min.js"></script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

    <script>
        const meetingNumber = @json($meetingId);
        const meetingPassword = @json($passCode);
        const userName = @json($userName);
        const signature = @json($signature);
        const sdkKey = @json($sdkKey);

        // ---------- Watermark ----------
        const watermark = document.createElement('div');
        watermark.className = 'watermark';
        document.body.appendChild(watermark);

        let x = Math.random() * window.innerWidth,
            y = Math.random() * window.innerHeight;
        let dx = (Math.random() - 0.5) * 2,
            dy = (Math.random() - 0.5) * 2;
        const speed = 3;

        function updateWatermark() {
            const now = new Date();
            watermark.textContent = `${userName} | ${now.toLocaleString()}`;
        }

        function animateWatermark() {
            x += dx * speed;
            y += dy * speed;

            const maxX = window.innerWidth - watermark.offsetWidth;
            const maxY = window.innerHeight - watermark.offsetHeight;

            if (x <= 0 || x >= maxX) dx = -dx;
            if (y <= 0 || y >= maxY) dy = -dy;

            x = Math.max(0, Math.min(x, maxX));
            y = Math.max(0, Math.min(y, maxY));

            watermark.style.left = x + 'px';
            watermark.style.top = y + 'px';

            requestAnimationFrame(animateWatermark);
        }

        setInterval(updateWatermark, 1000);
        animateWatermark();

        // ---------- Zoom SDK Init ----------
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        ZoomMtg.init({
            leaveUrl: "{{ url()->previous() }}",
            disableCORP: !window.crossOriginIsolated,
            success: function() {
                ZoomMtg.join({
                    sdkKey: sdkKey,
                    signature: signature,
                    meetingNumber: meetingNumber,
                    passWord: meetingPassword,
                    userName: userName,
                    success: res => console.log("Joined meeting", res),
                    error: err => console.error(err)
                });
            },
            error: err => console.error(err)
        });

        // ---------- Pusher Setup ----------
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true
        });

        pusher.connection.bind('connected', () => console.log('Pusher connected!'));
        pusher.connection.bind('error', err => console.error('Pusher error:', err));

        const channel = pusher.subscribe('zoom-chat');

        function showPopup(message) {
            console.log('Showing popup:', message);
            const container = document.getElementById('popup-container');

            const popup = document.createElement('div');
            popup.className = 'broadcast-popup';
            popup.innerText = message;

            // Add close button
            const closeBtn = document.createElement('button');
            closeBtn.className = 'close-btn';
            closeBtn.innerHTML = '&times;'; // × symbol
            closeBtn.onclick = () => {
                popup.style.opacity = '0';
                setTimeout(() => container.removeChild(popup), 300); // fade out
            };
            popup.appendChild(closeBtn);

            container.appendChild(popup);

            popup.offsetWidth; // force reflow
            popup.style.opacity = '1'; // fade in
        }


        // **Correct event binding**
        channel.bind('ZoomMessageSent', function(data) {
            showPopup(data.message);
        });

        // Optional: log all received events
        channel.bind_global(function(eventName, data) {
            console.log('Event received:', eventName, data);
        });

        // ---------- Optional Zoom Listeners ----------
        ZoomMtg.inMeetingServiceListener('onUserJoin', data => console.log('User joined', data));
        ZoomMtg.inMeetingServiceListener('onUserLeave', data => console.log('User left', data));
        ZoomMtg.inMeetingServiceListener('onMeetingStatus', data => console.log('Meeting status', data));
    </script>

</body>

</html>
