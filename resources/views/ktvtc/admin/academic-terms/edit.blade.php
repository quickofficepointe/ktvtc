@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Edit Academic Term')
@section('subtitle', $academicTerm->name ?? 'Edit term')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <a href="{{ route('admin.academic-terms.index') }}" class="ml-1 text-sm font-medium text-gray-600 hover:text-primary md:ml-2">
            Academic Terms
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <i class="fas fa-angle-right text-gray-400"></i>
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">{{ $academicTerm->code ?? '—' }}</span>
    </div>
</li>
@endsection

@section('header-actions')
<a href="{{ route('admin.academic-terms.show', $academicTerm) }}"
   class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition flex items-center space-x-2">
    <i class="fas fa-eye"></i>
    <span>View</span>
</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.academic-terms.update', $academicTerm) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-edit mr-2 text-primary"></i> Edit {{ $academicTerm->name ?? 'Academic Term' }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Code: <span class="font-mono">{{ $academicTerm->code ?? '—' }}</span>

                    @if($academicTerm->created_at)
                        · Created {{ $academicTerm->created_at->diffForHumans() }}
                    @else
                        · <span class="text-gray-400 italic">creation date unavailable</span>
                    @endif

                    @if($academicTerm->updated_at && $academicTerm->updated_at->ne($academicTerm->created_at))
                        · Updated {{ $academicTerm->updated_at->diffForHumans() }}
                    @endif
                </p>
            </div>

            <div class="p-6">
                @include('ktvtc.admin.academic-terms._form', ['term' => $academicTerm, 'mode' => 'edit'])
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between gap-3">
                <form action="{{ route('admin.academic-terms.destroy', $academicTerm) }}" method="POST"
                      onsubmit="return confirm('Delete this term? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 hover:bg-red-50 rounded-lg font-medium transition">
                        <i class="fas fa-trash mr-2"></i> Delete
                    </button>
                </form>

                <div class="flex gap-3">
                    <a href="{{ route('admin.academic-terms.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection