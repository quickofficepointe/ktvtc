@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Fee Structures')
@section('subtitle', 'Manage TVET/CDACC fee structures and payment plans')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Fee Management</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Fee Structures</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="exportToExcel()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-download"></i>
        <span>Export Excel</span>
    </button>
    <button onclick="openModal('addFeeStructureModal')" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Add Fee Structure</span>
    </button>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Your statistics cards code remains the same -->
</div>

<!-- Filters Section -->
<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <!-- Your filters code remains the same -->
</div>

<!-- Fee Structures Table -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <!-- Your table code remains the same -->
</div>

<!-- Add Fee Structure Modal -->
<div id="addFeeStructureModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity modal-overlay" onclick="closeModal('addFeeStructureModal')"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-6xl mx-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Add New Fee Structure</h3>
                    <p class="text-sm text-gray-600 mt-1">Create a new fee structure for TVET/CDACC courses</p>
                </div>
                <button onclick="closeModal('addFeeStructureModal')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
                <form id="addFeeStructureForm" method="POST" action="{{ route('admin.fees.structures.store') }}">
                    @csrf

                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Basic Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course *</label>
                                <select name="course_id" required
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Campus *</label>
                                <select name="campus_id" required
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                    <option value="">Select Campus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- TVET/CDACC Details -->
                    <div class="mb-8">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">TVET/CDACC Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CDACC Program Code</label>
                                <input type="text" name="cdacc_program_code"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                       placeholder="e.g., CDACC-ICT-2024">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">TVET Qualification Type</label>
                                <select name="tvet_qualification_type"
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                    <option value="">Select Type</option>
                                    <option value="artisan">Artisan</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="higher_diploma">Higher Diploma</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Details -->
                    <div class="mb-8">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Academic Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year *</label>
                                <input type="number" name="academic_year" required min="2000" max="2100"
                                       value="{{ date('Y') }}"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Intake Month *</label>
                                <select name="intake_month" required
                                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                    <option value="">Select Month</option>
                                    @foreach($intakeMonths as $month)
                                        <option value="{{ $month }}">{{ ucfirst($month) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Duration (Months) *</label>
                                <input type="number" name="total_course_months" required min="1" max="48"
                                       placeholder="e.g., 6"
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Fee Breakdown -->
                    <div class="mb-8">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8" role="tablist" aria-label="Fee categories">
                                <button type="button"
                                        onclick="switchFeeTab('institution')"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm active"
                                        id="tab-institution"
                                        role="tab"
                                        aria-selected="true"
                                        aria-controls="feeTab-institution">
                                    Institution Fees
                                </button>
                                <button type="button"
                                        onclick="switchFeeTab('workshop')"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                        id="tab-workshop"
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="feeTab-workshop">
                                    Workshop Fees
                                </button>
                                <button type="button"
                                        onclick="switchFeeTab('cdacc')"
                                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm"
                                        id="tab-cdacc"
                                        role="tab"
                                        aria-selected="false"
                                        aria-controls="feeTab-cdacc">
                                    CDACC Fees
                                </button>
                            </nav>
                        </div>

                        <!-- Institution Fees Tab -->
                        <div id="feeTab-institution" class="fee-tab active p-4 bg-gray-50 rounded-b-lg" role="tabpanel" aria-labelledby="tab-institution">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Registration Fee *</label>
                                    <input type="number" name="registration_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tuition per Month *</label>
                                    <input type="number" name="tuition_per_month" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Caution Money *</label>
                                    <input type="number" name="caution_money" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Student ID Fee *</label>
                                    <input type="number" name="student_id_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Library Fee *</label>
                                    <input type="number" name="library_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Medical Fee *</label>
                                    <input type="number" name="medical_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sports Fee *</label>
                                    <input type="number" name="sports_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Activity Fee *</label>
                                    <input type="number" name="activity_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="institution">
                                </div>
                            </div>
                        </div>

                        <!-- Workshop Fees Tab -->
                        <div id="feeTab-workshop" class="fee-tab hidden p-4 bg-gray-50 rounded-b-lg" role="tabpanel" aria-labelledby="tab-workshop">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Workshop Levy *</label>
                                    <input type="number" name="workshop_levy" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="workshop">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Practical Materials *</label>
                                    <input type="number" name="practical_materials" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="workshop">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tool Kit Deposit *</label>
                                    <input type="number" name="tool_kit_deposit" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="workshop">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Protective Clothing *</label>
                                    <input type="number" name="protective_clothing" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="workshop">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Industrial Attachment Fee *</label>
                                    <input type="number" name="industrial_attachment_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="workshop">
                                </div>
                            </div>
                        </div>

                        <!-- CDACC Fees Tab -->
                        <div id="feeTab-cdacc" class="fee-tab hidden p-4 bg-gray-50 rounded-b-lg" role="tabpanel" aria-labelledby="tab-cdacc">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CDACC Registration Fee *</label>
                                    <input type="number" name="cdacc_registration_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="cdacc">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CDACC Examination Fee *</label>
                                    <input type="number" name="cdacc_examination_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="cdacc">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">CDACC Certification Fee *</label>
                                    <input type="number" name="cdacc_certification_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="cdacc">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">TVET Authority Levy *</label>
                                    <input type="number" name="tvet_authority_levy" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="cdacc">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Trade Test Fee *</label>
                                    <input type="number" name="trade_test_fee" required min="0" step="0.01" value="0"
                                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm fee-input"
                                           data-category="cdacc">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="mb-8">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Fee Summary</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-white rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 font-medium">Monthly Total</p>
                                <p class="text-lg font-bold text-primary mt-2" id="summaryMonthly">Ksh 0.00</p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 font-medium">One-time Fees</p>
                                <p class="text-lg font-bold text-blue-600 mt-2" id="summaryOneTime">Ksh 0.00</p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 font-medium">Course Duration</p>
                                <p class="text-lg font-bold text-green-600 mt-2" id="summaryDuration">0 months</p>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600 font-medium">Total Course Fee</p>
                                <p class="text-lg font-bold text-red-600 mt-2" id="summaryTotal">Ksh 0.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="mb-8">
                        <h4 class="text-md font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-200">Additional Settings</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valid From *</label>
                                <input type="date" name="valid_from" required
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valid To *</label>
                                <input type="date" name="valid_to" required
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="flex items-center mb-4">
                                    <input type="checkbox" name="has_government_sponsorship"
                                           class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                                           onchange="toggleGovernmentSponsorship()">
                                    <span class="ml-2 text-sm text-gray-700">Has Government Sponsorship</span>
                                </label>

                                <div id="governmentSponsorshipFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Government Subsidy Amount</label>
                                        <input type="number" name="government_subsidy_amount" min="0" step="0.01" value="0"
                                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sponsorship Type</label>
                                        <select name="sponsorship_type"
                                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                            <option value="">Select Type</option>
                                            <option value="cdacc">CDACC</option>
                                            <option value="county">County Government</option>
                                            <option value="national">National Government</option>
                                            <option value="helb">HELB</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end items-center p-6 border-t border-gray-200 bg-gray-50 gap-3">
                <button onclick="closeModal('addFeeStructureModal')"
                        class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <button onclick="submitAddFeeStructureForm()"
                        class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors w-full sm:w-auto flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Create Fee Structure
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal (Fixed Structure) -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity modal-overlay" onclick="closeModal('deleteModal')"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <!-- Modal Header -->
            <div class="flex items-start p-6">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Confirm Delete</h3>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone.</p>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 pb-6">
                <p class="text-gray-700" id="deleteMessage"></p>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end items-center p-6 border-t border-gray-200 bg-gray-50 gap-3">
                <button onclick="closeModal('deleteModal')"
                        class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <button onclick="confirmDelete()"
                        class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors w-full sm:w-auto flex items-center justify-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Action Modal (Fixed Structure) -->
<div id="statusActionModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity modal-overlay" onclick="closeModal('statusActionModal')"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800" id="statusActionTitle"></h3>
                <button onclick="closeModal('statusActionModal')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <p class="text-gray-700 mb-6" id="statusActionMessage"></p>
                <form id="statusActionForm" method="POST" style="display: none;">
                    @csrf
                    @method('POST')
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end items-center p-6 border-t border-gray-200 bg-gray-50 gap-3">
                <button onclick="closeModal('statusActionModal')"
                        class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto">
                    Cancel
                </button>
                <button onclick="submitStatusAction()"
                        class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors w-full sm:w-auto flex items-center justify-center">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Calculate Fees Modal (Fixed Structure) -->
<div id="calculateFeesModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity modal-overlay" onclick="closeModal('calculateFeesModal')"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Calculate Fees</h3>
                <button onclick="closeModal('calculateFeesModal')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div id="calculateFeesContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Plans Modal (Fixed Structure) -->
<div id="paymentPlansModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity modal-overlay" onclick="closeModal('paymentPlansModal')"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Payment Plans</h3>
                <button onclick="closeModal('paymentPlansModal')"
                        class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div id="paymentPlansContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fee tab switching
    function switchFeeTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.fee-tab').forEach(tab => {
            tab.classList.remove('active');
            tab.classList.add('hidden');
            tab.setAttribute('aria-hidden', 'true');
        });

        // Show selected tab
        const activeTab = document.getElementById(`feeTab-${tabName}`);
        activeTab.classList.remove('hidden');
        activeTab.classList.add('active');
        activeTab.setAttribute('aria-hidden', 'false');

        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-primary', 'text-primary');
            button.classList.add('border-transparent', 'text-gray-500');
            button.setAttribute('aria-selected', 'false');
        });

        const activeButton = document.getElementById(`tab-${tabName}`);
        activeButton.classList.remove('border-transparent', 'text-gray-500');
        activeButton.classList.add('border-primary', 'text-primary');
        activeButton.setAttribute('aria-selected', 'true');
    }

    // Calculate fee summary
    function calculateFeeSummary() {
        let monthlyTotal = 0;
        let oneTimeFees = 0;

        // Calculate institution monthly fees
        document.querySelectorAll('.fee-input[data-category="institution"]').forEach(input => {
            const name = input.name;
            const value = parseFloat(input.value) || 0;

            // Check if this is a monthly fee (excluding one-time fees)
            const oneTimeFeeFields = ['registration_fee', 'caution_money', 'student_id_fee'];
            if (oneTimeFeeFields.includes(name)) {
                oneTimeFees += value;
            } else {
                monthlyTotal += value;
            }
        });

        // Add workshop fees (all are monthly)
        document.querySelectorAll('.fee-input[data-category="workshop"]').forEach(input => {
            monthlyTotal += parseFloat(input.value) || 0;
        });

        // Add CDACC fees (all are monthly except registration)
        document.querySelectorAll('.fee-input[data-category="cdacc"]').forEach(input => {
            if (input.name === 'cdacc_registration_fee') {
                oneTimeFees += parseFloat(input.value) || 0;
            } else {
                monthlyTotal += parseFloat(input.value) || 0;
            }
        });

        // Update summary
        document.getElementById('summaryMonthly').textContent = `Ksh ${monthlyTotal.toFixed(2)}`;
        document.getElementById('summaryOneTime').textContent = `Ksh ${oneTimeFees.toFixed(2)}`;

        // Get course duration
        const durationInput = document.querySelector('input[name="total_course_months"]');
        const duration = parseInt(durationInput?.value) || 0;
        document.getElementById('summaryDuration').textContent = `${duration} months`;

        // Calculate total course fee (one-time + monthly * duration)
        const totalCourseFee = oneTimeFees + (monthlyTotal * duration);
        document.getElementById('summaryTotal').textContent = `Ksh ${totalCourseFee.toFixed(2)}`;
    }

    // Toggle government sponsorship fields
    function toggleGovernmentSponsorship() {
        const checkbox = document.querySelector('input[name="has_government_sponsorship"]');
        const fields = document.getElementById('governmentSponsorshipFields');

        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    // Add event listeners for fee calculation
    document.addEventListener('DOMContentLoaded', function() {
        // Calculate fees on input change
        document.querySelectorAll('.fee-input, input[name="total_course_months"]').forEach(input => {
            input.addEventListener('input', calculateFeeSummary);
        });

        // Initial calculation
        calculateFeeSummary();

        // Add keyboard navigation for tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const tabName = this.id.replace('tab-', '');
                    switchFeeTab(tabName);
                }
            });
        });

        // Focus trap for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && document.querySelector('.modal:not(.hidden)')) {
                const modal = document.querySelector('.modal:not(.hidden)');
                const focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];

                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    });

    // Improved modal functions
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Focus first focusable element
            const focusableElements = modal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    };

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal:not(.hidden)');
            if (openModals.length > 0) {
                closeModal(openModals[0].id);
            }
        }
    });

    // Form submission
    function submitAddFeeStructureForm() {
        const form = document.getElementById('addFeeStructureForm');
        if (form) {
            // Validate required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                } else {
                    field.classList.remove('border-red-500');
                }
            });

            if (isValid) {
                form.submit();
            } else {
                showToast('Please fill in all required fields', 'error');
            }
        }
    }

    // Delete fee structure
    function deleteFeeStructure(structureId) {
        const form = document.getElementById('deleteForm');
        if (form) {
            form.action = `/admin/fees/structures/${structureId}`;
        }
        document.getElementById('deleteMessage').textContent =
            'Are you sure you want to delete this fee structure? This action cannot be undone.';
        openModal('deleteModal');
    }

    function confirmDelete() {
        const form = document.getElementById('deleteForm');
        if (form) {
            form.submit();
        }
    }

    // Status actions
    function approveFeeStructure(structureId) {
        document.getElementById('statusActionTitle').textContent = 'Approve Fee Structure';
        document.getElementById('statusActionMessage').textContent =
            'Are you sure you want to approve this fee structure? Approved structures cannot be edited.';

        const form = document.getElementById('statusActionForm');
        if (form) {
            form.action = `/admin/fees/structures/${structureId}/approve`;
        }

        openModal('statusActionModal');
    }

    function activateFeeStructure(structureId) {
        document.getElementById('statusActionTitle').textContent = 'Activate Fee Structure';
        document.getElementById('statusActionMessage').textContent =
            'Are you sure you want to activate this fee structure? This will deactivate any currently active structure for this course and campus.';

        const form = document.getElementById('statusActionForm');
        if (form) {
            form.action = `/admin/fees/structures/${structureId}/activate`;
        }

        openModal('statusActionModal');
    }

    function deactivateFeeStructure(structureId) {
        document.getElementById('statusActionTitle').textContent = 'Deactivate Fee Structure';
        document.getElementById('statusActionMessage').textContent =
            'Are you sure you want to deactivate this fee structure? Students will no longer be able to use this fee structure.';

        const form = document.getElementById('statusActionForm');
        if (form) {
            form.action = `/admin/fees/structures/${structureId}/deactivate`;
        }

        openModal('statusActionModal');
    }

    function submitStatusAction() {
        const form = document.getElementById('statusActionForm');
        if (form) {
            form.submit();
        }
    }

    // Toast function
    function showToast(message, type = 'success') {
        // Your existing toast code
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `flex items-center p-4 rounded-lg shadow-lg animate-fade-in ${
            type === 'success' ? 'bg-green-50 border-l-4 border-green-500' :
            type === 'error' ? 'bg-red-50 border-l-4 border-red-500' :
            type === 'warning' ? 'bg-yellow-50 border-l-4 border-yellow-500' :
            'bg-blue-50 border-l-4 border-blue-500'
        }`;

        const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

        const iconColor = type === 'success' ? 'text-green-500' :
                        type === 'error' ? 'text-red-500' :
                        type === 'warning' ? 'text-yellow-500' : 'text-blue-500';

        toast.innerHTML = `
            <i class="fas ${icon} ${iconColor} mr-3"></i>
            <div class="flex-1">
                <p class="text-sm font-medium ${
                    type === 'success' ? 'text-green-800' :
                    type === 'error' ? 'text-red-800' :
                    type === 'warning' ? 'text-yellow-800' : 'text-blue-800'
                }">${message}</p>
            </div>
            <button onclick="removeToast('${toastId}')" class="ml-4 ${
                type === 'success' ? 'text-green-400 hover:text-green-600' :
                type === 'error' ? 'text-red-400 hover:text-red-600' :
                type === 'warning' ? 'text-yellow-400 hover:text-yellow-600' : 'text-blue-400 hover:text-blue-600'
            }" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;

        toastContainer.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            removeToast(toastId);
        }, 5000);
    }

    function removeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (toast) {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }
</script>

<style>
    /* Custom modal styling */
    .modal {
        transition: opacity 0.3s ease;
    }

    .modal:not(.hidden) {
        display: block;
    }

    /* Tab styling */
    .tab-button {
        transition: all 0.2s ease;
    }

    .tab-button.active {
        border-color: #B91C1C;
        color: #B91C1C;
    }

    .tab-button:hover:not(.active) {
        color: #374151;
    }

    /* Form input focus styling */
    .fee-input:focus {
        border-color: #B91C1C;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.1);
        outline: none;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .modal-content {
            margin: 0;
            max-height: 100vh;
        }

        .fee-tab .grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .modal-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .modal-header h3 {
            margin-bottom: 0.5rem;
        }

        .modal-footer {
            flex-direction: column;
        }

        .modal-footer button {
            width: 100%;
        }
    }

    /* Animation for modal */
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal > div {
        animation: modalFadeIn 0.3s ease-out;
    }

    /* Scrollbar styling for modal content */
    .modal-body {
        scrollbar-width: thin;
        scrollbar-color: #CBD5E1 #F1F1F1;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #F1F1F1;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    /* Accessibility improvements */
    .tab-button:focus {
        outline: 2px solid #B91C1C;
        outline-offset: 2px;
    }

    input:focus, select:focus, textarea:focus {
        outline: 2px solid #B91C1C;
        outline-offset: 2px;
    }

    /* Screen reader only text */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
</style>
@endsection
