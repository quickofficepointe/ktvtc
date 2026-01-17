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
                <h1 class="text-3xl font-bold text-gray-900">Attendance Management</h1>
                <p class="text-gray-600 mt-2">Manage attendance sessions and tracking</p>
            </div>
            <button onclick="openCreateModal()"
                class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                New Session
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Sessions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $attendances->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Locked Sessions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('is_locked', true)->count() }}</p>
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
                    <p class="text-sm font-medium text-gray-600">Today's Sessions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $attendances->where('attendance_date', today())->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Attendances Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Attendance Sessions</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/4 align-top">
                            Session Details
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Course & Subject
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Timing & Location
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Recording Method
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Statistics
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/6 align-top">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50 transition-colors duration-150 align-top">
                            {{-- Session Details --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $attendance->session_name ?? 'General Session' }}</h4>
                                        <p class="text-xs text-gray-600">{{ $attendance->attendance_date->format('M d, Y') }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($attendance->attendable_type) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                            #{{ $attendance->attendable_id }}
                                        </span>
                                    </div>
                                    @if($attendance->mobileSchool)
                                        <div class="text-xs text-gray-500">
                                            {{ $attendance->mobileSchool->name }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Course & Subject --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    @if($attendance->course)
                                        <div class="text-primary">
                                            <p class="text-sm font-medium">{{ $attendance->course->course_name }}</p>
                                            <p class="text-xs">{{ $attendance->course->course_code }}</p>
                                        </div>
                                    @endif
                                    @if($attendance->subject)
                                        <div>
                                            <p class="text-sm text-gray-900">{{ $attendance->subject->subject_name }}</p>
                                            <p class="text-xs text-gray-600">{{ $attendance->subject->subject_code }}</p>
                                        </div>
                                    @endif
                                    @if(!$attendance->course && !$attendance->subject)
                                        <span class="text-xs text-gray-500">General Session</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Timing & Location --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    @if($attendance->start_time && $attendance->end_time)
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('h:i A') }} -
                                            {{ \Carbon\Carbon::parse($attendance->end_time)->format('h:i A') }}
                                        </div>
                                    @endif
                                    @if($attendance->venue)
                                        <div class="text-xs text-gray-600">
                                            {{ $attendance->venue }}
                                            @if($attendance->room)
                                                ({{ $attendance->room }})
                                            @endif
                                        </div>
                                    @endif
                                    @if($attendance->is_geofenced)
                                        <div class="text-xs text-green-600 font-medium">
                                            Geofenced
                                        </div>
                                    @endif
                                    @if($attendance->allow_late_marking && $attendance->late_threshold_minutes)
                                        <div class="text-xs text-gray-500">
                                            Late threshold: {{ $attendance->late_threshold_minutes }} mins
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Recording Method --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-2 py-1 text-xs rounded-full {{ $attendance->recording_method === 'qr_code' ? 'bg-purple-100 text-purple-800' : ($attendance->recording_method === 'biometric' ? 'bg-green-100 text-green-800' : ($attendance->recording_method === 'mobile_app' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->recording_method)) }}
                                    </span>
                                    @if($attendance->qr_code_data)
                                        <div class="text-xs text-primary">
                                            QR Code Available
                                        </div>
                                    @endif
                                    @if($attendance->topic_covered)
                                        <div class="text-xs text-gray-500 line-clamp-2">
                                            {{ $attendance->topic_covered }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Statistics --}}
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-1">
                                    <div class="text-sm text-gray-900">
                                        Expected: {{ $attendance->total_expected }}
                                    </div>
                                    <div class="grid grid-cols-2 gap-1 text-xs">
                                        <div class="text-green-600">Present: {{ $attendance->total_present }}</div>
                                        <div class="text-red-600">Absent: {{ $attendance->total_absent }}</div>
                                        <div class="text-yellow-600">Late: {{ $attendance->total_late }}</div>
                                        <div class="text-blue-600">Leave: {{ $attendance->total_leave }}</div>
                                    </div>
                                    @if($attendance->total_expected > 0)
                                        @php
                                            $attendanceRate = ($attendance->total_present / $attendance->total_expected) * 100;
                                        @endphp
                                        <div class="text-xs text-gray-500">
                                            Rate: {{ number_format($attendanceRate, 1) }}%
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 align-top">
                                <div class="flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <button onclick="openEditModal(
                                            '{{ $attendance->attendance_id }}',
                                            '{{ $attendance->attendance_date->format('Y-m-d') }}',
                                            '{{ $attendance->start_time }}',
                                            '{{ $attendance->end_time }}',
                                            '{{ $attendance->session_name }}',
                                            '{{ $attendance->attendable_type }}',
                                            '{{ $attendance->attendable_id }}',
                                            '{{ $attendance->subject_id }}',
                                            '{{ $attendance->course_id }}',
                                            '{{ $attendance->mobile_school_id }}',
                                            '{{ $attendance->venue }}',
                                            '{{ $attendance->room }}',
                                            '{{ $attendance->latitude }}',
                                            '{{ $attendance->longitude }}',
                                            '{{ $attendance->recording_method }}',
                                            '{{ $attendance->qr_code_data }}',
                                            '{{ $attendance->is_geofenced }}',
                                            '{{ $attendance->is_active }}',
                                            '{{ $attendance->is_locked }}',
                                            '{{ $attendance->allow_late_marking }}',
                                            '{{ $attendance->late_threshold_minutes }}',
                                            '{{ $attendance->total_expected }}',
                                            `{{ addslashes($attendance->topic_covered) }}`,
                                            `{{ addslashes($attendance->remarks) }}`
                                        )" class="px-3 py-1 text-xs rounded bg-blue-50 text-blue-600 hover:bg-blue-100">Edit</button>
                                        <button onclick="confirmDelete('{{ route('attendances.destroy', $attendance->attendance_id) }}')" class="px-3 py-1 text-xs rounded bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('attendances.records', $attendance->attendance_id) }}"
                                           class="px-3 py-1 text-xs rounded bg-green-50 text-green-600 hover:bg-green-100 text-center">
                                            Records
                                        </a>
                                        @if($attendance->is_locked)
                                            <form action="{{ route('attendances.unlock', $attendance->attendance_id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-1 text-xs rounded bg-yellow-50 text-yellow-600 hover:bg-yellow-100">
                                                    Unlock
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('attendances.lock', $attendance->attendance_id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-1 text-xs rounded bg-purple-50 text-purple-600 hover:bg-purple-100">
                                                    Lock
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    @if($attendance->recording_method === 'qr_code')
                                        <form action="{{ route('attendances.generate-qrcode', $attendance->attendance_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-full px-3 py-1 text-xs rounded bg-indigo-50 text-indigo-600 hover:bg-indigo-100">
                                                Generate QR
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('attendances.update-statistics', $attendance->attendance_id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="w-full px-3 py-1 text-xs rounded bg-gray-50 text-gray-600 hover:bg-gray-100">
                                            Update Stats
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-500 mb-2">No attendance sessions found</p>
                                    <p class="text-sm text-gray-400">Get started by creating your first attendance session.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Attendance Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 rounded-lg bg-white bg-opacity-20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Create Attendance Session</h3>
                            <p class="text-red-100 text-sm">Create a new attendance tracking session</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()" class="text-white hover:text-red-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="createForm" action="{{ route('attendances.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Session Details -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Session Details</h4>

                            <div>
                                <label for="session_name" class="block text-sm font-medium text-gray-700 mb-1">Session Name</label>
                                <input type="text" id="session_name" name="session_name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" id="attendance_date" name="attendance_date"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                    <input type="time" id="start_time" name="start_time"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                    <input type="time" id="end_time" name="end_time"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Course & Subject -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Course & Subject</h4>

                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                                <select id="course_id" name="course_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->course_id }}">{{ $course->course_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <select id="subject_id" name="subject_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->subject_id }}">{{ $subject->subject_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Location Details -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Location</h4>

                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                                <input type="text" id="venue" name="venue"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div>
                                <label for="room" class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                                <input type="text" id="room" name="room"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                    <input type="text" id="latitude" name="latitude"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                    <input type="text" id="longitude" name="longitude"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Recording Settings -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Recording Settings</h4>

                            <div>
                                <label for="recording_method" class="block text-sm font-medium text-gray-700 mb-1">Recording Method</label>
                                <select id="recording_method" name="recording_method"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="manual">Manual</option>
                                    <option value="qr_code">QR Code</option>
                                    <option value="biometric">Biometric</option>
                                    <option value="mobile_app">Mobile App</option>
                                </select>
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="checkbox" id="is_geofenced" name="is_geofenced" value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="is_geofenced" class="ml-2 text-sm text-gray-700">Enable Geofencing</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="allow_late_marking" name="allow_late_marking" value="1"
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="allow_late_marking" class="ml-2 text-sm text-gray-700">Allow Late Marking</label>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" id="is_active" name="is_active" value="1" checked
                                           class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active Session</label>
                                </div>
                            </div>

                            <div id="late_threshold_container" class="hidden">
                                <label for="late_threshold_minutes" class="block text-sm font-medium text-gray-700 mb-1">Late Threshold (minutes)</label>
                                <input type="number" id="late_threshold_minutes" name="late_threshold_minutes" min="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="md:col-span-2 space-y-4">
                            <h4 class="text-lg font-semibold text-gray-900 border-b pb-2">Additional Information</h4>

                            <div>
                                <label for="topic_covered" class="block text-sm font-medium text-gray-700 mb-1">Topic Covered</label>
                                <textarea id="topic_covered" name="topic_covered" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                            </div>

                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                <textarea id="remarks" name="remarks" rows="2"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                            Create Session
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Similar structure to create modal but for editing -->
    <!-- Content would be populated via JavaScript -->
</div>

<script>
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openEditModal(attendanceId, date, startTime, endTime, sessionName, attendableType, attendableId, subjectId, courseId, mobileSchoolId, venue, room, latitude, longitude, recordingMethod, qrCodeData, isGeofenced, isActive, isLocked, allowLateMarking, lateThresholdMinutes, totalExpected, topicCovered, remarks) {
    // Populate edit form with data and show modal
    // Implementation would be similar to create modal but with existing values
}

function confirmDelete(url) {
    if (confirm('Are you sure you want to delete this attendance session? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle late threshold visibility
document.getElementById('allow_late_marking').addEventListener('change', function() {
    document.getElementById('late_threshold_container').classList.toggle('hidden', !this.checked);
});
</script>
@endsection
