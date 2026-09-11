@extends('ktvtc.admin.layout.adminlayout')

@section('title', $academicTerm->name)
@section('subtitle', $academicTerm->code)

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('admin.academic-terms.index') }}" class="ml-1 text-sm font-medium text-gray-600 hover:text-primary md:ml-2">
            Academic Terms
        </a>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $academicTerm->name }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('admin.academic-terms.edit', $academicTerm) }}"
       class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition flex items-center space-x-2">
        <i class="fas fa-edit"></i>
        <span>Edit</span>
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <h2 class="text-2xl font-bold text-gray-800">{{ $academicTerm->name }}</h2>
                    @if($academicTerm->is_current)
                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">
                            <i class="fas fa-star mr-1"></i> Current
                        </span>
                    @endif
                    @if(!$academicTerm->is_active)
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                    @endif
                </div>
                <p class="text-sm text-gray-600">
                    Code: <span class="font-mono">{{ $academicTerm->code }}</span>
                    @if($academicTerm->short_code) · {{ $academicTerm->short_code }} @endif
                </p>
                @if($academicTerm->campus)
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-building mr-1"></i>{{ $academicTerm->campus->name }}
                    </p>
                @else
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-globe mr-1"></i>Global term (all campuses)
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @if(!$academicTerm->is_current)
                    <form method="POST" action="{{ route('admin.academic-terms.set-current', $academicTerm) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                            <i class="fas fa-star mr-1"></i> Set Current
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.academic-terms.toggle-registration', $academicTerm) }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-2 {{ $academicTerm->is_registration_open ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg text-sm font-medium">
                        <i class="fas fa-door-{{ $academicTerm->is_registration_open ? 'closed' : 'open' }} mr-1"></i>
                        {{ $academicTerm->is_registration_open ? 'Close Registration' : 'Open Registration' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Term Dates --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-calendar-day text-primary mr-2"></i> Term Dates
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Start</dt>
                    <dd class="font-medium">{{ $academicTerm->start_date?->format('l, d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">End</dt>
                    <dd class="font-medium">{{ $academicTerm->end_date?->format('l, d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Fee Due</dt>
                    <dd class="font-medium text-orange-600">{{ $academicTerm->fee_due_date?->format('d M Y') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Registration Window --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-user-plus text-primary mr-2"></i> Registration Window
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Opens</dt>
                    <dd class="font-medium">{{ $academicTerm->registration_start_date?->format('d M Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Closes</dt>
                    <dd class="font-medium">{{ $academicTerm->registration_end_date?->format('d M Y') ?? '—' }}</dd>
                </div>

                @if($academicTerm->allow_late_registration)
                    <div class="pt-2 border-t border-gray-200">
                        <dt class="text-gray-500 text-xs mb-1">Late Registration</dt>
                        <dd class="font-medium text-orange-600">
                            {{ $academicTerm->late_registration_start_date?->format('d M') }} →
                            {{ $academicTerm->late_registration_end_date?->format('d M Y') }}
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1">
                            Late fee: KES {{ number_format($academicTerm->late_registration_fee, 2) }}
                        </dd>
                    </div>
                @endif

                <div class="flex justify-between pt-2 border-t border-gray-200">
                    <dt class="text-gray-500">Currently Open?</dt>
                    <dd>
                        @if($academicTerm->isOpenForSelfEnrollment())
                            <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">
                                <i class="fas fa-check mr-1"></i> Yes
                            </span>
                        @else
                            <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
                                <i class="fas fa-times mr-1"></i> No
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Enrollments Summary --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-graduate text-primary mr-2"></i> Enrollments
            </h3>
            <span class="text-sm text-gray-500">{{ $enrollments->total() }} total</span>
        </div>

        @if($enrollments->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                <p class="text-gray-500">No enrollments for this term yet</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($enrollments as $enrollment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2">
                                    <p class="font-medium text-gray-800">{{ $enrollment->student_name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $enrollment->student_number }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="text-gray-800">{{ $enrollment->course_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->course_code }}</p>
                                </td>
                                <td class="px-3 py-2 text-right font-medium">
                                    KES {{ number_format($enrollment->total_fees, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right text-green-600 font-medium">
                                    KES {{ number_format($enrollment->amount_paid, 2) }}
                                </td>
                                <td class="px-3 py-2 text-right font-bold {{ $enrollment->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    KES {{ number_format($enrollment->balance, 2) }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span class="text-xs px-2 py-0.5 bg-{{ $enrollment->status_color }}-100 text-{{ $enrollment->status_color }}-700 rounded-full capitalize">
                                        {{ $enrollment->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($enrollments->hasPages())
                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Notes --}}
    @if($academicTerm->description || $academicTerm->notes)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-sticky-note text-primary mr-2"></i> Notes
            </h3>
            @if($academicTerm->description)
                <p class="text-sm text-gray-700 mb-2">{{ $academicTerm->description }}</p>
            @endif
            @if($academicTerm->notes)
                <p class="text-sm text-gray-500 italic">{{ $academicTerm->notes }}</p>
            @endif
        </div>
    @endif
</div>
@endsection