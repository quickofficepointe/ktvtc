@extends('ktvtc.admin.layout.adminlayout')

@section('title', 'Create Academic Term')
@section('subtitle', 'Define a new term and its enrollment window')

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
        <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Create</span>
    </div>
</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.academic-terms.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 to-transparent">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-plus mr-2 text-primary"></i> New Academic Term
                </h3>
            </div>

            <div class="p-6">
                @include('ktvtc.admin.academic-terms._form', ['term' => null, 'mode' => 'create'])
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                <a href="{{ route('admin.academic-terms.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg font-semibold">
                    <i class="fas fa-check mr-2"></i> Create Term
                </button>
            </div>
        </div>
    </form>
</div>
@endsection