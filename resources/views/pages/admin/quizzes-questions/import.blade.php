@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Question Management Card -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-primary">Question Management</h5>
                    </div>
                    <div class="card-body">

                        <!-- FILTERS & ACTIONS -->
                        <form id="filterForm" class="mb-4">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Course</label>
                                    <select name="course" class="form-select" id="courseSelect"
                                        onchange="updateSections('filter')">
                                        <option value="">Select Course</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Section</label>
                                    <select name="section" class="form-select" id="sectionSelect"
                                        onchange="updateLessons('filter')" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Lesson</label>
                                    <select name="lesson" class="form-select" id="lessonSelect" disabled>
                                        <option value="">Select Lesson</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3 g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Quiz</label>
                                    <select name="quiz_id" class="form-select" id="quizFilterSelect">
                                        <option value="">Select Quiz</option>
                                        @foreach ($quizzes as $quiz)
                                            <option value="{{ $quiz->id }}">{{ $quiz->quizTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Group</label>
                                    <select name="group" class="form-select" id="groupSelect" disabled>
                                        <option value="">Select Group</option>
                                    </select>
                                </div>

                                <div class="col-md-4 d-flex gap-2">
                                    <button type="button" class="btn btn-primary" onclick="filterQuestions()">
                                        <i class="bx bx-search"></i> Search
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                        <i class="bx bx-reset"></i> Reset
                                    </button>
                                </div>
                            </div>

                        </form>

                        <!-- Quiz Name Display -->
                        <div id="quizNameDisplay" class="mb-2" style="display:none;">
                            <small class="fw-semibold text-white px-2 py-1 rounded" style="background-color: #198754;">
                                Quiz Name: <span id="quizTitleSpan"></span>
                            </small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-2 mb-3" id="actionButtons" >
                            <button type="button" class="btn btn-success me-2" onclick="showActionModal('replicate')">
                                <i class="bx bx-copy"></i> Replicate
                            </button>
                            <button type="button" class="btn btn-primary" onclick="showActionModal('migrate')">
                                <i class="bx bx-move"></i> Migrate
                            </button>
                        </div>

                        <!-- Action Form -->
                        <div id="inlineActionForm" class="card mt-3" style="display: none;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span id="inlineActionTitle"></span>
                                <button type="button" class="btn-close" onclick="hideActionForm()"></button>
                            </div>
                            <div class="card-body">
                                <form id="actionForm">
                                    @csrf
                                    <input type="hidden" name="selectedQuestions" id="selectedQuestions">
                                    <input type="hidden" name="actionType" id="actionType">

                                    <div class="mb-3">
                                        <label class="form-label">Destination (Cascading)</label>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <select name="course" class="form-select" id="actionCourseSelect"
                                                    onchange="updateSections('action')">
                                                    <option value="">Select Course</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="section" class="form-select" id="actionSectionSelect"
                                                    onchange="updateLessons('action')" disabled>
                                                    <option value="">Select Section</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <select name="lesson" class="form-select" id="actionLessonSelect"
                                                    disabled>
                                                    <option value="">Select Lesson</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="text-center my-2"><strong>OR</strong></div>

                                        <label class="form-label">Destination Quiz</label>
                                        <select name="quiz_id" class="form-select" id="actionQuizSelect">
                                            <option value="">Select Quiz</option>
                                            @foreach ($quizzes as $quiz)
                                                <option value="{{ $quiz->id }}">{{ $quiz->quizTitle }}</option>
                                            @endforeach
                                        </select>
                                        <label class="form-label mt-3">Group (Optional)</label>
                                        <select name="group" class="form-select" id="actionGroupSelect" disabled>
                                            <option value="">Select Group</option>
                                        </select>

                                    </div>

                                    <div class="alert alert-info" id="actionSummary"></div>

                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2"
                                            onclick="hideActionForm()">Cancel</button>
                                        <button type="button" class="btn btn-primary"
                                            onclick="submitAction()">Confirm</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Questions Table -->
                        <div class="table-responsive mt-3" id="questionsContainer" style="display:none;">
                            <table class="table table-striped table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th width="50"><input type="checkbox" id="selectAll"></th>
                                        <th width="60">S.N.</th>
                                        <th>Question</th>
                                        <th>Explanation</th>
                                        <th>Type</th>
                                        <th>Course</th>
                                    </tr>
                                </thead>
                                <tbody id="questionsTable"></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
    // ===============================
    // QUIZ GROUP LOADING (FILTER)
    // ===============================
    document.getElementById('quizFilterSelect').addEventListener('change', async function() {
        const quizId = this.value;
        const groupSelect = document.getElementById('groupSelect');

        groupSelect.innerHTML = '<option value="">Select Group</option>';
        groupSelect.disabled = true;

        if (!quizId) return;

        try {
            const response = await fetch(`/admin/quizzes/${quizId}/groups`);
            const data = await response.json();

            if (data.groups && data.groups.length) {
                data.groups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group;
                    option.text = group;
                    groupSelect.appendChild(option);
                });
                groupSelect.disabled = false;
            }
        } catch (err) {
            console.error('Error loading groups:', err);
        }
    });

    // ===============================
    // QUIZ GROUP LOADING (ACTION FORM)
    // ===============================
    document.getElementById('actionQuizSelect').addEventListener('change', async function() {
        const quizId = this.value;
        const groupSelect = document.getElementById('actionGroupSelect');

        groupSelect.innerHTML = '<option value="">Select Group</option>';
        groupSelect.disabled = true;

        if (!quizId) return;

        try {
            const response = await fetch(`/admin/quizzes/${quizId}/groups`);
            const data = await response.json();

            if (data.groups && data.groups.length) {
                data.groups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group;
                    option.text = group;
                    groupSelect.appendChild(option);
                });
                groupSelect.disabled = false;
            }
        } catch (err) {
            console.error('Error loading action groups:', err);
        }
    });


    // ===========================================
    // GLOBAL VARIABLES
    // ===========================================
    let selectedQuestions = [];

    document.addEventListener('DOMContentLoaded', function() {
        loadCourses('filter');
        loadCourses('action');

        // Master checkbox for questions
        document.getElementById('selectAll').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.question-checkbox').forEach(cb => cb.checked = checked);
            updateSelectedQuestions();
        });
    });

    // ===============================
    // SELECTED QUESTIONS HANDLER
    // ===============================
    function updateSelectedQuestions() {
        selectedQuestions = Array.from(document.querySelectorAll('.question-checkbox:checked'))
            .map(cb => cb.value);

        document.getElementById('selectedQuestions').value = selectedQuestions.join(',');

        document.getElementById('actionButtons').style.display =
            selectedQuestions.length > 0 ? 'block' : 'none';
    }


    // ===============================
    // FILTER QUESTIONS
    // ===============================
    async function filterQuestions() {
        const formData = new FormData(document.getElementById('filterForm'));
        const filters = Object.fromEntries(formData);

        const tbody = document.getElementById('questionsTable');
        tbody.innerHTML =
            '<tr><td colspan="6" class="text-center"><div class="spinner-border"></div> Loading questions...</td></tr>';

        document.getElementById('questionsContainer').style.display = 'block';
        document.getElementById('actionButtons').style.display = 'none';

        try {
            const response = await fetch('{{ route('admin.quizzes-questions.filter') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(filters)
            });

            const data = await response.json();

            if (!data.success) throw new Error(data.message);

            displayQuestions(data.questions);
            updateQuizDisplay(filters.quiz_id);

        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${error.message}</td></tr>`;
        }
    }


    // ===============================
    // DISPLAY QUESTIONS IN TABLE
    // ===============================
    function displayQuestions(questions) {
        const tbody = document.getElementById('questionsTable');
        tbody.innerHTML = '';

        if (questions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No questions found.</td></tr>';
            document.getElementById('actionButtons').style.display = 'none';
            return;
        }

        questions.forEach((q, index) => {
            tbody.innerHTML += `
                <tr>
                    <td><input type="checkbox" class="question-checkbox" value="${q.id}" onchange="updateSelectedQuestions()"></td>
                    <td>${index + 1}</td>
                    <td>${q.questionText}</td>
                    <td>${q.explanation || '<em class="text-muted">No explanation</em>'}</td>
                    <td><span class="badge bg-secondary">${q.questionType}</span></td>
                    <td>${q.course || '-'}</td>
                </tr>
            `;
        });

        selectedQuestions = [];
        document.getElementById('selectedQuestions').value = '';
        document.getElementById('selectAll').checked = false;
    }


    // ===============================
    // UPDATE QUIZ TITLE DISPLAY
    // ===============================
    function updateQuizDisplay(quizId) {
        const quizDisplay = document.getElementById('quizNameDisplay');
        const quizTitleSpan = document.getElementById('quizTitleSpan');

        if (quizId) {
            const quizSelect = document.getElementById('quizFilterSelect');
            quizTitleSpan.textContent = quizSelect.options[quizSelect.selectedIndex].text;
            quizDisplay.style.display = 'block';
        } else {
            quizDisplay.style.display = 'none';
        }
    }


    // ===============================
    // RESET FILTERS
    // ===============================
    function resetFilters() {
        document.getElementById('filterForm').reset();
        document.getElementById('sectionSelect').disabled = true;
        document.getElementById('lessonSelect').disabled = true;
        document.getElementById('questionsContainer').style.display = 'none';
        document.getElementById('actionButtons').style.display = 'none';
        document.getElementById('quizNameDisplay').style.display = 'none';
        loadCourses('filter');
    }


    // ===============================
    // ACTION MODAL (REPLICATE / MIGRATE)
    // ===============================
    function showActionModal(type) {
        if (selectedQuestions.length === 0) {
            alert('Please select at least one question.');
            return;
        }

        document.getElementById('actionType').value = type;
        document.getElementById('inlineActionTitle').textContent =
            type === 'replicate' ? 'Replicate Questions' : 'Migrate Questions';

        document.getElementById('actionSummary').textContent =
            `You are about to ${type} ${selectedQuestions.length} question(s).`;

        document.getElementById('inlineActionForm').style.display = 'block';
    }

    function hideActionForm() {
        document.getElementById('inlineActionForm').style.display = 'none';
        document.getElementById('actionForm').reset();
        loadCourses('action');
    }


    // ===============================
    // SUBMIT ACTION (REPLICATE / MIGRATE)
    // ===============================
    async function submitAction() {
        const formData = new FormData(document.getElementById('actionForm'));
        const course = formData.get('course');
        const section = formData.get('section');
        const lesson = formData.get('lesson');
        const quizId = formData.get('quiz_id');

        if (!(course || section || lesson || quizId)) {
            alert('Select at least one destination: Quiz or Course/Section/Lesson.');
            return;
        }

        const url = formData.get('actionType') === 'replicate'
            ? '{{ route('admin.quizzes-questions.replicate') }}'
            : '{{ route('admin.quizzes-questions.migrate') }}';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                hideActionForm();
                filterQuestions();
            } else {
                alert('Error: ' + data.message);
            }

        } catch (err) {
            alert('Something went wrong: ' + err.message);
        }
    }


    // ===============================
    // CASCADING DROPDOWNS
    // ===============================
    async function loadCourses(type = 'filter') {
        const selectId = type === 'filter' ? 'courseSelect' : 'actionCourseSelect';
        const select = document.getElementById(selectId);

        select.innerHTML = '<option value="">Select Course</option>';

        try {
            const response = await fetch(`{{ route('admin.cascade.courses') }}`);
            const courses = await response.json();

            courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.id;
                option.text = course.courseName;
                select.appendChild(option);
            });

        } catch (err) {
            console.error('Error loading courses:', err);
        }
    }


    async function updateSections(type = 'filter') {
        const courseSelect = document.getElementById(type === 'filter' ? 'courseSelect' : 'actionCourseSelect');
        const sectionSelect = document.getElementById(type === 'filter' ? 'sectionSelect' : 'actionSectionSelect');

        sectionSelect.innerHTML = '<option value="">Select Section</option>';

        if (!courseSelect.value) {
            sectionSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`{{ url('/admin/cascade/sections') }}/${courseSelect.value}`);
            const sections = await response.json();

            sections.forEach(sec => {
                const option = document.createElement('option');
                option.value = sec.id;
                option.text = sec.sectionName;
                sectionSelect.appendChild(option);
            });

            sectionSelect.disabled = false;

        } catch (err) {
            console.error('Error loading sections:', err);
        }
    }


    async function updateLessons(type = 'filter') {
        const sectionSelect = document.getElementById(type === 'filter' ? 'sectionSelect' : 'actionSectionSelect');
        const lessonSelect = document.getElementById(type === 'filter' ? 'lessonSelect' : 'actionLessonSelect');

        lessonSelect.innerHTML = '<option value="">Select Lesson</option>';

        if (!sectionSelect.value) {
            lessonSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`{{ url('/admin/cascade/lessons') }}/${sectionSelect.value}`);
            const lessons = await response.json();

            lessons.forEach(lesson => {
                const option = document.createElement('option');
                option.value = lesson.id;
                option.text = lesson.lessonName;
                lessonSelect.appendChild(option);
            });

            lessonSelect.disabled = false;

        } catch (err) {
            console.error('Error loading lessons:', err);
        }
    }

</script>

@endsection
