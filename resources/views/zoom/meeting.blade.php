<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zoom Meeting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Zoom SDK CSS -->
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.12.0/css/bootstrap.css" />

    <style>
        html, body { height: 100%; margin: 0; }
        #zmmtg-root { width: 100%; height: 100%; background: #000; }

        /* Hide Zoom UI elements */
        #app-signal,
        .waiting-room-container .wr-default-bg,
        .waiting-room-container .wr-content-default,
        .page-footer,
        #participant,
        .more-button__item-box,
        .wr-header { display: none !important; }

        /* Watermark styling */
        .watermark {
            position: fixed;
            background-color: rgba(255,255,255,0.8);
            padding: 5px 10px;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            z-index: 10000;
            pointer-events: none;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div id="zmmtg-root"></div>

<!-- Zoom WebSDK scripts -->
<script src="https://source.zoom.us/3.12.0/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/lodash.min.js"></script>
<script src="https://source.zoom.us/3.12.0/zoom-meeting-3.12.0.min.js"></script>

<script>
    const meetingNumber = @json($meetingId);
    const meetingPassword = @json($passCode);
    const userName = @json($userName);
    const signature = @json($signature);
    const sdkKey = @json($sdkKey);

    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();

    
    // Join Zoom
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
                success: function(res) { console.log("Joined meeting", res); },
                error: function(err) { console.error(err); }
            });
        },
        error: function(err) { console.error(err); }
    });

    // Meeting event listeners
    ZoomMtg.inMeetingServiceListener('onUserJoin', data => console.log('User joined', data));
    ZoomMtg.inMeetingServiceListener('onUserLeave', data => console.log('User left', data));
    ZoomMtg.inMeetingServiceListener('onMeetingStatus', data => console.log('Meeting status', data));
</script>
</body>
</html>
