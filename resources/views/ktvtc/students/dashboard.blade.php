@extends('ktvtc.students.layout.studentlayout')

@section('content')
<div class="relative">

    {{-- Main dashboard content --}}
    <div class="{{ !$isApproved ? 'blur-sm pointer-events-none select-none' : '' }}">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-gray-600">This is your student dashboard.</p>

        {{-- Example stats/cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div class="p-6 bg-white shadow rounded-2xl">
                <h2 class="font-semibold text-gray-700">Enrolled Courses</h2>
                <p class="text-3xl font-bold text-primary mt-2">3</p>
            </div>
            <div class="p-6 bg-white shadow rounded-2xl">
                <h2 class="font-semibold text-gray-700">Assignments Pending</h2>
                <p class="text-3xl font-bold text-primary mt-2">5</p>
            </div>
            <div class="p-6 bg-white shadow rounded-2xl">
                <h2 class="font-semibold text-gray-700">Library Books</h2>
                <p class="text-3xl font-bold text-primary mt-2">2</p>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    @if(!$isApproved)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Account Pending Approval</h2>
                <p class="text-gray-600 mb-6">
                    Your profile has been submitted and is awaiting admin approval.
                    You’ll be notified once your account is activated.
                </p>

                {{-- Logout --}}
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-xl shadow">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
