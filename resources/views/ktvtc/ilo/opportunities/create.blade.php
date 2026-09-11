@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Create Opportunity')
@section('subtitle', 'Offer attachment slots to a course for a term')

@section('header-actions')
<a href="{{ route('ilo.opportunities.index') }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
    <i class="fas fa-arrow-left mr-2"></i> Back
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('ilo.opportunities.store') }}" class="max-w-4xl">
    @csrf

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-bullhorn text-primary mr-2"></i> Opportunity Details</h3>
        </div>

        <div class="p-6 space-y-6">

            {{-- Company + Course --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company <span class="text-red-500">*</span></label>
                    <select name="company_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('company_id') border-red-500 @enderror">
                        <option value="">Select Company</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}"
                                {{ old('company_id', $preselectedCompany->id ?? '') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
                    <select name="course_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('course_id') border-red-500 @enderror">
                        <option value="">Select Course</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} @if($c->code) ({{ $c->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Term + Slots --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
                    <select name="term_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">No Term</option>
                        @foreach($terms as $t)
                            <option value="{{ $t->id }}" {{ old('term_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} @if($t->code) ({{ $t->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Number of Slots <span class="text-red-500">*</span></label>
                    <input type="number" name="slots" value="{{ old('slots', 3) }}" min="1" max="500" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('slots') border-red-500 @enderror">
                    @error('slots')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Application window --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-calendar-check text-primary mr-2"></i> Application Window</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Opens On</label>
                        <input type="date" name="application_opens_at" value="{{ old('application_opens_at') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Closes On</label>
                        <input type="date" name="application_closes_at" value="{{ old('application_closes_at') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Attachment period --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-calendar-alt text-primary mr-2"></i> Attachment Period</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-align-left text-primary mr-2"></i> Description</h4>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title (short summary)</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="e.g. Engine diagnostics & repair attachment"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4"
                                  placeholder="What will the student learn / do?"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                        <textarea name="requirements" rows="3"
                                  placeholder="Skills, prior modules, tools to bring..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('requirements') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" value="{{ old('location') }}"
                               placeholder="e.g. Industrial Area, Nairobi"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Perks --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-gift text-primary mr-2"></i> Perks</h4>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1"
                               {{ old('is_paid') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="is_paid" class="text-sm text-gray-700">Paid Attachment</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="provides_transport" id="provides_transport" value="1"
                               {{ old('provides_transport') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="provides_transport" class="text-sm text-gray-700">Transport Provided</label>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-toggle-on text-primary mr-2"></i> Publication</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft (save and publish later)</option>
                            <option value="open"  {{ old('status') == 'open'  ? 'selected' : '' }}>Open (visible to students immediately)</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 self-end">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                    </div>
                </div>
            </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <a href="{{ route('ilo.opportunities.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i> Create Opportunity
            </button>
        </div>
    </div>
</form>
@endsection