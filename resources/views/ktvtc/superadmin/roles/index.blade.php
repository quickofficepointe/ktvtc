@extends('ktvtc.superadmin.layout.superadminlayout')

@section('title', 'Roles')
@section('subtitle', 'Each role opens one area of the system; roles are fixed and assigned per user')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-5 overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">#</th><th class="py-2 px-3">Role</th><th class="py-2 px-3 text-right">Users</th><th class="py-2 px-3 text-right">Inactive</th><th></th></tr></thead>
        <tbody>
            @foreach($roles as $role)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-3 text-gray-400">{{ $role['id'] }}</td>
                    <td class="py-2 px-3 font-medium">{{ $role['name'] }}</td>
                    <td class="py-2 px-3 text-right">{{ $role['total'] }}</td>
                    <td class="py-2 px-3 text-right text-gray-500">{{ $role['inactive'] }}</td>
                    <td class="py-2 px-3 text-right"><a href="{{ route('super-admin.users.index', ['role' => $role['id']]) }}" class="text-sm text-blue-600 hover:underline">View users</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="text-xs text-gray-500 mt-3">To change someone's role, edit the user.</p>
</div>
@endsection
