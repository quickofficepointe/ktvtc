@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'System Information')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 max-w-3xl">
    <table class="w-full text-sm">
        @foreach($info as $key => $value)
            <tr class="border-b">
                <td class="py-2 text-gray-500 w-1/3">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                <td class="py-2 font-mono text-xs">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endsection
