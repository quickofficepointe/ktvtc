@extends('ktvtc.students.layout.studentlayout')

@section('title', $attachment ? 'Edit Attachment Details' : 'Attachment Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <a href="{{ route('student.attachment.details') }}"
       class="inline-flex items-center text-sm text-gray-600 hover:text-primary">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-briefcase text-primary mr-2"></i>
            {{ $attachment ? 'Edit Attachment Details' : 'Fill In Attachment Details' }}
        </h1>
        <p class="text-gray-500 mt-1">
            Provide the details of where you're attached and who supervises you.
            The ILO office will review and confirm.
        </p>
    </div>

    <form method="POST"
          action="{{ $attachment
              ? route('student.attachment.details.update', $attachment)
              : route('student.attachment.details.store') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($attachment)@method('PUT')@endif

        @if($application)
            <input type="hidden" name="application_id" value="{{ $application->id }}">
        @endif

        {{-- SELF-FOUND HINT --}}
        @if(empty($hasPrefill))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold">Declaring a self-found placement</p>
                        <p class="mt-1">
                            Fill in the details of the company where you'll be attached.
                            If the company is not in our list, choose <strong>"Other"</strong> and type the details.
                            The ILO office will review and approve your submission.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══════════ 1. COMPANY ═══════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold mr-3">1</span>
                Company Details
            </h3>

            <div class="space-y-5">

                {{-- Registry dropdown --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pick a company from the registry <span class="text-xs text-gray-500 font-normal">(optional)</span>
                    </label>
                    <select name="company_id" id="company_select"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">— Select a company or type below —</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}"
                                    data-name="{{ $c->name }}"
                                    data-sector="{{ $c->sector }}"
                                    data-address="{{ $c->physical_address }}"
                                    data-phone="{{ $c->company_phone }}"
                                    data-email="{{ $c->company_email }}"
                                    {{ old('company_id', $attachment->company_id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}@if($c->sector) — {{ $c->sector }}@endif
                            </option>
                        @endforeach
                        <option value="other" {{ old('company_id') === 'other' ? 'selected' : '' }}>
                            ➕ Other (company not in the list)
                        </option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        If your company is not listed, choose <strong>Other</strong> and enter the details below.
                    </p>
                </div>

                {{-- Free-text company fields --}}
                <div id="company_free_text" class="space-y-5 pt-2">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Company Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="company_name" id="company_name_input"
                               value="{{ old('company_name', $attachment->company_name ?? $application?->opportunity?->company?->name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @error('company_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sector</label>
                            <input type="text" name="company_sector"
                                   value="{{ old('company_sector', $attachment->company_sector ?? $application?->opportunity?->company?->sector) }}"
                                   placeholder="Automotive, ICT, Hospitality..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone</label>
                            <input type="text" name="company_phone"
                                   value="{{ old('company_phone', $attachment->company_phone ?? $application?->opportunity?->company?->company_phone) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                            <input type="text" name="company_address"
                                   value="{{ old('company_address', $attachment->company_address ?? $application?->opportunity?->company?->physical_address) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
                            <input type="email" name="company_email"
                                   value="{{ old('company_email', $attachment->company_email ?? $application?->opportunity?->company?->company_email) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════ 2. SUPERVISOR ═══════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold mr-3">2</span>
                On-Site Supervisor
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Supervisor Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="supervisor_name" required
                           value="{{ old('supervisor_name', $attachment->supervisor_name ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('supervisor_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position / Title</label>
                    <input type="text" name="supervisor_position"
                           value="{{ old('supervisor_position', $attachment->supervisor_position ?? '') }}"
                           placeholder="Workshop Manager, HR Officer..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Supervisor Phone <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="supervisor_phone" required
                           value="{{ old('supervisor_phone', $attachment->supervisor_phone ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('supervisor_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supervisor Email</label>
                    <input type="email" name="supervisor_email"
                           value="{{ old('supervisor_email', $attachment->supervisor_email ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>
        </div>

        {{-- ═══════════ 3. PERIOD ═══════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold mr-3">3</span>
                Attachment Period
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" required
                           value="{{ old('start_date', $attachment?->start_date?->format('Y-m-d') ?? $application?->opportunity?->start_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('start_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" required
                           value="{{ old('end_date', $attachment?->end_date?->format('Y-m-d') ?? $application?->opportunity?->end_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('end_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Job Description</label>
                    <textarea name="job_description" rows="3"
                              placeholder="What tasks will you be doing at the company?"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('job_description', $attachment->job_description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Learning Objectives</label>
                    <textarea name="learning_objectives" rows="3"
                              placeholder="What do you hope to learn or gain from this attachment?"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('learning_objectives', $attachment->learning_objectives ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ═══════════ 4. DOCUMENTS ═══════════ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                <span class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold mr-3">4</span>
                Supporting Documents
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Acceptance Letter from Company
                        <span class="text-xs text-gray-500 font-normal">— PDF/image, max 5MB</span>
                    </label>
                    @if($attachment?->acceptance_letter_path)
                        <div class="mb-2">
                            <a href="{{ Storage::disk('public')->url($attachment->acceptance_letter_path) }}"
                               target="_blank"
                               class="text-sm text-primary hover:underline">
                                <i class="fas fa-file-pdf mr-1"></i> Current file
                            </a>
                        </div>
                    @endif
                    <input type="file" name="acceptance_letter" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('acceptance_letter')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Placement Letter from School
                        <span class="text-xs text-gray-500 font-normal">— optional</span>
                    </label>
                    @if($attachment?->placement_letter_path)
                        <div class="mb-2">
                            <a href="{{ Storage::disk('public')->url($attachment->placement_letter_path) }}"
                               target="_blank"
                               class="text-sm text-primary hover:underline">
                                <i class="fas fa-file-pdf mr-1"></i> Current file
                            </a>
                        </div>
                    @endif
                    <input type="file" name="placement_letter" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    @error('placement_letter')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea name="student_notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('student_notes', $attachment->student_notes ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex justify-end gap-3">
            <a href="{{ route('student.attachment.details') }}"
               class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i>
                {{ $attachment ? 'Save Changes' : 'Submit Details' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('company_select');
    const freeText = document.getElementById('company_free_text');
    const nameInput = document.getElementById('company_name_input');
    const sectorInput = document.querySelector('[name="company_sector"]');
    const addressInput = document.querySelector('[name="company_address"]');
    const phoneInput = document.querySelector('[name="company_phone"]');
    const emailInput = document.querySelector('[name="company_email"]');

    if (!select) return;

    function toggle() {
        const val = select.value;

        if (val === '' || val === 'other') {
            // Show free-text fields
            freeText.classList.remove('hidden');

            // If a registered company was previously selected and user
            // switched to Other, keep whatever's in the inputs.
        } else {
            // Hide free-text fields and prefill from the selected option
            const opt = select.options[select.selectedIndex];

            if (opt) {
                nameInput.value = opt.dataset.name || '';
                sectorInput.value = opt.dataset.sector || '';
                addressInput.value = opt.dataset.address || '';
                phoneInput.value = opt.dataset.phone || '';
                emailInput.value = opt.dataset.email || '';
            }

            // Keep the free-text visible too, so user can adjust fields
            // (some students will want to add a specific branch address)
            freeText.classList.remove('hidden');
        }
    }

    select.addEventListener('change', toggle);
    toggle();
});
</script>
@endpush
@endsection