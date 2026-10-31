{{-- Fee changes that are not payments (discounts, bursaries, charges, refunds, course-change transfers), for statements. Needs $studentId. --}}
@php
    $feeAdjustments = \App\Models\FeeAdjustment::with(['enrollment', 'termRegistration'])->where('student_id', $studentId)->orderBy('created_at')->get();
@endphp
@if($feeAdjustments->isNotEmpty())
    <h3 style="margin: 30px 0 10px; color: #E63946;">Adjustments, Refunds and Transfers</h3>
    <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr style="background: #f3f4f6; text-align: left;">
                <th style="padding: 6px;">Date</th>
                <th style="padding: 6px;">Course</th>
                <th style="padding: 6px;">Item</th>
                <th style="padding: 6px; text-align: right;">Fees (KES)</th>
                <th style="padding: 6px; text-align: right;">Paid (KES)</th>
                <th style="padding: 6px;">Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeAdjustments as $adj)
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 6px;">{{ $adj->created_at->format('d/m/Y') }}</td>
                    <td style="padding: 6px;">{{ $adj->enrollment->course_name ?? '' }}</td>
                    <td style="padding: 6px;">{{ $adj->label }}</td>
                    <td style="padding: 6px; text-align: right;">{{ (float) $adj->fees_change ? number_format($adj->fees_change, 2) : '' }}</td>
                    <td style="padding: 6px; text-align: right;">{{ (float) $adj->paid_change ? number_format($adj->paid_change, 2) : '' }}</td>
                    <td style="padding: 6px;">{{ $adj->reason }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
