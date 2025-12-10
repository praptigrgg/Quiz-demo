<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quiz Achievement Certificate</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px;
            font-family: Georgia, serif;
        }
        .outer {
            border: 5px solid #b8860b;
            padding: 18px;
        }
        .inner {
            border: 1px solid #b8860b;
            padding: 22px;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8b5a00;
        }
        .header .tagline {
            font-size: 11px;
            margin-top: 4px;
            color: #555;
        }
        .body {
            text-align: center;
            font-size: 13px;
            line-height: 1.6;
        }
        .name {
            margin: 10px 0 5px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .course {
            margin: 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .meta {
            margin-top: 18px;
            font-size: 11px;
        }
        .meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta td {
            padding: 2px 4px;
        }
        .meta-label {
            font-weight: bold;
        }
        .signatures {
            margin-top: 35px;
        }
        .signatures table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
            font-size: 11px;
        }
        .sig-img {
            height: 40px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 3px 10px 0 10px;
        }
        .footer-note {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
<div class="outer">
    <div class="inner">
           <div class="meta">
            <table>
                <tr>
                    <td class="meta-label" style="width: 20%;">Serialno:</td>
                    <td style="width: 30%;">{{ $serial ?? '' }}</td>
                </tr>
            </table>
        </div>
        <div class="header">
            <h1>Quiz Completion Certificate</h1>
            <div class="tagline">
                Awarded for successfully completing the quiz and demonstrating competency.
            </div>
        </div>

        <div class="body">
            This is to recognize

            <div class="name">
                {{ $user ?? '' }}
            </div>

            for successfully completing the quiz on

            <div class="course">
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
                        <div>{{ $instructor_position ?? 'Quiz Instructor' }}</div>
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

        <div class="footer-note">
            This certificate is digitally generated and valid without a physical seal.
        </div>
    </div>
</div>
</body>
</html>
