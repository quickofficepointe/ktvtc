@extends('ktvtc.website.layout.websitelayout')

@section('title', 'Certifications Management')
@section('header-title', 'Certifications & Accreditations')

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-green-800 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Certifications</h1>
            <p class="text-gray-600 mt-2">Manage accreditations, examination bodies, and professional certifications</p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Add Certification
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Certifications</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certifications->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $certifications->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Exam Bodies</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $examBodies->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Accreditations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $accreditations->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Certification Types Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 px-6">
            <nav class="flex -mb-px space-x-8">
                <button onclick="showTab('all')" class="tab-btn active py-4 text-sm font-medium border-b-2 border-primary text-primary">All Certifications</button>
                <button onclick="showTab('accreditation')" class="tab-btn py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">Accreditations</button>
                <button onclick="showTab('examination_body')" class="tab-btn py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">Examination Bodies</button>
                <button onclick="showTab('professional_body')" class="tab-btn py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">Professional Bodies</button>
                <button onclick="showTab('registration')" class="tab-btn py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">Registrations</button>
            </nav>
        </div>

        {{-- All Certifications Tab --}}
        <div id="tab-all" class="tab-content p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Logo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Issuing Body</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($certifications as $cert)
                        <tr>
                            <td class="px-6 py-4">
                                @if($cert->logo_url)
                                    <img src="{{ $cert->logo_url }}" alt="{{ $cert->name }}" class="w-10 h-10 object-contain">
                                @else
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-certificate text-gray-400"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cert->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $cert->issuing_body }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($cert->certification_type == 'accreditation') bg-blue-100 text-blue-800
                                    @elseif($cert->certification_type == 'examination_body') bg-green-100 text-green-800
                                    @elseif($cert->certification_type == 'professional_body') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ str_replace('_', ' ', ucfirst($cert->certification_type)) }}
                                </span>
                              </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $cert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $cert->is_active ? 'bg-green-400' : 'bg-gray-400' }}"></span>
                                    {{ $cert->is_active ? 'Active' : 'Inactive' }}
                                </span>
                              </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold text-sm">
                                    {{ $cert->display_order }}
                                </span>
                              </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button onclick="openEditModal({{ $cert->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button onclick="toggleStatus({{ $cert->id }}, {{ $cert->is_active ? 'true' : 'false' }})"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-white {{ $cert->is_active ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-green-600 hover:bg-green-700' }} transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cert->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                        </svg>
                                        {{ $cert->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button onclick="confirmDelete({{ $cert->id }})"
                                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                              </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No certifications found</h3>
                                        <p class="text-gray-600 mb-4">Get started by creating your first certification.</p>
                                        <button onclick="openCreateModal()" class="bg-primary hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">Add Certification</button>
                                    </div>
                                  </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Accreditation Tab --}}
        <div id="tab-accreditation" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Logo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Issuing Body</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr></thead>
                    <tbody>
                        @forelse($accreditations as $cert)
                        <tr>
                            <td class="px-6 py-4">@if($cert->logo_url)<img src="{{ $cert->logo_url }}" class="w-10 h-10 object-contain">@else<div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-certificate text-gray-400"></i></div>@endif</td>
                            <td class="px-6 py-4 font-medium">{{ $cert->name }}</td>
                            <td class="px-6 py-4">{{ $cert->issuing_body }}</td>
                            <td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $cert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $cert->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-6 py-4"><button onclick="openEditModal({{ $cert->id }})" class="text-primary hover:text-red-700 mr-2">Edit</button><button onclick="confirmDelete({{ $cert->id }})" class="text-red-600 hover:text-red-800">Delete</button></td>
                        </tr>
                        @empty<td colspan="5" class="text-center py-8 text-gray-500">No accreditations found</td>@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Examination Bodies Tab --}}
        <div id="tab-examination_body" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Logo</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Issuing Body</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th></thead>
                    <tbody>
                        @forelse($examBodies as $cert)
                        <tr><td class="px-6 py-4">@if($cert->logo_url)<img src="{{ $cert->logo_url }}" class="w-10 h-10 object-contain">@else<div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-certificate text-gray-400"></i></div>@endif</td><td class="px-6 py-4 font-medium">{{ $cert->name }}</td><td class="px-6 py-4">{{ $cert->issuing_body }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $cert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $cert->is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-6 py-4"><button onclick="openEditModal({{ $cert->id }})" class="text-primary hover:text-red-700 mr-2">Edit</button><button onclick="confirmDelete({{ $cert->id }})" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                        @empty<td colspan="5" class="text-center py-8 text-gray-500">No examination bodies found</td>@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Professional Bodies Tab --}}
        <div id="tab-professional_body" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Logo</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Issuing Body</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th></thead>
                    <tbody>
                        @forelse($professionalBodies as $cert)
                        <tr><td class="px-6 py-4">@if($cert->logo_url)<img src="{{ $cert->logo_url }}" class="w-10 h-10 object-contain">@else<div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-certificate text-gray-400"></i></div>@endif</td><td class="px-6 py-4 font-medium">{{ $cert->name }}</td><td class="px-6 py-4">{{ $cert->issuing_body }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $cert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $cert->is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-6 py-4"><button onclick="openEditModal({{ $cert->id }})" class="text-primary hover:text-red-700 mr-2">Edit</button><button onclick="confirmDelete({{ $cert->id }})" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                        @empty<td colspan="5" class="text-center py-8 text-gray-500">No professional bodies found</td>@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Registrations Tab --}}
        <div id="tab-registration" class="tab-content hidden p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Logo</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Name</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Issuing Body</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Status</th><th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th></thead>
                    <tbody>
                        @forelse($registrations as $cert)
                        <td><td class="px-6 py-4">@if($cert->logo_url)<img src="{{ $cert->logo_url }}" class="w-10 h-10 object-contain">@else<div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-certificate text-gray-400"></i></div>@endif</td><td class="px-6 py-4 font-medium">{{ $cert->name }}</td><td class="px-6 py-4">{{ $cert->issuing_body }}</td><td class="px-6 py-4"><span class="px-2 py-1 text-xs rounded-full {{ $cert->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ $cert->is_active ? 'Active' : 'Inactive' }}</span></td><td class="px-6 py-4"><button onclick="openEditModal({{ $cert->id }})" class="text-primary hover:text-red-700 mr-2">Edit</button><button onclick="confirmDelete({{ $cert->id }})" class="text-red-600 hover:text-red-800">Delete</button></td></tr>
                        @empty<td colspan="5" class="text-center py-8 text-gray-500">No registrations found</td>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Create Certification Modal --}}
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeCreateModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Add New Certification</h3>
                            <p class="text-red-100 text-sm">Add accreditation or examination body</p>
                        </div>
                    </div>
                    <button onclick="closeCreateModal()" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="createForm" action="{{ route('website.certifications.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certification Name *</label><input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Issuing Body *</label><input type="text" name="issuing_body" required class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certification Type *</label>
                            <select name="certification_type" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                                <option value="">Select Type</option>
                                <option value="accreditation">Accreditation</option>
                                <option value="examination_body">Examination Body</option>
                                <option value="professional_body">Professional Body</option>
                                <option value="registration">Registration</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label><input type="number" name="display_order" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number</label><input type="text" name="certificate_number" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label><input type="url" name="website" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label><input type="date" name="issue_date" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label><input type="date" name="expiry_date" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-2">Description</label><textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="createLogoPreview" class="mx-auto h-20 w-20 object-cover rounded-lg mb-2 hidden">
                                    <svg id="createLogoPlaceholder" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="create_logo" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500">
                                            <span>Upload logo</span>
                                            <input id="create_logo" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'createLogoPreview', 'createLogoPlaceholder')">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div><h4 class="text-lg font-semibold text-gray-900 mb-1">Active Status</h4><p class="text-sm text-gray-600">Show this certification on website</p></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeCreateModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg">Create Certification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Certification Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeEditModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-red-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div><h3 class="text-xl font-bold text-white">Edit Certification</h3><p class="text-red-100 text-sm">Update certification details</p></div>
                    </div>
                    <button onclick="closeEditModal()" class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certification Name *</label><input type="text" id="edit_name" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Issuing Body *</label><input type="text" id="edit_issuing_body" name="issuing_body" required class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certification Type *</label><select id="edit_certification_type" name="certification_type" required class="w-full border border-gray-300 rounded-lg px-4 py-3">
                            <option value="accreditation">Accreditation</option><option value="examination_body">Examination Body</option><option value="professional_body">Professional Body</option><option value="registration">Registration</option>
                        </select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label><input type="number" id="edit_display_order" name="display_order" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Certificate Number</label><input type="text" id="edit_certificate_number" name="certificate_number" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label><input type="url" id="edit_website" name="website" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label><input type="date" id="edit_issue_date" name="issue_date" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label><input type="date" id="edit_expiry_date" name="expiry_date" class="w-full border border-gray-300 rounded-lg px-4 py-3"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-2">Description</label><textarea id="edit_description" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Image</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                <div class="space-y-1 text-center">
                                    <img id="editLogoPreview" class="mx-auto h-20 w-20 object-cover rounded-lg mb-2">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="edit_logo" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-red-500">
                                            <span>Change logo</span>
                                            <input id="edit_logo" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewEditLogo(this)">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Leave empty to keep current</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div><h4 class="text-lg font-semibold text-gray-900 mb-1">Active Status</h4><p class="text-sm text-gray-600">Show this certification on website</p></div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-900">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-6 py-3 bg-primary text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-lg">Update Certification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(`tab-${tabId}`).classList.remove('hidden');
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'text-primary');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    event.target.classList.add('border-primary', 'text-primary');
}

function previewImage(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.classList.remove('hidden'); if(placeholder) placeholder.classList.add('hidden'); };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewEditLogo(input) {
    const preview = document.getElementById('editLogoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}

function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); document.body.style.overflow = 'auto'; document.getElementById('createForm').reset(); document.getElementById('createLogoPreview').classList.add('hidden'); document.getElementById('createLogoPlaceholder').classList.remove('hidden'); }

function openEditModal(id) {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    fetch(`/website/certifications/${id}`).then(r=>r.json()).then(data => {
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_issuing_body').value = data.issuing_body;
        document.getElementById('edit_certification_type').value = data.certification_type;
        document.getElementById('edit_display_order').value = data.display_order;
        document.getElementById('edit_certificate_number').value = data.certificate_number || '';
        document.getElementById('edit_website').value = data.website || '';
        document.getElementById('edit_issue_date').value = data.issue_date || '';
        document.getElementById('edit_expiry_date').value = data.expiry_date || '';
        document.getElementById('edit_description').value = data.description || '';
        document.getElementById('edit_is_active').checked = data.is_active;
        if (data.logo_url) document.getElementById('editLogoPreview').src = data.logo_url;
        document.getElementById('editForm').action = `/website/certifications/${id}`;
    });
}
function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }

function toggleStatus(id, currentStatus) {
    if(confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this certification?`)){
        fetch(`/website/certifications/${id}/toggle-status`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
        .then(r=>r.json()).then(data=>{if(data.success) location.reload();});
    }
}

function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this certification?')){
        fetch(`/website/certifications/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}})
        .then(r=>r.json()).then(data=>{if(data.success) location.reload();});
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); } });
</script>
@endsection
