@extends('ktvtc.finance.layouts.app')

@section('title', 'High School Students')
@section('subtitle', 'Manage all high school students')

@section('header-actions')
<div class="flex flex-wrap gap-2">
    <a href="{{ route('finance.hs-students.create') }}" class="bg-primary hover:bg-primary-dark text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm shadow-md hover:shadow-lg transition">
        <i class="fas fa-plus mr-2"></i> Add Student
    </a>
    <a href="{{ route('finance.hs-students.import') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-import mr-2"></i> Import
    </a>
    <a href="{{ route('finance.hs-students.export') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg font-semibold flex items-center text-sm transition">
        <i class="fas fa-file-export mr-2"></i> Export
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="finance-card p-3">
            <p class="text-xs text-gray-500">Total Students</p>
            <p class="text-xl font-bold text-gray-800">{{ number_format($totalStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-green-500">
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-xl font-bold text-green-600">{{ number_format($activeStudents ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500">With Cards</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($studentsWithCards ?? 0) }}</p>
        </div>
        <div class="finance-card p-3 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500">Without Cards</p>
            <p class="text-xl font-bold text-yellow-600">{{ number_format($studentsWithoutCards ?? 0) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="finance-card p-3">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Admission, Phone..." class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary w-48 sm:w-64 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Class</label>
                <select name="class" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All Classes</option>
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class }}" {{ request('class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('finance.hs-students.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition text-sm ml-1">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="finance-card p-3 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Student</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Admission</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Class</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Parent Phone</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Card</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600">Balance</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($students ?? [] as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">
                                <div class="flex items-center">
                                    <div class="w-7 h-7 rounded-full bg-gray-200 overflow-hidden flex-shrink-0">
                                        @if($student->profile_picture)
                                            <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-gray-400 w-full h-full flex items-center justify-center text-xs"></i>
                                        @endif
                                    </div>
                                    <span class="ml-2 font-medium text-gray-800 text-sm truncate max-w-[120px]">{{ $student->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2 text-sm">{{ $student->admission_number }}</td>
                            <td class="px-3 py-2 text-sm">{{ $student->class }}</td>
                            <td class="px-3 py-2 text-sm">{{ $student->parent_phone ?? 'N/A' }}</td>
                            <td class="px-3 py-2">
                                @if($student->cardAccount)
                                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-600 rounded">Issued</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">No Card</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-medium">
                                KES {{ number_format($student->cardAccount->balance ?? 0, 2) }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="text-xs px-2 py-0.5 rounded
                                    @if($student->status === 'active') bg-green-100 text-green-600
                                    @elseif($student->status === 'inactive') bg-gray-100 text-gray-500
                                    @else bg-blue-100 text-blue-600 @endif">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <a href="{{ route('finance.hs-students.show', $student) }}" class="text-primary hover:text-primary-dark" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.hs-students.edit', $student) }}" class="text-blue-600 hover:text-blue-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$student->cardAccount)
                                        <a href="{{ route('finance.cards.create', ['student_id' => $student->id]) }}" class="text-green-600 hover:text-green-800" title="Issue Card">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500">No students found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 border-t pt-3">
            {{ $students->links() }}
        </div>
    </div>
</div>
@endsection
