<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Training Completion Certificate</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 35px;
            font-family: Arial, sans-serif;
        }
        .outer {
            border: 6px solid #2e7d32;
            padding: 18px;
        }
        .inner {
            border: 1px dashed #2e7d32;
            padding: 25px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1b5e20;
        }
        .header p {
            margin: 4px 0;
            font-size: 11px;
            color: #555;
        }
        .content {
            text-align: center;
            font-size: 13px;
            line-height: 1.6;
        }
        .name {
            margin: 12px 0 5px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .course {
            margin: 8px 0 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .meta {
            margin-top: 25px;
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
            color: #1b5e20;
        }
        .signatures {
            margin-top: 40px;
        }
        .signatures table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            text-align: center;
        }
        .signatures td {
            padding: 0 10px;
        }
        .sig-img {
            height: 45px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 4px 10px 0 10px;
        }
    </style>
</head>
<body>
<div class="outer">
    <div class="inner">
                <div class="meta">
            <table>
                <tr>
                    <td class="meta-label" style="width: 20%;">Serial No:</td>
                    <td style="width: 30%;">{{ $serial ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="header">
            <h1>Training Completion Certificate</h1>
            <p>This certificate is awarded to the following participant for successfully completing the training program.</p>
        </div>

        <div class="content">
            This is to certify that

            <div class="name">
                {{ $user ?? '' }}
            </div>

            has successfully completed the training

            <div class="course">
               {{ $entity_name }}
            </div>

            conducted on {{ $issued_at ?? '' }}.
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
                        <div>{{ $instructor_position ?? 'Trainer / Facilitator' }}</div>
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
    </div>
</div>
</body>
</html>
