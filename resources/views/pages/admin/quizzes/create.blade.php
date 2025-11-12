@extends('layouts.app')

@section('title', 'Create New Quiz')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-1">Quiz Management</h4>
        <p class="mb-4">Create new quizzes with questions and answers</p>

        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card p-3">
                    <div class="d-flex card-header align-items-center justify-content-between">
                        <div class="col-md-6">
                            <h4 class="card-title text-primary">Create New Quiz</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="bx bx-reset"></i> Reset
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Validation --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        {{-- Quiz Form --}}
                        <form action="{{ route('admin.quizzes.store') }}" method="POST" id="quizForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                {{-- LEFT SIDE --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quizTitle" class="form-label">Quiz Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="quizTitle" name="quizTitle" required value="{{ old('quizTitle') }}" placeholder="Enter quiz title">
                                    </div>

                                    {{-- Pricing Type --}}
                                    <div class="mb-3">
                                        <label class="form-label">Pricing Type <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pricingType" id="pricing_free" value="free" {{ old('pricingType', 'free') == 'free' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pricing_free">Free</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="pricingType" id="pricing_paid" value="paid" {{ old('pricingType') == 'paid' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pricing_paid">Paid</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Valid Days --}}
                                    <div id="validDaysField" class="mb-3" style="display:none;">
                                        <label for="valid_days_after_enrollment" class="form-label">Valid Days After Enrollment</label>
                                        <input type="number" class="form-control" name="valid_days_after_enrollment" id="valid_days_after_enrollment" min="1" value="{{ old('valid_days_after_enrollment') }}">
                                    </div>

                                    {{-- Pricing Fields --}}
                                    <div id="pricingFields" style="display:none;">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="normal_price" class="form-label">Normal Price</label>
                                                <input type="number" class="form-control" name="normal_price" id="normal_price" step="0.01" value="{{ old('normal_price') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="discount_price" class="form-label">Discount Price</label>
                                                <input type="number" class="form-control" name="discount_price" id="discount_price" step="0.01" value="{{ old('discount_price') }}">
                                                <div class="invalid-feedback" id="discountPriceError" style="display:none;">
                                                    Discount price cannot exceed normal price.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="discount_start_date" class="form-label">Discount Start Date</label>
                                                <input type="date" class="form-control" name="discount_start_date" id="discount_start_date" min="{{ date('Y-m-d') }}" value="{{ old('discount_start_date') }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="discount_end_date" class="form-label">Discount End Date</label>
                                                <input type="date" class="form-control" name="discount_end_date" id="discount_end_date" min="{{ date('Y-m-d') }}" value="{{ old('discount_end_date') }}">
                                                <div class="invalid-feedback" id="discountEndDateError" style="display:none;">
                                                    Discount end date cannot be earlier than start date.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Thumbnail --}}
                                    <div class="mb-3">
                                        <label for="quizThumbnail" class="form-label">Quiz Thumbnail <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="quizThumbnail" name="quizThumbnail" accept="image/*">
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-3">
                                        <label for="quizDescription" class="form-label">Quiz Description</label>
                                        <textarea id="quizDescription" name="quizDescription" class="form-control">{{ old('quizDescription') }}</textarea>
                                    </div>
                                </div>

                                {{-- RIGHT SIDE --}}
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="quizDuration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quizDuration" name="quizDuration" min="1" value="{{ old('quizDuration', 30) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quizTotalMarks" class="form-label">Total Marks <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quizTotalMarks" name="quizTotalMarks" min="1" value="{{ old('quizTotalMarks', 100) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="quizPassMark" class="form-label">Passing Marks <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quizPassMark" name="quizPassMark" min="1" value="{{ old('quizPassMark', 40) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="correctAnswerMarks" class="form-label">Marks per Correct Answer <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="correctAnswerMarks" name="correctAnswerMarks" min="1" value="{{ old('correctAnswerMarks', 1) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="negativeMarking" class="form-label">Negative Marking (%)</label>
                                        <input type="number" class="form-control" id="negativeMarking" name="negativeMarking" min="0" max="100" value="{{ old('negativeMarking', 0) }}">
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_one_time" name="is_one_time" value="1" {{ old('is_one_time') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_one_time">Is One Time Quiz</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_live_quiz" name="is_live_quiz" value="1" {{ old('is_live_quiz') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_live_quiz">Is Live Quiz</label>
                                    </div>

                                    <div id="live_time_fields" style="display:none;">
                                        <div class="mb-3">
                                            <label for="live_start_time" class="form-label">Live Start Time</label>
                                            <input type="datetime-local" class="form-control" id="live_start_time" name="live_start_time" value="{{ old('live_start_time') }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="live_end_time" class="form-label">Live End Time</label>
                                            <input type="datetime-local" class="form-control" id="live_end_time" name="live_end_time" value="{{ old('live_end_time') }}">
                                            <div class="invalid-feedback" id="end_time_error" style="display:none;">End time must be later than start time.</div>
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="is_quiz_group" name="is_quiz_group" value="1" {{ old('is_quiz_group') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_quiz_group">Enable Quiz Groups</label>
                                    </div>

                                    {{-- Simplified Group Fields --}}
                                    <div id="quiz_group_fields" style="display:none;">
                                        <div id="group_inputs_container" class="d-flex flex-column gap-2 mb-1"></div>
                                        <div id="group_warning" class="text-warning small" style="display:none;">At least one group is required when group is enabled.</div>
                                    </div>

                                    <div class="form-check mb-3 mt-3">
                                        <input type="checkbox" class="form-check-input" id="enableReview" name="enableReview" value="1" {{ old('enableReview') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enableReview">Enable Review</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="showInListQuiz" name="showInListQuiz" value="1" {{ old('showInListQuiz') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showInListQuiz">Add to Quiz List</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="publishStatus" name="is_publish" value="1" {{ old('is_publish') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publishStatus">Publish Immediately</label>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Quiz</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dependencies --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pricing fields toggle
    const pricingRadios = document.querySelectorAll('input[name="pricingType"]');
    const pricingFields = document.getElementById('pricingFields');
    const validDaysField = document.getElementById('validDaysField');

    function togglePricingFields() {
        const selected = document.querySelector('input[name="pricingType"]:checked').value;
        pricingFields.style.display = selected === 'paid' ? 'block' : 'none';
        validDaysField.style.display = 'block';
        document.querySelectorAll('#pricingFields input').forEach(i => {
            i.disabled = selected !== 'paid';
        });
    }
    pricingRadios.forEach(r => r.addEventListener('change', togglePricingFields));
    togglePricingFields();

    // Summernote init
    $('#quizDescription').summernote({
        placeholder: 'Enter quiz description...',
        height: 200,
        tabsize: 2
    });

    // Live quiz toggle
    const liveQuiz = document.getElementById('is_live_quiz');
    const liveFields = document.getElementById('live_time_fields');
    function toggleLiveFields() {
        liveFields.style.display = liveQuiz.checked ? 'block' : 'none';
    }
    liveQuiz.addEventListener('change', toggleLiveFields);
    toggleLiveFields();

    // Quiz group section
    const quizGroupCheckbox = document.getElementById('is_quiz_group');
    const quizGroupFields = document.getElementById('quiz_group_fields');
    const groupContainer = document.getElementById('group_inputs_container');
    const warningText = document.getElementById('group_warning');

    function showWarning(show) {
        warningText.style.display = show ? 'block' : 'none';
    }

    function createGroupInput(value = '') {
        const wrapper = document.createElement('div');
        wrapper.classList.add('d-flex', 'align-items-center', 'gap-2');

        wrapper.innerHTML = `
            <input type="text" name="quiz_groups[]" class="form-control" placeholder="Enter group name" value="${value}" required>
            <button type="button" class="btn btn-outline-primary btn-sm add-group"><i class="bx bx-plus"></i></button>
            <button type="button" class="btn btn-danger btn-sm remove-group"><i class="bx bx-trash"></i></button>
        `;

        const addBtn = wrapper.querySelector('.add-group');
        const removeBtn = wrapper.querySelector('.remove-group');

        addBtn.addEventListener('click', () => {
            groupContainer.appendChild(createGroupInput());
            showWarning(false);
        });

        removeBtn.addEventListener('click', () => {
            if (groupContainer.children.length > 1) {
                wrapper.remove();
                showWarning(false);
            } else {
                showWarning(true);
            }
        });

        return wrapper;
    }

    quizGroupCheckbox.addEventListener('change', () => {
        quizGroupFields.style.display = quizGroupCheckbox.checked ? 'block' : 'none';
        if (quizGroupCheckbox.checked && groupContainer.children.length === 0) {
            groupContainer.appendChild(createGroupInput());
        }
    });

    if (quizGroupCheckbox.checked) {
        quizGroupFields.style.display = 'block';
        if (groupContainer.children.length === 0) groupContainer.appendChild(createGroupInput());
    }
});
</script>
@endsection
