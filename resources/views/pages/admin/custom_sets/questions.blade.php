@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            {{-- Question Form --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="text-primary">Create New Custom Set Question</h5>
                    <div>
                        <a href="{{ route('admin.custom_sets.index') }}" class="btn btn-outline-secondary"><i
                                class="bx bx-reset"></i></a>
                        <a href="{{ route('admin.custom_sets.questions.import', $customSet->id) }}"
                            class="btn btn-outline-primary">
                            Import Questions
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.custom_sets.questions.store', $customSet->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="custom_set_id" value="{{ $customSet->id }}">

                        <div class="mb-3">
                            <label class="form-label">Question <span class="text-danger">*</span></label>
                            <textarea name="questionText" class="form-control" placeholder="Enter question" required>{{ old('questionText') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question Type</label>
                            <select name="questionType" id="questionType" class="form-control">
                                <option value="multiple_choice"
                                    {{ old('questionType') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice
                                </option>
                                <option value="true_false" {{ old('questionType') == 'true_false' ? 'selected' : '' }}>
                                    True/False</option>
                                <option value="subjective" {{ old('questionType') == 'subjective' ? 'selected' : '' }}>
                                    Subjective</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Is Mandatory?</label>
                            <select name="isMandatory" class="form-control">
                                <option value="1" {{ old('isMandatory', 1) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('isMandatory') == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="mb-3" id="answerOptionsContainer">
                            <label class="form-label">Answer Options</label>
                            <div id="answerOptions"></div>
                            <button type="button" id="addAnswerOption" class="btn btn-success btn-sm mt-2">Add
                                Option</button>
                        </div>

                        <div class="mb-3" id="subjectiveAnswerContainer" style="display: none;">
                            <label class="form-label">Sample Answer (Optional)</label>
                            <textarea name="subjectiveAnswer" class="form-control" rows="3">{{ old('subjectiveAnswer') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Explanation</label>
                            <textarea name="explanation" class="form-control" rows="3">{{ old('explanation') }}</textarea>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="isQuestionShuffle" class="form-check-input" id="isQuestionShuffle"
                                value="1" {{ old('isQuestionShuffle') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isQuestionShuffle">Shuffle Questions</label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" name="isAnswerShuffle" class="form-check-input" id="isAnswerShuffle"
                                value="1" {{ old('isAnswerShuffle') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isAnswerShuffle">Shuffle Answers</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Question</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Existing Questions --}}
        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-primary">Custom Set Questions</h5>
                </div>
                <div class="card-body">
                    @if ($questions->isEmpty())
                        <p class="text-muted">No questions available yet.</p>
                    @else
                        <div class="accordion" id="questionsAccordion">
                            @foreach ($questions as $index => $question)
                                @php
                                    $questionId = 'question-' . $question->id;
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $questionId }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $questionId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-controls="collapse-{{ $questionId }}">
                                            {{ $index + 1 }}. {{ Str::limit($question->questionText, 80) }}
                                        </button>
                                    </h2>

                                    <div id="collapse-{{ $questionId }}"
                                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                        aria-labelledby="heading-{{ $questionId }}" data-bs-parent="#questionsAccordion">
                                        <div class="accordion-body">
                                            @if ($question->questionType === 'subjective')
                                                <p><strong>Sample Answer:</strong>
                                                    {{ $question->options->first()?->subjectiveAnswer ?? '-' }}</p>
                                            @else
                                                <ul class="ps-4 mt-2">
                                                    @foreach ($question->options as $option)
                                                        <li>
                                                            {{ $option->optionText }}
                                                            @if ($option->isCorrect)
                                                                <span class="badge bg-success">Correct</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <p><strong>Explanation:</strong> {{ $question->explanation ?? '-' }}</p>

                                            <div class="mt-2 d-flex gap-2">
                                                <a href="#" class="btn btn-sm btn-outline-secondary"><i
                                                        class="bx bx-edit"></i> Edit</a>

                                                <form
                                                    action="{{ route('admin.custom_sets.questions.destroy', [$customSet->id, $question->id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                            class="bx bx-trash"></i> Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionTypeSelect = document.getElementById('questionType');
            const answerOptionsContainer = document.getElementById('answerOptionsContainer');
            const subjectiveContainer = document.getElementById('subjectiveAnswerContainer');

            function toggleFields() {
                const type = questionTypeSelect.value;
                if (type === 'subjective') {
                    answerOptionsContainer.style.display = 'none';
                    subjectiveContainer.style.display = 'block';
                } else {
                    answerOptionsContainer.style.display = 'block';
                    subjectiveContainer.style.display = 'none';
                }
            }

            questionTypeSelect.addEventListener('change', toggleFields);
            toggleFields(); // initial

            let optionCount = 0;
            document.getElementById('addAnswerOption')?.addEventListener('click', function() {
                optionCount++;
                const div = document.createElement('div');
                div.classList.add('mb-2', 'd-flex', 'align-items-center', 'gap-2');
                div.innerHTML = `
            <input type="text" name="options[${optionCount}][text]" class="form-control form-control-sm" placeholder="Option text" required style="width: auto;">
            <div class="form-check">
                <input type="radio" name="correct_option" value="${optionCount}" class="form-check-input" required>
                <label class="form-check-label">Correct</label>
            </div>
            <button type="button" class="btn btn-sm btn-danger removeOption">Remove</button>
        `;
                document.getElementById('answerOptions').appendChild(div);
                div.querySelector('.removeOption').addEventListener('click', () => div.remove());
            });
        });
    </script>
@endsection
