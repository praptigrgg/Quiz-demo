<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Course Completion Certificate</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 40px;
            font-family: "Times New Roman", serif;
        }
        .outer-border {
            border: 8px double #1a237e;
            padding: 25px;
        }
        .inner-border {
            border: 2px solid #1a237e;
            padding: 30px;
        }
        .title {
            text-align: center;
            margin-bottom: 10px;
        }
        .title h1 {
            font-size: 32px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .body-text {
            text-align: center;
            font-size: 14px;
            line-height: 1.6;
        }
        .student-name {
            font-size: 22px;
            font-weight: bold;
            margin: 10px 0;
            text-decoration: underline;
        }
        .course-name {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .meta-row {
            margin-top: 25px;
            font-size: 12px;
        }
        .meta-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-row td {
            padding: 2px 5px;
        }
        .meta-label {
            font-weight: bold;
        }
        .signatures {
            margin-top: 45px;
        }
        .signatures table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 12px;
        }
        .signatures td {
            padding-top: 10px;
        }
        .sig-img {
            height: 50px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 5px 20px 0 20px;
        }
        .serial-text {
            text-align: right;
            font-size: 11px;
            margin-top: 5px;
            color: #444;
        }
    </style>
</head>
<body>
<div class="outer-border">
    <div class="inner-border">
          <div class="meta-row">
            <table>
                <tr>
                    <td class="meta-label" style="width: 20%;">Serial No.:</td>
                    <td style="width: 20%;">{{ $serial ?? '' }}</td>
                </tr>
            </table>
        </div>
        <div class="title">
            <h1>Certificate of Course Completion</h1>
        </div>

        <div class="subtitle">
            This is to certify that
        </div>

        <div class="body-text">
            <div class="student-name">
                {{ $user ?? '' }}
            </div>

            has successfully completed the course

            <div class="course-name">
                {{ $entity_name }}
            </div>

            on {{ $issued_at ?? '' }}.
        </div>



        <div class="signatures">
            <table>
                <tr>
                    <td>
                        @if(!empty($instructor_signature_b64))
                            <img src="{{ $instructor_signature_b64 }}" class="sig-img" alt="Instructor Signature">
                        @endif
                        <div class="sig-line"></div>
                        <div><strong>{{ $instructor ?? '' }}</strong></div>
                        <div>{{ $instructor_position ?? 'Instructor' }}</div>
                    </td>

                    @if(!empty($signatories))
                        @foreach($signatories as $sg)
                            <td>
                                @if(!empty($sg['signature']))
                                    <img src="{{ $sg['signature'] }}" class="sig-img" alt="Signatory Signature">
                                @endif
                                <div class="sig-line"></div>
                                <div><strong>{{ $sg['name'] ?? '' }}</strong></div>
                                <div>{{ $sg['position'] ?? '' }}</div>
                            </td>
                        @endforeach
                    @endif
                </tr>
            </table>
        </div>

        <div class="serial-text">
            Ref: {{ $serial ?? '' }}
        </div>
    </div>
</div>
</body>
</html>
