@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-primary">Assign Activity to Meeting</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="assignmentForm">
                {{-- Meeting ID --}}
                <div class="mb-3">
                    <label for="meeting_id" class="form-label">Meeting ID</label>
                    <input type="text" name="meeting_id" id="meeting_id" class="form-control"
                        placeholder="Enter Meeting ID">
                </div>

                {{-- Activity Type --}}
                <div class="mb-3">
                    <label for="assignable_type" class="form-label">Activity Type</label>
                    <select name="assignable_type" id="assignable_type" class="form-select" required>
                        <option value="">Select Activity Type</option>
                        <option value="quiz">Quiz</option>
                        <option value="live">Live Set</option>
                    </select>
                </div>

                {{-- Quiz Selection --}}
                <div class="mb-3 position-relative" id="quizSelectContainer" style="display:none;">
                    <label for="quizSearch" class="form-label">Search and Select Quiz</label>
                    <input type="text" id="quizSearch" placeholder="Search Quiz..." class="form-control mb-2">
                    <ul id="quizResults" class="list-group position-absolute w-100"
                        style="z-index:1000; max-height:200px; overflow-y:auto; display:none;"></ul>
                    <input type="hidden" name="assignable_id" id="assignable_id">
                </div>

                {{-- Live Set Questions --}}
                <div id="liveSetContainer" style="display:none;">
                    <div id="questionsContainer"></div>
                    <button type="button" class="btn btn-outline-primary mt-3" id="addQuestionBtn">
                        <i class="bi bi-plus-circle"></i> Add Question
                    </button>
                </div>

                {{-- Assign Now Checkbox --}}
                <div class="form-check mb-3 mt-4" id="assignNowContainer" style="display:none;">
                    <input type="checkbox" class="form-check-input" id="assignNow" name="assignNow" checked>
                    <label class="form-check-label" for="assignNow">Assign to meeting immediately</label>
                </div>

                {{-- Submit --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-100">Assign Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #quizResults li:hover {
        background-color: #f1f1f1;
        cursor: pointer;
    }

    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 15px;
        background-color: #f8f9fa;
        transition: all 0.2s;
    }

    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .optionField {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 5px;
    }

    .optionField input[type="text"] {
        flex: 1;
    }
</style>

<script>
    const typeSelect = document.getElementById('assignable_type');
    const quizContainer = document.getElementById('quizSelectContainer');
    const liveContainer = document.getElementById('liveSetContainer');
    const quizSearch = document.getElementById('quizSearch');
    const quizResults = document.getElementById('quizResults');
    const assignableInput = document.getElementById('assignable_id');
    const questionsContainer = document.getElementById('questionsContainer');
    const addQuestionBtn = document.getElementById('addQuestionBtn');
    const assignNowContainer = document.getElementById('assignNowContainer');
    const assignNowCheckbox = document.getElementById('assignNow');
    const meetingInput = document.getElementById('meeting_id');

    let questionCount = 0;

    // Show/hide containers and auto-add first question
    typeSelect.addEventListener('change', function() {
        quizContainer.style.display = this.value === 'quiz' ? 'block' : 'none';
        liveContainer.style.display = this.value === 'live' ? 'block' : 'none';
        assignNowContainer.style.display = this.value === 'live' ? 'block' : 'none';
        questionsContainer.innerHTML = '';
        questionCount = 0;

        if (this.value === 'live') addQuestion();
        toggleMeetingRequirement();
    });

    function toggleMeetingRequirement() {
        if (typeSelect.value === 'live') {
            meetingInput.required = assignNowCheckbox.checked;
        } else if (typeSelect.value === 'quiz') {
            meetingInput.required = true;
        } else {
            meetingInput.required = false;
        }
    }

    assignNowCheckbox.addEventListener('change', toggleMeetingRequirement);
    toggleMeetingRequirement();

    // Quiz search autocomplete
    quizSearch.addEventListener('input', function() {
        const query = this.value.trim();
        if (!query) {
            quizResults.style.display = 'none';
            quizResults.innerHTML = '';
            assignableInput.value = '';
            return;
        }

        fetch("{{ route('admin.live.search') }}?query=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                quizResults.innerHTML = '';
                if (data.length) {
                    data.forEach(q => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action';
                        li.textContent = q.quizTitle;
                        li.dataset.id = q.id;
                        quizResults.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.className = 'list-group-item text-muted';
                    li.textContent = 'No results found';
                    quizResults.appendChild(li);
                }
                quizResults.style.display = 'block';
            }).catch(err => console.error(err));
    });

    quizResults.addEventListener('click', function(e) {
        if (e.target.tagName === 'LI' && e.target.dataset.id) {
            quizSearch.value = e.target.textContent;
            assignableInput.value = e.target.dataset.id;
            quizResults.style.display = 'none';
        }
    });

    document.addEventListener('click', e => {
        if (!quizSearch.contains(e.target) && !quizResults.contains(e.target)) {
            quizResults.style.display = 'none';
        }
    });

    // Add Question
    function addQuestion() {
        const qIndex = questionCount++;
        const qCard = document.createElement('div');
        qCard.className = 'question-card';
        qCard.innerHTML = `
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <strong>Question ${qIndex+1}</strong>
                <button type="button" class="btn btn-sm btn-danger removeQuestionBtn">Remove</button>
            </div>
            <select name="questions[${qIndex}][questionType]" class="form-select mb-2 questionType" data-index="${qIndex}" required>
                <option value="">Select Type</option>
                <option value="mcq">MCQ</option>
                <option value="true_false">True/False</option>
                <option value="subjective">Subjective</option>
            </select>
            <div class="questionDetails" data-index="${qIndex}" style="margin-left:10px"></div>
        `;
        questionsContainer.appendChild(qCard);
    }

    addQuestionBtn.addEventListener('click', addQuestion);

    // Question type change
    questionsContainer.addEventListener('change', function(e) {
        if (!e.target.classList.contains('questionType')) return;
        const type = e.target.value;
        const index = e.target.dataset.index;
        const container = questionsContainer.querySelector(`.questionDetails[data-index="${index}"]`);
        container.innerHTML = '';

        if (!type) return;

        // Question text input
        const qText = document.createElement('input');
        qText.type = 'text';
        qText.name = `questions[${index}][questionText]`;
        qText.placeholder = 'Enter question text';
        qText.className = 'form-control mb-2';
        qText.required = true;
        container.appendChild(qText);

        if (type === 'mcq' || type === 'true_false') {
            const options = type === 'mcq' ? [] : ['True', 'False'];
            options.forEach((optText, i) => {
                const div = document.createElement('div');
                div.className = 'optionField mb-1';
                div.innerHTML = `
                    <input type="text" name="questions[${index}][options][${i}][optionText]" class="form-control" value="${optText}" ${type==='true_false'?'readonly':''}>
                    <div class="form-check">
                        <input type="checkbox" name="questions[${index}][options][${i}][isCorrect]" class="form-check-input">
                        <label class="form-check-label">Correct</label>
                    </div>
                `;
                container.appendChild(div);
            });

            if (type === 'mcq') {
                const addBtn = document.createElement('button');
                addBtn.type = 'button';
                addBtn.className = 'btn btn-sm btn-outline-secondary addOptionBtn mt-1';
                addBtn.textContent = 'Add Option';
                container.appendChild(addBtn);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-outline-danger removeOptionBtn mt-1 ms-1';
                removeBtn.textContent = 'Remove Option';
                container.appendChild(removeBtn);
            }
        }
    });

    // MCQ option handling
    questionsContainer.addEventListener('click', function(e) {
        const parent = e.target.closest('.question-card');
        if (!parent) return;
        const details = parent.querySelector('.questionDetails');
        const index = details.dataset.index;

        if (e.target.classList.contains('addOptionBtn')) addMCQOption(details, index);
        if (e.target.classList.contains('removeOptionBtn')) {
            const options = details.querySelectorAll('.optionField');
            if (options.length > 1) options[options.length - 1].remove();
        }
        if (e.target.classList.contains('removeQuestionBtn')) {
            parent.remove();
            renumberQuestions();
        }
    });

    function addMCQOption(container, qIndex) {
        const optIndex = container.querySelectorAll('.optionField').length;
        const div = document.createElement('div');
        div.className = 'optionField mb-1';
        div.innerHTML = `
            <input type="text" name="questions[${qIndex}][options][${optIndex}][optionText]" class="form-control" placeholder="Option ${optIndex+1}" required>
            <div class="form-check">
                <input type="checkbox" name="questions[${qIndex}][options][${optIndex}][isCorrect]" class="form-check-input">
                <label class="form-check-label">Correct</label>
            </div>
        `;
        const addBtn = container.querySelector('.addOptionBtn');
        if (addBtn) container.insertBefore(div, addBtn);
    }

    function renumberQuestions() {
        const questionCards = questionsContainer.querySelectorAll('.question-card');
        questionCards.forEach((card, index) => {
            const title = card.querySelector('strong');
            title.textContent = `Question ${index + 1}`;
        });
    }

    // Form submission
    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Convert FormData to proper JSON structure
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            const keys = key.match(/[^\[\]]+/g);
            let ref = data;
            keys.forEach((k, i) => {
                if (i === keys.length - 1) {
                    // Checkbox handling: convert "on" to 1
                    if (value === 'on') ref[k] = 1;
                    else ref[k] = value;
                } else {
                    if (!ref[k]) ref[k] = {};
                    ref = ref[k];
                }
            });
        });

        // Include assignNow explicitly
        data.assignNow = assignNowCheckbox.checked ? 1 : 0;

        const type = typeSelect.value;
        let url = '';
        if (type === 'quiz') url = "{{ route('admin.live.assignToMeeting') }}";
        else if (type === 'live') url = "{{ route('admin.live.storeAndAssign') }}";
        else {
            alert('Please select an activity type.');
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert(res.message);
                this.reset();
                questionsContainer.innerHTML = '';
                quizContainer.style.display = 'none';
                liveContainer.style.display = 'none';
                assignNowContainer.style.display = 'none';
                toggleMeetingRequirement();
            } else {
                alert('Error: ' + (res.message || 'Something went wrong'));
            }
        })
        .catch(err => console.error(err));
    });
</script>
@endsection
