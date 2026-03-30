<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Slip - {{ $registration->registration_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none; }
            .print-only { display: block; }
            .page-break { page-break-after: always; }
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .slip {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #2563eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background: #1e3a8a;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .title {
            text-align: center;
            margin-bottom: 30px;
        }
        .title h2 {
            margin: 0;
            color: #374151;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .title .registration-number {
            font-size: 16px;
            color: #1e3a8a;
            margin-top: 10px;
            font-weight: bold;
            font-family: monospace;
        }
        .photo-section {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .photo-placeholder {
            width: 120px;
            height: 140px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
        }
        .photo-placeholder i {
            font-size: 40px;
            color: #9ca3af;
            margin-bottom: 5px;
        }
        .photo-placeholder span {
            font-size: 12px;
            color: #6b7280;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-label {
            color: #6B7280;
        }
        .info-value {
            color: #374151;
            font-weight: 500;
        }
        .exam-details {
            background: #F9FAFB;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .exam-details h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #374151;
        }
        .exam-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .exam-label {
            width: 120px;
            font-weight: 600;
            color: #374151;
        }
        .exam-value {
            flex: 1;
            color: #1e3a8a;
            font-weight: 500;
        }
        .important-note {
            background: #FEF3C7;
            border: 1px solid #F59E0B;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .important-note h4 {
            margin: 0 0 10px 0;
            color: #92400E;
            font-size: 16px;
        }
        .important-note ul {
            margin: 0;
            padding-left: 20px;
            color: #92400E;
            font-size: 13px;
        }
        .important-note li {
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6B7280;
            border-top: 1px dashed #e5e7eb;
            padding-top: 20px;
        }
        .footer p {
            margin: 5px 0;
        }
        .barcode {
            text-align: center;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 20px;
            letter-spacing: 2px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.knec {
            background: #FEE2E2;
            color: #B91C1C;
        }
        .badge.cdacc {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .badge.nita {
            background: #D1FAE5;
            color: #065F46;
        }
        .badge.tveta {
            background: #F3E8FF;
            color: #6B21A8;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .print-button {
            background: #1e3a8a;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .print-button:hover {
            background: #1e3a8a;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(30, 58, 138, 0.05);
            white-space: nowrap;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="slip">
        <!-- Header -->
        <div class="header">
            <h1>KENSWED COLLEGE</h1>
            <p>EXAMINATION SLIP</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="title">
                <h2>EXAMINATION ENTRY SLIP</h2>
                <div class="registration-number">Reg No: {{ $registration->registration_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="badge {{ strtolower($registration->exam_body) }}">{{ $registration->exam_body }}</span>
                </div>
            </div>

            <!-- Photo Section -->
            <div class="photo-section">
                <div class="photo-placeholder">
                    <i class="fas fa-camera"></i>
                    <span>PASSPORT</span>
                    <span>PHOTO</span>
                </div>
            </div>

            <!-- Student Information -->
            <div class="info-grid">
                <div class="info-box">
                    <h3>Student Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value">{{ $registration->student->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Student No:</span>
                        <span class="info-value">{{ $registration->student->student_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Course:</span>
                        <span class="info-value">{{ $registration->enrollment->course->name ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="info-box">
                    <h3>Examination Details</h3>
                    <div class="info-row">
                        <span class="info-label">Exam Type:</span>
                        <span class="info-value">{{ $registration->exam_type }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Index No:</span>
                        <span class="info-value">{{ $registration->index_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Registration Date:</span>
                        <span class="info-value">{{ $registration->registration_date->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Exam Schedule -->
            <div class="exam-details">
                <h3>EXAMINATION SCHEDULE</h3>
                <div class="exam-row">
                    <span class="exam-label">Exam Date:</span>
                    <span class="exam-value">{{ $registration->exam_date->format('l, F j, Y') }}</span>
                </div>
                @if($registration->exam_time)
                <div class="exam-row">
                    <span class="exam-label">Exam Time:</span>
                    <span class="exam-value">{{ $registration->exam_time }}</span>
                </div>
                @endif
                @if($registration->exam_venue)
                <div class="exam-row">
                    <span class="exam-label">Exam Venue:</span>
                    <span class="exam-value">{{ $registration->exam_venue }}</span>
                </div>
                @endif
            </div>

            <!-- Important Instructions -->
            <div class="important-note">
                <h4>IMPORTANT INSTRUCTIONS</h4>
                <ul>
                    <li>Report at the examination centre at least 30 minutes before the scheduled time.</li>
                    <li>Bring this slip, your student ID, and a valid identification card.</li>
                    <li>Mobile phones and other electronic devices are strictly prohibited.</li>
                    <li>Write your index number on all answer booklets.</li>
                    <li>Follow all instructions from the invigilator.</li>
                </ul>
            </div>

            <!-- Barcode / QR Code -->
            <div class="barcode">
                *{{ $registration->registration_number }}*
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>This is a computer generated examination slip. No signature required.</p>
                <p>KENSWED COLLEGE - {{ now()->format('F j, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="actions no-print">
        <button onclick="window.print()" class="print-button">
            <i class="fas fa-print"></i> Print Exam Slip
        </button>
        <button onclick="window.close()" class="print-button" style="background: #6B7280; margin-left: 10px;">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>
