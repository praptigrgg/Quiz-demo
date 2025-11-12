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
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Course</label>
                                <select name="course" class="form-select" id="courseSelect" onchange="updateSections()">
                                    <option value="">Select Course</option>
                                    @foreach (config('hierarchy.courses') as $courseKey => $course)
                                        <option value="{{ $courseKey }}">{{ $course['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Section</label>
                                <select name="section" class="form-select" id="sectionSelect" onchange="updateLessons()" disabled>
                                    <option value="">Select Section</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Lesson</label>
                                <select name="lesson" class="form-select" id="lessonSelect" onchange="updateGroups()" disabled>
                                    <option value="">Select Lesson</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Group</label>
                                <select name="group" class="form-select" id="groupSelect" disabled>
                                    <option value="">Select Group</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <!-- Quiz Name -->
                    @if (isset($quiz))
                    <div class="mb-2">
                        <small class="fw-semibold text-white px-2 py-1 rounded" style="background-color: #198754;">
                            Quiz Name: {{ $quiz->quizTitle }}
                        </small>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="mt-3 mb-3" id="actionButtons">
                        <button type="button" class="btn btn-success me-2" onclick="showActionModal('replicate')">
                            <i class="bx bx-copy"></i> Replicate
                        </button>
                        <button type="button" class="btn btn-primary" onclick="showActionModal('migrate')">
                            <i class="bx bx-move"></i> Migrate
                        </button>
                    </div>

                    <!-- QUESTIONS TABLE -->
                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAll"></th>
                                    <th width="60">S.N.</th>
                                    <th>Question</th>
                                    <th>Explanation</th>
                                    <th>Type</th>
                                    <th>Group</th>
                                    <th>Source</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="questionsTable">
                                @forelse($questions as $index => $question)
                                <tr>
                                    <td><input type="checkbox" class="question-checkbox" value="{{ $question->id }}" onchange="updateSelectedQuestions()"></td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $question->questionText }}</td>
                                    <td>{!! $question->explanation ?? '<em>No explanation</em>' !!}</td>
                                    <td>{{ $question->questionType }}</td>
                                    <td>{{ $question->group_name ?? '-' }}</td>
                                    <td>{{ $question->source_type ?? 'original' }}</td>
                                    <td>
                                        <a href="{{ route('admin.quizzes-questions.edit', $question->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <form action="{{ route('admin.quizzes-questions.destroy', $question->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No questions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Inline Action Form -->
                    <div id="inlineActionForm" class="card mt-3" style="display: none;">
                        <div class="card-header" id="inlineActionTitle"></div>
                        <div class="card-body">
                            <form id="actionForm">
                                @csrf
                                <input type="hidden" name="selectedQuestions" id="selectedQuestions">
                                <input type="hidden" name="actionType" id="actionType">

                                <div class="mb-3">
                                    <label class="form-label">Destination (Cascading)</label>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3">
                                            <select name="course" class="form-select" id="actionCourseSelect" onchange="updateActionSections()">
                                                <option value="">Select Course</option>
                                                @foreach (config('hierarchy.courses') as $courseKey => $course)
                                                    <option value="{{ $courseKey }}">{{ $course['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="section" class="form-select" id="actionSectionSelect" onchange="updateActionLessons()" disabled>
                                                <option value="">Select Section</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="lesson" class="form-select" id="actionLessonSelect" onchange="updateActionGroups()" disabled>
                                                <option value="">Select Lesson</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="group" class="form-select" id="actionGroupSelect" disabled>
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

                                <button type="button" class="btn btn-secondary me-2" id="cancelActionBtn">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="submitAction()">Confirm</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
const hierarchy = @json(config('hierarchy.courses'));
let selectedQuestions = [];

// ----- Cascading Dropdowns (Filter) -----
function updateSections() {
    const course = document.getElementById('courseSelect').value;
    const sectionSelect = document.getElementById('sectionSelect');
    const lessonSelect = document.getElementById('lessonSelect');
    const groupSelect = document.getElementById('groupSelect');

    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    lessonSelect.innerHTML = '<option value="">Select Lesson</option>';
    groupSelect.innerHTML = '<option value="">Select Group</option>';

    if(course && hierarchy[course]){
        sectionSelect.disabled = false;
        Object.entries(hierarchy[course]['sections']).forEach(([key, val])=>{
            sectionSelect.innerHTML += `<option value="${key}">${val}</option>`;
        });
    } else {
        sectionSelect.disabled = true;
        lessonSelect.disabled = true;
        groupSelect.disabled = true;
    }
}

function updateLessons() {
    const course = document.getElementById('courseSelect').value;
    const section = document.getElementById('sectionSelect').value;
    const lessonSelect = document.getElementById('lessonSelect');
    const groupSelect = document.getElementById('groupSelect');

    lessonSelect.innerHTML = '<option value="">Select Lesson</option>';
    groupSelect.innerHTML = '<option value="">Select Group</option>';

    if(course && section && hierarchy[course]?.lessons?.[section]){
        lessonSelect.disabled = false;
        Object.entries(hierarchy[course]['lessons'][section]).forEach(([key, val])=>{
            lessonSelect.innerHTML += `<option value="${key}">${val}</option>`;
        });
    } else {
        lessonSelect.disabled = true;
        groupSelect.disabled = true;
    }
}

function updateGroups() {
    const groupSelect = document.getElementById('groupSelect');
    groupSelect.disabled = false;
    groupSelect.innerHTML = '<option value="">Select Group</option>';
    ['Group A', 'Group B', 'Group C'].forEach(g=>{
        groupSelect.innerHTML += `<option value="${g}">${g}</option>`;
    });
}

// ----- Cascading Dropdowns (Action Form) -----
function updateActionSections(){
    const course = document.getElementById('actionCourseSelect').value;
    const sectionSelect = document.getElementById('actionSectionSelect');
    const lessonSelect = document.getElementById('actionLessonSelect');
    const groupSelect = document.getElementById('actionGroupSelect');

    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    lessonSelect.innerHTML = '<option value="">Select Lesson</option>';
    groupSelect.innerHTML = '<option value="">Select Group</option>';

    if(course && hierarchy[course]){
        sectionSelect.disabled = false;
        Object.entries(hierarchy[course]['sections']).forEach(([key,val])=>{
            sectionSelect.innerHTML += `<option value="${key}">${val}</option>`;
        });
    } else {
        sectionSelect.disabled = true;
        lessonSelect.disabled = true;
        groupSelect.disabled = true;
    }
}

function updateActionLessons(){
    const course = document.getElementById('actionCourseSelect').value;
    const section = document.getElementById('actionSectionSelect').value;
    const lessonSelect = document.getElementById('actionLessonSelect');
    const groupSelect = document.getElementById('actionGroupSelect');

    lessonSelect.innerHTML = '<option value="">Select Lesson</option>';
    groupSelect.innerHTML = '<option value="">Select Group</option>';

    if(course && section && hierarchy[course]?.lessons?.[section]){
        lessonSelect.disabled = false;
        Object.entries(hierarchy[course]['lessons'][section]).forEach(([key,val])=>{
            lessonSelect.innerHTML += `<option value="${key}">${val}</option>`;
        });
    } else {
        lessonSelect.disabled = true;
        groupSelect.disabled = true;
    }
}

function updateActionGroups(){
    const groupSelect = document.getElementById('actionGroupSelect');
    groupSelect.disabled = false;
    groupSelect.innerHTML = '<option value="">Select Group</option>';
    ['Group A','Group B','Group C'].forEach(g=>{
        groupSelect.innerHTML += `<option value="${g}">${g}</option>`;
    });
}

// ----- Question Selection -----
document.getElementById('selectAll').addEventListener('change', function(){
    const checked = this.checked;
    document.querySelectorAll('.question-checkbox').forEach(cb => cb.checked = checked);
    updateSelectedQuestions();
});

function updateSelectedQuestions(){
    selectedQuestions = Array.from(document.querySelectorAll('.question-checkbox:checked')).map(cb=>cb.value);
    document.getElementById('selectedQuestions').value = selectedQuestions.join(',');
}

// ----- Show Action Modal -----
function showActionModal(type){
    if(selectedQuestions.length===0) return alert('Select at least one question');
    document.getElementById('actionType').value = type;
    document.getElementById('inlineActionTitle').textContent = type==='replicate' ? 'Replicate Questions' : 'Migrate Questions';
    document.getElementById('actionSummary').textContent = `You are about to ${type} ${selectedQuestions.length} question(s)`;
    document.getElementById('inlineActionForm').style.display = 'block';
}

// Cancel
document.getElementById('cancelActionBtn').addEventListener('click',()=>{
    document.getElementById('inlineActionForm').style.display='none';
});

// ----- Submit Action -----
function submitAction(){
    const formData = new FormData(document.getElementById('actionForm'));

    const hasCascading = formData.get('course') && formData.get('section') && formData.get('lesson') && formData.get('group');
    const hasQuiz = formData.get('quiz_id');

    if(!hasCascading && !hasQuiz) return alert('Select either cascading destination or quiz');

    const url = formData.get('actionType')==='replicate' ?
        '{{ route('admin.quizzes-questions.replicate') }}' :
        '{{ route('admin.quizzes-questions.migrate') }}';

    fetch(url,{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Accept':'application/json'
        },
        body: formData
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            alert(data.message);
            document.getElementById('inlineActionForm').style.display='none';
            location.reload();
        } else alert('Error: '+(data.message||'Something went wrong'));
    })
    .catch(err=>{
        console.error(err);
        alert('Something went wrong');
    });
}

// ----- Mutually Exclusive Destinations -----
const actionCourse = document.getElementById('actionCourseSelect');
const actionSection = document.getElementById('actionSectionSelect');
const actionLesson = document.getElementById('actionLessonSelect');
const actionGroup = document.getElementById('actionGroupSelect');
const actionQuiz = document.getElementById('actionQuizSelect');

function disableQuizIfCascadingSelected(){
    if(actionCourse.value || actionSection.value || actionLesson.value || actionGroup.value){
        actionQuiz.value='';
        actionQuiz.disabled=true;
    } else actionQuiz.disabled=false;
}
function disableCascadingIfQuizSelected(){
    if(actionQuiz.value){
        actionCourse.value='';
        actionSection.value='';
        actionLesson.value='';
        actionGroup.value='';
        actionSection.disabled=true;
        actionLesson.disabled=true;
        actionGroup.disabled=true;
    }
}
[actionCourse,actionSection,actionLesson,actionGroup].forEach(el=>el.addEventListener('change',disableQuizIfCascadingSelected));
actionQuiz.addEventListener('change',disableCascadingIfQuizSelected);
</script>
@endsection
