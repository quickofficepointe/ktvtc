@extends('ktvtc.finance.layouts.app')

@section('title', 'Fee Structure')
@section('subtitle', 'Manage course fee structures')

@section('content')
<div class="space-y-6">
    <!-- Info Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600"></i>
        </div>
        <div>
            <h4 class="font-semibold text-blue-800">Fee Structure Management</h4>
            <p class="text-sm text-blue-700">Configure fee structures for different courses and programs. Changes will affect new enrollments.</p>
        </div>
    </div>

    <!-- Fee Structure Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <h3 class="font-bold text-gray-800 text-lg mb-4 flex items-center">
            <i class="fas fa-cog text-primary mr-2"></i>
            Default Fee Structure
        </h3>
        <form method="POST" action="{{ route('finance.settings.fee-structure.update') }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Default Registration Fee</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="registration_fee" value="{{ config('finance.registration_fee', 5000) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">One-time fee charged at registration</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Default Tuition Fee</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="tuition_fee" value="{{ config('finance.tuition_fee', 15000) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Per semester tuition fee</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Default Examination Fee</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="exam_fee" value="{{ config('finance.exam_fee', 2000) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Per semester examination fee</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Default Library Fee</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="library_fee" value="{{ config('finance.library_fee', 1000) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Per semester library fee</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Default Activity Fee</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="activity_fee" value="{{ config('finance.activity_fee', 500) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Per semester activity fee</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Late Payment Penalty</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">KES</span>
                        <input type="number" name="late_penalty" value="{{ config('finance.late_penalty', 500) }}" class="w-full pl-16 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Charged for late payments</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-semibold">
                    <i class="fas fa-save mr-2"></i> Save Fee Structure
                </button>
            </div>
        </form>
    </div>

    <!-- Course-specific Fee Structures -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-red-400 to-primary"></div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-list text-primary mr-2"></i>
                Course-Specific Fee Structures
            </h3>
            <button onclick="openModal('addCourseFeeModal')" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition text-sm font-semibold">
                <i class="fas fa-plus mr-2"></i> Add Course Fee
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-semibold">Course</th>
                        <th class="pb-2 font-semibold text-right">Tuition</th>
                        <th class="pb-2 font-semibold text-right">Registration</th>
                        <th class="pb-2 font-semibold text-right">Exam</th>
                        <th class="pb-2 font-semibold text-right">Library</th>
                        <th class="pb-2 font-semibold text-right">Total</th>
                        <th class="pb-2 font-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courseFees ?? [] as $fee)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 font-medium">{{ $fee->course->name ?? 'N/A' }}</td>
                            <td class="py-2 text-right">KES {{ number_format($fee->tuition, 2) }}</td>
                            <td class="py-2 text-right">KES {{ number_format($fee->registration, 2) }}</td>
                            <td class="py-2 text-right">KES {{ number_format($fee->exam, 2) }}</td>
                            <td class="py-2 text-right">KES {{ number_format($fee->library, 2) }}</td>
                            <td class="py-2 text-right font-bold">KES {{ number_format($fee->total, 2) }}</td>
                            <td class="py-2 text-center">
                                <button onclick="editCourseFee({{ $fee->id }})" class="text-primary hover:text-primary-dark mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteCourseFee({{ $fee->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-500">No course-specific fees configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Course Fee Modal -->
<div id="addCourseFeeModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Add Course Fee Structure</h3>
            <button onclick="closeModal('addCourseFeeModal')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('finance.settings.fee-structure.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                    <select name="course_id" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                        <option value="">Select Course</option>
                        @foreach($courses ?? [] as $course)
                            <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tuition Fee <span class="text-red-500">*</span></label>
                    <input type="number" name="tuition" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Registration Fee <span class="text-red-500">*</span></label>
                    <input type="number" name="registration" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Examination Fee <span class="text-red-500">*</span></label>
                    <input type="number" name="exam" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Library Fee <span class="text-red-500">*</span></label>
                    <input type="number" name="library" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal('addCourseFeeModal')" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark font-semibold transition-colors">
                    <i class="fas fa-save mr-2"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editCourseFee(id) {
        toastr.info('Edit functionality coming soon');
    }

    function deleteCourseFee(id) {
        if (confirm('Are you sure you want to delete this course fee structure?')) {
            toastr.info('Delete functionality coming soon');
        }
    }
</script>
@endpush
