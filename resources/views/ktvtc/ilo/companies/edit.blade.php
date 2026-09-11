@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Edit Company')
@section('subtitle', $company->name)

@section('header-actions')
<a href="{{ route('ilo.companies.show', $company) }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
    <i class="fas fa-arrow-left mr-2"></i> Back
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('ilo.companies.update', $company) }}" class="max-w-4xl">
    @csrf @method('PUT')

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-edit text-primary mr-2"></i> Edit {{ $company->name }}</h3>
        </div>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sector</label>
                    <input type="text" name="sector" value="{{ old('sector', $company->sector) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                    <select name="size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">Select</option>
                        @foreach(['small','medium','large'] as $s)
                            <option value="{{ $s }}" {{ old('size', $company->size) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" name="website" value="{{ old('website', $company->website) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                    <input type="text" name="physical_address" value="{{ old('physical_address', $company->physical_address) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Address</label>
                    <input type="text" name="postal_address" value="{{ old('postal_address', $company->postal_address) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Town</label>
                    <input type="text" name="town" value="{{ old('town', $company->town) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                    <input type="text" name="county" value="{{ old('county', $company->county) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                    <input type="text" name="contact_person_name" value="{{ old('contact_person_name', $company->contact_person_name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                    <input type="text" name="contact_person_position" value="{{ old('contact_person_position', $company->contact_person_position) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                    <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone', $company->contact_person_phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                    <input type="email" name="contact_person_email" value="{{ old('contact_person_email', $company->contact_person_email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $company->company_phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
                    <input type="email" name="company_email" value="{{ old('company_email', $company->company_email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('notes', $company->notes) }}</textarea>
                <div class="flex items-center gap-2 mt-4">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           {{ old('is_active', $company->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <a href="{{ route('ilo.companies.show', $company) }}" class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>
</form>
@endsection