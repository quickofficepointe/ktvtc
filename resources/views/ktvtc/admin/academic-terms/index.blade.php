@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Academic Terms')
@section('subtitle', 'Manage academic terms and enrollment windows')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Management</span>
    </div>
</li>
<li aria-current="page">
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Academic Terms</span>
    </div>
</li>
@endsection

@section('header-actions')
<div class="flex space-x-2">
    <button onclick="openModal('createTermModal', '2xl')" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium transition-colors flex items-center space-x-2 shadow-md">
        <i class="fas fa-plus"></i>
        <span>New Term</span>
    </button>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Terms</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $totalTerms }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-primary-light flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Terms</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $activeTerms }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Current Term</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $currentTerms }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-star text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Registration Open</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ $registrationOpen }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                    <i class="fas fa-door-open text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.academic-terms.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name, code or year..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>

            @if(auth()->user()->role == 2)
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Campus</label>
                    <select name="campus_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Year</label>
                <select name="academic_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Years</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.academic-terms.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($academicTerms as $term)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $term->name }}</p>
                                    @if($term->campus)
                                        <p class="text-xs text-gray-500"><i class="fas fa-building mr-1"></i>{{ $term->campus->name }}</p>
                                    @else
                                        <p class="text-xs text-gray-400">Global term</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs px-2 py-1 bg-gray-100 rounded">{{ $term->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <div>{{ $term->start_date?->format('d M Y') }} → {{ $term->end_date?->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">Fee due: {{ $term->fee_due_date?->format('d M Y') ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($term->registration_start_date && $term->registration_end_date)
                                    <div class="text-gray-600">
                                        {{ $term->registration_start_date->format('d M') }} → {{ $term->registration_end_date->format('d M Y') }}
                                    </div>
                                @else
                                    <span class="text-gray-400">Not set</span>
                                @endif
                                @if($term->allow_late_registration && $term->late_registration_end_date)
                                    <div class="text-xs text-orange-600 mt-0.5">
                                        Late until {{ $term->late_registration_end_date->format('d M') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    @if($term->is_current)
                                        <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-semibold">
                                            <i class="fas fa-star mr-1"></i> Current
                                        </span>
                                    @elseif($term->is_active)
                                        <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Active</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">Inactive</span>
                                    @endif

                                    @if($term->is_registration_open)
                                        <span class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">
                                            <i class="fas fa-door-open mr-1"></i> Open
                                        </span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full">
                                            <i class="fas fa-door-closed mr-1"></i> Closed
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.academic-terms.show', $term) }}"
                                       class="p-1.5 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg transition" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.academic-terms.edit', $term) }}"
                                       class="p-1.5 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <div class="relative">
                                        <button type="button"
                                                onclick="toggleTermMenu({{ $term->id }})"
                                                class="p-1.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition" title="More">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div id="termMenu-{{ $term->id }}"
                                             class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-20">
                                            <div class="py-1 text-left">
                                                @if(!$term->is_current)
                                                    <form method="POST" action="{{ route('admin.academic-terms.set-current', $term) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                                            <i class="fas fa-star mr-2"></i> Set as Current
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($term->is_active)
                                                    <form method="POST" action="{{ route('admin.academic-terms.deactivate', $term) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-yellow-600 hover:bg-gray-50 flex items-center">
                                                            <i class="fas fa-pause mr-2"></i> Deactivate
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.academic-terms.activate', $term) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-50 flex items-center">
                                                            <i class="fas fa-play mr-2"></i> Activate
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('admin.academic-terms.toggle-registration', $term) }}">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-50 flex items-center">
                                                        <i class="fas fa-door-{{ $term->is_registration_open ? 'closed' : 'open' }} mr-2"></i>
                                                        {{ $term->is_registration_open ? 'Close Registration' : 'Open Registration' }}
                                                    </button>
                                                </form>

                                                <div class="border-t border-gray-200 my-1"></div>

                                                <form method="POST" action="{{ route('admin.academic-terms.destroy', $term) }}"
                                                      onsubmit="return confirm('Delete this term? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50 flex items-center">
                                                        <i class="fas fa-trash mr-2"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <i class="fas fa-calendar-times text-5xl text-gray-300 mb-3"></i>
                                <p class="text-lg font-semibold text-gray-600">No academic terms yet</p>
                                <p class="text-sm text-gray-400 mt-1">Create your first term to start accepting enrollments.</p>
                                <button onclick="openModal('createTermModal', '2xl')"
                                        class="mt-4 px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                                    <i class="fas fa-plus mr-2"></i> Create Term
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($academicTerms->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $academicTerms->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- CREATE TERM MODAL --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="createTermModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 modal-overlay" onclick="closeModal('createTermModal')"></div>

        <div class="relative modal-content bg-white rounded-xl shadow-2xl max-w-4xl w-full">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-calendar-plus mr-2 text-primary"></i> Create Academic Term
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Fill in the details below</p>
                    </div>
                    <button onclick="closeModal('createTermModal')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.academic-terms.store') }}" method="POST" id="createTermForm">
                @csrf
                <div class="p-6 max-h-[70vh] overflow-y-auto">

                    @include('ktvtc.admin.academic-terms._form', ['term' => null, 'mode' => 'create'])

                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('createTermModal')"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                        <i class="fas fa-check mr-2"></i> Create Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleTermMenu(termId) {
    const menu = document.getElementById(`termMenu-${termId}`);
    document.querySelectorAll('[id^="termMenu-"]').forEach(m => {
        if (m.id !== `termMenu-${termId}`) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="termMenu-"]') && !e.target.closest('button[onclick*="toggleTermMenu"]')) {
        document.querySelectorAll('[id^="termMenu-"]').forEach(m => m.classList.add('hidden'));
    }
});
</script>
@endpush