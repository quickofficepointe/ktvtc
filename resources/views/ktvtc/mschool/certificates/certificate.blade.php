@extends('ktvtc.mschool.layout.mschoollayout')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <div class="flex items-center mb-2">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 font-medium">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Certificates Management</h1>
                <p class="text-gray-600 mt-2">Manage student certificates and verifications</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Issue Certificate
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Certificates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certificates->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Issued</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certificates->where('status', 'issued')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Revoked</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certificates->where('is_revoked', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Expired</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certificates->where('status', 'expired')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Certificates Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Certificates</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Certificate Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Student & Course
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Dates & Validity
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Verification
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Status
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse($certificates as $certificate)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Certificate Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $certificate->certificate_number }}</h4>
                                        @if($certificate->serial_number)
                                            <p class="text-xs text-gray-600">Serial: {{ $certificate->serial_number }}</p>
                                        @endif
                                    </div>
                                    <div class="text-primary">
                                        <p class="text-sm font-medium">{{ $certificate->template->template_name }}</p>
                                        <p class="text-xs">{{ $certificate->template->template_type }}</p>
                                    </div>
                                    @if($certificate->generated_pdf_path)
                                        <div class="text-xs">
                                            <a href="{{ route('certificates.download', $certificate->certificate_id) }}"
                                               class="text-primary hover:underline flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Download PDF
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Student & Course --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 text-sm">{{ $certificate->student->full_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $certificate->student->student_code }}</p>
                                    </div>
                                    <div class="text-primary">
                                        <p class="text-sm font-medium">{{ $certificate->course->course_name }}</p>
                                        <p class="text-xs">{{ $certificate->course->course_code }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Dates & Validity --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">
                                        Issued: {{ $certificate->issue_date->format('M d, Y') }}
                                    </div>
                                    @if($certificate->expiry_date)
                                        <div class="text-xs {{ $certificate->isExpired() ? 'text-red-600' : 'text-gray-600' }}">
                                            Expires: {{ $certificate->expiry_date->format('M d, Y') }}
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-500">No expiry</div>
                                    @endif
                                    @if($certificate->generated_at)
                                        <div class="text-xs text-gray-500">
                                            Generated: {{ $certificate->generated_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                    @if($certificate->is_revoked && $certificate->revoked_date)
                                        <div class="text-xs text-red-600">
                                            Revoked: {{ $certificate->revoked_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Verification --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $certificate->is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $certificate->is_verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                    @if($certificate->verification_url)
                                        <div class="text-xs">
                                            <a href="{{ $certificate->verification_url }}" target="_blank" class="text-primary hover:underline flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                                Verify URL
                                            </a>
                                        </div>
                                    @endif
                                    @if($certificate->qr_code_data)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            QR Code
                                        </span>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        Views: {{ $certificate->view_count }}, Downloads: {{ $certificate->download_count }}
                                    </div>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $certificate->status === 'issued' ? 'bg-green-100 text-green-800' : ($certificate->status === 'revoked' ? 'bg-red-100 text-red-800' : ($certificate->status === 'expired' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($certificate->status) }}
                                    </span>
                                    @if($certificate->is_revoked)
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            Revoked
                                        </span>
                                    @endif
                                    @if($certificate->isValid())
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            Valid
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                            Invalid
                                        </span>
                                    @endif
                                    <div class="flex flex-wrap gap-1">
                                        @if($certificate->allow_download)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-green-100 text-green-800">Download</span>
                                        @endif
                                        @if($certificate->allow_sharing)
                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs rounded bg-blue-100 text-blue-800">Sharing</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <button onclick="openEditModal(
                                            '{{ $certificate->certificate_id }}',
                                            '{{ $certificate->template_id }}',
                                            '{{ $certificate->student_id }}',
                                            '{{ $certificate->enrollment_id }}',
                                            '{{ $certificate->course_id }}',
                                            '{{ $certificate->certificate_number }}',
                                            '{{ $certificate->serial_number }}',
                                            `{{ json_encode($certificate->certificate_data) }}`,
                                            '{{ $certificate->issue_date->format('Y-m-d') }}',
                                            '{{ $certificate->expiry_date ? $certificate->expiry_date->format('Y-m-d') : '' }}',
                                            '{{ $certificate->status }}',
                                            '{{ $certificate->is_verified }}',
                                            '{{ $certificate->verification_url }}',
                                            '{{ $certificate->qr_code_data }}',
                                            `{{ addslashes($certificate->issuance_remarks) }}`,
                                            '{{ $certificate->is_revoked }}',
                                            '{{ $certificate->revoked_date ? $certificate->revoked_date->format('Y-m-d') : '' }}',
                                            `{{ addslashes($certificate->revocation_reason) }}`,
                                            '{{ $certificate->allow_download }}',
                                            '{{ $certificate->allow_sharing }}'
                                        )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                        <button onclick="confirmDelete('{{ route('certificates.destroy', $certificate->certificate_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                    </div>
                                    @if($certificate->generated_pdf_path)
                                        <a href="{{ route('certificates.download', $certificate->certificate_id) }}"
                                           class="px-3 py-1 text-xs rounded bg-green-50 text-green-600 hover:bg-green-100 text-center">
                                            Download
                                        </a>
                                    @endif
                                    @if(!$certificate->is_revoked && $certificate->status === 'issued')
                                        <button onclick="openRevokeModal('{{ $certificate->certificate_id }}')"
                                                class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">
                                            Revoke
                                        </button>
                                    @endif
                                    @if($certificate->is_revoked)
                                        <form action="{{ route('certificates.restore', $certificate->certificate_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-full px-3 py-1 text-xs rounded bg-green-50 text-green-600 hover:bg-green-100">
                                                Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500 mb-2">No certificates found</p>
                                    <p class="text-sm text-gray-400">Get started by issuing your first certificate.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Certificate Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Issue New Certificate</h3>
                            <p class="text-red-100 text-sm">Create and issue a certificate to student</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form action="{{ route('certificates.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Certificate Identification -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Certificate Identification
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number *</label>
                                <input type="text" name="certificate_number" value="{{ old('certificate_number') }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., CERT-2024-001">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Serial Number</label>
                                <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="e.g., SN-2024-001">
                            </div>
                        </div>
                    </div>

                    <!-- Associated Entities -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Associated Entities
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template *</label>
                                <select name="template_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Template</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->template_id }}" {{ old('template_id') == $template->template_id ? 'selected' : '' }}>
                                            {{ $template->template_name }} ({{ $template->template_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                <select name="student_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                                            {{ $student->full_name }} ({{ $student->student_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment *</label>
                                <select name="enrollment_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Enrollment</option>
                                    @foreach($enrollments as $enrollment)
                                        <option value="{{ $enrollment->enrollment_id }}" {{ old('enrollment_id') == $enrollment->enrollment_id ? 'selected' : '' }}>
                                            {{ $enrollment->student->full_name }} - {{ $enrollment->course->course_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select name="course_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}" {{ old('course_id') == $course->course_id ? 'selected' : '' }}>
                                            {{ $course->course_name }} ({{ $course->course_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Data -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                            </svg>
                            Certificate Content
                        </h4>
                        <div id="certificateDataFields" class="space-y-4">
                            <!-- Dynamic fields will be populated based on template selection -->
                            <div class="text-center text-gray-500 py-4" id="noDataFieldsMessage">
                                Select a template to configure certificate content
                            </div>
                        </div>
                    </div>

                    <!-- Dates & Validity -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Dates & Validity
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date *</label>
                                <input type="date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Status & Verification -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Status & Verification
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select name="status" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="generated" {{ old('status') == 'generated' ? 'selected' : '' }}>Generated</option>
                                    <option value="issued" {{ old('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                                    <option value="revoked" {{ old('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_verified" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('is_verified', true) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Verified Certificate</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="allow_download" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('allow_download', true) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Allow Download</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="allow_sharing" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary"
                                           {{ old('allow_sharing', true) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Allow Sharing</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Additional Information
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Verification URL</label>
                                <input type="url" name="verification_url" value="{{ old('verification_url') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="https://example.com/verify/...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Data</label>
                                <input type="text" name="qr_code_data" value="{{ old('qr_code_data') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                       placeholder="Data for QR code generation">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Issuance Remarks</label>
                                <textarea name="issuance_remarks" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                          placeholder="Additional remarks about this certificate issuance">{{ old('issuance_remarks') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Issue Certificate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Certificate Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Edit Certificate</h3>
                            <p class="text-red-100 text-sm">Update certificate information</p>
                        </div>
                    </div>
                    <button onclick="closeEditModal()"
                            class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Certificate Identification -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Certificate Identification
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="editCertificateId" name="certificate_id">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number *</label>
                                <input type="text" id="editCertificateNumber" name="certificate_number" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Serial Number</label>
                                <input type="text" id="editSerialNumber" name="serial_number"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Associated Entities -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Associated Entities
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Template *</label>
                                <select id="editTemplateId" name="template_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Template</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->template_id }}">{{ $template->template_name }} ({{ $template->template_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student *</label>
                                <select id="editStudentId" name="student_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}">{{ $student->full_name }} ({{ $student->student_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enrollment *</label>
                                <select id="editEnrollmentId" name="enrollment_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Enrollment</option>
                                    @foreach($enrollments as $enrollment)
                                        <option value="{{ $enrollment->enrollment_id }}">{{ $enrollment->student->full_name }} - {{ $enrollment->course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select id="editCourseId" name="course_id" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}">{{ $course->course_name }} ({{ $course->course_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Data -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                            </svg>
                            Certificate Content
                        </h4>
                        <div id="editCertificateDataFields" class="space-y-4">
                            <!-- Dynamic fields will be populated based on template selection -->
                        </div>
                    </div>

                    <!-- Dates & Validity -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Dates & Validity
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date *</label>
                                <input type="date" id="editIssueDate" name="issue_date" required
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                <input type="date" id="editExpiryDate" name="expiry_date"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>
                        </div>
                    </div>

                    <!-- Status & Verification -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Status & Verification
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                <select id="editStatus" name="status" required
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    <option value="draft">Draft</option>
                                    <option value="generated">Generated</option>
                                    <option value="issued">Issued</option>
                                    <option value="revoked">Revoked</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>

                            <div class="space-y-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editIsVerified" name="is_verified" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Verified Certificate</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editAllowDownload" name="allow_download" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Allow Download</span>
                                </label>

                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editAllowSharing" name="allow_sharing" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm text-gray-700">Allow Sharing</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Additional Information
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Verification URL</label>
                                <input type="url" id="editVerificationUrl" name="verification_url"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Data</label>
                                <input type="text" id="editQrCodeData" name="qr_code_data"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Issuance Remarks</label>
                                <textarea id="editIssuanceRemarks" name="issuance_remarks" rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                            </div>

                            <div class="border-t pt-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="editIsRevoked" name="is_revoked" value="1"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="text-sm font-medium text-gray-700">Revoke Certificate</span>
                                </label>

                                <div id="revocationDetails" class="mt-3 space-y-3 hidden">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Revocation Date</label>
                                        <input type="date" id="editRevokedDate" name="revoked_date"
                                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Revocation Reason</label>
                                        <textarea id="editRevocationReason" name="revocation_reason" rows="3"
                                                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                            Update Certificate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Revoke Certificate Modal -->
<div id="revokeModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeRevokeModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Revoke Certificate</h3>
                        <p class="text-gray-600 text-sm">This action cannot be undone.</p>
                    </div>
                </div>

                <form id="revokeForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Revocation Date *</label>
                            <input type="date" name="revoked_date" value="{{ date('Y-m-d') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Revocation Reason *</label>
                            <textarea name="revocation_reason" rows="4" required
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                                      placeholder="Provide reason for revoking this certificate..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeRevokeModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                            Revoke Certificate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeDeleteModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Delete Certificate</h3>
                        <p class="text-gray-600 text-sm">This action cannot be undone.</p>
                    </div>
                </div>

                <p class="text-gray-700 mb-6">Are you sure you want to delete this certificate? All associated data will be permanently removed.</p>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors">
                            Delete Certificate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Template data storage
let templatesData = @json($templates->keyBy('template_id')->toArray());

// Modal Functions
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openEditModal(
    certificateId, templateId, studentId, enrollmentId, courseId, certificateNumber,
    serialNumber, certificateData, issueDate, expiryDate, status, isVerified,
    verificationUrl, qrCodeData, issuanceRemarks, isRevoked, revokedDate,
    revocationReason, allowDownload, allowSharing
) {
    // Set form action
    document.getElementById('editForm').action = `/certificates/${certificateId}`;

    // Populate form fields
    document.getElementById('editCertificateId').value = certificateId;
    document.getElementById('editTemplateId').value = templateId;
    document.getElementById('editStudentId').value = studentId;
    document.getElementById('editEnrollmentId').value = enrollmentId;
    document.getElementById('editCourseId').value = courseId;
    document.getElementById('editCertificateNumber').value = certificateNumber || '';
    document.getElementById('editSerialNumber').value = serialNumber || '';
    document.getElementById('editIssueDate').value = issueDate || '';
    document.getElementById('editExpiryDate').value = expiryDate || '';
    document.getElementById('editStatus').value = status;
    document.getElementById('editIsVerified').checked = isVerified === '1';
    document.getElementById('editVerificationUrl').value = verificationUrl || '';
    document.getElementById('editQrCodeData').value = qrCodeData || '';
    document.getElementById('editIssuanceRemarks').value = issuanceRemarks || '';
    document.getElementById('editIsRevoked').checked = isRevoked === '1';
    document.getElementById('editRevokedDate').value = revokedDate || '';
    document.getElementById('editRevocationReason').value = revocationReason || '';
    document.getElementById('editAllowDownload').checked = allowDownload === '1';
    document.getElementById('editAllowSharing').checked = allowSharing === '1';

    // Toggle revocation details
    toggleRevocationDetailsEdit();

    // Load certificate data fields
    loadCertificateDataFieldsEdit(templateId, JSON.parse(certificateData || '{}'));

    document.getElementById('editModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openRevokeModal(certificateId) {
    document.getElementById('revokeForm').action = `/certificates/${certificateId}/revoke`;
    document.getElementById('revokeModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeRevokeModal() {
    document.getElementById('revokeModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function confirmDelete(deleteUrl) {
    document.getElementById('deleteForm').action = deleteUrl;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Template selection handler
function handleTemplateSelection(templateId, isEdit = false) {
    const containerId = isEdit ? 'editCertificateDataFields' : 'certificateDataFields';
    const container = document.getElementById(containerId);

    if (!templateId) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">Select a template to configure certificate content</div>';
        return;
    }

    const template = templatesData[templateId];
    if (!template || !template.dynamic_fields) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">No dynamic fields configured for this template</div>';
        return;
    }

    let fieldsHtml = '';
    const dynamicFields = JSON.parse(template.dynamic_fields);

    dynamicFields.forEach((field, index) => {
        const fieldName = field.field_name;
        const fieldLabel = fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        fieldsHtml += `
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <label class="block text-sm font-medium text-gray-700 mb-2">${fieldLabel}</label>
                <input type="text" name="certificate_data[${fieldName}]"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Enter ${fieldLabel.toLowerCase()}">
            </div>
        `;
    });

    container.innerHTML = fieldsHtml;
}

function loadCertificateDataFieldsEdit(templateId, certificateData) {
    const container = document.getElementById('editCertificateDataFields');

    if (!templateId) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">Select a template to configure certificate content</div>';
        return;
    }

    const template = templatesData[templateId];
    if (!template || !template.dynamic_fields) {
        container.innerHTML = '<div class="text-center text-gray-500 py-4">No dynamic fields configured for this template</div>';
        return;
    }

    let fieldsHtml = '';
    const dynamicFields = JSON.parse(template.dynamic_fields);

    dynamicFields.forEach((field, index) => {
        const fieldName = field.field_name;
        const fieldLabel = fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const fieldValue = certificateData[fieldName] || '';

        fieldsHtml += `
            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                <label class="block text-sm font-medium text-gray-700 mb-2">${fieldLabel}</label>
                <input type="text" name="certificate_data[${fieldName}]" value="${fieldValue}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Enter ${fieldLabel.toLowerCase()}">
            </div>
        `;
    });

    container.innerHTML = fieldsHtml;
}

// Toggle revocation details
function toggleRevocationDetailsEdit() {
    const isRevoked = document.getElementById('editIsRevoked').checked;
    document.getElementById('revocationDetails').classList.toggle('hidden', !isRevoked);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Create modal template change
    const createTemplateSelect = document.querySelector('select[name="template_id"]');
    if (createTemplateSelect) {
        createTemplateSelect.addEventListener('change', function() {
            handleTemplateSelection(this.value, false);
        });
    }

    // Edit modal template change
    const editTemplateSelect = document.getElementById('editTemplateId');
    if (editTemplateSelect) {
        editTemplateSelect.addEventListener('change', function() {
            handleTemplateSelection(this.value, true);
        });
    }

    // Edit modal revocation toggle
    const revokeCheckbox = document.getElementById('editIsRevoked');
    if (revokeCheckbox) {
        revokeCheckbox.addEventListener('change', toggleRevocationDetailsEdit);
    }

    // Auto-generate certificate number
    const certNumberInput = document.querySelector('input[name="certificate_number"]');
    if (certNumberInput && !certNumberInput.value) {
        const now = new Date();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        certNumberInput.value = `CERT-${now.getFullYear()}-${random}`;
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
        closeRevokeModal();
        closeDeleteModal();
    }
});
</script>
@endsection
