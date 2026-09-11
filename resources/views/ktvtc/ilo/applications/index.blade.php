@extends('ktvtc.ilo.layout.ilolayout')

@section('title', 'Applications')
@section('subtitle', 'Review student applications to attachment opportunities')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Pending</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Shortlisted</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['shortlisted'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Approved</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['approved'] ?? 0 }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <p class="text-sm text-gray-500">Rejected</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['rejected'] ?? 0 }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('ilo.applications.index') }}"
          class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">

        <div class="md:col-span-5">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Student name or number..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">
                <option value="">All Statuses</option>
                @foreach(['pending','shortlisted','approved','rejected','withdrawn'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-4 flex gap-2">
            <a href="{{ route('ilo.applications.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                Reset
            </a>
            <button type="submit"
                    class="flex-1 px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-medium text-sm">
                <i class="fas fa-search mr-2"></i> Filter
            </button>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Company / Opportunity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Documents</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Applied</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($applications as $app)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('ilo.applications.show', $app) }}"
                               class="font-medium text-gray-800 hover:text-primary">
                                {{ $app->student->full_name ?? '—' }}
                            </a>
                            <p class="text-xs text-gray-500 font-mono">
                                {{ $app->student->student_number ?? '' }}
                            </p>
                        </td>

                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">
                                {{ $app->opportunity->company->name ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $app->opportunity->title ?? 'Opportunity' }}
                            </p>
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $app->opportunity->course->name ?? '—' }}
                            @if($app->opportunity->course?->code)
                                <span class="text-xs text-gray-400 font-mono block">
                                    {{ $app->opportunity->course->code }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2 text-xs">
                                @if($app->cv_path)
                                    <a href="{{ Storage::disk('public')->url($app->cv_path) }}"
                                       target="_blank"
                                       class="text-primary hover:text-primary-dark"
                                       title="CV">
                                        <i class="fas fa-file-pdf"></i> CV
                                    </a>
                                @endif
                                @if($app->cover_letter_path)
                                    <a href="{{ Storage::disk('public')->url($app->cover_letter_path) }}"
                                       target="_blank"
                                       class="text-primary hover:text-primary-dark"
                                       title="Cover Letter">
                                        <i class="fas fa-file-alt"></i> Letter
                                    </a>
                                @endif
                                @if(!$app->cv_path && !$app->cover_letter_path)
                                    <span class="text-gray-400">—</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $badge = match($app->status) {
                                    'pending'     => 'bg-amber-100 text-amber-700',
                                    'shortlisted' => 'bg-blue-100 text-blue-700',
                                    'approved'    => 'bg-green-100 text-green-700',
                                    'rejected'    => 'bg-red-100 text-red-700',
                                    'withdrawn'   => 'bg-gray-100 text-gray-700',
                                    default       => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $badge }}">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right text-xs text-gray-500">
                            {{ $app->created_at?->format('d M Y') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('ilo.applications.show', $app) }}"
                                   class="p-2 text-gray-600 hover:text-primary hover:bg-gray-100 rounded-lg"
                                   title="Review">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($app->canBeReviewed())
                                    <form method="POST" action="{{ route('ilo.applications.approve', $app) }}"
                                          class="inline"
                                          onsubmit="return confirm('Approve this application? A slot will be reserved.')">
                                        @csrf
                                        <button type="submit"
                                                class="p-2 text-green-600 hover:bg-green-50 rounded-lg"
                                                title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('ilo.applications.reject', $app) }}"
                                          class="inline"
                                          onsubmit="return confirm('Reject this application?')">
                                        @csrf
                                        <input type="hidden" name="remarks" value="Rejected via quick action">
                                        <button type="submit"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fas fa-file-signature text-5xl text-gray-300 mb-3"></i>
                            <p class="text-lg font-semibold text-gray-600">No applications yet</p>
                            <p class="text-sm text-gray-400 mt-1">
                                Once students apply to your opportunities, they'll show up here.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $applications->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection