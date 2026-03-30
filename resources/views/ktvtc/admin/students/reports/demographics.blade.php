@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Demographics Report')
@section('subtitle', 'Analyze student demographics and distributions')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Students</span>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Reports</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Demographics</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportToExcel()"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Report</span>
    </button>
    <a href="{{ route('admin.students.index') }}"
       class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Students</span>
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Students</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalStudents ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Male</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($genderDistribution->where('gender', 'male')->first()->count ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-mars text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ $totalStudents > 0 ? round(($genderDistribution->where('gender', 'male')->first()->count ?? 0) / $totalStudents * 100, 1) : 0 }}% of total
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Female</p>
                <p class="text-3xl font-bold text-pink-600 mt-2">{{ number_format($genderDistribution->where('gender', 'female')->first()->count ?? 0) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-pink-50 flex items-center justify-center">
                <i class="fas fa-venus text-pink-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-2 text-sm text-gray-500">
            {{ $totalStudents > 0 ? round(($genderDistribution->where('gender', 'female')->first()->count ?? 0) / $totalStudents * 100, 1) : 0 }}% of total
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Average Age</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ round($averageAge ?? 0, 1) }}</p>
            </div>
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Gender Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Gender Distribution</h3>
        <div class="h-64">
            <canvas id="genderChart"></canvas>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <span class="text-sm text-gray-600">Male</span>
                <p class="text-xl font-bold text-blue-600">{{ number_format($genderDistribution->where('gender', 'male')->first()->count ?? 0) }}</p>
            </div>
            <div class="text-center p-3 bg-pink-50 rounded-lg">
                <span class="text-sm text-gray-600">Female</span>
                <p class="text-xl font-bold text-pink-600">{{ number_format($genderDistribution->where('gender', 'female')->first()->count ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Age Distribution Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Age Distribution</h3>
        <div class="h-64">
            <canvas id="ageChart"></canvas>
        </div>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- County Distribution -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Counties</h3>
        <div class="space-y-4">
            @forelse($countyDistribution as $county)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ $county->county ?: 'Not Specified' }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($county->count) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $percentage = $totalStudents > 0 ? round(($county->count / $totalStudents) * 100, 1) : 0;
                    @endphp
                    <div class="bg-primary rounded-full h-2" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% of total</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No county data available</p>
            @endforelse
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Categories</h3>
        <div class="space-y-4">
            @php
                $categoryColors = [
                    'regular' => 'bg-green-500',
                    'alumnus' => 'bg-purple-500',
                    'staff_child' => 'bg-blue-500',
                    'sponsored' => 'bg-amber-500',
                    'scholarship' => 'bg-indigo-500',
                ];
            @endphp
            @forelse($categoryDistribution as $category)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $category->student_category ?: 'Not Specified')) }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($category->count) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $percentage = $totalStudents > 0 ? round(($category->count / $totalStudents) * 100, 1) : 0;
                        $color = $categoryColors[$category->student_category] ?? 'bg-gray-500';
                    @endphp
                    <div class="{{ $color }} rounded-full h-2" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% of total</p>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">No category data available</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Status Distribution Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Status Distribution</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($statusDistribution as $status)
            <div class="bg-gray-50 p-4 rounded-lg text-center">
                <span class="text-sm text-gray-600">{{ ucfirst($status->status ?: 'Not Specified') }}</span>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($status->count) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $totalStudents > 0 ? round(($status->count / $totalStudents) * 100, 1) : 0 }}%
                </p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Age Range Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Age Range Breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age Range</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number of Students</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($ageRanges as $range => $count)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <span class="font-medium text-gray-900">
                            @php
                                $label = match($range) {
                                    'under_18' => 'Under 18 years',
                                    '18_25' => '18 - 25 years',
                                    '26_35' => '26 - 35 years',
                                    '36_45' => '36 - 45 years',
                                    '46_plus' => '46 years and above',
                                    default => $range
                                };
                            @endphp
                            {{ $label }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-gray-900 font-medium">{{ number_format($count) }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $percentage = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center">
                            <span class="text-gray-600 mr-3">{{ $percentage }}%</span>
                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity modal-overlay" onclick="closeModal('exportModal')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Export Demographics Report</h3>
                    <button onclick="closeModal('exportModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('admin.students.export') }}" method="GET" id="exportForm">
                    <input type="hidden" name="report" value="demographics">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                            <select name="format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="xlsx">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Sections</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_gender" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">Gender Distribution</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_age" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">Age Distribution</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_county" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">County Distribution</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_category" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">Student Categories</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="include_status" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-600">Status Distribution</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_charts" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-600">Include charts in PDF</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button onclick="closeModal('exportModal')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitExport()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Chart
        const genderCtx = document.getElementById('genderChart')?.getContext('2d');
        if (genderCtx) {
            const male = {{ $genderDistribution->where('gender', 'male')->first()->count ?? 0 }};
            const female = {{ $genderDistribution->where('gender', 'female')->first()->count ?? 0 }};
            const other = {{ $genderDistribution->where('gender', 'other')->first()->count ?? 0 }};

            new Chart(genderCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Male', 'Female', 'Other'],
                    datasets: [{
                        data: [male, female, other],
                        backgroundColor: ['#3B82F6', '#EC4899', '#8B5CF6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    }
                }
            });
        }

        // Age Chart
        const ageCtx = document.getElementById('ageChart')?.getContext('2d');
        if (ageCtx) {
            new Chart(ageCtx, {
                type: 'bar',
                data: {
                    labels: ['Under 18', '18-25', '26-35', '36-45', '46+'],
                    datasets: [{
                        label: 'Number of Students',
                        data: [
                            {{ $ageRanges['under_18'] ?? 0 }},
                            {{ $ageRanges['18_25'] ?? 0 }},
                            {{ $ageRanges['26_35'] ?? 0 }},
                            {{ $ageRanges['36_45'] ?? 0 }},
                            {{ $ageRanges['46_plus'] ?? 0 }}
                        ],
                        backgroundColor: '#3B82F6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#E5E7EB'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });

    function exportToExcel() {
        openModal('exportModal');
    }

    function submitExport() {
        document.getElementById('exportForm').submit();
    }

    // Modal functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('exportModal');
        }
    });
</script>

<style>
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
@endsection
