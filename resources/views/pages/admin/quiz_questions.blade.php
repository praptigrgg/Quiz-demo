@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12 mb-4">
            <!-- Question Form -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="text-primary">Create New Quiz Question</h5>
                    <div>
                        <a href="{{ route('admin.quizzes.index') }}" class="btn btn-outline-secondary"><i
                                class="bx bx-reset"></i></a>
                        <a href="{{ route('admin.quizzes.questions.import', $quiz->id) }}" class="btn btn-outline-primary">
                            Import Questions
                        </a>

                    </div>
                </div>

                <div class="card-body">

                    {{-- Excel Upload --}}
                    <form action="{{ route('admin.quizzes-questions.excel.store') }}" method="POST"
                        enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                        <div class="input-group">
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <button class="btn btn-primary" type="submit">Upload Excel</button>
                        </div>
                    </form>
                    <a href="/storage/quiz/quiz.xlsx" target="_blank" class="btn btn-outline-success mb-3">
                        <i class="bx bx-download"></i> Download Excel Template
                    </a>

                    {{-- Manual Question Form --}}
                    <form action="{{ route('admin.quizzes.questions.store', $quiz->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

                        <div class="mb-3">
                            <label class="form-label">Question <span class="text-danger">*</span></label>
                            <textarea name="questionText" class="form-control" placeholder="Enter question" required>{{ old('questionText') }}</textarea>
                        </div>

                        {{-- Group Assignment --}}
                        <div class="mb-3">
                            <label class="form-label">Assign to Group?</label>
                            <div class="form-check">
                                <input type="checkbox" name="is_group" class="form-check-input" id="isGroupCheck"
                                    value="1" {{ old('is_group') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isGroupCheck">Yes, assign this question to a
                                    group</label>
                            </div>
                        </div>

                        <div class="mb-3" id="groupSelectDiv" style="display: none;">
                            <label class="form-label">Select Group</label>
                            @php
                                $groups = $quiz->groups ?? collect();
                            @endphp

                            @if (count($groups) > 0)
                                <select name="group_name" class="form-control">
                                    <option value="">Select Group</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group }}">{{ $group }}</option>
                                    @endforeach
                                </select>
                            @else
                                <p class="text-muted">No groups available</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question Type</label>
                            <select name="questionType" class="form-control">
                                <option value="multiple_choice"
                                    {{ old('questionType') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice
                                </option>
                                <option value="true_false" {{ old('questionType') == 'true_false' ? 'selected' : '' }}>
                                    True/False</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Is Mandatory?</label>
                            <select name="isMandatory" class="form-control">
                                <option value="1" {{ old('isMandatory', 1) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('isMandatory') == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Answer Options</label>
                            <div id="answerOptions"></div>
                            <button type="button" id="addAnswerOption" class="btn btn-success btn-sm mt-2">Add
                                Option</button>
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

        {{-- Existing Questions grouped in accordion --}}
        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-primary">Quiz Questions</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="quizGroupsAccordion">
                        @foreach ($paginatedGroups as $groupName => $paginator)
                            @php
                                $groupSlug = \Str::slug($groupName);
                                $isActive = $activeGroup === $groupSlug || (!$activeGroup && $loop->first);
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $groupSlug }}">
                                    <button class="accordion-button {{ $isActive ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupSlug }}"
                                        aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                                        aria-controls="collapse-{{ $groupSlug }}">
                                        Group {{ $groupName }} ({{ $paginator->total() }} Questions)
                                    </button>
                                </h2>
                                <div id="collapse-{{ $groupSlug }}"
                                    class="accordion-collapse collapse {{ $isActive ? 'show' : '' }}"
                                    aria-labelledby="heading-{{ $groupSlug }}" data-bs-parent="#quizGroupsAccordion">
                                    <div class="accordion-body" id="accordion-body-{{ $groupSlug }}">

                                        {{-- Questions --}}
                                        @foreach ($paginator as $index => $question)
                                            <div class="mb-4 border-bottom pb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6>{{ ($paginator->currentPage() - 1) * $paginator->perPage() + $loop->iteration }}.
                                                        {{ $question->questionText }}</h6>
                                                    <div>
                                                        <a href="{{ route('admin.quizzes-questions.edit', $question->id) }}"
                                                            class="btn btn-sm btn-outline-secondary"><i
                                                                class="bx bx-edit"></i></a>
                                                        <a href="javascript:void(0);"
                                                            class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-delete-url="{{ route('admin.quizzes-questions.destroy', $question->id) }}"><i
                                                                class="bx bx-trash"></i></a>
                                                    </div>
                                                </div>
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
                                            </div>
                                        @endforeach

                                        {{-- Pagination --}}
                                        <div class="d-flex justify-content-between align-items-center mt-4">
                                            <div class="text-muted">
                                                Showing {{ ($paginator->currentPage() - 1) * $paginator->perPage() + 1 }}
                                                to
                                                {{ min($paginator->currentPage() * $paginator->perPage(), $paginator->total()) }}
                                                of {{ $paginator->total() }} questions
                                            </div>

                                            <nav aria-label="Question pagination">
                                                <ul class="pagination mb-0">
                                                    {{-- Previous --}}
                                                    @if ($paginator->onFirstPage())
                                                        <li class="page-item disabled">
                                                            <span class="page-link text-muted"><i
                                                                    class="bx bx-chevron-left"></i></span>
                                                        </li>
                                                    @else
                                                        <li class="page-item">
                                                            <a class="page-link text-success"
                                                                href="{{ $paginator->previousPageUrl() }}"><i
                                                                    class="bx bx-chevron-left"></i></a>
                                                        </li>
                                                    @endif

                                                    {{-- Pages --}}
                                                    @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                                                        @if ($page == $paginator->currentPage())
                                                            <li class="page-item"><span
                                                                    class="page-link bg-success border-success text-white">{{ $page }}</span>
                                                            </li>
                                                        @else
                                                            <li class="page-item"><a class="page-link text-success"
                                                                    href="{{ $url }}">{{ $page }}</a>
                                                            </li>
                                                        @endif
                                                    @endforeach

                                                    {{-- Next --}}
                                                    @if ($paginator->hasMorePages())
                                                        <li class="page-item"><a class="page-link text-success"
                                                                href="{{ $paginator->nextPageUrl() }}"><i
                                                                    class="bx bx-chevron-right"></i></a></li>
                                                    @else
                                                        <li class="page-item disabled"><span
                                                                class="page-link text-muted"><i
                                                                    class="bx bx-chevron-right"></i></span></li>
                                                    @endif
                                                </ul>
                                            </nav>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .page-link.text-success {
            color: #198754 !important;
            border-color: #dee2e6;
        }

        .page-link.text-success:hover {
            color: #146c43 !important;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .page-link.bg-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
        }

        .page-link.bg-success:hover {
            background-color: #146c43 !important;
            border-color: #146c43 !important;
        }

        .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let optionCount = 0;

            // Add answer options dynamically
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

            // Group toggle
            const isGroupCheck = document.getElementById('isGroupCheck');
            const groupSelectDiv = document.getElementById('groupSelectDiv');
            if (isGroupCheck) {
                isGroupCheck.addEventListener('change', () => {
                    groupSelectDiv.style.display = isGroupCheck.checked ? 'block' : 'none';
                });
                if (isGroupCheck.checked) groupSelectDiv.style.display = 'block';
            }

            // Preserve accordion state and scroll position
            function preserveAccordionState() {
                const openAccordion = document.querySelector('.accordion-collapse.show');
                if (openAccordion) sessionStorage.setItem('activeAccordion', openAccordion.id);
                sessionStorage.setItem('scrollPosition', window.pageYOffset);
            }

            function restoreAccordionState() {
                const id = sessionStorage.getItem('activeAccordion');
                if (id) {
                    const accordion = document.getElementById(id);
                    if (accordion) new bootstrap.Collapse(accordion, {
                        toggle: true
                    });
                }
            }

            function restoreScrollPosition() {
                const y = sessionStorage.getItem('scrollPosition');
                if (y) setTimeout(() => window.scrollTo(0, parseInt(y)), 100);
            }

            // Smooth scroll helper
            function smoothScrollTo(element) {
                const rect = element.getBoundingClientRect();
                const absoluteY = rect.top + window.scrollY - 100; // small offset
                window.scrollTo({
                    top: absoluteY,
                    behavior: 'smooth'
                });
            }

            // AJAX pagination
            function attachPaginationHandlers() {
                document.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        preserveAccordionState();

                        const url = this.getAttribute('href');
                        const accordionBody = this.closest('.accordion-body');
                        const accordionItem = accordionBody.closest('.accordion-item');

                        accordionBody.innerHTML = `
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;

                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(res => res.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const newDoc = parser.parseFromString(html, 'text/html');
                                const newContent = newDoc.querySelector(`#${accordionBody.id}`)
                                    .innerHTML;
                                accordionBody.innerHTML = newContent;
                                attachPaginationHandlers();
                                smoothScrollTo(accordionItem);
                            })
                            .catch(() => {
                                accordionBody.innerHTML =
                                    '<div class="text-danger">Failed to load questions.</div>';
                            });
                    });
                });
            }

            attachPaginationHandlers();
            restoreAccordionState();
            restoreScrollPosition();
        });
    </script>

@endsection
