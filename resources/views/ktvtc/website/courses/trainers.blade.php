@extends('ktvtc.website.layout.websitelayout')

@section('title', 'Assign Trainers — ' . $course->name)

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
                <a href="{{ route('website.courses.index') }}" class="hover:text-primary">Courses</a>
                <span class="mx-2">/</span>
                <span>Trainers</span>
            </div>
            <h1 class="text-2xl font-bold text-dark">{{ $course->name }}</h1>
            <p class="text-gray-600 mt-1">
                Manage trainers assigned to this course
                @if($course->code)
                    <span class="ml-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $course->code }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('website.courses.index') }}"
           class="inline-flex items-center text-primary hover:underline text-sm font-medium">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Courses
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ═══ Assign Form ═══ --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                <h2 class="font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Assign a Trainer
                </h2>
            </div>

            <div class="p-6">
                @if($availableTrainers->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">
                            All approved trainers are already assigned to this course.
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('website.courses.trainers.assign', $course) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Trainer <span class="text-red-500">*</span>
                            </label>
                            <select name="user_id" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                                <option value="">— Select a trainer —</option>
                                @foreach($availableTrainers as $trainer)
                                    <option value="{{ $trainer->id }}">
                                        {{ $trainer->name }}@if($trainer->email) ({{ $trainer->email }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="role"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                                <option value="trainer">Trainer</option>
                                <option value="lead">Lead Trainer</option>
                                <option value="assistant">Assistant</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg font-medium transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Assign Trainer
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ═══ Currently Assigned ═══ --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4 flex items-center justify-between">
                <h2 class="font-semibold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Currently Assigned
                </h2>
                <span class="bg-white bg-opacity-20 text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                    {{ $course->trainers->count() }}
                </span>
            </div>

            <div class="p-6">
                @if($course->trainers->isEmpty())
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">No trainers assigned to this course yet.</p>
                    </div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($course->trainers as $trainer)
                            <li class="py-3 flex items-center justify-between">
                                <div class="flex items-center min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-primary bg-opacity-10 flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $trainer->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $trainer->email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                    @php $role = $trainer->pivot->role ?? 'trainer'; @endphp
                                    <span class="text-xs px-2 py-1 rounded-full font-medium
                                        @if($role === 'lead') bg-yellow-100 text-yellow-700
                                        @elseif($role === 'assistant') bg-blue-100 text-blue-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ ucfirst($role) }}
                                    </span>

                                    <form method="POST"
                                          action="{{ route('website.courses.trainers.remove', [$course, $trainer]) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($trainer->name) }} from this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 hover:bg-red-50 p-1.5 rounded transition-colors"
                                                title="Remove trainer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Help hint --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800 flex items-start">
            <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span>
                Only approved users with the <strong>Trainer</strong> role appear in the picker.
                If a trainer is missing, ensure their account is approved and their role is set to Trainer (role 8).
            </span>
        </p>
    </div>
</div>
@endsection