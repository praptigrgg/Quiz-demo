@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Generate Certificate </h2>
          {{-- Flash success message --}}
          @if(session('success'))
              <div class="alert alert-success">
                  {{ session('success') }}
              </div>
          @endif

          {{-- Flash error message --}}
          @if(session('error'))
              <div class="alert alert-danger">
                  {{ session('error') }}
              </div>
          @endif


    {{-- Global errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

     @php
    use App\Models\CertificateTemplate;

    // ensure $form is always an array
    $form = $form ?? [];

    // if $templates is not passed from controller, load from DB here
    $templates = $templates ?? CertificateTemplate::all();
@endphp

    <form action="{{ route('admin.certificates.preview') }}"
          method="POST" enctype="multipart/form-data"
          class="p-4 border rounded bg-light shadow-sm">
        @csrf

        {{-- Learner --}}
        <div class="mb-3">
            <label class="form-label">Learner Name</label>
            <input type="text"
                   name="user"
                   class="form-control"
                   value="{{ old('user', $form['user'] ?? '') }}"
                   placeholder="e.g. Ms. Aarati Subedi"
                   required>
            @error('user')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- Course --}}
        <div class="mb-3">
            <label class="form-label" id="entityLabel"> Course / Quiz / Training Name</label>
            <input type="text"
                name="course"
                id="entityInput"
                class="form-control"
                value="{{ old('course', $form['course'] ?? '') }}"
                placeholder="e.g. Data Science"
                required>
            @error('course')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        {{-- Hidden Course ID --}}
        <input type="hidden"
            name="course_id"
            value="{{ old('course_id', $form['course_id'] ?? 1) }}">



{{-- Certificate Template --}}
<div class="mb-3">
    <label class="form-label">Certificate Template</label>
    <select name="template_id" class="form-control" id="templateSelect" required>
        <option value="">-- Select Template --</option>
        @foreach($templates as $tpl)
            <option value="{{ $tpl->id }}"
                    data-slug="{{ $tpl->slug }}"
                {{ old('template_id', $form['template_id'] ?? '') == $tpl->id ? 'selected' : '' }}>
                {{ $tpl->name }}
            </option>
        @endforeach
    </select>
    @error('template_id')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
</div>



        {{-- ===================== Primary Instructor ===================== --}}
        <div class="card mb-4">
            <div class="card-header"><strong>Primary Instructor</strong></div>
            <div class="card-body">
                <div class="row g-3 align-items-start">

                    {{-- Name --}}
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Name</label>
                        <input type="text" class="form-control"
                               name="instructor[name]"
                               value="{{ old('instructor.name', $form['instructor']['name'] ?? '') }}"
                               placeholder="Name" required>
                        @error('instructor.name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Position --}}
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Position</label>
                        <input type="text" class="form-control"
                               name="instructor[position]"
                               value="{{ old('instructor.position', $form['instructor']['position'] ?? '') }}"
                               placeholder="Position" required>
                        @error('instructor.position')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Signature --}}
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Signature (PNG/JPG)</label>

                        @php
                            // existing signature path (from old() or form in session)
                            $instructorSigPath = old(
                                'instructor.existing_signature_path',
                                $form['instructor']['signature_path'] ?? null
                            );
                        @endphp

                        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                            {{-- File input --}}
                            <input type="file"
                                   class="form-control w-auto"
                                   name="instructor[signature]"
                                   accept="image/png,image/jpeg">

                            {{-- Existing signature preview --}}
                            @if($instructorSigPath)
                                <div class="text-center">
                                    <div class="small text-muted mb-1">Current</div>
                                    <img src="{{ asset('storage/'.$instructorSigPath) }}"
                                         id="instructor-signature-current"
                                         alt="Current signature"
                                         style="height:50px; border:1px solid #ddd; border-radius:4px;">
                                </div>
                            @endif

                            {{-- New file preview --}}
                            <img id="instructor-signature-preview"
                                 alt="New signature preview"
                                 style="height:50px; border:1px solid #ddd; border-radius:4px; display:none;">
                        </div>

                        @error('instructor.signature')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror

                        {{-- keep/remove flags --}}
                        @if($instructorSigPath)
                            <input type="hidden"
                                   name="instructor[existing_signature_path]"
                                   value="{{ $instructorSigPath }}">
                            <input type="hidden"
                                   name="instructor[keep_existing_signature]"
                                   value="1">

                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" value="1"
                                       name="instructor[remove_signature]" id="ins-remove">
                                <label class="form-check-label" for="ins-remove">
                                    Remove signature
                                </label>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- ===================== Additional Signatories ===================== --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Additional Signatories</strong>
                <button type="button" id="add-signatory" class="btn btn-sm btn-outline-primary">
                    + Add Signatory
                </button>
            </div>

            <div class="card-body" id="signatories-list">
                @php
                    // After validation errors: use old('signatories')
                    // On Edit or first load: use form['signatories'] from session
                    $sgs = old('signatories', $form['signatories'] ?? [
                        ['name' => '', 'position' => '', 'signature_path' => null]
                    ]);
                @endphp

                @foreach($sgs as $i => $sg)
                    @php
                        $sgPath = old(
                            "signatories.$i.existing_signature_path",
                            $sg['signature_path'] ?? null
                        );
                    @endphp

                    <div class="signatory-row mb-3" data-index="{{ $i }}">
                        <div class="row g-3 align-items-start">

                            {{-- Name --}}
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Name</label>
                                <input type="text" class="form-control"
                                       name="signatories[{{ $i }}][name]"
                                       value="{{ old("signatories.$i.name", $sg['name'] ?? '') }}"
                                       placeholder="Name">
                                @error("signatories.$i.name")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Position --}}
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Position</label>
                                <input type="text" class="form-control"
                                       name="signatories[{{ $i }}][position]"
                                       value="{{ old("signatories.$i.position", $sg['position'] ?? '') }}"
                                       placeholder="Position">
                                @error("signatories.$i.position")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Signature --}}
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Signature (optional)</label>

                                <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                                    {{-- File input --}}
                                    <input type="file"
                                           class="form-control w-auto sg-signature-input"
                                           name="signatories[{{ $i }}][signature]"
                                           accept="image/png,image/jpeg">

                                    {{-- Existing signature --}}
                                    @if($sgPath)
                                        <div class="text-center">
                                            <div class="small text-muted mb-1">Current</div>
                                            <img src="{{ asset('storage/'.$sgPath) }}"
                                                 alt="Signature"
                                                 class="sg-signature-current"
                                                 style="height:50px; border:1px solid #ddd; border-radius:4px;">
                                        </div>
                                    @endif

                                    {{-- New preview --}}
                                    <img class="sg-signature-preview"
                                         alt="New signature preview"
                                         style="height:50px; border:1px solid #ddd; border-radius:4px; display:none;">
                                </div>

                                @error("signatories.$i.signature")
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                @if($sgPath)
                                    <input type="hidden"
                                           name="signatories[{{ $i }}][existing_signature_path]"
                                           value="{{ $sgPath }}">
                                    <input type="hidden"
                                           name="signatories[{{ $i }}][keep_existing_signature]"
                                           value="1">

                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" value="1"
                                               name="signatories[{{ $i }}][remove_signature]"
                                               id="sg-{{ $i }}-remove">
                                        <label class="form-check-label" for="sg-{{ $i }}-remove">
                                            Remove
                                        </label>
                                    </div>
                                @endif
                            </div>

                            {{-- Remove button --}}
                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-outline-danger remove-signatory">
                                    &times;
                                </button>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary px-4">
            Preview Certificate
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const templateSelect = document.getElementById('templateSelect');
    const entityLabel    = document.getElementById('entityLabel');
    const entityInput    = document.getElementById('entityInput');

    function updateEntityUI() {
        const opt  = templateSelect.options[templateSelect.selectedIndex];
        const slug = opt ? (opt.getAttribute('data-slug') || '') : '';

        let labelText = 'Course Name';
        let placeholder = 'e.g. Data Science';

        if (slug.includes('quiz')) {
            labelText = 'Quiz Name';
            placeholder = 'e.g. Final Assessment Quiz';
        } else if (slug.includes('training')) {
            labelText = 'Training Name';
            placeholder = 'e.g. Python Bootcamp 2025';
        } else {
            // default: course
            labelText = 'Course Name';
            placeholder = 'e.g. Data Science';
        }

        entityLabel.textContent   = labelText;
        entityInput.placeholder   = placeholder;
    }

    if (templateSelect) {
        updateEntityUI(); // run on page load (for edit mode)
        templateSelect.addEventListener('change', updateEntityUI);
    }
});
</script>

<script>
(function () {
    const list = document.getElementById('signatories-list');
    const addBtn = document.getElementById('add-signatory');

    function makeRow(idx) {
        const div = document.createElement('div');
        div.className = 'signatory-row mb-3';
        div.dataset.index = idx;
        div.innerHTML = `
          <div class="row g-3 align-items-start">
            <div class="col-md-3">
              <label class="form-label small mb-1">Name</label>
              <input type="text" class="form-control"
                     name="signatories[${idx}][name]"
                     placeholder="Name">
            </div>
            <div class="col-md-3">
              <label class="form-label small mb-1">Position</label>
              <input type="text" class="form-control"
                     name="signatories[${idx}][position]"
                     placeholder="Position">
            </div>
            <div class="col-md-5">
              <label class="form-label small mb-1">Signature (optional)</label>
              <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                <input type="file"
                       class="form-control w-auto sg-signature-input"
                       name="signatories[${idx}][signature]"
                       accept="image/png,image/jpeg">
                <img class="sg-signature-preview"
                     alt="New signature preview"
                     style="height:50px; border:1px solid #ddd; border-radius:4px; display:none;">
              </div>
            </div>
            <div class="col-md-1 d-flex align-items-center">
              <button type="button" class="btn btn-outline-danger remove-signatory">&times;</button>
            </div>
          </div>
        `;
        return div;
    }

    // Add new signatory row
    addBtn?.addEventListener('click', () => {
        const idx = list.querySelectorAll('.signatory-row').length;
        list.appendChild(makeRow(idx));
    });

    // Remove signatory row (or clear if only one)
    list.addEventListener('click', (e) => {
        if (e.target.closest('.remove-signatory')) {
            const rows = list.querySelectorAll('.signatory-row');
            if (rows.length === 1) {
                const row = rows[0];
                row.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
                row.querySelectorAll('input[type="file"]').forEach(i => i.value = null);
                row.querySelectorAll('.sg-signature-preview').forEach(i => {
                    i.style.display = 'none';
                    i.src = '';
                });
                row.querySelectorAll('.sg-signature-current').forEach(i => i.style.opacity = 1);
            } else {
                e.target.closest('.signatory-row').remove();
            }
        }
    });

    // Primary instructor live preview
    const insInput   = document.querySelector('input[name="instructor[signature]"]');
    const insPreview = document.getElementById('instructor-signature-preview');
    const insCurrent = document.getElementById('instructor-signature-current');

    if (insInput && insPreview) {
        insInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                insPreview.style.display = 'none';
                insPreview.src = '';
                if (insCurrent) insCurrent.style.opacity = 1;
                return;
            }
            const reader = new FileReader();
            reader.onload = function (ev) {
                insPreview.src = ev.target.result;
                insPreview.style.display = 'block';
                if (insCurrent) insCurrent.style.opacity = 0.4;
            };
            reader.readAsDataURL(file);
        });
    }

    // Signatories live preview
    if (list) {
        list.addEventListener('change', function (e) {
            const input = e.target.closest('.sg-signature-input');
            if (!input) return;

            const row = input.closest('.signatory-row');
            const preview = row.querySelector('.sg-signature-preview');
            const current = row.querySelector('.sg-signature-current');
            const file = input.files && input.files[0];

            if (!preview) return;

            if (!file) {
                preview.style.display = 'none';
                preview.src = '';
                if (current) current.style.opacity = 1;
                return;
            }

            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
                preview.style.display = 'block';
                if (current) current.style.opacity = 0.4;
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>
@endpush
