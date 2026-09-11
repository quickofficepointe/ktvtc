@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure Management')
@section('subtitle', 'Manage per-term course fees')

@section('header-actions')
<div class="flex space-x-2">
    <a href="{{ route('finance.fee-structure.export') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export</span>
    </a>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Fee Structures</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Per-Term Fees</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats['per_term'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-blue-600 mt-2">{{ $stats['active'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Legacy (No Term)</p>
                    <p class="text-2xl font-bold text-gray-500 mt-2">{{ $stats['legacy'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-gray-50 flex items-center justify-center">
                    <i class="fas fa-history text-gray-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-6 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search Course</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Course name or code..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm">
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Academic Term</label>
                <select name="term_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ request('term_id') == $term->id ? 'selected' : '' }}>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Course</label>
                <select name="course_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary text-sm">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('finance.fee-structure.index') }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition text-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campus</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Fees</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($feeStructures as $fs)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $fs->course->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $fs->course->code ?? '' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($fs->term)
                                    <span class="text-sm font-medium text-gray-800">{{ $fs->term->name }}</span>
                                    <div class="text-xs text-gray-500">{{ $fs->term->academic_year }}</div>
                                @else
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">Legacy</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $fs->campus->name ?? 'All Campuses' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-primary">KES {{ number_format($fs->total_course_fee, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($fs->is_approved)
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full font-medium">
                                        <i class="fas fa-check-circle mr-1"></i> Approved
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded-full font-medium">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @endif

                                @if(!$fs->is_active)
                                    <div class="mt-1">
                                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">Inactive</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('finance.fee-structure.show', $fs) }}"
                                       class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('finance.fee-structure.edit', $fs) }}"
                                       class="p-2 text-gray-600 hover:text-amber-600 hover:bg-gray-100 rounded-lg transition"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('finance.fee-structure.destroy', $fs) }}" method="POST"
                                          onsubmit="return confirm('Delete this fee structure?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-file-invoice-dollar text-3xl text-gray-300 mb-3 block"></i>
                                <p class="text-lg font-medium">No fee structures yet</p>
                                <p class="text-sm mt-1">Go to a course to create its first term fee.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feeStructures->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $feeStructures->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection