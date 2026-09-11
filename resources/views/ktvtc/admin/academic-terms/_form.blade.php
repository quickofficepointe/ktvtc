{{-- resources/views/ktvtc/admin/academic-terms/_form.blade.php --}}
{{-- Shared form for create and edit --}}

@php
    $isEdit = isset($mode) && $mode === 'edit';
    $t = $term ?? null;
@endphp

<div class="space-y-6">

    {{-- Basic Info --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-info-circle mr-1 text-primary"></i> Basic Information
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if(auth()->user()->role == 2)
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Campus</label>
                    <select name="campus_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Global Term (all campuses)</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ old('campus_id', $t?->campus_id) == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ auth()->user()->role == 2 ? '' : 'md:col-span-2' }}">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Term Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       value="{{ old('name', $t?->name) }}"
                       placeholder="e.g. Term 1 2026"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Code <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code" required
                       value="{{ old('code', $t?->code) }}"
                       placeholder="e.g. T1-2026"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1">Must be unique</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Short Code</label>
                <input type="text" name="short_code" maxlength="10"
                       value="{{ old('short_code', $t?->short_code) }}"
                       placeholder="T1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Term Number <span class="text-red-500">*</span>
                </label>
                <select name="term_number" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ old('term_number', $t?->term_number) == $i ? 'selected' : '' }}>
                            Term {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Academic Year <span class="text-red-500">*</span>
                </label>
                <input type="number" name="academic_year" required
                       value="{{ old('academic_year', $t?->academic_year ?? date('Y')) }}"
                       min="2000" max="{{ date('Y') + 5 }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Academic Year Name</label>
                <input type="text" name="academic_year_name"
                       value="{{ old('academic_year_name', $t?->academic_year_name) }}"
                       placeholder="2025/2026"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>
        </div>
    </div>

    {{-- Term Dates --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-calendar-day mr-1 text-primary"></i> Term Dates
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Start Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="start_date" required
                       value="{{ old('start_date', $t?->start_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    End Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="end_date" required
                       value="{{ old('end_date', $t?->end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Fee Due Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="fee_due_date" required
                       value="{{ old('fee_due_date', $t?->fee_due_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>

    {{-- Registration Window --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-user-plus mr-1 text-primary"></i> Registration Window
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Registration Opens</label>
                <input type="date" name="registration_start_date"
                       value="{{ old('registration_start_date', $t?->registration_start_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Registration Closes</label>
                <input type="date" name="registration_end_date"
                       value="{{ old('registration_end_date', $t?->registration_end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>

    {{-- Late Registration --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-clock mr-1 text-primary"></i> Late Registration (Optional)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Late Registration Opens</label>
                <input type="date" name="late_registration_start_date"
                       value="{{ old('late_registration_start_date', $t?->late_registration_start_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Late Registration Closes</label>
                <input type="date" name="late_registration_end_date"
                       value="{{ old('late_registration_end_date', $t?->late_registration_end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Late Registration Fee (KES)</label>
                <input type="number" name="late_registration_fee" step="0.01" min="0"
                       value="{{ old('late_registration_fee', $t?->late_registration_fee ?? 0) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>

            <div class="flex items-end">
                <label class="flex items-center">
                    <input type="checkbox" name="allow_late_registration" value="1"
                           {{ old('allow_late_registration', $t?->allow_late_registration) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="ml-2 text-sm font-medium text-gray-700">Allow late registration</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Exam Dates --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-pencil-alt mr-1 text-primary"></i> Exam Dates (Optional)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Exam Registration Opens</label>
                <input type="date" name="exam_registration_start_date"
                       value="{{ old('exam_registration_start_date', $t?->exam_registration_start_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Exam Registration Closes</label>
                <input type="date" name="exam_registration_end_date"
                       value="{{ old('exam_registration_end_date', $t?->exam_registration_end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Exam Starts</label>
                <input type="date" name="exam_start_date"
                       value="{{ old('exam_start_date', $t?->exam_start_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Exam Ends</label>
                <input type="date" name="exam_end_date"
                       value="{{ old('exam_end_date', $t?->exam_end_date?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
            </div>
        </div>
    </div>

    {{-- Status Flags --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-toggle-on mr-1 text-primary"></i> Status
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_active" value="1"
                       {{ old('is_active', $t?->is_active ?? true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm font-medium text-gray-700">Term is Active</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_current" value="1"
                       {{ old('is_current', $t?->is_current) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm font-medium text-gray-700">Set as Current</span>
            </label>

            <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                <input type="checkbox" name="is_registration_open" value="1"
                       {{ old('is_registration_open', $t?->is_registration_open) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm font-medium text-gray-700">Registration Open</span>
            </label>
        </div>

        <p class="text-xs text-gray-500 mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Only one term can be current at a time. Setting this term as current will unset the others.
        </p>
    </div>

    {{-- Notes --}}
    <div>
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">
            <i class="fas fa-sticky-note mr-1 text-primary"></i> Notes (Optional)
        </h4>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                          placeholder="Short description...">{{ old('description', $t?->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Internal Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"
                          placeholder="Notes for admin/finance...">{{ old('notes', $t?->notes) }}</textarea>
            </div>
        </div>
    </div>

    @if($isEdit)
        <input type="hidden" name="sort_order" value="{{ old('sort_order', $t?->sort_order ?? 0) }}">
    @else
        <input type="hidden" name="sort_order" value="0">
    @endif
</div>