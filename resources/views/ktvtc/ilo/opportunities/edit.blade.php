@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Edit Opportunity')
@section('subtitle', ($opportunity->company->name ?? '') . ' · ' . ($opportunity->course->name ?? ''))

@section('header-actions')
<a href="{{ route('ilo.opportunities.show', $opportunity) }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
    <i class="fas fa-arrow-left mr-2"></i> Back
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('ilo.opportunities.update', $opportunity) }}" class="max-w-4xl">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-edit text-primary mr-2"></i> Edit Opportunity</h3>
        </div>

        <div class="p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company <span class="text-red-500">*</span></label>
                    <select name="company_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ old('company_id', $opportunity->company_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
                    <select name="course_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ old('course_id', $opportunity->course_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} @if($c->code) ({{ $c->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                    <select name="term_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">No Term</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ old('term_id', $opportunity->term_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} @if($t->code) ({{ $t->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Slots <span class="text-red-500">*</span></label>
                    <input type="number" name="slots" value="{{ old('slots', $opportunity->slots) }}" min="1" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">
                        Currently filled: {{ $opportunity->slots_taken }}. New slots cannot be less than this.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Application Opens</label>
                    <input type="date" name="application_opens_at"
                           value="{{ old('application_opens_at', $opportunity->application_opens_at?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Application Closes</label>
                    <input type="date" name="application_closes_at"
                           value="{{ old('application_closes_at', $opportunity->application_closes_at?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Attachment Start</label>
                    <input type="date" name="start_date"
                           value="{{ old('start_date', $opportunity->start_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Attachment End</label>
                    <input type="date" name="end_date"
                           value="{{ old('end_date', $opportunity->end_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" value="{{ old('title', $opportunity->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('description', $opportunity->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                    <textarea name="requirements" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('requirements', $opportunity->requirements) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" name="location" value="{{ old('location', $opportunity->location) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-6">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_paid" id="is_paid" value="1"
                           {{ old('is_paid', $opportunity->is_paid) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_paid" class="text-sm text-gray-700">Paid Attachment</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="provides_transport" id="provides_transport" value="1"
                           {{ old('provides_transport', $opportunity->provides_transport) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="provides_transport" class="text-sm text-gray-700">Transport Provided</label>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        @foreach(['draft','open','closed','filled'] as $s)
                            <option value="{{ $s }}" {{ old('status', $opportunity->status) == $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 self-end">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $opportunity->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <a href="{{ route('ilo.opportunities.show', $opportunity) }}" class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>
</form>
@endsection