<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zoom Meeting with Quiz Popup</title>

    <!-- Zoom SDK CSS -->
    <link type="text/css" rel="stylesheet" href="https://source.zoom.us/3.12.0/css/bootstrap.css" />
<style>
    /* General body/layout */
    html, body {
        height: 100%;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #000;
        color: #fff;
    }

    #layout {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    #meeting-wrapper {
        flex: 1 1 100%;
        transition: flex-basis 0.3s ease;
    }

    #popup-container {
        flex: 0 0 0;
        max-width: 0;
        height: 100%;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        padding: 0;
        box-sizing: border-box;
        transition: flex-basis 0.3s ease, max-width 0.3s ease, padding 0.3s ease;
        background: #0b1a12;
    }

    body.quiz-open #meeting-wrapper {
        flex: 0 0 60%;
    }

    body.quiz-open #popup-container {
        flex: 0 0 40%;
        max-width: 40%;
        padding: 20px;
        box-shadow: -4px 0 20px rgba(0,0,0,0.5);
    }

    /* Popup card */
    .broadcast-popup {
        background: #1b2c21;
        color: #fff;
        padding: 20px 24px;
        border-radius: 12px;
        max-width: 100%;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.5);
        opacity: 0;
        transform: translateY(-16px);
        transition: opacity 0.3s ease, transform 0.3s ease;
        margin-top: 16px;
        border-left: 4px solid #00e676;
    }

    .broadcast-popup.show {
        opacity: 1;
        transform: translateY(0);
    }

    .broadcast-popup h3 {
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 1.5rem;
        color: #00e676;
    }

    .broadcast-popup p {
        margin-bottom: 16px;
        line-height: 1.6;
        color: #cfd8dc;
    }

    /* Timer */
    #countdownTimer {
        font-weight: bold;
        color: #ffd600;
        margin-bottom: 16px;
        font-size: 1rem;
    }

    /* Quiz options */
    .quiz-question {
        background: #24332a;
        padding: 14px 16px;
        margin-bottom: 14px;
        border-radius: 10px;
    }

    .quiz-question p {
        margin: 0 0 8px 0;
        font-weight: 500;
    }

    .quiz-option-label {
        display: block;
        padding: 10px 14px;
        margin-bottom: 8px;
        border-radius: 8px;
        cursor: pointer;
        background: rgba(255,255,255,0.05);
        transition: background 0.2s, transform 0.2s;
        user-select: none;
    }

    .quiz-option-label:hover {
        background: rgba(0,230,118,0.2);
        transform: translateX(2px);
    }

    .quiz-option-label input {
        margin-right: 10px;
    }

    textarea {
        width: 100%;
        height: 60px;
        border-radius: 8px;
        padding: 8px;
        border: none;
        background: #324036;
        color: #fff;
        resize: vertical;
    }

    /* Submit button */
    #submitQuizBtn {
        margin-top: 16px;
        padding: 12px 20px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        border-radius: 8px;
        background: #00e676;
        color: #000;
        font-size: 1rem;
        transition: background 0.2s, transform 0.2s;
    }

    #submitQuizBtn:disabled {
        background: #555;
        cursor: not-allowed;
    }

    #submitQuizBtn:hover:not(:disabled) {
        background: #00c853;
        transform: scale(1.03);
    }
</style>

</head>
<body>

<div id="layout">
    <div id="meeting-wrapper">
        <div id="zmmtg-root"></div>
    </div>
    <div id="popup-container"></div>
</div>

<!-- Zoom & Pusher SDKs -->
<script src="https://source.zoom.us/3.12.0/lib/vendor/react.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/react-dom.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/redux.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/redux-thunk.min.js"></script>
<script src="https://source.zoom.us/3.12.0/lib/vendor/lodash.min.js"></script>
<script src="https://source.zoom.us/3.12.0/zoom-meeting-3.12.0.min.js"></script>
<script src="https://js.pusher.com/8.0/pusher.min.js"></script>

<script>
    // Backend config
    const meetingNumber = @json($meetingId);
    const meetingPassword = @json($passCode);
    const userName = @json($userName);
    const signature = @json($signature);
    const sdkKey = @json($sdkKey);

    // Zoom init
    ZoomMtg.preLoadWasm();
    ZoomMtg.prepareWebSDK();
    ZoomMtg.init({
        leaveUrl: "{{ url()->previous() }}",
        success: () => {
            ZoomMtg.join({
                sdkKey,
                signature,
                meetingNumber,
                passWord: meetingPassword,
                userName,
                success: () => {},
                error: err => {}
            });
        },
        error: err => {}
    });

    // Pusher init
    const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        forceTLS: true
    });
    const channel = pusher.subscribe("zoom-meeting." + meetingNumber);

    // Popup queue
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

    function showPopup(data, onClose = () => {}) {
        document.body.classList.add("quiz-open");

        const activity = data.assignable;
        const container = document.getElementById("popup-container");
        const popup = document.createElement("div");
        popup.className = "broadcast-popup";

        const timerMinutes = data.timer ?? 1;
        let remaining = timerMinutes * 60;
        let timerInterval = null;

        function formatTime(sec) {
            const hours = Math.floor(sec / 3600);
            const minutes = Math.floor((sec % 3600) / 60);
            const seconds = sec % 60;
            return `${hours}h ${minutes}m ${seconds}s`;
        }

        let html = `<h3>${activity.title}</h3>`;
        html += activity.description ? `<p>${activity.description}</p>` : '';
        html += `<div id="countdownTimer">Time Left: ${formatTime(remaining)}</div>`;

        activity.questions.forEach((q, i) => {
            html += `<div class="quiz-question" data-q="${i}">
                        <p><strong>Q${i+1}:</strong> ${q.questionText}</p>
                        <ul style="list-style:none;padding-left:0;">`;

            if (q.options && q.options.length) {
                q.options.forEach(opt => {
                    html += `<li>
                                <label class="quiz-option-label">
                                    <input type="radio" name="q${i}"
                                        data-is-correct="${opt.isCorrect ? 1 : 0}"
                                        data-option-id="${opt.id}">
                                    ${opt.optionText}
                                </label>
                             </li>`;
                });
            } else {
                html += `<textarea name="q${i}" style="width:100%;height:60px;border-radius:6px;padding:6px;"></textarea>`;
            }

            html += `</ul></div>`;
        });

        html += `<button id="submitQuizBtn" disabled>Submit</button>`;
        popup.innerHTML = html;
        container.appendChild(popup);
        setTimeout(() => popup.classList.add("show"), 20);

        const submitBtn = popup.querySelector("#submitQuizBtn");

        function checkReady() {
            const ready = [...popup.querySelectorAll(".quiz-question")].every((q, i) => {
                const radio = popup.querySelector(`input[name="q${i}"]:checked`);
                const text = popup.querySelector(`textarea[name="q${i}"]`);
                return radio || (text && text.value.trim() !== "");
            });
            submitBtn.disabled = !ready;
        }

        popup.querySelectorAll(".quiz-question").forEach(qDiv => {
            qDiv.addEventListener("input", checkReady);
        });

        async function submitAnswers() {
            submitBtn.disabled = true;

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

            try {
                await fetch("/meeting-responses", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ responses })
                });
            } catch (err) {}

            clearInterval(timerInterval);
            setTimeout(() => {
                popup.remove();
                document.body.classList.remove("quiz-open");
                onClose();
            }, 500);
        }

        submitBtn.onclick = submitAnswers;

        const timerEl = popup.querySelector("#countdownTimer");
        timerInterval = setInterval(() => {
            remaining--;
            timerEl.textContent = `Time Left: ${formatTime(remaining)}`;
            if (remaining <= 0) {
                clearInterval(timerInterval);
                submitAnswers();
            }
        }, 1000);
    }

    channel.bind("ActivityAssigned", data => {
        showPopupQueued(data);
    });
</script>

</body>
</html>
