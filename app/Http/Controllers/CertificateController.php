<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Certificate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CertificateTemplate;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Show the form (prefilled if session has prior data).
     * GET /admin/certificates/form
     */
    public function form(Request $request)
    {
        $form = $request->session()->get('cert.form', []);
        $templates = CertificateTemplate::all();

        return view('admin.certificates.upload_form', compact('form', 'templates'));
    }


    /**
     * Preview (POST builds payload; GET reuses last payload in session).
     * GET|POST /admin/certificates/preview
     */
    public function preview(Request $request)
    {
        // If POST: validate form and build payload + form state
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'user'        => 'required|string|max:255',
                'serial'      => 'nullable|string|max:255',
                'course'      => 'required|string|max:255',
                'course_id'   => 'required|integer',
                'issue_date'  => 'nullable|date',
                'template_id' => 'required|exists:certificate_templates,id',

                // primary instructor
                'instructor.name'        => 'required|string|max:120',
                'instructor.position'    => 'required|string|max:120',
                'instructor.signature'   => 'nullable|image|mimes:png,jpg,jpeg|max:512',
                'instructor.keep_existing_signature' => 'nullable|in:0,1',
                'instructor.existing_signature_path' => 'nullable|string',
                'instructor.remove_signature'        => 'nullable|in:0,1',

                // additional signatories
                'signatories'                          => 'nullable|array',
                'signatories.*.name'                   => 'nullable|string|max:120',
                'signatories.*.position'               => 'nullable|string|max:120',
                'signatories.*.signature'              => 'nullable|image|mimes:png,jpg,jpeg|max:512',
                'signatories.*.existing_signature_path' => 'nullable|string',
                'signatories.*.keep_existing_signature' => 'nullable|in:0,1',
                'signatories.*.remove_signature'       => 'nullable|in:0,1',
            ]);

            // Load the selected template
            $template = CertificateTemplate::findOrFail($data['template_id']);
            $slug = $template->slug;

            if (str_contains($slug, 'quiz')) {
                $templateType = 'quiz';
            } elseif (str_contains($slug, 'training')) {
                $templateType = 'training';
            } else {
                // default
                $templateType = 'course';
            }

            // Dynamic label
            $entityLabel = match ($templateType) {
                'quiz'     => 'Quiz',
                'training' => 'Training',
                default    => 'Course',
            };

            // Ensure directories for signatures
            Storage::disk('public')->makeDirectory('signatures/instructors');
            Storage::disk('public')->makeDirectory('signatures/authorities');

            /* --------- Primary instructor signature --------- */
            $insExistingPath = $request->input('instructor.existing_signature_path');
            $insKeep   = (int) $request->input('instructor.keep_existing_signature', 0);
            $insRemove = (int) $request->input('instructor.remove_signature', 0);

            $instructorSigPath = null;

            if ($insRemove === 1) {
                $instructorSigPath = null;
            } elseif ($request->hasFile('instructor.signature')) {
                $instructorSigPath = $request->file('instructor.signature')
                    ->store('signatures/instructors', 'public');
            } elseif ($insKeep === 1 && $insExistingPath) {
                $instructorSigPath = $insExistingPath;
            }

            $instructorSigB64 = null;
            if ($instructorSigPath) {
                $instructorSigB64 = 'data:image/png;base64,' .
                    base64_encode(file_get_contents(
                        storage_path('app/public/' . $instructorSigPath)
                    ));
            }

            /* --------- Additional signatories --------- */
            $rawSignatories     = $request->input('signatories', []);
            $signatoriesPayload = [];
            $signatoriesForm    = [];

            foreach ($rawSignatories as $i => $sg) {
                $name = trim($sg['name'] ?? '');
                $pos  = trim($sg['position'] ?? '');

                $existing = $sg['existing_signature_path'] ?? null;
                $keep     = (int) ($sg['keep_existing_signature'] ?? 0);
                $remove   = (int) ($sg['remove_signature'] ?? 0);

                $sigPath = null;
                if ($remove === 1) {
                    $sigPath = null;
                } elseif ($request->hasFile("signatories.$i.signature")) {
                    $sigPath = $request->file("signatories.$i.signature")
                        ->store('signatures/authorities', 'public');
                } elseif ($keep === 1 && $existing) {
                    $sigPath = $existing;
                }

                // For PDF payload (only non-empty)
                if ($name || $pos || $sigPath) {
                    $sigB64 = null;
                    if ($sigPath) {
                        $sigB64 = 'data:image/png;base64,' .
                            base64_encode(file_get_contents(
                                storage_path('app/public/' . $sigPath)
                            ));
                    }

                    $signatoriesPayload[] = [
                        'name'           => $name,
                        'position'       => $pos,
                        'signature'      => $sigB64,
                        'signature_path' => $sigPath,
                    ];
                }

                // For form (keep all rows)
                $signatoriesForm[] = [
                    'name'           => $name,
                    'position'       => $pos,
                    'signature_path' => $sigPath,
                ];
            }

            /* --------- Issue date handling --------- */
            $issueDateRaw = $data['issue_date'] ?? null;

            if ($issueDateRaw) {
                $issueDate = Carbon::parse($issueDateRaw);
                $issueDateFormatted = $issueDate->format('F d, Y');
            } else {
                // fallback to today if no issue_date sent from form
                $issueDate = now();
                $issueDateRaw = $issueDate->toDateString();
                $issueDateFormatted = $issueDate->format('F d, Y');
            }

            /* --------- Final payload used by BOTH preview + PDF --------- */
            $payload = [
                'serial'                    => $data['serial'] ?? null,
                'issue_date'                => $issueDateRaw,
                'issued_at'                 => $issueDateFormatted,

                'user'                      => $data['user'],

                // generic fields
                'entity_label'              => $entityLabel,      // Course | Quiz | Training
                'entity_name'               => $data['course'],   // reuse field for now
                'template_type'             => $templateType,     // course | quiz | training

                // old fields (kept for DB and compatibility)
                'course'                    => $data['course'],
                'course_id'                 => $data['course_id'],

                // instructor
                'instructor'                => $data['instructor']['name'],
                'instructor_position'       => $data['instructor']['position'],
                'instructor_signature_b64'  => $instructorSigB64,
                'instructor_signature_path' => $instructorSigPath,

                'signatories'               => $signatoriesPayload,
                'template_id'               => $template->id,
                'template_view'             => $template->view_name,
            ];


            // Form state for edit later
            $form = [
                'user'       => $data['user'],
                'course'     => $data['course'],
                'serial'     => $data['serial'] ?? null,
                'issue_date' => $issueDateRaw,
                'course_id'  => $data['course_id'],
                'template_id' => $template->id,
                'instructor' => [
                    'name'           => $data['instructor']['name'],
                    'position'       => $data['instructor']['position'],
                    'signature_path' => $instructorSigPath,
                ],
                'signatories' => $signatoriesForm,
            ];

            // Save in session - approve() & previewPdf() will re-use this EXACT payload
            $request->session()->put('cert.payload', $payload);
            $request->session()->put('cert.form', $form);
        }

        // For GET or after POST above, read from session
        $payload = $request->session()->get('cert.payload');
        if (!$payload) {
            return redirect()
                ->route('admin.certificates.form')
                ->with('error', 'No preview data found. Please fill the form first.');
        }

        // Choose template view (fallback to simple)
        $viewName = $payload['template_view'] ?? 'admin.certificates.simple';

        // Optional HTML preview (even if your page mainly uses iframe)
        $certificateHtml = view($viewName, $payload)->render();
        $templates = CertificateTemplate::all();

        return view('admin.certificates.preview', compact('certificateHtml', 'templates'));
    }

    /**
     * From preview, go back to the form with fields prefilled.
     * GET /admin/certificates/edit
     */
    public function edit(Request $request)
    {
        $form = $request->session()->get('cert.form');
        $templates = CertificateTemplate::all();

        if (!$form) {
            return redirect()
                ->route('admin.certificates.form')
                ->with('error', 'No preview data to edit. Please fill the form first.');
        }

        return view('admin.certificates.upload_form', compact('form', 'templates'));
    }

    /**
     * Approve & SAVE the PDF using the session payload.
     * Also store row in certificates table and redirect to list.
     * POST /admin/certificates/approve
     */
    public function approve(Request $request)
    {
        $payload = $request->session()->get('cert.payload');

        if (!$payload) {
            return redirect()
                ->route('admin.certificates.form')
                ->with('error', 'Preview expired. Please re-submit the form.');
        }

        // Ensure serial is set
        $serial = $payload['serial'] ?? null;
        if (!$serial || trim($serial) === '') {
            $serial = strtoupper(Str::random(10));
            $payload['serial'] = $serial;
            $request->session()->put('cert.payload', $payload);
        }

        // 1) Generate PDF using selected template
        $viewName = $payload['template_view'] ?? 'admin.certificates.simple';

        $pdf = Pdf::loadView($viewName, $payload)
            ->setPaper('A4', 'landscape');

        // 2) Create folder name
        $folderName = 'cert_' . time();

        // Make directory on *public* disk (this maps to storage/app/public)
        Storage::disk('public')->makeDirectory("certificates/{$folderName}");

        $fileName = 'certificate.pdf';

        // Save file to storage/app/public/certificates/{folderName}/certificate.pdf
        Storage::disk('public')->put("certificates/{$folderName}/{$fileName}", $pdf->output());

        // 3) Save DB row
        Certificate::create([
            'folder'      => $folderName,
            'file_name'   => $fileName,
            'user_name'   => $payload['user'],
            'serial_no'   => $serial,
            'course'      => $payload['course'],
            'course_id'   => $payload['course_id'],
            'template_id' => $payload['template_id'] ?? null,
        ]);

        // 4) Clear session
        $request->session()->forget('cert.payload');
        $request->session()->forget('cert.form');

        // 5) Redirect to list
        return redirect()
            ->route('admin.certificates.list')
            ->with('success', 'Certificate generated and saved successfully!');
    }

    /**
     * PDF preview for iframe (no saving).
     * GET /admin/certificates/preview-pdf
     */
    public function previewPdf(Request $request)
    {
        $payload = $request->session()->get('cert.payload');

        if (!$payload) {
            return redirect()
                ->route('admin.certificates.form')
                ->with('error', 'No preview data found. Please fill the form first.');
        }

        $viewName = $payload['template_view'] ?? 'admin.certificates.simple';

        $pdf = Pdf::loadView($viewName, $payload)
            ->setPaper('A4', 'landscape');

        return $pdf->stream('certificate-preview.pdf');
    }

    /**
     * Legacy/manual download using direct request data.
     * You can remove this if you no longer use it.
     */
    public function downloadCertificate(Request $request)
    {
        $data = [
            'user'       => $request->user,
            'serial'     => $request->serial,
            'course'     => $request->course,
            'issue_date' => $request->issue_date,
        ];

        $pdf = Pdf::loadView('admin.certificates.simple', $data);

        $folderName = 'cert_' . time();
        Storage::disk('public')->makeDirectory("certificates/{$folderName}");

        $fileName = 'certificate.pdf';
        Storage::disk('public')->put("certificates/{$folderName}/{$fileName}", $pdf->output());

        Certificate::create([
            'folder'    => $folderName,
            'file_name' => $fileName,
            'user_name' => $request->user,
            'course'    => $request->course,
            'serial_no' => $request->serial,
            'course_id' => $request->course_id,
        ]);

        return redirect()->route('admin.certificates.list')
            ->with('success', 'Certificate generated successfully!');
    }

    /**
     * List all generated certificates.
     * GET /admin/certificates/list
     */
    public function certificateList()
    {
        $certificates = Certificate::latest()->get();

        return view('admin.certificates.list', compact('certificates'));
    }

    /**
     * View stored PDF by certificate id.
     * GET /admin/certificates/{certificate}/view
     */
    public function viewPdf(Certificate $certificate)
    {
        $path = storage_path(
            'app/public/certificates/' . $certificate->folder . '/' . $certificate->file_name
        );

        if (!file_exists($path)) {
            return back()->with('error', 'Certificate file not found.');
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $certificate->file_name . '"',
        ]);
    }
}
