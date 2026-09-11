@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Companies')
@section('subtitle', 'Registry of host companies for student attachments')

@section('header-actions')
<a href="{{ route('ilo.companies.create') }}"
   class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition">
    <i class="fas fa-plus mr-2"></i> Add Company
</a>
@endsection

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-3 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Total Companies</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Active</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">With Open Opportunities</p>
        <p class="text-2xl font-bold text-primary mt-1">{{ $stats['with_open_ops'] ?? 0 }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('ilo.companies.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
        <div class="md:col-span-6">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name, sector, contact..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>
        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sector</label>
            <select name="sector" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <option value="">All Sectors</option>
                @foreach($sectors as $s)
                    <option value="{{ $s }}" {{ request('sector') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-3 flex gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium">
                Filter
            </button>
            <a href="{{ route('ilo.companies.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sector</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Opportunities</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('ilo.companies.show', $company) }}" class="font-medium text-gray-800 hover:text-primary">
                                {{ $company->name }}
                            </a>
                            @if($company->town)
                                <p class="text-xs text-gray-500"><i class="fas fa-map-marker-alt mr-1"></i>{{ $company->town }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $company->sector ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if($company->contact_person_name)
                                <p class="text-gray-800">{{ $company->contact_person_name }}</p>
                            @endif
                            @if($company->contact_person_phone)
                                <p class="text-xs text-gray-500"><i class="fas fa-phone mr-1"></i>{{ $company->contact_person_phone }}</p>
                            @endif
                            @if($company->contact_person_email)
                                <p class="text-xs text-gray-500"><i class="fas fa-envelope mr-1"></i>{{ $company->contact_person_email }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-light text-primary">
                                {{ $company->opportunities_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($company->is_active)
                                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">Active</span>
                            @else
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('ilo.companies.show', $company) }}" class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ilo.companies.edit', $company) }}" class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('ilo.companies.destroy', $company) }}" method="POST"
                                      onsubmit="return confirm('Delete this company?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-600 hover:text-red-600 hover:bg-gray-100 rounded-lg" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <i class="fas fa-building text-5xl text-gray-300 mb-3"></i>
                            <p class="text-lg font-semibold text-gray-600">No companies yet</p>
                            <p class="text-sm text-gray-400 mt-1">Register your first company to get started.</p>
                            <a href="{{ route('ilo.companies.create') }}" class="mt-4 inline-block px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                                <i class="fas fa-plus mr-2"></i> Register Company
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($companies->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $companies->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection