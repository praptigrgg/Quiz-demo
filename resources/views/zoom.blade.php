@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="zmmtg-root"></div>
        <div id="popup-container"></div>
        <!-- Removed debug-log div -->
    </div>

    <!-- Zoom SDK CSS -->
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.12.0/css/bootstrap.css" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        #zmmtg-root {
            width: 100%;
            height: 100%;
            background: #000;
        }

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

        /* Removed #debug-log CSS */
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
           ZOOM INIT
        ---------------------------------------------------------- */
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        ZoomMtg.init({
            leaveUrl: "{{ url()->previous() }}",
            success: () => {
                // Zoom SDK initialized

                ZoomMtg.join({
                    sdkKey,
                    signature,
                    meetingNumber,
                    passWord: meetingPassword,
                    userName,
                    success: () => {/* Joined Zoom meeting */},
                    error: err => {/* Zoom join error */}
                });
            },
            error: err => {/* Zoom init error */}
        });

        /* ----------------------------------------------------------
           PUSHER INIT
        ---------------------------------------------------------- */
        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true
        });

        pusher.connection.bind("connected", () => {/* Pusher connected */});
        pusher.connection.bind("state_change", state => {/* Pusher state changed */});
        pusher.connection.bind("error", err => {/* Pusher error */});

        const channel = pusher.subscribe("zoom-meeting." + meetingNumber);
        channel.bind("pusher:subscription_succeeded", () => {/* Subscribed to channel */});
        channel.bind("pusher:subscription_error", e => {/* Channel subscription error */});

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
           POPUP RENDERER WITH TIMER (HHh MMm SSs) + ELAPSED TIME
        ---------------------------------------------------------- */
        function showPopup(data, onClose = () => {}) {
            // Removed debugLog call

            const activity = data.assignable;
            const container = document.getElementById("popup-container");
            const popup = document.createElement("div");
            popup.className = "broadcast-popup";

            /* TIMER ELEMENT */
            const timerMinutes = data.timer ?? 1; // backend timer in minutes
            let remaining = timerMinutes * 60; // convert to seconds
            let timerInterval = null;
            const startTime = Date.now(); // track quiz start

            function formatTime(sec) {
                const hours = Math.floor(sec / 3600);
                const minutes = Math.floor((sec % 3600) / 60);
                const seconds = sec % 60;
                return `${hours}h ${minutes}m ${seconds}s`;
            }

            let html = `
                <h3>${activity.title}</h3>
                ${activity.description ? `<p>${activity.description}</p>` : ''}
                <div id="countdownTimer" style="
                    font-size:18px;
                    font-weight:bold;
                    color:#ffe082;
                    margin-bottom:10px;
                ">
                    Time Left: ${formatTime(remaining)}
                </div>
            `;

            /* QUESTIONS */
            activity.questions.forEach((q, i) => {
                html += `<div class="quiz-question" data-q="${i}">
                    <p><strong>Q${i + 1}:</strong> ${q.questionText}</p>
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

            /* ENABLE/DISABLE SUBMIT */
            const submitBtn = popup.querySelector("#submitQuizBtn");
            function checkReady() {
                const ready = [...popup.querySelectorAll(".quiz-question")].every((q, i) => {
                    const radio = popup.querySelector(`input[name="q${i}"]:checked`);
                    const text = popup.querySelector(`textarea[name="q${i}"]`);
                    return radio || (text && text.value.trim() !== "");
                });
                submitBtn.disabled = !ready;
            }
            popup.querySelectorAll(".quiz-question").forEach((qDiv, idx) => {
                qDiv.addEventListener("input", checkReady);
            });

            /* SUBMISSION FUNCTION */
            async function submitAnswers(auto = false) {
                submitBtn.disabled = true;

                const elapsedTime = Math.floor((Date.now() - startTime) / 1000); // seconds taken

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
                        is_correct: radio ? parseInt(radio.dataset.isCorrect) : null,
                    });
                });

                // Removed debugLog calls

                try {
                    await fetch("/meeting-responses", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            responses,
                            elapsed_time: elapsedTime // <-- NEW
                        })
                    });
                } catch (err) {
                    // Removed debugLog calls
                }

                /* Score Display */
                let score = responses.filter(r => r.is_correct).length;
                const result = document.createElement("p");
                result.style.fontWeight = "bold";
                result.style.marginTop = "10px";
                result.innerHTML = auto ?
                    `⏳ Time's up! Auto-submitted.<br>Your score: <strong>${score}</strong>` :
                    `Your score: <strong>${score}</strong>`;
                popup.appendChild(result);

                clearInterval(timerInterval);

                setTimeout(() => {
                    popup.remove();
                    onClose();
                }, 2500);
            }

            submitBtn.onclick = () => submitAnswers(false);

            /* COUNTDOWN */
            const timerEl = popup.querySelector("#countdownTimer");
            timerInterval = setInterval(() => {
                remaining--;
                timerEl.textContent = `Time Left: ${formatTime(remaining)}`;

                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    submitAnswers(true); // AUTO SUBMIT
                }
            }, 1000);
        }

        /* EVENT LISTENER */
        channel.bind("ActivityAssigned", data => {
            // Removed debugLog calls
            showPopupQueued(data);
        });
    </script>
@endsection
