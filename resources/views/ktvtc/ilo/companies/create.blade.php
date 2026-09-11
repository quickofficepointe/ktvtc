@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Register Company')
@section('subtitle', 'Add a host organization to the attachment registry')

@section('header-actions')
<a href="{{ route('ilo.companies.index') }}"
   class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
    <i class="fas fa-arrow-left mr-2"></i> Back
</a>
@endsection

@section('content')
<form method="POST" action="{{ route('ilo.companies.store') }}" class="max-w-4xl">
    @csrf

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-building text-primary mr-2"></i> Company Details</h3>
        </div>

        <div class="p-6 space-y-6">

            {{-- Basic --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sector</label>
                    <input type="text" name="sector" value="{{ old('sector') }}"
                           placeholder="Automotive, ICT, Hospitality..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Size</label>
                    <select name="size" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                        <option value="">Select</option>
                        <option value="small"  {{ old('size') == 'small'  ? 'selected' : '' }}>Small</option>
                        <option value="medium" {{ old('size') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="large"  {{ old('size') == 'large'  ? 'selected' : '' }}>Large</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" name="website" value="{{ old('website') }}"
                           placeholder="https://..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                </div>
            </div>

            {{-- Address --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Address</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Physical Address</label>
                        <input type="text" name="physical_address" value="{{ old('physical_address') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Postal Address</label>
                        <input type="text" name="postal_address" value="{{ old('postal_address') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Town</label>
                        <input type="text" name="town" value="{{ old('town') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">County</label>
                        <input type="text" name="county" value="{{ old('county') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Contact person --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-user-tie text-primary mr-2"></i> Primary Contact Person</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="contact_person_name" value="{{ old('contact_person_name') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <input type="text" name="contact_person_position" value="{{ old('contact_person_position') }}"
                               placeholder="HR Manager, Workshop Supervisor..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="text" name="contact_person_phone" value="{{ old('contact_person_phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="contact_person_email" value="{{ old('contact_person_email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Company-wide contact --}}
            <div class="pt-4 border-t border-gray-100">
                <h4 class="font-medium text-gray-800 mb-3"><i class="fas fa-phone text-primary mr-2"></i> Company Contacts</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
                        <input type="email" name="company_email" value="{{ old('company_email') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                    </div>
                </div>
            </div>

            {{-- Notes / status --}}
            <div class="pt-4 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">{{ old('notes') }}</textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
            <a href="{{ route('ilo.companies.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                <i class="fas fa-save mr-2"></i> Save Company
            </button>
        </div>
    </div>
</form>
@endsection