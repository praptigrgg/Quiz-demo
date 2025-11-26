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

        /* Hide Zoom UI you don’t want */
        #app-signal,
        .waiting-room-container .wr-default-bg,
        .waiting-room-container .wr-content-default,
        .page-footer,
        #participant,
        .more-button__item-box,
        .wr-header {
            display: none !important;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            background-color: rgba(255,255,255,0.8);
            padding: 5px 10px;
            border-radius: 5px;
            z-index: 10000;
            font-size: 14px;
            pointer-events: none;
        }

        /* Popup container */
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
            background: rgba(6,66,38,0.96);
            color: #fff;
            padding: 18px 22px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 24px rgba(0,0,0,0.55);
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

        .quiz-option-label {
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.08);
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 6px;
            cursor: pointer;
        }

        .quiz-option-label:hover { background: rgba(255,255,255,0.18); }
        .explanation { display: none; color: #d8ffd8; }

        #submitQuizBtn {
            background: #0a8a56;
            color: #fff;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        #submitQuizBtn:disabled {
            background: #1e4b36;
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media(max-width:600px){
            #popup-container{ right:10px; left:10px; width:auto; }
        }
    </style>
</head>

<body>
    <div id="zmmtg-root"></div>
    <div id="popup-container"></div>

    <!-- Zoom SDK -->
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

        /* WATERMARK --------------------------------------- */

        const watermark = document.createElement("div");
        watermark.className = "watermark";
        document.body.appendChild(watermark);

        let x = 50, y = 50, dx = 1.5, dy = 1.5;

        function updateWatermark(){
            watermark.textContent = userName + " | " + new Date().toLocaleString();
        }
        setInterval(updateWatermark,1000);

        function animateWatermark(){
            x += dx; y += dy;
            const maxX = window.innerWidth - watermark.offsetWidth;
            const maxY = window.innerHeight - watermark.offsetHeight;
            if(x<=0 || x>=maxX) dx = -dx;
            if(y<=0 || y>=maxY) dy = -dy;
            watermark.style.left = x+"px";
            watermark.style.top = y+"px";
            requestAnimationFrame(animateWatermark);
        }
        animateWatermark();

        /* ZOOM JOIN --------------------------------------- */

        ZoomMtg.preLoadWasm();
        ZoomMtg.prepareWebSDK();
        ZoomMtg.init({
            leaveUrl: "{{ url()->previous() }}",
            success: () => {
                ZoomMtg.join({
                    sdkKey, signature, meetingNumber,
                    passWord: meetingPassword, userName
                });
            }
        });

        /* PUSHER LISTENERS -------------------------------- */

        const pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            forceTLS: true
        });

        const channel = pusher.subscribe("zoom-meeting." + meetingNumber);

        /* QUEUE SYSTEM ------------------------------------ */

        let popupQueue = [];
        let popupActive = false;

        function showPopupQueued(data){
            popupQueue.push(data);
            if(!popupActive) processQueue();
        }

        function processQueue(){
            if(popupQueue.length === 0){
                popupActive = false;
                return;
            }
            popupActive = true;
            const next = popupQueue.shift();
            showPopup(next,()=>{
                popupActive = false;
                processQueue();
            });
        }

        /* POPUP + QUIZ ------------------------------------ */

        function showPopup(data, onClose = ()=>{}){
            const quiz = data.quiz;
            const container = document.getElementById("popup-container");

            const popup = document.createElement("div");
            popup.className = "broadcast-popup";

            /* TIMER BAR */
            let timeLeft = quiz.timer ?? 30;

            const timerBar = document.createElement("div");
            timerBar.style.cssText="width:100%;background:rgba(255,255,255,0.2);height:12px;border-radius:6px;margin-bottom:15px;";
            const timerFill = document.createElement("div");
            timerFill.style.cssText="width:100%;height:100%;background:#ff9800;transition:width 1s linear;";
            timerBar.appendChild(timerFill);

            let html = `<h3>${quiz.quizTitle}</h3>`;
            if(quiz.quizDescription) html+=`<p>${quiz.quizDescription}</p>`;

            html+=``;
            quiz.questions.forEach((q,i)=>{
                html += `
                <div class="quiz-question">
                    <p><strong>Q${i+1}:</strong> ${q.questionText}</p>
                    <ul style="list-style:none;padding-left:0;">`;
                q.options.forEach(opt=>{
                    html+=`
                        <li>
                            <label class="quiz-option-label">
                                <input type="radio" name="q${i}" data-is-correct="${opt.isCorrect}">
                                ${opt.optionText}
                            </label>
                        </li>`;
                });
                html+=`</ul>
                    <p class="explanation"></p>
                </div>`;
            });

            html += `<button id="submitQuizBtn" disabled>Submit</button>`;
            popup.innerHTML = html;
            popup.prepend(timerBar);
            container.appendChild(popup);
            setTimeout(()=> popup.classList.add("show"),20);

            const submitBtn = popup.querySelector("#submitQuizBtn");

            /* REQUIRE ANSWERING ALL QUESTIONS */
            popup.querySelectorAll(".quiz-question").forEach((qDiv,idx)=>{
                qDiv.addEventListener("change", ()=>{
                    const allAnswered = [...popup.querySelectorAll(".quiz-question")].every((q,i)=>
                        q.querySelector(`input[name="q${i}"]:checked`)
                    );
                    submitBtn.disabled = !allAnswered;
                });
            });

            /* TIMER COUNTDOWN */
            const countdown = setInterval(()=>{
                timeLeft--;
                timerFill.style.width = (timeLeft / (quiz.timer ?? 30))*100 + "%";
                if(timeLeft <= 0){
                    clearInterval(countdown);
                    submitBtn.click();
                }
            },1000);

            /* SUBMIT QUIZ */
            submitBtn.onclick = ()=>{
                clearInterval(countdown);

                let score = 0;
                popup.querySelectorAll(".quiz-question").forEach((qDiv,idx)=>{
                    const selected = qDiv.querySelector(`input[name="q${idx}"]:checked`);
                    const explanation = qDiv.querySelector(".explanation");

                    qDiv.querySelectorAll(`input[name="q${idx}"]`).forEach(inp=>{
                        const isCorrect = inp.dataset.isCorrect === "1";
                        if(isCorrect){
                            inp.parentElement.style.background="#c8e6c9";
                            if(selected === inp) score++;
                        } else if(selected === inp){
                            inp.parentElement.style.background="#ffcdd2";
                        }
                    });

                    if(quiz.questions[idx].explanation){
                        explanation.textContent = "Explanation: " + quiz.questions[idx].explanation;
                        explanation.style.display="block";
                    }
                });

                const result = document.createElement("p");
                result.style.marginTop = "10px";
                result.style.fontWeight = "bold";
                result.textContent = `You scored ${score} / ${quiz.questions.length}`;
                popup.appendChild(result);

                submitBtn.disabled = true;
                submitBtn.textContent = "Submitted";

                setTimeout(()=>{
                    popup.remove();
                    onClose();
                }, 2500);
            };
        }

        /* EVENT LISTENER ---------------------------------- */
        channel.bind("QuizAssigned", data => showPopupQueued(data));
    </script>
</body>
</html>
