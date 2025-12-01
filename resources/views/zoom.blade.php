@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="zmmtg-root"></div>
        <div id="popup-container"></div>
        <div id="debug-log"></div>
    </div>

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

        /* Popup dashboard */
        #popup-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10001;
            display: flex;
            flex-direction: column;
            pointer-events: none;
        }

        .broadcast-popup {
            background: rgba(6, 66, 38, 0.96);
            color: #fff;
            padding: 18px 22px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.55);
            opacity: 0;
            transform: translateY(-12px);
            transition: opacity .28s ease, transform .28s ease;
            pointer-events: auto;
            margin-top: 12px;
        }

        .broadcast-popup.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Debug console */
        #debug-log {
            position: fixed;
            bottom: 10px;
            left: 10px;
            max-height: 260px;
            width: 360px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.75);
            color: #fff;
            font-size: 12px;
            padding: 10px;
            border-radius: 8px;
            z-index: 10002;
            font-family: monospace;
        }
    </style>

    <!-- Zoom & Pusher SDKs -->
    <script src="https://source.zoom.us/3.12.0/lib/vendor/react.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/react-dom.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/redux.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/redux-thunk.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/lib/vendor/lodash.min.js"></script>
    <script src="https://source.zoom.us/3.12.0/zoom-meeting-3.12.0.min.js"></script>

    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>

    <script>
        /* ----------------------------------------------------------
           CONFIG FROM BACKEND
        ---------------------------------------------------------- */
        const meetingNumber = @json($meetingId);
        const meetingPassword = @json($passCode);
        const userName = @json($userName);
        const signature = @json($signature);
        const sdkKey = @json($sdkKey);

        /* ----------------------------------------------------------
           DEBUG LOGGER
        ---------------------------------------------------------- */
        function debugLog(msg) {
            console.log(msg);

            const logDiv = document.getElementById("debug-log");
            const p = document.createElement("p");
            p.textContent = msg;

            logDiv.appendChild(p);
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        /* Capture JS errors */
        window.onerror = function(message, source, lineno, colno, error) {
            debugLog("JS ERROR: " + message + " at " + source + ":" + lineno);
        };

        /* ----------------------------------------------------------
           DEBUG OUTPUT START
        ---------------------------------------------------------- */
        debugLog("🔧 Debug window active.");
        debugLog("Meeting Number: " + meetingNumber);
        debugLog("Subscribing to: zoom-meeting." + meetingNumber);

        /* ----------------------------------------------------------
           ZOOM INIT
        ---------------------------------------------------------- */
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        ZoomMtg.init({
            leaveUrl: "{{ url()->previous() }}",
            success: () => {
                debugLog("Zoom SDK initialized");

                ZoomMtg.join({
                    sdkKey,
                    signature,
                    meetingNumber,
                    passWord: meetingPassword,
                    userName,
                    success: () => debugLog("Joined Zoom meeting"),
                    error: err => debugLog("Zoom join error: " + JSON.stringify(err))
                });
            },
            error: err => debugLog("Zoom init error: " + JSON.stringify(err))
        });

        /* ----------------------------------------------------------
           PUSHER INIT + ADVANCED DEBUGGING
        ---------------------------------------------------------- */
        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true
        });

        debugLog("Connecting to Pusher...");

        pusher.connection.bind("connected", () => debugLog("🟢 Pusher connected"));
        pusher.connection.bind("state_change", state => debugLog("Pusher state: " + JSON.stringify(state)));
        pusher.connection.bind("error", err => debugLog("🔴 Pusher error: " + JSON.stringify(err)));

        const channel = pusher.subscribe("zoom-meeting." + meetingNumber);

        channel.bind("pusher:subscription_succeeded", () => debugLog("🟢 Subscribed to channel"));
        channel.bind("pusher:subscription_error", e => debugLog("🔴 Channel subscription error: " + JSON.stringify(e)));

        /* ----------------------------------------------------------
           POPUP QUEUE SYSTEM
        ---------------------------------------------------------- */
        let popupQueue = [];
        let popupActive = false;

        function showPopupQueued(data) {
            popupQueue.push(data);
            if (!popupActive) processQueue();
        }

        function processQueue() {
            if (!popupQueue.length) {
                popupActive = false;
                return;
            }
            popupActive = true;

            const next = popupQueue.shift();
            showPopup(next, () => {
                popupActive = false;
                processQueue();
            });
        }

        /* ----------------------------------------------------------
           POPUP RENDERER
        ---------------------------------------------------------- */
        function showPopup(data, onClose = () => {}) {
            debugLog("Rendering popup...");

            const activity = data.assignable;
            const container = document.getElementById("popup-container");

            const popup = document.createElement("div");
            popup.className = "broadcast-popup";

            let html = `<h3>${activity.title}</h3>`;
            if (activity.description) html += `<p>${activity.description}</p>`;

            activity.questions.forEach((q, i) => {
                html += `<div class="quiz-question">
                    <p><strong>Q${i+1}:</strong> ${q.questionText}</p>
                    <ul style="list-style:none;padding-left:0;">`;

                if (q.options && q.options.length) {
                    q.options.forEach(opt => {
                        html += `<li>
                    <label>
                        <input type="radio" name="q${i}"
                            data-is-correct="${opt.isCorrect ? 1 : 0}"
                            data-option-id="${opt.id}">
                        ${opt.optionText}
                    </label>
                </li>`;
                    });
                } else {
                    html += `<textarea name="q${i}" style="width:100%;height:60px;"></textarea>`;
                }

                html += `</ul></div>`;
            });

            html += `<button id="submitQuizBtn" disabled>Submit</button>`;
            popup.innerHTML = html;
            container.appendChild(popup);

            setTimeout(() => popup.classList.add("show"), 20);

            /* Enable / Disable submit */
            const submitBtn = popup.querySelector("#submitQuizBtn");
            popup.querySelectorAll(".quiz-question").forEach((qDiv, idx) => {
                qDiv.addEventListener("input", () => {
                    const ready = [...popup.querySelectorAll(".quiz-question")].every((q, i) => {
                        const radio = popup.querySelector(`input[name="q${i}"]:checked`);
                        const text = popup.querySelector(`textarea[name="q${i}"]`);
                        return radio || (text && text.value.trim() !== "");
                    });
                    submitBtn.disabled = !ready;
                });
            });

            /* Submit response */
            submitBtn.onclick = async () => {
                const responses = [];

                activity.questions.forEach((q, i) => {
                    const radio = popup.querySelector(`input[name="q${i}"]:checked`);
                    const textarea = popup.querySelector(`textarea[name="q${i}"]`);

                    responses.push({
                        meeting_assignment_id: data.assignment_id,
                        questionable_id: q.id,

                        questionable_type: data.assignable_type.includes("LiveSet") ?
                            "App\\Models\\LiveSetQuestion" : "App\\Models\\QuizQuestion",

                        selected_option_id: radio ? radio.dataset.optionId : null,
                        subjective_answer: textarea ? textarea.value : null,
                        is_correct: radio ? parseInt(radio.dataset.isCorrect) : null
                    });
                });

                debugLog("Submitting responses: " + JSON.stringify(responses));

                try {
                    const res = await fetch("/meeting-responses", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            responses
                        })
                    });

                    debugLog("Server response: " + await res.text());
                } catch (err) {
                    debugLog("Error sending responses: " + err);
                }

                let score = responses.filter(r => r.is_correct).length;

                const resultEl = document.createElement("p");
                resultEl.style.fontWeight = "bold";
                resultEl.style.marginTop = "10px";
                resultEl.textContent = `You scored ${score} / ${activity.questions.length}`;
                popup.appendChild(resultEl);

                submitBtn.disabled = true;
                submitBtn.textContent = "Submitted";

                setTimeout(() => {
                    popup.remove();
                    onClose();
                }, 2500);
            };
        }

        /* ----------------------------------------------------------
           EVENT LISTENER
        ---------------------------------------------------------- */
        // Listen for ActivityAssigned event
        channel.bind("ActivityAssigned", data => {
            debugLog("🟢 ActivityAssigned event received");
            console.log("🎯 Assigned Activity Data:", data);

            debugLog(`Assigned activity title: ${data.assignable.title}`);

            // Show the popup
            showPopupQueued(data);
        });
    </script>
@endsection
