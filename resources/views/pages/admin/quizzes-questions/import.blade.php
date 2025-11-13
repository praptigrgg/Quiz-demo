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
                                <div class="col-md-3">
                                    <label class="form-label">Course</label>
                                    <select name="course" class="form-select" id="courseSelect"
                                        onchange="updateSections('filter')">
                                        <option value="">Select Course</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Section</label>
                                    <select name="section" class="form-select" id="sectionSelect"
                                        onchange="updateLessons('filter')" disabled>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Lesson</label>
                                    <select name="lesson" class="form-select" id="lessonSelect"
                                        onchange="updateGroups('filter')" disabled>
                                        <option value="">Select Lesson</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Group</label>
                                    <select name="group_name" class="form-select" id="groupSelect" disabled>
                                        <option value="">Select Group</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3 g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Quiz</label>
                                    <select name="quiz_id" class="form-select" id="quizFilterSelect">
                                        <option value="">Select Quiz</option>
                                        @foreach ($quizzes as $quiz)
                                            <option value="{{ $quiz->id }}">{{ $quiz->quizTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
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
                        <div class="mt-2 mb-3" id="actionButtons" style="display:none;">
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
                                            <div class="col-md-3">
                                                <select name="course" class="form-select" id="actionCourseSelect"
                                                    onchange="updateSections('action')">
                                                    <option value="">Select Course</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="section" class="form-select" id="actionSectionSelect"
                                                    onchange="updateLessons('action')" disabled>
                                                    <option value="">Select Section</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="lesson" class="form-select" id="actionLessonSelect"
                                                    onchange="updateGroups('action')" disabled>
                                                    <option value="">Select Lesson</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="group_name" class="form-select" id="actionGroupSelect"
                                                    disabled>
                                                    <option value="">Select Group</option>
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
                                        <th>Group</th>
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
        let selectedQuestions = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadCourses('filter');
            loadCourses('action');

            document.getElementById('selectAll').addEventListener('change', function() {
                const checked = this.checked;
                document.querySelectorAll('.question-checkbox').forEach(cb => cb.checked = checked);
                updateSelectedQuestions();
            });

            setupMutualExclusion();
        });

        function updateSelectedQuestions() {
            selectedQuestions = Array.from(document.querySelectorAll('.question-checkbox:checked')).map(cb => cb.value);
            document.getElementById('selectedQuestions').value = selectedQuestions.join(',');
            document.getElementById('actionButtons').style.display = selectedQuestions.length > 0 ? 'block' : 'none';
        }

        async function filterQuestions() {
            const formData = new FormData(document.getElementById('filterForm'));
            const filters = Object.fromEntries(formData);
            const tbody = document.getElementById('questionsTable');
            tbody.innerHTML =
                '<tr><td colspan="7" class="text-center"><div class="spinner-border"></div> Loading questions...</td></tr>';
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
                if (!data.success) throw new Error(data.message || 'Filter failed');
                displayQuestions(data.questions);
                updateQuizDisplay(filters.quiz_id);
            } catch (error) {
                console.error('Filter error:', error);
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading questions: ' +
                    error.message + '</td></tr>';
                document.getElementById('actionButtons').style.display = 'none';
            }
        }

        function displayQuestions(questions) {
            const tbody = document.getElementById('questionsTable');
            tbody.innerHTML = '';
            if (questions.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="7" class="text-center">No questions found matching your criteria.</td></tr>';
                document.getElementById('actionButtons').style.display = 'none';
            } else {
                questions.forEach((q, index) => {
                    tbody.innerHTML += `
                    <tr>
                        <td><input type="checkbox" class="question-checkbox" value="${q.id}" onchange="updateSelectedQuestions()"></td>
                        <td>${index + 1}</td>
                        <td>${q.questionText}</td>
                        <td>${q.explanation || '<em class="text-muted">No explanation</em>'}</td>
                        <td><span class="badge bg-secondary">${q.questionType}</span></td>
                        <td>${q.group_name || '-'}</td>
                        <td>${q.course || '-'}</td>
                    </tr>
                `;
                });
            }
            selectedQuestions = [];
            document.getElementById('selectedQuestions').value = '';
            document.getElementById('selectAll').checked = false;
        }

        function updateQuizDisplay(quizId) {
            const quizDisplay = document.getElementById('quizNameDisplay');
            const quizTitleSpan = document.getElementById('quizTitleSpan');
            if (quizId) {
                const quizSelect = document.getElementById('quizFilterSelect');
                const selectedOption = quizSelect.options[quizSelect.selectedIndex];
                quizTitleSpan.textContent = selectedOption.text;
                quizDisplay.style.display = 'block';
            } else {
                quizDisplay.style.display = 'none';
            }
        }

        function resetFilters() {
            document.getElementById('filterForm').reset();
            document.getElementById('sectionSelect').disabled = true;
            document.getElementById('lessonSelect').disabled = true;
            document.getElementById('groupSelect').disabled = true;
            document.getElementById('questionsContainer').style.display = 'none';
            document.getElementById('actionButtons').style.display = 'none';
            document.getElementById('quizNameDisplay').style.display = 'none';
            loadCourses('filter');
        }

        function showActionModal(type) {
            if (selectedQuestions.length === 0) {
                alert('Please select at least one question');
                return;
            }
            document.getElementById('actionType').value = type;
            document.getElementById('inlineActionTitle').textContent = type === 'replicate' ? 'Replicate Questions' :
                'Migrate Questions';
            document.getElementById('actionSummary').textContent =
                `You are about to ${type} ${selectedQuestions.length} question(s)`;
            document.getElementById('inlineActionForm').style.display = 'block';
        }

        function hideActionForm() {
            document.getElementById('inlineActionForm').style.display = 'none';
            document.getElementById('actionForm').reset();
            loadCourses('action');
        }

        async function submitAction() {
            const formData = new FormData(document.getElementById('actionForm'));
            const actionType = formData.get('actionType');

            const hasCascading = formData.get('course') && formData.get('section') && formData.get('lesson') && formData
                .get('group_name');
            const hasQuiz = formData.get('quiz_id');

            if (!hasCascading && !hasQuiz) {
                alert('Please select either cascading destination or quiz');
                return;
            }

            const url = actionType === 'replicate' ?
                '{{ route('admin.quizzes-questions.replicate') }}' :
                '{{ route('admin.quizzes-questions.migrate') }}';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    hideActionForm();
                    filterQuestions();
                } else {
                    alert('Error: ' + (data.message || 'Something went wrong'));
                }
            } catch (error) {
                console.error('Action error:', error);
                alert('Something went wrong: ' + error.message);
            }
        }

        function setupMutualExclusion() {
            const actionCourse = document.getElementById('actionCourseSelect');
            const actionSection = document.getElementById('actionSectionSelect');
            const actionLesson = document.getElementById('actionLessonSelect');
            const actionGroup = document.getElementById('actionGroupSelect');
            const actionQuiz = document.getElementById('actionQuizSelect');

            [actionCourse, actionSection, actionLesson, actionGroup].forEach(el => {
                el?.addEventListener('change', () => {
                    if (actionCourse.value || actionSection.value || actionLesson.value || actionGroup
                        .value) {
                        actionQuiz.value = '';
                        actionQuiz.disabled = true;
                    } else {
                        actionQuiz.disabled = false;
                    }
                });
            });

            actionQuiz?.addEventListener('change', () => {
                if (actionQuiz.value) {
                    actionCourse.value = '';
                    actionSection.value = '';
                    actionLesson.value = '';
                    actionGroup.value = '';
                    actionSection.disabled = true;
                    actionLesson.disabled = true;
                    actionGroup.disabled = true;
                    actionCourse.dispatchEvent(new Event('change'));
                } else {
                    actionSection.disabled = !actionCourse.value;
                    actionLesson.disabled = !actionSection.value;
                    actionGroup.disabled = !actionLesson.value;
                }
            });
        }

        // ===== Cascading Dropdown Functions =====
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

        async function updateGroups(type = 'filter') {
            const lessonSelect = document.getElementById(type === 'filter' ? 'lessonSelect' : 'actionLessonSelect');
            const groupSelect = document.getElementById(type === 'filter' ? 'groupSelect' : 'actionGroupSelect');

            groupSelect.innerHTML = '<option value="">Select Group</option>';
            if (!lessonSelect.value) {
                groupSelect.disabled = true;
                return;
            }

            try {
                const response = await fetch(`{{ url('/admin/cascade/groups') }}/${lessonSelect.value}`);
                const groups = await response.json();
                groups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.name;
                    option.text = group.name;
                    groupSelect.appendChild(option);
                });
                groupSelect.disabled = false;
            } catch (err) {
                console.error('Error loading groups:', err);
            }
        }
    </script>
@endsection
