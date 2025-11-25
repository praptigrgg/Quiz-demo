<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Zoom Meeting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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

        /* Hidden Zoom UI elements */
        #app-signal,
        .waiting-room-container .wr-default-bg,
        .waiting-room-container .wr-content-default,
        .page-footer,
        #participant,
        .more-button__item-box,
        .wr-header {
            display: none !important;
        }

        /* Floating watermark */
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

        /* Quiz popup container */
        #popup-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10001;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .broadcast-popup {
            background-color: rgba(3, 62, 16, 0.9);
            color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            text-shadow: 1px 1px 3px #000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
            margin-top: 10px;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: auto;
            text-align: left;
            max-width: 400px;
            max-height: 70vh;
            overflow-y: auto;
            position: relative;
        }

        .broadcast-popup h3 {
            margin: 0;
            font-size: 20px;
        }

        .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 18px;
            color: #fff;
            cursor: pointer;
        }

        .quiz-option-label {
            display: block;
            padding: 8px 12px;
            margin-bottom: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .quiz-option-label:hover {
            background-color: #e0f7fa;
        }
    </style>
</head>

<body>
    <div id="zmmtg-root"></div>
    <div id="popup-container"></div>

    <!-- Zoom Web SDK -->
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

        /* --- Moving watermark --- */
        const watermark = document.createElement('div');
        watermark.className = 'watermark';
        document.body.appendChild(watermark);

        let x = 50, y = 50, dx = 1.5, dy = 1.5;

        function updateWatermark() {
            const now = new Date();
            watermark.textContent = `${userName} | ${now.toLocaleString()}`;
        }
        setInterval(updateWatermark, 1000);

        function animateWatermark() {
            x += dx;
            y += dy;

            const maxX = window.innerWidth - watermark.offsetWidth;
            const maxY = window.innerHeight - watermark.offsetHeight;

            if (x <= 0 || x >= maxX) dx = -dx;
            if (y <= 0 || y >= maxY) dy = -dy;

            watermark.style.left = x + 'px';
            watermark.style.top = y + 'px';

            requestAnimationFrame(animateWatermark);
        }
        animateWatermark();


        /* --- Zoom SDK Init --- */
        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();

        ZoomMtg.init({
            leaveUrl: "{{ url()->previous() }}",
            disableCORP: !window.crossOriginIsolated,
            success: function() {
                ZoomMtg.join({
                    sdkKey,
                    signature,
                    meetingNumber,
                    passWord: meetingPassword,
                    userName,
                    success: res => console.log("Joined meeting", res),
                    error: err => console.error(err)
                });
            }
        });


        /* --- Pusher Setup --- */
        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true
        });

        const channel = pusher.subscribe('zoom-meeting.' + meetingNumber);


        /* --- Show Quiz Popup --- */
        function showPopup(data) {
            console.log("Popup received data:", data);

            if (!data || !data.quiz) {
                console.log("Popup blocked: invalid data");
                return;
            }

            const quiz = data.quiz;

            const container = document.getElementById('popup-container');
            const popup = document.createElement('div');
            popup.className = 'broadcast-popup';

            let html = `<h3>${quiz.quizTitle}</h3>`;
            if (quiz.quizDescription) {
                html += `<p>${quiz.quizDescription}</p>`;
            }

            if (quiz.questions?.length) {
                quiz.questions.forEach((q, i) => {
                    html += `
                        <div class="quiz-question">
                            <p><strong>Q${i + 1}:</strong> ${q.questionText}</p>
                            <ul style="padding-left:0; list-style:none;">
                    `;

                    q.options.forEach(opt => {
                        html += `
                            <li>
                                <label class="quiz-option-label">
                                    <input type="radio" name="q${i}" data-is-correct="${opt.isCorrect}">
                                    ${opt.optionText}
                                </label>
                            </li>
                        `;
                    });

                    html += `
                            </ul>
                            <p class="explanation" style="display:none; color:#fff; font-style:italic;"></p>
                        </div>`;
                });

                html += `
                    <button id="submitQuizBtn" style="
                        background:#03623c; color:#fff; border:none; padding:10px; border-radius:5px; margin-top:10px;">
                        Submit
                    </button>`;
            }

            popup.innerHTML = html;

            const closeBtn = document.createElement('button');
            closeBtn.className = 'close-btn';
            closeBtn.innerHTML = "&times;";
            closeBtn.onclick = () => popup.remove();
            popup.appendChild(closeBtn);

            container.appendChild(popup);

            setTimeout(() => popup.style.opacity = 1, 10);

            /* Quiz submission logic */
            const submitBtn = document.getElementById('submitQuizBtn');
            if (submitBtn) {
                submitBtn.onclick = () => {
                    let score = 0;

                    const questions = popup.querySelectorAll('.quiz-question');
                    questions.forEach((qDiv, idx) => {
                        const selected = qDiv.querySelector(`input[name="q${idx}"]:checked`);
                        const explanation = qDiv.querySelector('.explanation');

                        qDiv.querySelectorAll(`input[name="q${idx}"]`).forEach(inp => {
                            if (inp.dataset.isCorrect === "1") {
                                inp.parentElement.style.backgroundColor = "#c8e6c9";
                                if (selected === inp) score++;
                            } else if (selected && selected !== inp) {
                                inp.parentElement.style.backgroundColor = "#ffcdd2";
                            }
                        });

                        if (quiz.questions[idx].explanation) {
                            explanation.textContent = "Explanation: " + quiz.questions[idx].explanation;
                            explanation.style.display = "block";
                        }
                    });

                    const result = document.createElement("p");
                    result.textContent = `You scored ${score} out of ${quiz.questions.length}`;
                    result.style.marginTop = "10px";
                    popup.appendChild(result);

                    submitBtn.disabled = true;
                    submitBtn.textContent = "Submitted";
                };
            }
        }

        /* Bind event */
        channel.bind("QuizAssigned", function(data) {
            console.log("QuizAssigned event received:", data);
            setTimeout(() => showPopup(data), 300);
        });

    </script>
</body>

</html>
