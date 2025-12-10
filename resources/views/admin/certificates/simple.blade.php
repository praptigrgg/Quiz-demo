<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>

    <style>
        /* A4 landscape, no default margins; we control everything */
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            align-content: center;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            background: #ffffff;
            color: #222;
        }

        /* =============== PAGE BOX (SMALLER THAN A4) =============== */
        .page {
            /* A4 width ≈ 842pt, height ≈ 595pt
               We keep this box comfortably smaller */
            width: 760pt;              /* safe width -> no cutting on sides */
            min-height: 500pt;         /* still smaller than 595pt */
            margin: 20pt auto;         /* center horizontally + top/bottom margin */
            box-sizing: border-box;
        }

        .certificate {
            border: 4pt double #222;   /* visible double line on all 4 sides */
            padding: 24pt 30pt;
            box-sizing: border-box;
        }

        h1, p {
            margin: 0;
        }

        /* =============== HEADER =============== */
        .header {
            margin-bottom: 18pt;
            font-size: 11pt;
            font-weight: 700;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-left {
            text-align: left;
        }

        .header-right {
            text-align: right;
        }

        .badge-code {
            font-family: monospace;
            background: #f1f1f1;
            padding: 2pt 4pt;
            border-radius: 3pt;
            border: 1pt solid #ddd;
            display: inline-block;
        }

        /* =============== TITLE + TEXT =============== */
        .title {
            text-align: center;
            font-size: 30pt;
            letter-spacing: 1.2pt;
            text-transform: uppercase;
            color: #52657A;
            margin-top: 4pt;
        }

        .line {
            width: 50%;
            height: 2pt;
            background: #111;
            margin: 10pt auto 20pt;
        }

        .subtitle {
            text-align: center;
            font-size: 13pt;
            color: #666;
            margin-bottom: 12pt;
        }

        .name {
            text-align: center;
            font-size: 26pt;
            font-weight: 700;
            margin-bottom: 16pt;
        }

        .text {
            text-align: center;
            font-size: 13.5pt;
            line-height: 1.4;
            max-width: 70%;
            margin: 0 auto;
            color: #333;
        }

        .highlight {
            font-weight: 700;
        }

        .issued {
            text-align: center;
            margin-top: 16pt;
            font-size: 13pt;
        }

        /* =============== SIGNATURE ROW =============== */
        .sig-table-wrapper {
            margin-top: 28pt;
        }

        table.sig-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* equal columns */
        }

        table.sig-table td {
            text-align: center;
            padding: 0 8pt;
            vertical-align: top;
        }

        .sig-img {
            height: 32pt;
            margin-bottom: 6pt;
        }

        .sig-line {
            width: 80%;
            height: 1pt;
            background: #333;
            margin: 6pt auto 6pt;
        }

        .sig-name {
            font-weight: 700;
            font-size: 11.5pt;
        }

        .sig-role {
            font-size: 10pt;
            color: #555;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="certificate">

        {{-- HEADER --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">Your LMS</td>
                    <td class="header-right">
                        Serial: <span class="badge-code">{{ $serial }}</span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- TITLE --}}
        <div class="title">CERTIFICATE OF COMPLETION</div>
        <div class="line"></div>

        {{-- MAIN TEXT --}}
        <p class="subtitle">This certifies that</p>
        <div class="name">{{ $user }}</div>

        <p class="text">
            has successfully completed the course
            <span class="highlight">“{{ $course }}”</span>
            under the instruction of
            <span class="highlight">{{ $instructor }}</span>.
        </p>

        <p class="issued">
            Issued on <strong>{{ $issued_at }}</strong>
        </p>

        {{-- SIGNATURES --}}
        <div class="sig-table-wrapper">
            <table class="sig-table">
                <tr>
                    {{-- Primary instructor --}}
                    <td>
                        @if(!empty($instructor_signature_b64))
                            <img src="{{ $instructor_signature_b64 }}" class="sig-img" alt="Instructor signature">
                        @endif
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ $instructor }}</div>
                        <div class="sig-role">{{ $instructor_position }}</div>
                    </td>

                    {{-- Additional signatories --}}
                    @if(!empty($signatories) && is_array($signatories))
                        @foreach($signatories as $sg)
                            <td>
                                @if(!empty($sg['signature']))
                                    <img src="{{ $sg['signature'] }}" class="sig-img" alt="Signature">
                                @endif
                                <div class="sig-line"></div>
                                <div class="sig-name">{{ $sg['name'] }}</div>
                                <div class="sig-role">{{ $sg['position'] }}</div>
                            </td>
                        @endforeach
                    @endif
                </tr>
            </table>
        </div>

    </div>
</div>
</body>
</html>
